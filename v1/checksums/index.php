<?php

require_once dirname(__DIR__) . '/functions.php';

$files = array_map('basename', glob(__DIR__ . '/md5/*.json'));
usort($files, function($a, $b) {
	return str_replace('+', '/', $a) <=> str_replace('+', '/', $b);
});

$files_grouped_1 = ['release' => [], 'nightly' => []];
$nightly_patterns = [
	'nightly',
	'migration',
	'1.0.0-alpha0',
];
foreach ($files as $file) {
	$file_is_nightly = false;
	foreach ($nightly_patterns as $nightly_pattern) {
		if (strpos($file, $nightly_pattern) !== false) {
			$file_is_nightly = true;
			break;
		}
	}
	$files_grouped_1[$file_is_nightly ? 'nightly' : 'release'][] = $file;
}

if (is_browser()) {
	$files_grouped_2 = ['release' => $files_grouped_1['release'], 'nightly' => []];
	foreach ($files_grouped_1['nightly'] as $file_nightly) {
		$version = strtok($file_nightly, '+');
		if (!isset($files_grouped_2['nightly'][$version])) {
			$files_grouped_2['nightly'][$version] = [];
		}
		$files_grouped_2['nightly'][$version][] = $file_nightly;
	}
	echo "<h2>Checksums API responses (release):</h2>\n";
	echo "<ul>\n";
	foreach ($files_grouped_2['release'] as $release_file) {
		echo "<li>$release_file: <a href=\"md5/$release_file\">md5</a></li>\n";
	}
	echo "</ul>\n";
	echo "<h2>Checksums API responses (nightly/migration):</h2>\n";
	echo <<<HTML
<style>
summary {
	color: blue;
	cursor: pointer;
}
</style>
<script>
function hideshow(show) {
	Array.from(document.querySelectorAll('details')).forEach(function(el) {
		el.open = show;
	});
}
document.write('<p><a href="javascript:hideshow(true)">Expand all</a> | <a href="javascript:hideshow(false)">Collapse all</a></p>');
</script>

HTML;
	foreach ($files_grouped_2['nightly'] as $nightly_version => $nightly_files) {
		$count = count($nightly_files);
		$s = ($count === 1 ? '' : 's');
		echo "<details><summary>$nightly_version+* ($count build$s)</summary>\n";
		echo "<ul>\n";
		foreach ($nightly_files as $nightly_file) {
			echo "<li>$nightly_file: <a href=\"md5/$nightly_file\">md5</a></li>\n";
		}
		echo "</ul></details>\n";
	}
} else {
	$files_grouped_2 = ['release' => [], 'nightly' => []];
	foreach ($files_grouped_1 as $type => $files) {
		foreach ($files as $file) {
			$files_grouped_2[$type][] = "md5/$file";
		}
	}
	header('Content-Type: application/json');
	echo json_encode($files_grouped_2);
}
