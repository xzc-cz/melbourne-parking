# Melbourne Parking Management System

A WordPress-based parking management system for Melbourne, providing real-time parking information and eco-friendly travel solutions for commuters.

## Project Overview

This project provides comprehensive parking management solutions for Melbourne CBD:

- **Vehicle Analytics**: Vehicle ownership trends and population growth analysis
- **Real-time Parking**: Live parking availability and search functionality
- **Predictive Analytics**: Smart parking availability predictions
- **Historical Data**: Comprehensive parking pattern analysis
- **Environmental Impact**: CO2 emissions calculator and sustainability metrics
- **Green Solutions**: Eco-friendly parking options and public transport integration

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.1.3
- **Maps**: Leaflet.js
- **Charts**: Chart.js
- **Icons**: Font Awesome 6.0

## Installation Requirements

### System Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- Web server (Apache/Nginx)
- At least 100MB available disk space

### PHP Extension Requirements
- PDO
- PDO_MySQL
- cURL
- JSON
- mbstring

## Installation Steps

### 1. Environment Setup

#### Using XAMPP (Recommended)
1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Copy project files to `htdocs` directory

#### Using WAMP
1. Download and install [WAMP](https://www.wampserver.com/)
2. Start WAMP services
3. Copy project files to `www` directory

#### Using MAMP
1. Download and install [MAMP](https://www.mamp.info/)
2. Start MAMP services
3. Copy project files to `htdocs` directory

### 2. Database Setup

1. Open phpMyAdmin (usually http://localhost/phpmyadmin)
2. Create new database `melbourne_parking`
3. Select character set `utf8mb4_unicode_ci`

### 3. Configuration

1. Edit `wp-config.php` file
2. Update database connection information:
   ```php
   define( 'DB_NAME', 'melbourne_parking' );
   define( 'DB_USER', 'root' );
   define( 'DB_PASSWORD', '' ); // Fill in if you have a password
   define( 'DB_HOST', 'localhost:3307' );
   ```

### 4. File Permissions

Ensure the following directories are writable:
- `wp-content/`
- `wp-content/themes/`
- `wp-content/plugins/`

## Running the Project

### Local Access
1. Start web server and MySQL services
2. Access in browser: `http://localhost/melbourne-parking/`

### Project Structure
```
melbourne-parking/
├── index.php                 # Main entry file
├── wp-config.php            # Configuration file
├── wp-settings.php          # WordPress settings
├── wp-includes/             # Core files
│   └── functions.php        # Core functions
├── wp-content/              # Content directory
│   └── themes/              # Theme directory
│       └── melbourne-parking-theme/  # Custom theme
│           ├── index.php    # Theme homepage
│           ├── header.php   # Header template
│           └── footer.php   # Footer template
├── dashboard.php            # Data insights and analytics page
├── parking-map.php          # Real-time parking and predictions page
├── eco-travel.php           # Environmental impact and sustainability page
├── data-sources.php         # Data sources information page
├── data/                    # Data directory
│   └── melbourne-parking-data.php  # Comprehensive parking data
└── README.md               # Project documentation
```

## Features

### 1. Data Insights (dashboard.php)
- Vehicle ownership trends charts
- Melbourne population growth analysis
- CBD population density impact analysis
- Urban infrastructure impact assessment

### 2. Real-time Parking (parking-map.php)
- Interactive map displaying parking spots
- Real-time parking availability search
- Predictive parking availability charts
- Historical parking data analysis
- Parking spot navigation functionality

### 3. Eco Travel (eco-travel.php)
- CO2 emissions calculation for different travel modes
- Environmental impact comparison analysis
- Green parking options recommendations
- Environmental achievement statistics

## Data Sources

The project uses the following real data sources:

1. **Motor Vehicle Census Data**: Australian Bureau of Statistics (ABS)
   - Source: https://www.abs.gov.au/methodologies/motor-vehicle-census-australia-methodology/31-jan-2021
   - Used for: Vehicle ownership trends and analytics

2. **Regional Population Data**: Australian Bureau of Statistics (ABS)
   - Source: https://www.abs.gov.au/statistics/people/population/regional-population/2021/32180DS0001_2001-21.xlsx
   - Used for: Population growth analysis and urban planning

3. **On-street Parking Bay Sensors**: City of Melbourne Open Data
   - Source: https://data.melbourne.vic.gov.au/explore/dataset/on-street-parking-bay-sensors/information
   - Used for: Real-time parking data and availability

4. **On-street Parking Bays**: City of Melbourne Open Data
   - Source: https://data.melbourne.vic.gov.au/explore/dataset/on-street-parking-bays/information/
   - Used for: Parking location data and mapping

5. **Sign Plates in Parking Zones**: City of Melbourne Open Data
   - Source: https://data.melbourne.vic.gov.au/explore/dataset/sign-plates-located-in-each-parking-zone/table/
   - Used for: Parking zone information and restrictions

6. **Parking Zones Linked to Street Segments**: City of Melbourne Open Data
   - Source: https://data.melbourne.vic.gov.au/explore/dataset/parking-zones-linked-to-street-segments/table/
   - Used for: Zone mapping and geographic boundaries

7. **Melbourne LGA Boundaries**: ABS
   - Source: https://www.abs.gov.au/statistics/standards/australian-statistical-geography-standard-asgs-edition-3/jul2021-jun2026/access-and-downloads/digital-boundary-files
   - Used for: Geographic boundaries and spatial analysis

8. **Individual Carbon Footprint Calculation**: Kaggle Dataset
   - Source: https://www.kaggle.com/datasets/dumanmesut/individual-carbon-footprint-calculation
   - Used for: Environmental impact calculations and sustainability

9. **Public Transport Victoria (PTV) GTFS**: Open Data
   - Source: https://opendata.transport.vic.gov.au/dataset/gtfs-realtime
   - Used for: Public transport connections and green options

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Troubleshooting

### Common Issues

1. **Database Connection Failure**
   - Check if MySQL service is running
   - Verify database connection information
   - Confirm database has been created

2. **Blank Page Display**
   - Check PHP error logs
   - Confirm file permissions are correct
   - Verify PHP version requirements

3. **Map Not Displaying**
   - Check internet connection
   - Confirm Leaflet.js library loads successfully
   - Verify map container ID is correct

4. **Charts Not Displaying**
   - Check Chart.js library loading
   - Confirm Canvas elements exist
   - Verify data format is correct

### Debug Mode

Enable debugging in `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

## Development Notes

### Adding New Features
1. Add functionality code to appropriate page
2. Update navigation menu
3. Add necessary styles and scripts
4. Test feature completeness

### Customizing Theme
1. Modify files under `wp-content/themes/melbourne-parking-theme/`
2. Update CSS styles
3. Add new page templates

### Database Extension
1. Create new data tables
2. Update database connection class
3. Add data operation functions

## License

This project is for learning and demonstration purposes only.

## Contact

For questions or suggestions, please contact the development team.

---

**Note**: This is a demonstration project. For actual deployment, the following is required:
- Configure real data sources
- Add user authentication
- Implement data security measures
- Optimize performance
- Add error handling 