<?php
header( 'Content-Type: application/json' );

// Parameters for migration plugin.  Managed by a ClassicPress server because
// most new versions of WordPress and ClassicPress don't require any changes to
// the migration plugin other than updating these parameters.

// ClassicPress build info. See:
// https://github.com/ClassyBot/ClassicPress-nightly/releases
$build_version = '1.1.1';
$build_date = '20191018';

$version = "$build_version+migration.$build_date";
$build_url = 'https://github.com/ClassyBot/ClassicPress-nightly'
	. "/releases/download/$build_version%2Bmigration.$build_date"
	. "/ClassicPress-nightly-$build_version-migration.$build_date.zip";

echo json_encode( [
	// WordPress versions allowed for migration.
	'wordpress' => [
		'min'   => '4.9.0',
		'max'   => '5.7.2',
		'other' => [
			'#^4\.9$#',
			'#^5\.8-(alpha|beta|rc)#i',
		],
	],
	// ClassicPress build to use for migration.
	'classicpress' => [
		'build'   => $build_url,
		'version' => $version,
	],
	'plugins' => [
		'wp-config-file-editor/wp-config-file-editor.php',
		'disable-wp-core-updates-advance/disable-wp-core-updates-advance.php',
		'disable-wordpress-updates/disable-updates.php',
		'wp-downgrade/wp-downgrade.php',
		'wordfence/wordfence.php',
	],
	'themes' => [
		'twentytwentyone',
	]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
