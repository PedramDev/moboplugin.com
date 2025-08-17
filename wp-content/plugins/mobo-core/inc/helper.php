<?php

namespace MoboCore;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function parseJson($json) {
    $data = json_decode($json, true);
    if ($data === null) {
        throw new \Exception("Error decoding JSON.");
    }
    
    foreach ($data as $item) {
        yield $item;
    }
}