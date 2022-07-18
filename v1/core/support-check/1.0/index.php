<?php
/**
 * REST API Endpoint used to check the recommended ClassicPress PHP version.
 * This is similar to http://api.wordpress.org/core/serve-happy/1.0/ 
 *
 * Response should be an array with:
 *  'recommended_version' - string - The PHP version recommended by ClassicPress.
 *  'is_supported' - boolean - Whether the PHP version is actively supported.
 *  'is_secure' - boolean - Whether the PHP version receives security updates.
 *  'is_acceptable' - boolean - Whether the PHP version is still acceptable for ClassicPress.
 */
$json = [
    'recommended_version' => '5.6',
    'is_supported'        => true,
    'is_secure'           => false,
    'is_acceptable'       => false,
];

header('Content-Type: application/json');
echo json_encode($json);
