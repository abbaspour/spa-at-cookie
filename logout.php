<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dotenv-loader.php';

// logout.php
use Auth0\SDK\API\Authentication;

$auth0_api = new Authentication(
    $_ENV['AUTH0_DOMAIN'],
    $_ENV['AUTH0_CLIENT_ID']
);

$returnTo = $_ENV['ORIGIN_URI'] . '/index.html';

$auth0_logout_url = $auth0_api->get_logout_link($returnTo, $_ENV['AUTH0_CLIENT_ID']);

session_start();
session_destroy();
setcookie($_ENV['ACCESS_TOKEN_COOKIE_NAME'], 'expired', time() - 3600);

header('Location: ' . $auth0_logout_url);

exit();
