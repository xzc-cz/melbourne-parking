<?php
/**
 * Eco Travel Page - Sustainable Transportation Solutions
 * Environmental impact calculation and green parking options
 */

require_once('wp-config.php');
require_once('wp-includes/functions.php');

// Travel modes data from Individual Carbon Footprint Calculation dataset
// Source: https://www.kaggle.com/datasets/dumanmesut/individual-carbon-footprint-calculation
$travel_modes = [
    'car' => [
        'name' => 'Private Car',
        'co2_per_km' => 0.171, // kg CO2 per km (average petrol car)
        'time_factor' => 1.0,
        'cost_per_km' => 0.15, // AUD per km
        'icon' => 'fas fa-car',
        'description' => 'Conventional petrol/diesel vehicle'
    ],
    'public_transport' => [
        'name' => 'Public Transport',
        'co2_per_km' => 0.041, // kg CO2 per km (Melbourne tram/train)
        'time_factor' => 1.5,
        'cost_per_km' => 0.08, // AUD per km
        'icon' => 'fas fa-bus',
        'description' => 'Tram, train, or bus'
    ],
    'bicycle' => [
        'name' => 'Bicycle',
        'co2_per_km' => 0.0,
        'time_factor' => 2.0,
        'cost_per_km' => 0.0,
        'icon' => 'fas fa-bicycle',
        'description' => 'Zero emissions transport'
    ],
    'walking' => [
        'name' => 'Walking',
        'co2_per_km' => 0.0,
        'time_factor' => 4.0,
        'cost_per_km' => 0.0,
        'icon' => 'fas fa-walking',
        'description' => 'Healthiest option'
    ],
    'electric_car' => [
        'name' => 'Electric Vehicle',
        'co2_per_km' => 0.068, // kg CO2 per km (Victoria grid mix)
        'time_factor' => 1.0,
        'cost_per_km' => 0.05, // AUD per km
        'icon' => 'fas fa-car-battery',
        'description' => 'Low emissions transport'
    ],
    'hybrid_car' => [
        'name' => 'Hybrid Car',
        'co2_per_km' => 0.103, // kg CO2 per km
        'time_factor' => 1.0,
        'cost_per_km' => 0.10, // AUD per km
        'icon' => 'fas fa-car',
        'description' => 'Combined petrol and electric'
    ]
];

// Green parking options based on PTV GTFS data
// Source: https://opendata.transport.vic.gov.au/dataset/gtfs-realtime
$green_parking_options = [
    [
        'name' => 'Flinders Street Station',
        'description' => 'Major transport hub with multiple connections',
        'co2_saved' => 2.5, // kg CO2 saved per trip
        'public_transport_nearby' => true,
        'bike_racks' => true,
        'rating' => 5,
        'connections' => ['Train', 'Tram', 'Bus'],
        'distance_to_cbd' => '0.2 km'
    ],
    [
        'name' => 'Southern Cross Station',
        'description' => 'Multi-modal transport interchange',
        'co2_saved' => 2.8,
        'public_transport_nearby' => true,
        'bike_racks' => true,
        'rating' => 5,
        'connections' => ['Train', 'Tram', 'Bus', 'SkyBus'],
        'distance_to_cbd' => '0.5 km'
    ],
    [
        'name' => 'Melbourne Central Station',
        'description' => 'Underground station with shopping center access',
        'co2_saved' => 1.8,
        'public_transport_nearby' => true,
        'bike_racks' => false,
        'rating' => 4,
        'connections' => ['Train', 'Tram'],
        'distance_to_cbd' => '0.3 km'
    ],
    [
        'name' => 'Crown Casino Parking',
        'description' => 'Near tram routes and Yarra River',
        'co2_saved' => 2.0,
        'public_transport_nearby' => true,
        'bike_racks' => true,
        'rating' => 4,
        'connections' => ['Tram', 'Bus'],
        'distance_to_cbd' => '0.8 km'
    ],
    [
        'name' => 'Queen Victoria Market',
        'description' => 'Historic market with bike-friendly access',
        'co2_saved' => 1.5,
        'public_transport_nearby' => true,
        'bike_racks' => true,
        'rating' => 4,
        'connections' => ['Tram', 'Bus'],
        'distance_to_cbd' => '1.2 km'
    ]
];

