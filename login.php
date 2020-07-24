<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dotenv-loader.php';

use Auth0\SDK\API\Authentication;
use Auth0\SDK\Auth0;
use Auth0\SDK\Helpers\TransientStoreHandler;
use Auth0\SDK\Store\SessionStore;

$transient_store = new SessionStore();
$state_handler = new TransientStoreHandler($transient_store);
$state_value = $state_handler->issue(Auth0::TRANSIENT_STATE_KEY);

$format = $_GET['format'] ?? '';

$redirect_uri = $_ENV['ORIGIN_URI'] . '/cb.php' . ($format === 'silent' ? '?format=silent' : '');

$auth0_api = new Authentication(
    $_ENV['AUTH0_DOMAIN'],
    $_ENV['AUTH0_CLIENT_ID'],
    $_ENV['AUTH0_CLIENT_SECRET']
);

$url = $auth0_api->get_authorize_link(
    'code',
    $redirect_uri,
    null,
    $state_value,
    [
        'audience' => $_ENV['AUTH0_AUDIENCE'],
        'scope' => 'openid email address',
        'prompt' => ($format === 'silent' ? 'none' : '')
    ]
);

header('Location: '.$url);

exit();

