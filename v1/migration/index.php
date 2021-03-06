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
		'max'   => '5.5.3',
		'other' => [
			'#^4\.9$#',
			'#^5\.5\.4-(alpha|beta|rc)#i',
			'#^5\.6-(alpha|beta|rc)#i',
		],
	],
	// ClassicPress build to use for migration.
	'classicpress' => [
		'build'   => $build_url,
		'version' => $version,
	],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