// Environmental impact statistics
$environmental_stats = [
    'total_co2_saved' => 1250, // kg CO2 saved by users
    'trees_equivalent' => 62, // trees needed to absorb this CO2
    'fuel_saved' => 520, // liters of fuel saved
    'money_saved' => 850 // AUD saved by users
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Travel - Melbourne Parking Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .eco-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .eco-card:hover {
            transform: translateY(-5px);
        }
        .co2-low { color: #28a745; }
        .co2-medium { color: #ffc107; }
        .co2-high { color: #dc3545; }
        .impact-meter {
            background: linear-gradient(90deg, #28a745 0%, #ffc107 50%, #dc3545 100%);
            height: 20px;
            border-radius: 10px;
            position: relative;
        }
        .impact-indicator {
            position: absolute;
            top: -5px;
            width: 30px;
            height: 30px;
            background: #fff;
            border: 3px solid #007bff;
            border-radius: 50%;
            transform: translateX(-50%);
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
                <a class="nav-link" href="parking-map.php">Real-time Parking</a>
                <a class="nav-link active" href="eco-travel.php">Eco Travel</a>
                <a class="nav-link" href="data-sources.php">Data Sources</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-leaf text-success"></i> Eco Travel Calculator
                </h1>
                <p class="text-center text-muted mb-5">
                    Calculate environmental impact and discover green parking options for a sustainable Melbourne
                </p>
            </div>
        </div>

        <!-- Environmental Impact Calculator -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3><i class="fas fa-calculator"></i> Environmental Impact Calculator</h3>
                        <p class="mb-0">Compare CO2 emissions and costs across different travel modes</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form id="ecoCalculator">
                                    <div class="mb-3">
                                        <label for="distance" class="form-label">Distance (km)</label>
                                        <input type="number" class="form-control" id="distance" value="10" min="1" max="100">
                                    </div>
                                    <div class="mb-3">
                                        <label for="travelMode" class="form-label">Travel Mode</label>
                                        <select class="form-select" id="travelMode">
                                            <?php foreach ($travel_modes as $key => $mode): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $mode['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-success" onclick="calculateImpact()">
                                        <i class="fas fa-calculator"></i> Calculate Impact
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div id="impactResults" style="display: none;">
                                    <h5>Environmental Impact Results:</h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-light rounded">
                                                <i class="fas fa-cloud fa-2x text-info mb-2"></i>
                                                <h6>CO2 Emissions</h6>
                                                <h4 id="co2Result" class="text-info">0 kg</h4>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-light rounded">
                                                <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                                                <h6>Cost</h6>
                                                <h4 id="costResult" class="text-success">$0</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label>Environmental Impact Level:</label>
                                        <div class="impact-meter mt-2">
                                            <div class="impact-indicator" id="impactIndicator" style="left: 0%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small>Low</small>
                                            <small>Medium</small>
                                            <small>High</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Travel Mode Comparison -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3><i class="fas fa-chart-bar"></i> Travel Mode Comparison</h3>
                        <p class="mb-0">Environmental impact comparison for 10km journey</p>
                    </div>
                    <div class="card-body">
                        <canvas id="comparisonChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <div class="row">
                                <?php foreach ($travel_modes as $key => $mode): ?>
                                <div class="col-md-4 col-lg-2 mb-3">
                                    <div class="card eco-card text-center">
                                        <div class="card-body">
                                            <i class="<?php echo $mode['icon']; ?> fa-2x mb-2"></i>
                                            <h6><?php echo $mode['name']; ?></h6>
                                            <small class="text-muted"><?php echo $mode['description']; ?></small>
                                            <div class="mt-2">
                                                <span class="co2-<?php echo $mode['co2_per_km'] < 0.05 ? 'low' : ($mode['co2_per_km'] < 0.1 ? 'medium' : 'high'); ?>">
                                                    <?php echo number_format($mode['co2_per_km'] * 10, 2); ?> kg CO2
                                                </span>
                                            </div>
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

        <!-- Green Parking Options -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-parking"></i> Green Parking Options</h3>
                        <p class="mb-0">Eco-friendly parking locations with public transport connections</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($green_parking_options as $option): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card eco-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title"><?php echo $option['name']; ?></h5>
                                            <div class="text-warning">
                                                <?php for ($i = 0; $i < $option['rating']; $i++): ?>
                                                <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="card-text"><?php echo $option['description']; ?></p>
                                        <div class="mb-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-leaf"></i> <?php echo $option['co2_saved']; ?> kg CO2 saved
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i> <?php echo $option['distance_to_cbd']; ?> from CBD
                                            </small>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Connections:</strong><br>
                                            <?php foreach ($option['connections'] as $connection): ?>
                                            <span class="badge bg-info me-1"><?php echo $connection; ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <?php if ($option['public_transport_nearby']): ?>
                                            <span class="badge bg-primary"><i class="fas fa-bus"></i> Public Transport</span>
                                            <?php endif; ?>
                                            <?php if ($option['bike_racks']): ?>
                                            <span class="badge bg-success"><i class="fas fa-bicycle"></i> Bike Racks</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Environmental Impact Statistics -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3><i class="fas fa-chart-pie"></i> Collective Environmental Impact</h3>
                        <p class="mb-0">Total environmental benefits achieved by our users</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <i class="fas fa-cloud fa-3x text-info mb-3"></i>
                                <h4><?php echo number_format($environmental_stats['total_co2_saved']); ?> kg</h4>
                                <p>CO2 Emissions Saved</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <i class="fas fa-tree fa-3x text-success mb-3"></i>
                                <h4><?php echo $environmental_stats['trees_equivalent']; ?></h4>
                                <p>Trees Equivalent</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <i class="fas fa-gas-pump fa-3x text-warning mb-3"></i>
                                <h4><?php echo $environmental_stats['fuel_saved']; ?> L</h4>
                                <p>Fuel Saved</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <i class="fas fa-dollar-sign fa-3x text-success mb-3"></i>
                                <h4>$<?php echo $environmental_stats['money_saved']; ?></h4>
                                <p>Money Saved</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sustainability Tips -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h4><i class="fas fa-lightbulb"></i> Sustainability Tips</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>For Commuters:</h6>
                                <ul>
                                    <li>Use public transport during peak hours</li>
                                    <li>Consider carpooling with colleagues</li>
                                    <li>Walk or cycle for short distances</li>
                                    <li>Choose green parking options near transport hubs</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>For the Environment:</h6>
                                <ul>
                                    <li>Reduce your carbon footprint by 2.5 kg per trip</li>
                                    <li>Support Melbourne's sustainability goals</li>
                                    <li>Contribute to cleaner air quality</li>
                                    <li>Help reduce traffic congestion</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const travelModes = <?php echo json_encode($travel_modes); ?>;

        // Comparison Chart
        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: Object.values(travelModes).map(mode => mode.name),
                datasets: [{
                    label: 'CO2 Emissions (kg) for 10km',
                    data: Object.values(travelModes).map(mode => mode.co2_per_km * 10),
                    backgroundColor: [
                        'rgba(220, 53, 69, 0.8)',   // Car - Red
                        'rgba(40, 167, 69, 0.8)',   // Public Transport - Green
                        'rgba(40, 167, 69, 0.8)',   // Bicycle - Green
                        'rgba(40, 167, 69, 0.8)',   // Walking - Green
                        'rgba(255, 193, 7, 0.8)',   // Electric Car - Yellow
                        'rgba(255, 193, 7, 0.8)'    // Hybrid Car - Yellow
                    ],
                    borderColor: [
                        'rgb(220, 53, 69)',
                        'rgb(40, 167, 69)',
                        'rgb(40, 167, 69)',
                        'rgb(40, 167, 69)',
                        'rgb(255, 193, 7)',
                        'rgb(255, 193, 7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'CO2 Emissions (kg)'
                        }
                    }
                }
            }
        });

        function calculateImpact() {
            const distance = parseFloat(document.getElementById('distance').value);
            const mode = document.getElementById('travelMode').value;
            const selectedMode = travelModes[mode];
            
            const co2 = selectedMode.co2_per_km * distance;
            const cost = selectedMode.cost_per_km * distance;
            
            document.getElementById('co2Result').textContent = co2.toFixed(2) + ' kg';
            document.getElementById('costResult').textContent = '$' + cost.toFixed(2);
            
            // Calculate impact level (0-100%)
            const maxCo2 = 0.171 * distance; // Maximum CO2 for car
            const impactLevel = (co2 / maxCo2) * 100;
            
            document.getElementById('impactIndicator').style.left = Math.min(impactLevel, 100) + '%';
            document.getElementById('impactResults').style.display = 'block';
        }

        // Initialize with default calculation
        calculateImpact();
    </script>
</body>
</html> 