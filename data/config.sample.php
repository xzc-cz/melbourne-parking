<?php
/**
 * Copy this file to data/config.php and fill in your settings.
 */

// Optional App Token from your Socrata account (recommended for higher rate limits)
define('SODA_APP_TOKEN', '');

// Domain is usually data.melbourne.vic.gov.au
define('SODA_DOMAIN', 'data.melbourne.vic.gov.au');

// Cache TTL in seconds (e.g. 300 = 5 minutes)
define('SODA_CACHE_TTL', 300);

// Resource IDs (4x4) for City of Melbourne datasets
// NOTE: Please confirm the exact IDs on the dataset pages (API / API Endpoint section)
// Examples below are common but may change; verify before use.
define('SODA_RS_SENSORS', 'vh2v-4nfs');       // On-street Parking Bay Sensors (example)
define('SODA_RS_BAYS',    'dtpv-d4pf');       // On-street Parking Bays (example)
define('SODA_RS_SIGNS',   '');                // Sign plates located in each parking zone
define('SODA_RS_ZONES',   '');                // Parking zones linked to street segments

// Opendatasoft Explore API dataset slugs (alternative to Socrata for some datasets)
define('ODS_DOMAIN', 'data.melbourne.vic.gov.au');
define('ODS_DS_BAYS', 'on-street-parking-bays'); // dataset slug from your console
define('ODS_DS_SENSORS', 'on-street-parking-bay-sensors'); // dataset slug from your console

?>


