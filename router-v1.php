<?php

// NOTE: DO NOT expose this script at a public URL!

// Prevent usage in a webserver.
if ('cli-server' !== php_sapi_name()) {
	die('Development only');
}

$file = __DIR__ . '/v1' . $_SERVER['REQUEST_URI'];
$index = $file . '/index.php';
if (is_dir($file) && file_exists($index)) {
	// Add trailing / if not already present
	if (!preg_match('#/$#', $file)) {
		header('HTTP/1.1 302 Found');
		header("Location: ${_SERVER['REQUEST_URI']}/");
		die();
	}
	// Load index.php file
	require $index;
} else if (preg_match('#\.json$#', $file) && file_exists($file)) {
	header('Content-Type: application/json');
	readfile($file);
	die();
} else {
	return false;
}
