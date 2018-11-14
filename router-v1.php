<?php

// NOTE: DO NOT expose this script at a public URL!

$file = __DIR__ . '/v1' . $_SERVER['REQUEST_URI'];
$index = $file . '/index.php';
if (is_dir($file) && file_exists($index)) {
	require $index;
} else if (preg_match('#\.json$#', $file) && file_exists($file)) {
	header('Content-Type: application/json');
	readfile($file);
} else {
	return false;
}
