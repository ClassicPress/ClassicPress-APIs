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

 // No php version passed.
if ( ! isset( $_GET['php_version'] ) ) {
    $json = [
        'code'    => 'missing_param',
        'message' => 'Missing parameter: ' . $_GET['php_version'],
        'status'  => 400
    ];
    header('Content-Type: application/json');
    echo json_encode($json);

    return;
}

$php_version              = $_GET['php_version'];
$recommended_version      = '7.4';
$latest_supported_version = '8.0';

// Allowed set of details.
$allowed_php_versions_for_check = ['5.6', '7.0', '7.1', '7.2', '7.3', '7.4', '8.0', '8.1','8.2'];

if ( ! in_array( $php_version, $allowed_php_versions_for_check ) ) {
    $json = [
        'code'    => 'missing_param',
        'message' => 'Unsupported Version: ' . $php_version,
        'status'  => 400
    ];
    header('Content-Type: application/json');
    echo json_encode($json);

    return;
}

// For versions less than 7.4 in array not recommended.
if ( floatval( $php_version ) <= 7.3 ) {
    $json = [
        'recommended_version' => $recommended_version,
        'is_supported'        => true,
        'is_secure'           => false,
        'is_acceptable'       => false,
    ];
}

/**
 * For versions greater than 7.3 in array recommended and supported.
 * Change this when 7.4 is deprecated.
 */
if ( floatval( $php_version ) >= 7.4 ) {
    $json = [
        'recommended_version' => $recommended_version,
        'is_supported'        => true,
        'is_secure'           => true,
        'is_acceptable'       => true,
    ];
}

// For versions greater than 7.3 in array recommended and not supported.
if ( floatval( $php_version ) > $latest_supported_version ) {
    $json = [
        'recommended_version' => $recommended_version,
        'is_supported'        => false,
        'is_secure'           => true,
        'is_acceptable'       => true,
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
