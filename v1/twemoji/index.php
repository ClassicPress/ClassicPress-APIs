<?php

require_once dirname(__DIR__) . '/functions.php';

$files = array_map('basename', glob(__DIR__ . '/*.json'));

if (is_browser()) {
	echo "<h2>Twemoji API responses:</h2>\n";
	echo "<ul>\n";
	foreach ($files as $file) {
		echo "<li><a href=\"$file\">$file</a></li>\n";
	}
	echo "</ul>\n";
} else {
	header('Content-Type: application/json');
	echo json_encode($files);
}
