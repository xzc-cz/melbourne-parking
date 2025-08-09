<?php
/**
 * Data Sources Information Page
 * Shows all Melbourne parking data sources used in the project
 */

require_once('wp-config.php');
require_once('wp-includes/functions.php');
require_once('data/melbourne-parking-data.php');
require_once('data/parking-data-service.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sources - Melbourne Parking Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                <a class="nav-link" href="eco-travel.php">Eco Travel</a>
                <a class="nav-link active" href="data-sources.php">Data Sources</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-database text-primary"></i> Melbourne Parking Data Sources
                </h1>
                <p class="text-center text-muted mb-5">
                    Comprehensive overview of all real data sources used in this project
                </p>
            </div>
        </div>

        <!-- City of Melbourne Open Data Sources -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-city"></i> City of Melbourne Open Data Platform</h3>
                        <p class="mb-0">Official parking data from Melbourne City Council</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-sensor"></i> On-street Parking Bay Sensors</h5>
                                        <p><strong>Source:</strong> <a href="https://data.melbourne.vic.gov.au/explore/dataset/on-street-parking-bay-sensors/" target="_blank">City of Melbourne Open Data</a></p>
                                        <p><strong>Description:</strong> Real-time sensor data from parking bays across Melbourne CBD</p>
                                        <p><strong>Data Points:</strong> <?php echo count($parking_sensors); ?> sensors</p>
                                        <p><strong>Update Frequency:</strong> Real-time (every 5 minutes)</p>
                                        <p><strong>Coverage:</strong> Melbourne CBD parking zones</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-parking"></i> On-street Parking Bays</h5>
                                        <p><strong>Source:</strong> <a href="https://data.melbourne.vic.gov.au/explore/dataset/on-street-parking-bays/" target="_blank">City of Melbourne Open Data</a></p>
                                        <p><strong>Description:</strong> Complete inventory of parking bays with restrictions and rates</p>
                                        <p><strong>Data Points:</strong> <?php echo count($parking_bays); ?> bays</p>
                                        <p><strong>Update Frequency:</strong> Monthly</p>
                                        <p><strong>Coverage:</strong> All on-street parking in Melbourne</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-sign"></i> Sign Plates in Parking Zones</h5>
                                        <p><strong>Source:</strong> <a href="https://data.melbourne.vic.gov.au/explore/dataset/sign-plates-located-in-each-parking-zone/" target="_blank">City of Melbourne Open Data</a></p>
                                        <p><strong>Description:</strong> Parking restriction signs and their locations</p>
                                        <p><strong>Data Points:</strong> <?php echo count($parking_signs); ?> signs</p>
                                        <p><strong>Update Frequency:</strong> Quarterly</p>
                                        <p><strong>Coverage:</strong> All parking zones in Melbourne</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-map-marked-alt"></i> Parking Zones Linked to Street Segments</h5>
                                        <p><strong>Source:</strong> <a href="https://data.melbourne.vic.gov.au/explore/dataset/parking-zones-linked-to-street-segments/" target="_blank">City of Melbourne Open Data</a></p>
                                        <p><strong>Description:</strong> Geographic mapping of parking zones to street segments</p>
                                        <p><strong>Data Points:</strong> <?php echo count($parking_zones); ?> zone segments</p>
                                        <p><strong>Update Frequency:</strong> Quarterly</p>
                                        <p><strong>Coverage:</strong> All parking zones in Melbourne</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Data Statistics -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3><i class="fas fa-chart-line"></i> Real-time Data Statistics</h3>
                        <p class="mb-0">Current parking availability from Melbourne sensors</p>
                    </div>
                    <div class="card-body">
                        <?php 
                        $dataset = get_parking_real_time_dataset();
                        $real_time_data = $dataset['stats'];
                        ?>
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-sensor fa-3x text-primary mb-3"></i>
                                    <h4><?php echo $real_time_data['total']; ?></h4>
                                    <p>Total Sensors</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h4><?php echo $real_time_data['vacant']; ?></h4>
                                    <p>Available Spots</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                                    <h4><?php echo $real_time_data['occupied']; ?></h4>
                                    <p>Occupied Spots</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-percentage fa-3x text-info mb-3"></i>
                                    <h4><?php echo $real_time_data['availability_percentage']; ?>%</h4>
                                    <p>Availability Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone Statistics -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3><i class="fas fa-map"></i> Parking Zone Statistics</h3>
                        <p class="mb-0">Detailed statistics for each parking zone</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($zone_statistics as $zone_name => $stats): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $zone_name; ?></h5>
                                        <ul class="list-unstyled">
                                            <li><strong>Total Bays:</strong> <?php echo $stats['total_bays']; ?></li>
                                            <li><strong>Sensor Coverage:</strong> <?php echo $stats['sensor_coverage']; ?></li>
                                            <li><strong>Average Rate:</strong> <?php echo $stats['average_rate']; ?></li>
                                            <li><strong>Peak Hours:</strong> <?php echo $stats['peak_hours']; ?></li>
                                            <li><strong>Best Availability:</strong> <?php echo $stats['best_availability']; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Quality Information -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3><i class="fas fa-shield-alt"></i> Data Quality & Reliability</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Data Quality Metrics:</h5>
                                <ul>
                                    <li><strong>Source Reliability:</strong> Official City of Melbourne data</li>
                                    <li><strong>Update Frequency:</strong> Real-time to quarterly</li>
                                    <li><strong>Coverage:</strong> Complete Melbourne CBD</li>
                                    <li><strong>Accuracy:</strong> Sensor-based real-time data</li>
                                    <li><strong>Completeness:</strong> All major parking zones included</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Data Usage:</h5>
                                <ul>
                                    <li><strong>Real-time Availability:</strong> Live sensor data</li>
                                    <li><strong>Historical Analysis:</strong> Pattern recognition</li>
                                    <li><strong>Predictive Modeling:</strong> Future availability</li>
                                    <li><strong>Zone Management:</strong> Rate and restriction data</li>
                                    <li><strong>Navigation:</strong> Geographic coordinates</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h3><i class="fas fa-code"></i> API & Integration Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Data Access:</h5>
                                <ul>
                                    <li><strong>Format:</strong> JSON, CSV, XML</li>
                                    <li><strong>Authentication:</strong> Public access</li>
                                    <li><strong>Rate Limits:</strong> None specified</li>
                                    <li><strong>Documentation:</strong> Available on City of Melbourne website</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Integration Details:</h5>
                                <ul>
                                    <li><strong>Data Processing:</strong> PHP-based aggregation</li>
                                    <li><strong>Real-time Updates:</strong> 5-minute intervals</li>
                                    <li><strong>Error Handling:</strong> Fault-tolerant sensor data</li>
                                    <li><strong>Backup Data:</strong> Historical patterns</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 