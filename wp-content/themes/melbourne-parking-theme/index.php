<?php
/**
 * 墨尔本停车管理系统主题 - 主页面
 */

get_header(); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="hero-section text-center py-5">
                <h1 class="display-4">Melbourne Parking Management System</h1>
                <p class="lead">Real-time parking information and eco-friendly travel solutions for Melbourne commuters</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Data Insights</h5>
                    <p class="card-text">View vehicle ownership trends and population growth data to understand urban congestion causes</p>
                    <a href="dashboard.php" class="btn btn-primary">View Data</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-map-marker-alt fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Real-time Parking</h5>
                    <p class="card-text">Find real-time available parking spots, view historical data and predictions</p>
                    <a href="parking-map.php" class="btn btn-success">Find Parking</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-leaf fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Eco Travel</h5>
                    <p class="card-text">Compare environmental impact of different travel modes, choose green parking options</p>
                    <a href="eco-travel.php" class="btn btn-info">Eco Travel</a>
                </div>
            </div>
        </div>
    </div>
    
    
</div>

<?php get_footer(); ?> 