<?php
// Read .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch(\Dotenv\Exception\InvalidPathException $ex) {
    // Ignore if no dotenv
}
