<?php
declare(strict_types = 1);

$silent_mode = false;

if(! empty($_GET['format']) && $_GET['format'] === 'silent')
    $silent_mode = true;

if (! empty($_GET['error']) || ! empty($_GET['error_description'])) {
    error(htmlspecialchars($_GET['error_description']));
}

// Nothing to do.
if (empty($_GET['code'])) {
    error(htmlspecialchars('No authorization code found.'));
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dotenv-loader.php';
require __DIR__ . '/enc.php';

use Auth0\SDK\API\Authentication;
use Auth0\SDK\Auth0;
use Auth0\SDK\Helpers\TransientStoreHandler;
use Auth0\SDK\Store\SessionStore;

$authorization_code = $_GET['code'];

// Validate callback state.
$transient_store = new SessionStore();
$state_handler = new TransientStoreHandler($transient_store);
if (! $state_handler->verify(Auth0::TRANSIENT_STATE_KEY, ( $_GET['state'] ?? null ))) {
    error('Invalid state.');
}

// Instantiate the Authentication class with the client secret.
$auth0_api = new Authentication(
    $_ENV['AUTH0_DOMAIN'],
    $_ENV['AUTH0_CLIENT_ID'],
    $_ENV['AUTH0_CLIENT_SECRET']
);

$redirect_uri = $_ENV['ORIGIN_URI'] . '/cb.php';

try {
    $code_exchange_result = $auth0_api->code_exchange($authorization_code, $redirect_uri);
} catch (Exception $e) {
    error($e->getMessage());
}

$APP_URI = $_ENV['ORIGIN_URI'] . '/app.html';

// todo: validate id_token, validate nonce, c_hash, at_hash

$access_token = $code_exchange_result['access_token'];

$access_token_enc=encrypt($access_token);
$cookie_name = $_ENV['ACCESS_TOKEN_COOKIE_NAME'];
header("Set-Cookie: $cookie_name=$access_token_enc; secure; httpOnly; SameSite=Strict");

if($silent_mode)
    echo render_iframe('ok');
else
    header('Location: '.$APP_URI);

exit();

function error($description) {
    global $silent_mode;
    if($silent_mode) {
        echo render_iframe($description);
    } else {
        printf('<h1>Error</h1><p>%s</p>', htmlspecialchars($_GET['error_description']));
    }
    die();
}

function render_iframe($error) {
    $ORIGIN_URI = $_ENV['ORIGIN_URI'];
    return "<!DOCTYPE html>
<html lang=\"en\">
<head><title>Callback Response</title></head>
<body>
<script type=\"text/javascript\">(function (window) {
    const authorizationResponse = {type: \"authorization_response\", response: {\"error\": \"$error\"}};
    const mainWin = (window.opener) ? window.opener : window.parent;
    mainWin.postMessage(authorizationResponse, \"$ORIGIN_URI\");
})(this);</script>
</body>
</html>";
}