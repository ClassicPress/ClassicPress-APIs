<?php
header( 'Content-Type: application/json' );

// Parameters for migration plugin.  Managed by a ClassicPress server because
// most new versions of WordPress and ClassicPress don't require any changes to
// the migration plugin other than updating these parameters.

$build_version = '1.0.1';
$build_date = '20190313';

$version = "$build_version+migration.$build_date";
$build_url = 'https://github.com/ClassyBot/ClassicPress-nightly'
	. "/releases/download/$build_version%2Bmigration.$build_date"
	. "/ClassicPress-nightly-$build_version-migration.$build_date.zip";

echo json_encode( [
	// WordPress versions allowed for migration.
	'wordpress' => [
		'min'   => '4.9.0',
		'max'   => '5.1.1',
		'other' => [
			'#^4\.9$#',
			'#^5\.1\.2-(alpha|beta|rc)#i',
			'#^5\.2-(alpha|beta|rc)#i',
		],
	],
	// ClassicPress build to use for migration.
	'classicpress' => [
		'build'   => $build_url,
		'version' => $version,
	],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
