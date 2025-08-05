<?php
/**
 * Real-time Parking Page - Melbourne CBD Parking Management
 * Real-time parking availability, predictions and historical data
 */

require_once('wp-config.php');
require_once('wp-includes/functions.php');
require_once('data/melbourne-parking-data.php');

// Get real-time availability from comprehensive data
$real_time_data = getRealTimeAvailability();

// Convert sensor data to parking spots for display
$parking_spots = [];
foreach ($parking_sensors as $sensor) {
    $parking_spots[] = [
        'id' => $sensor['sensor_id'],
        'name' => $sensor['street_name'] . ' Parking Bay',
        'lat' => $sensor['lat'],
        'lng' => $sensor['lng'],
        'available' => $sensor['status'] === 'vacant' ? 1 : 0,
        'total' => 1,
        'price' => getBayRate($sensor['bay_id']),
        'zone' => $sensor['zone'],
        'sensor_id' => $sensor['sensor_id'],
        'status' => $sensor['status'],
        'last_updated' => $sensor['last_updated']
    ];
}

// Helper function to get bay rate
function getBayRate($bay_id) {
    global $parking_bays;
    foreach ($parking_bays as $bay) {
        if ($bay['bay_id'] === $bay_id) {
            return $bay['rate'];
        }
    }
    return '$5.50/hour'; // Default rate
}

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
                            <input type="text" class="form-control" id="searchLocation" placeholder="Enter address or landmark">
                        </div>
                        <div class="col-md-3">
                            <label for="priceFilter" class="form-label">Price Range</label>
                            <select class="form-select" id="priceFilter">
                                <option value="">All Prices</option>
                                <option value="low">Under $4/hour</option>
                                <option value="medium">$4-$5/hour</option>
                                <option value="high">Over $5/hour</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="availabilityFilter" class="form-label">Availability</label>
                            <select class="form-select" id="availabilityFilter">
                                <option value="">All</option>
                                <option value="high">High (>50%)</option>
                                <option value="medium">Medium (20-50%)</option>
                                <option value="low">Low (<20%)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-primary w-100" onclick="filterParking()">
                                <i class="fas fa-search"></i> Search
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

        <!-- Parking Predictions -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3><i class="fas fa-chart-line"></i> Parking Availability Predictions</h3>
                        <p class="mb-0">Predictive analysis for optimal parking times</p>
                    </div>
                    <div class="card-body">
                        <canvas id="predictionChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <h5>Prediction Insights:</h5>
                            <ul>
                                <li><strong>Best time to park:</strong> Early morning (6-8 AM) or late evening (6-8 PM)</li>
                                <li><strong>Peak congestion:</strong> 9 AM - 5 PM weekdays</li>
                                <li><strong>Weekend availability:</strong> Generally higher than weekdays</li>
                                <li><strong>Weather impact:</strong> Rain increases parking demand by 15%</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historical Data Analysis -->
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="fas fa-history"></i> Historical Parking Patterns</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="historicalChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <h6>Pattern Analysis:</h6>
                            <ul>
                                <li>Morning rush: 6-9 AM (lowest availability)</li>
                                <li>Midday: 10 AM - 3 PM (moderate availability)</li>
                                <li>Evening: 4-7 PM (improving availability)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-secondary text-white">
                        <h4><i class="fas fa-clock"></i> Time-based Availability</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="timeChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <h6>Optimal Parking Times:</h6>
                            <ul>
                                <li><strong>Weekdays:</strong> Before 8 AM or after 6 PM</li>
                                <li><strong>Weekends:</strong> Anytime (higher availability)</li>
                                <li><strong>Public Holidays:</strong> Excellent availability</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parking Zones Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-info-circle"></i> Parking Zones Information - City of Melbourne Data</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($parking_zones as $zone => $info): ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?php echo $zone; ?></h5>
                                        <p class="card-text">
                                            <strong>Rate:</strong> <?php echo $info['rate']; ?><br>
                                            <strong>Max Time:</strong> <?php echo $info['max_time']; ?><br>
                                            <strong>Total Bays:</strong> <?php echo $info['total_bays']; ?><br>
                                            <strong>Sensor Coverage:</strong> <?php echo $info['sensor_coverage']; ?><br>
                                            <strong>Peak Hours:</strong> <?php echo $info['peak_hours']; ?><br>
                                            <strong>Best Availability:</strong> <?php echo $info['best_availability']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([-37.8136, 144.9631], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add parking spots to map
        const parkingData = <?php echo json_encode($parking_spots); ?>;
        
        parkingData.forEach(spot => {
            let color = 'red'; // Default to occupied
            let statusText = 'Occupied';
            
            if (spot.status === 'vacant') {
                color = 'green';
                statusText = 'Available';
            } else if (spot.status === 'fault') {
                color = 'orange';
                statusText = 'Sensor Fault';
            }
            
            const marker = L.marker([spot.lat, spot.lng])
                .addTo(map)
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
            
            marker.setIcon(L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color: ${color}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            }));
        });

        // Prediction Chart
        const predictionCtx = document.getElementById('predictionChart').getContext('2d');
        new Chart(predictionCtx, {
            type: 'line',
            data: {
                labels: ['6AM', '8AM', '10AM', '12PM', '2PM', '4PM', '6PM', '8PM'],
                datasets: [{
                    label: 'Predicted Availability (%)',
                    data: [85, 25, 15, 20, 30, 45, 70, 80],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
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

        // Historical Chart
        const historicalCtx = document.getElementById('historicalChart').getContext('2d');
        new Chart(historicalCtx, {
            type: 'bar',
            data: {
                labels: ['6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM'],
                datasets: [{
                    label: 'Morning',
                    data: <?php echo json_encode($historical_data['morning']); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)'
                }, {
                    label: 'Afternoon',
                    data: <?php echo json_encode($historical_data['afternoon']); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)'
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

        // Time-based Chart
        const timeCtx = document.getElementById('timeChart').getContext('2d');
        new Chart(timeCtx, {
            type: 'doughnut',
            data: {
                labels: ['High Availability', 'Medium Availability', 'Low Availability'],
                datasets: [{
                    data: [40, 35, 25],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Filter function
        function filterParking() {
            const location = document.getElementById('searchLocation').value;
            const price = document.getElementById('priceFilter').value;
            const availability = document.getElementById('availabilityFilter').value;
            
            // Implement filtering logic here
            console.log('Filtering:', { location, price, availability });
            
            // For now, just show an alert
            alert('Filtering functionality would be implemented here with real API calls to City of Melbourne data.');
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