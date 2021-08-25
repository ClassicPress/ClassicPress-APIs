<?php

require_once dirname(__DIR__) . '/functions.php';

$files = array_map('basename', glob(__DIR__ . '/*.json'));
$files = array_filter($files, function($filename) {
	return !preg_match('#\.(latest|upgrade)\.json$#', $filename);
});
usort($files, function($a, $b) {
	return str_replace('+', '/', $a) <=> str_replace('+', '/', $b);
});

if (is_browser()) {
	echo "<h2>Upgrade API responses (release):</h2>\n";
	echo "<ul>\n";
	foreach ($files as $file) {
		if (strpos($file, 'nightly') === false) {
			echo "<li><a href=\"$file\">$file</a></li>\n";
		}
	}
	echo "</ul>\n";
	echo "<h2>Upgrade API responses (nightly):</h2>\n";
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
    $nightly_files = [];
	foreach ($files as $file) {
		$nightly_pos = strpos($file, 'nightly');
        if ($nightly_pos === false) {
            continue;
        }
        $year = substr($file, $nightly_pos + 8, 4);
        if (!isset($nightly_files[$year])) {
            $nightly_files[$year] = [];
        }
        $nightly_files[$year][] = $file;
	}
    foreach ($nightly_files as $year => $year_files) {
		$count = count($year_files);
		$s = ($count === 1 ? '' : 's');
		echo "<details><summary>$year ($count build$s)</summary>\n";
		echo "<ul>\n";
		foreach ($year_files as $file) {
            echo "<li><a href=\"$file\">$file</a></li>\n";
		}
		echo "</ul></details>\n";
    }
} else {
	header('Content-Type: application/json');
	echo json_encode($files);
}
