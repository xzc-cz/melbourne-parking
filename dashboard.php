<?php
/**
 * Data Insights Page - Melbourne Parking Analytics
 * Vehicle ownership trends and population growth data
 */

require_once('wp-config.php');
require_once('wp-includes/functions.php');

// Real data from report.txt - Motor Vehicle Census and ABS Regional Population
$vehicle_data = [
    ['year' => '2019', 'registrations' => 19700000], // Australia total
    ['year' => '2020', 'registrations' => 20100000],
    ['year' => '2021', 'registrations' => 20500000],
    ['year' => '2022', 'registrations' => 20900000],
    ['year' => '2023', 'registrations' => 21300000]
];

// Melbourne population data from ABS
$population_data = [
    ['year' => '2019', 'population' => 5078000], // Greater Melbourne
    ['year' => '2020', 'population' => 5129000],
    ['year' => '2021', 'population' => 5181000],
    ['year' => '2022', 'population' => 5233000],
    ['year' => '2023', 'population' => 5285000]
];

// Melbourne CBD population data
$cbd_population_data = [
    ['year' => '2019', 'cbd_population' => 45000], // City of Melbourne LGA
    ['year' => '2020', 'cbd_population' => 46000],
    ['year' => '2021', 'cbd_population' => 47000],
    ['year' => '2022', 'cbd_population' => 48000],
    ['year' => '2023', 'cbd_population' => 49000]
];

// Parking search time data from report
$parking_search_data = [
    ['year' => '2019', 'hours' => 17],
    ['year' => '2020', 'hours' => 18],
    ['year' => '2021', 'hours' => 19],
    ['year' => '2022', 'hours' => 20],
    ['year' => '2023', 'hours' => 21]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Insights - Melbourne Parking Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Melbourne Parking System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link active" href="dashboard.php">Data Insights</a>
                <a class="nav-link" href="parking-map.php">Real-time Parking</a>
                <a class="nav-link" href="eco-travel.php">Eco Travel</a>
                <a class="nav-link" href="data-sources.php">Data Sources</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-chart-line text-primary"></i> Data Insights Dashboard
                </h1>
                <p class="text-center text-muted mb-5">
                    Understanding Melbourne's vehicle ownership trends and population growth impact on urban congestion
                </p>
            </div>
        </div>

        <!-- Vehicle Ownership Trends -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-car"></i> Vehicle Ownership Trends</h3>
                        <p class="mb-0">Australia's motor vehicle registrations growth trend over 5 years</p>
                    </div>
                    <div class="card-body">
                        <canvas id="vehicleChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <h5>Key Insights:</h5>
                            <ul>
                                <li>Vehicle registrations increased by <strong>8.1%</strong> over 5 years</li>
                                <li>Reached <strong>21.3 million</strong> registered vehicles in 2023</li>
                                <li>Average annual growth rate of <strong>1.6%</strong></li>
                                <li>Higher vehicle density leads to increased parking demand</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Population Growth Data -->
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h4><i class="fas fa-users"></i> Greater Melbourne Population</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="populationChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <h6>Population Growth:</h6>
                            <ul>
                                <li>Greater Melbourne population: <strong>5.285 million</strong> (2023)</li>
                                <li>Annual growth rate: <strong>1.0%</strong></li>
                                <li>Increased by <strong>207,000</strong> people since 2019</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h4><i class="fas fa-city"></i> Melbourne CBD Population</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="cbdChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <h6>CBD Density Impact:</h6>
                            <ul>
                                <li>CBD population: <strong>49,000</strong> (2023)</li>
                                <li>High density area with limited parking</li>
                                <li>Increased congestion during peak hours</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parking Search Time Impact -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3><i class="fas fa-clock"></i> Parking Search Time Impact</h3>
                        <p class="mb-0">Average hours spent searching for parking annually</p>
                    </div>
                    <div class="card-body">
                        <canvas id="searchTimeChart" width="400" height="200"></canvas>
                        <div class="mt-3">
                            <h5>Problem Statement:</h5>
                            <p>Melbourne commuters spend an average of <strong>21 hours per year</strong> searching for parking spaces, 
                            contributing to congestion, pollution, and wasted time. This inefficiency stems from the absence of 
                            real-time data on available parking spaces.</p>
                            
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle"></i> Impact on Commuters:</h6>
                                <ul>
                                    <li>Increased fuel consumption and costs</li>
                                    <li>Higher stress levels and frustration</li>
                                    <li>Reduced productivity and time waste</li>
                                    <li>Contribution to urban air pollution</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Sources -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h4><i class="fas fa-database"></i> Data Sources</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Official Data Sources:</h6>
                                <ul>
                                    <li><strong>Motor Vehicle Census, Australia 2021</strong> - ABS</li>
                                    <li><strong>Regional Population 2021</strong> - Australian Bureau of Statistics</li>
                                    <li><strong>On-street Parking Bay Sensors</strong> - City of Melbourne</li>
                                    <li><strong>On-street Parking Bays</strong> - City of Melbourne</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Additional Sources:</h6>
                                <ul>
                                    <li><strong>Sign Plates in Parking Zones</strong> - City of Melbourne</li>
                                    <li><strong>Parking Zones Linked to Street Segments</strong> - City of Melbourne</li>
                                    <li><strong>Melbourne LGA Boundaries</strong> - ABS</li>
                                    <li><strong>Individual Carbon Footprint Calculation</strong> - Kaggle Dataset</li>
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
        // Vehicle Ownership Chart
        const vehicleCtx = document.getElementById('vehicleChart').getContext('2d');
        new Chart(vehicleCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($vehicle_data, 'year')); ?>,
                datasets: [{
                    label: 'Vehicle Registrations (millions)',
                    data: <?php echo json_encode(array_column($vehicle_data, 'registrations')); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Registrations (millions)'
                        }
                    }
                }
            }
        });

        // Population Chart
        const populationCtx = document.getElementById('populationChart').getContext('2d');
        new Chart(populationCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($population_data, 'year')); ?>,
                datasets: [{
                    label: 'Greater Melbourne Population (millions)',
                    data: <?php echo json_encode(array_column($population_data, 'population')); ?>,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Population (millions)'
                        }
                    }
                }
            }
        });

        // CBD Population Chart
        const cbdCtx = document.getElementById('cbdChart').getContext('2d');
        new Chart(cbdCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($cbd_population_data, 'year')); ?>,
                datasets: [{
                    label: 'Melbourne CBD Population (thousands)',
                    data: <?php echo json_encode(array_column($cbd_population_data, 'cbd_population')); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Population (thousands)'
                        }
                    }
                }
            }
        });

        // Parking Search Time Chart
        const searchTimeCtx = document.getElementById('searchTimeChart').getContext('2d');
        new Chart(searchTimeCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($parking_search_data, 'year')); ?>,
                datasets: [{
                    label: 'Hours Spent Searching for Parking',
                    data: <?php echo json_encode(array_column($parking_search_data, 'hours')); ?>,
                    borderColor: 'rgb(255, 159, 64)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Hours per Year'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 