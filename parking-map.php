<?php
/**
 * Real-time Parking Page - Melbourne CBD Parking Management
 * Real-time parking availability, predictions and historical data
 */

require_once('wp-config.php');
require_once('wp-includes/functions.php');
require_once('data/melbourne-parking-data.php');
require_once('data/parking-data-service.php');

// Get real-time availability via API with fallback
$dataset = get_parking_real_time_dataset();
$real_time_data = $dataset['stats'];
$parking_spots = $dataset['spots'];

// Historical parking availability data from comprehensive dataset
$historical_data = [
    'morning' => array_values($historical_patterns['weekday_morning']),
    'afternoon' => array_values($historical_patterns['weekday_afternoon']),
    'evening' => array_values($historical_patterns['weekday_evening'])
];

// Parking zones data from comprehensive dataset
$parking_zones = [];
foreach ($zone_statistics as $zone_name => $stats) {
    $parking_zones[$zone_name] = [
        'rate' => $stats['average_rate'],
        'max_time' => '2-4 hours',
        'total_bays' => $stats['total_bays'],
        'sensor_coverage' => $stats['sensor_coverage'],
        'peak_hours' => $stats['peak_hours'],
        'best_availability' => $stats['best_availability']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time Parking - Melbourne Parking Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #map {
            height: 500px;
            width: 100%;
            border-radius: 10px;
        }
        .parking-card {
            transition: transform 0.2s;
        }
        .parking-card:hover {
            transform: translateY(-2px);
        }
        .availability-high { color: #28a745; }
        .availability-medium { color: #ffc107; }
        .availability-low { color: #dc3545; }
        .search-box {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Melbourne Parking System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="dashboard.php">Data Insights</a>
                <a class="nav-link active" href="parking-map.php">Real-time Parking</a>
                <a class="nav-link" href="eco-travel.php">Eco Travel</a>
                <a class="nav-link" href="data-sources.php">Data Sources</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-map-marker-alt text-success"></i> Real-time Parking Map
                </h1>
                <p class="text-center text-muted mb-5">
                    Find available parking spots in Melbourne CBD with real-time data and predictions
                </p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="search-box">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="searchLocation" class="form-label">Search Location</label>
                            <input type="text" class="form-control" id="searchLocation" placeholder="Enter address (e.g., 200 Queen St)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-outline-secondary w-100" type="button" onclick="locateAddress()">
                                <i class="fas fa-location-dot"></i> Go
                            </button>
                        </div>
                        <div class="col-md-3"></div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-primary w-100" onclick="filterParking(true)">
                                <i class="fas fa-filter"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Parking Map -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3><i class="fas fa-map"></i> Interactive Parking Map</h3>
                        <p class="mb-0">Real-time parking availability across Melbourne CBD using City of Melbourne Open Data</p>
                    </div>
                    <div class="card-body">
                        <!-- Real-time Statistics -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="fas fa-sensor fa-2x text-primary mb-2"></i>
                                    <h5><?php echo $real_time_data['total']; ?></h5>
                                    <small>Total Sensors</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <h5><?php echo $real_time_data['vacant']; ?></h5>
                                    <small>Available Spots</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                    <h5><?php echo $real_time_data['occupied']; ?></h5>
                                    <small>Occupied Spots</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                                    <h5><?php echo $real_time_data['availability_percentage']; ?>%</h5>
                                    <small>Availability Rate</small>
                                </div>
                            </div>
                        </div>
                        
                        <div id="map"></div>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-circle text-success"></i> Available (Vacant)
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-circle text-danger"></i> Occupied
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-circle text-warning"></i> Sensor Fault
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- Parking Predictions (street-specific) -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3><i class="fas fa-chart-line"></i> Today's Parking Availability Predictions — <span id="predictionStreet"></span></h3>
                        <p class="mb-0">Click a marker on the map to select a Melbourne CBD street</p>
                    </div>
                    <div class="card-body">
                        <canvas id="predictionChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historical Data Analysis -->
        
        <div class="row mb-5">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="fas fa-history"></i> Historical Parking Patterns — <span id="streetNameHistorical"></span></h4>
                    </div>
                    <div class="card-body">
                        <canvas id="historicalChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([-37.8136, 144.9631], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Cluster group for massive datasets
        const clusterGroup = L.markerClusterGroup({
            showCoverageOnHover: false,
            disableClusteringAtZoom: 16,
            maxClusterRadius: 60,
        });

        // Add parking spots to map
        const parkingData = <?php echo json_encode($parking_spots); ?>;

        function createMarker(spot) {
            let color = 'red';
            let statusText = 'Occupied';
            if (spot.status === 'fault') { color = 'orange'; statusText = 'Sensor Fault'; }
            else if (spot.available) { color = 'green'; statusText = 'Available'; }

            const marker = L.marker([spot.lat, spot.lng])
                .bindPopup(`
                    <strong>${spot.name}</strong><br>
                    Status: ${statusText}<br>
                    Price: ${spot.price}<br>
                    Zone: ${spot.zone}<br>
                    Last Updated: ${spot.last_updated}<br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="navigateToParking(${spot.lat}, ${spot.lng})">
                        Navigate
                    </button>
                `);
            marker.on('click', () => {
                const street = streetFromSpot(spot);
                selectStreetFromMap(street);
            });
            marker.setIcon(L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color: ${color}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            }));
            return marker;
        }

        parkingData.forEach(spot => {
            if (spot.lat && spot.lng) {
                clusterGroup.addLayer(createMarker(spot));
            }
        });
        map.addLayer(clusterGroup);

        // Prediction Chart (area-specific)
        const predictionCtx = document.getElementById('predictionChart').getContext('2d');
        const predictionChart = new Chart(predictionCtx, {
            type: 'line',
            data: {
                labels: ['6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM'],
                datasets: [{
                    label: 'Predicted Availability (%)',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Availability (%)'
                        }
                    }
                }
            }
        });

        // Historical Chart (street-specific) - single series by hour
        const historicalCtx = document.getElementById('historicalChart').getContext('2d');
        const historicalChart = new Chart(historicalCtx, {
            type: 'line',
            data: {
                labels: ['6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM'],
                datasets: [{
                    label: 'Historical Availability (%)',
                    data: [],
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Availability (%)'
                        }
                    }
                }
            }
        });

        // Helpers
        function streetFromSpot(s) {
            const nm = String(s.name || '');
            return nm.replace(/\s*Parking Bay$/i, '').trim();
        }

        function computeStreetStats(streetKeyword = '') {
            const spots = parkingData;
            let available = 0, occupied = 0, fault = 0, total = 0;
            spots.forEach(s => {
                const street = streetFromSpot(s).toLowerCase();
                const matches = !streetKeyword || street.includes(streetKeyword);
                if (matches) {
                    total++;
                    if (s.status === 'fault') fault++;
                    else if (s.available) available++; else occupied++;
                }
            });
            return { available, occupied, fault, total };
        }

        // Choose curve patterns based on status composition
        // Returns {predMultipliers: number[], histSeries: number[]}
        function choosePatterns(stats) {
            const total = stats.total || 1;
            const ar = stats.available / total; // availability ratio
            const or = stats.occupied / total;  // occupied ratio
            const fr = stats.fault / total;     // fault ratio

            // Mode A: Availability-high (ar >= 0.6)
            const modeA_pred = [0.95,0.9,0.85,0.8,0.78,0.8,0.82,0.85,0.88,0.9,0.93,0.95,0.97,0.95,0.92];
            const modeA_hist = [88,82,75,68,60,62,65,70,75,80];

            // Mode B: Occupied-high (or >= 0.6)
            const modeB_pred = [0.7,0.55,0.4,0.35,0.3,0.35,0.4,0.45,0.5,0.55,0.6,0.65,0.7,0.68,0.65];
            const modeB_hist = [40,35,30,28,25,27,30,35,40,45];

            // Mode C: Fault-noticeable (fr >= 0.1)
            // Flatter curve with uncertainty penalty
            const modeC_pred = [0.6,0.58,0.56,0.55,0.55,0.56,0.58,0.6,0.6,0.62,0.62,0.6,0.6,0.58,0.56];
            const modeC_hist = [55,54,53,52,52,53,54,55,56,55];

            if (fr >= 0.1) {
                return { predMultipliers: modeC_pred, histSeries: modeC_hist };
            }
            if (or >= 0.6) {
                return { predMultipliers: modeB_pred, histSeries: modeB_hist };
            }
            if (ar >= 0.6) {
                return { predMultipliers: modeA_pred, histSeries: modeA_hist };
            }
            // Mixed: interpolate between A and B by availability ratio
            const w = Math.max(0, Math.min(1, (ar - 0.3) / 0.3));
            const mix = (a,b)=>a.map((v,i)=> Math.round((w*v + (1-w)*b[i]) * 100)/100);
            return { predMultipliers: mix(modeA_pred, modeB_pred), histSeries: mix(modeA_hist, modeB_hist) };
        }

        function updatePredictionCharts(streetKwRaw = '') {
            const streetKw = (streetKwRaw || '').trim().toLowerCase();
            document.getElementById('predictionStreet').textContent = streetKw || 'All CBD Streets';

            const stats = computeStreetStats(streetKw);
            const patterns = choosePatterns(stats);

            // Compute simple predicted availability curve for the selected area
            // Heuristic: base on current ratio and selected pattern multipliers
            const labels = predictionChart.data.labels;
            const base = stats.total > 0 ? Math.round((stats.available / stats.total) * 100) : 0;
            const multipliers = patterns.predMultipliers;
            predictionChart.data.datasets[0].data = labels.map((_, i) => Math.max(0, Math.min(100, Math.round(base * multipliers[i]))));
            predictionChart.update();
        }

        function updateHistoricalChart(streetKwRaw = '') {
            const streetKw = (streetKwRaw || '').trim().toLowerCase();
            document.getElementById('streetNameHistorical').textContent = streetKw || 'All CBD Streets';

            // Street-specific historical pattern using selected mode
            const base = computeStreetStats(streetKw);
            const { histSeries } = choosePatterns(base);
            historicalChart.data.datasets[0].data = histSeries;
            historicalChart.update();
        }

        function selectStreetFromMap(street) {
            updatePredictionCharts(street);
            updateHistoricalChart(street);
        }

        updatePredictionCharts('');
        updateHistoricalChart('');

        // Filter function
        async function filterParking(forceSensors = false) {
            const mode = 'sensors';
            const params = new URLSearchParams({ mode });
            try {
                const resp = await fetch('api/parking-search.php?' + params.toString(), { cache: 'no-store' });
                if (!resp.ok) throw new Error('Network error');
                const data = await resp.json();
                updateMap(data.spots);
                updateStats(data.stats);
            } catch (e) {
                console.error(e);
                alert('Failed to fetch filtered data.');
            }
        }

        // Geocode using Nominatim (OpenStreetMap) - client side
        async function locateAddress() {
            const q = document.getElementById('searchLocation').value.trim();
            if (!q) {
                alert('请输入地址');
                return;
            }
            try {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q + ', Melbourne, VIC');
                const resp = await fetch(url, { headers: { 'Accept-Language': 'en' } });
                if (!resp.ok) throw new Error('geocode failed');
                const arr = await resp.json();
                if (!Array.isArray(arr) || arr.length === 0) {
                    alert('未找到该地址');
                    return;
                }
                const lat = parseFloat(arr[0].lat);
                const lon = parseFloat(arr[0].lon);
                map.setView([lat, lon], 17);
            } catch (err) {
                console.error(err);
                alert('地址解析失败，请重试');
            }
        }

        function clearMarkers() {
            clusterGroup.clearLayers();
        }

        function updateMap(spots) {
            clearMarkers();
            spots.forEach(spot => {
                if (spot.lat && spot.lng) {
                    clusterGroup.addLayer(createMarker(spot));
                }
            });
        }

        function updateStats(stats) {
            const containers = document.querySelectorAll('.card-body .row.mb-3 .col-md-3 h5');
            if (containers.length >= 4) {
                containers[0].textContent = stats.total;
                containers[1].textContent = stats.vacant;
                containers[2].textContent = stats.occupied;
                containers[3].textContent = stats.availability_percentage + '%';
            }
        }

        // Navigation function
        function navigateToParking(lat, lng) {
            // Open Google Maps with directions
            const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html> 