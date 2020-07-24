<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dotenv-loader.php';
require __DIR__ . '/enc.php';

header("Content-Type: application/json");

function get_access_token() {
    $at = get_at_from_authorization_header();
    if(! isset($at))
        $at = get_at_from_cookie();
    if(isset($at)) return decrypt($at);
    return null;
}

function get_at_from_authorization_header() {
    $requestHeaders = apache_request_headers();
    $authorizationHeader = isset($requestHeaders['authorization']) ? $requestHeaders['authorization'] : $requestHeaders['Authorization'];
    if ($authorizationHeader == null) {
        return null;
    }
    return str_ireplace('bearer ', '', $authorizationHeader);
}

function get_at_from_cookie() {
    $cookie_name = $_ENV['ACCESS_TOKEN_COOKIE_NAME'];
    return isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : null;
}

// foreach (getallheaders() as $name => $value) { error_log("$name: $value"); }

$x_auth_token = get_access_token();

if (!isset($x_auth_token)) {
    $user = 'unknown';
    $result = 'unauthorized';
    $exp = 0;
} else {
    $access_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $x_auth_token)[1]))), true);

    $user = $access_token['sub'];
    $exp = $access_token['exp'];
    // TODO: proper checks here using a JWT library (out of scope)
    // Example at: https://auth0.com/docs/quickstart/backend/php/01-authorization
    if ($exp < time())
        $result = 'expired';
    else
        $result = time();
}

$data = array('user' => $user, 'exp' => $exp, 'result' => $result);
echo json_encode($data);

exit();

