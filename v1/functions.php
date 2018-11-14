<?php

function is_browser() {
	$accept = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT'] ?? '', 20));
	foreach ($accept as $mime_type) {
		if (preg_match('#^text/html(;|$)#', $mime_type)) {
			return true;
		}
	}
	return false;
}
