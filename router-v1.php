<?php

$index = __DIR__ . '/v1' . $_SERVER['REQUEST_URI'] . '/index.php';
if (file_exists($index)) {
	require $index;
} else {
	return false;
}
