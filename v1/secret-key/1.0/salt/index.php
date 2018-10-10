<?php

echo "<pre>
define('AUTH_KEY',         '".str_random()."');
define('SECURE_AUTH_KEY',  '".str_random()."');
define('LOGGED_IN_KEY',    '".str_random()."');
define('NONCE_KEY',        '".str_random()."');
define('AUTH_SALT',        '".str_random()."');
define('SECURE_AUTH_SALT', '".str_random()."');
define('LOGGED_IN_SALT',   '".str_random()."');
define('NONCE_SALT',       '".str_random()."');
</pre>";


/**
 * Generate a "random" string.
 *
 * @param  int  $length
 * @return string
 */
function str_random($length=64) {
    return substr(base64_encode(random_bytes($length+2)),0,-2);
}
