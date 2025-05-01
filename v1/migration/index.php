<?php
header( 'Content-Type: application/json' );

// Parameters for migration plugin.  Managed by a ClassicPress server because
// most new versions of WordPress and ClassicPress don't require any changes to
// the migration plugin other than updating these parameters.

// ClassicPress build info. See:
// https://github.com/ClassyBot/ClassicPress-v2-nightly/releases
$build_version = '2.4.1';
$build_date = '20250313';

$version = "$build_version+migration.$build_date";
$build_url = 'https://github.com/ClassyBot/ClassicPress-v2-nightly'
	. "/releases/download/$build_version%2Bmigration.$build_date"
	. "/ClassicPress-nightly-$build_version-migration.$build_date.zip";

// ClassicPress build info. See:
// https://github.com/ClassyBot/ClassicPress-v1-nightly/releases
$v1_build_version = '1.7.3';
$v1_build_date = '20240309';

$v1_version = "$v1_build_version+migration.$v1_build_date";
$v1_build_url = 'https://github.com/ClassyBot/ClassicPress-v1-nightly'
	. "/releases/download/$v1_build_version%2Bmigration.$v1_build_date"
	. "/ClassicPress-nightly-$v1_build_version-migration.$v1_build_date.zip";

$wp49 = "https://wordpress.org/wordpress-4.9.26.zip";

$wp62 = "https://wordpress.org/wordpress-6.2.6.zip";

echo json_encode( [
	// WordPress versions allowed for migration.
	'wordpress' => [
		'min'   => '4.9.0',
		'max'   => '6.8.1',
		'other' => [
			'#^4\.9$#',
			'#^6\.9-(alpha|beta|rc)#i',
		],
	],
	// ClassicPress build to use for migration.
	'classicpress' => [
		'build'   => $build_url,
		'version' => $version,
	],
	'php' => [
		'min' => '7.4',
		'max' => '8.4.999',
		'max_display' => '8.4.x',
	],
	'links' => [
		'ClassicPress v2'  => $build_url,
		'ClassicPress v1'  => $v1_build_url,
		'WordPress Latest' => 'https://wordpress.org/latest.zip',
		'WordPress 6.2.x'  => $wp62,
		'WordPress 4.9.x'  => $wp49,
	],
	'plugins' => [
		'wp-config-file-editor/wp-config-file-editor.php',
		'disable-wp-core-updates-advance/disable-wp-core-updates-advance.php',
		'disable-wordpress-updates/disable-updates.php',
		'wp-downgrade/wp-downgrade.php',
		'wp-views/wp-views.php',
		'types/wpcf.php',
		'cred-frontend-editor/plugin.php',
		'types-access/types-access.php',
		'toolset-blocks/wp-views.php',
		'toolset-maps/toolset-maps-loader.php',
		'woocommerce-views/views-woocommerce.php',
		'cred-commerce/plugin.php',
		'elementor/elementor.php',
		'review-widgets-for-trustpilot/review-widgets-for-trustpilot.php',
	],
	'themes' => [
		'twentytwentyone',
		'kadence',
		'astra',
	],
	'defaults' => [
		'theme_name' => 'Twenty Seventeen',
		'theme_url'  => 'https://wordpress.org/themes/twentyseventeen/',
	],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
