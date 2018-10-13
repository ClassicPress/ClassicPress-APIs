<?php

echo "<pre>
define( 'AUTH_KEY'        , '" . str_random() . "' );
define( 'SECURE_AUTH_KEY' , '" . str_random() . "' );
define( 'LOGGED_IN_KEY'   , '" . str_random() . "' );
define( 'NONCE_KEY'       , '" . str_random() . "' );
define( 'AUTH_SALT'       , '" . str_random() . "' );
define( 'SECURE_AUTH_SALT', '" . str_random() . "' );
define( 'LOGGED_IN_SALT'  , '" . str_random() . "' );
define( 'NONCE_SALT'      , '" . str_random() . "' );
</pre>";

/**
 * Generate a random string.
 *
 * @param  int  $length
 * @return string
 */
function str_random($length = 64) {
    do {
        $str = base64_encode(random_bytes($length));
        $str = str_replace('/', '', $str);
        $str = str_replace('+', '', $str);
        $str = str_replace('=', '', $str);
    } while (strlen($str) < $length);
    return substr($str, 0, $length);
}

$is_browser = false;
$accept = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT'] ?? '', 20));
foreach ($accept as $mime_type) {
    if (preg_match('#^text/html(;|$)#', $mime_type)) {
        $is_browser = true;
        break;
    }
}

if ($is_browser) {
    // Send some extra info for humans
    echo <<<HTML
<style>
    p, pre.info {
        max-width: 660px;
    }
    pre.info {
        white-space: pre-wrap;
        word-break: break-all;
        font-size: 80%;
    }
</style>
<hr>
<p>
    This feature is <strong>deprecated</strong>!  We highly recommend
    generating your own keys and salts instead of relying on a remote service
    to do this for you.
</p>
<p>
    If you have <code>python</code> installed, try running this command (all on
    one line):
</p>
<pre class="info">
python -c "import random; import string; r = random.SystemRandom(); print(chr(10).join('define( {q}{0}{q}{1}, {q}{2}{q} );'.format(key,' '*(16-len(key)),''.join(r.choice(string.ascii_letters+string.digits)for _ in range(64)),q=chr(39))for key in 'AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT'.split(' ')))"
</pre>
<p>
    Or, if you're using a <code>.env</code> file for configuration (also
    recommended), try this:
</p>
<pre class="info">
python -c "import random; import string; r = random.SystemRandom(); print(chr(10).join('{0}={q}{1}{q}'.format(key,''.join(r.choice(string.ascii_letters+string.digits)for _ in range(64)),q=chr(34))for key in 'AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT'.split(' ')))"
</pre>
HTML;
}
