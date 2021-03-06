<?php

require_once dirname(__DIR__) . '/functions.php';

$files = array_map('basename', glob(__DIR__ . '/*.json'));
usort($files, function($a, $b) {
	return str_replace('+', '/', $a) <=> str_replace('+', '/', $b);
});

if (is_browser()) {
	echo "<h2>Checksums API responses (release):</h2>\n";
	echo "<ul>\n";
	foreach ($files as $file) {
		if (strpos($file, 'nightly') === false && strpos($file, 'migration') === false) {
			echo "<li><a href=\"$file\">$file</a></li>\n";
		}
	}
	echo "</ul>\n";
	echo "<h2>Checksums API responses (nightly):</h2>\n";
	echo "<ul>\n";
	foreach ($files as $file) {
		if (strpos($file, 'nightly') !== false || strpos($file, 'migration') !== false) {
			echo "<li><a href=\"$file\">$file</a></li>\n";
		}
	}
	echo "</ul>\n";
} else {
	header('Content-Type: application/json');
	echo json_encode($files);
}
