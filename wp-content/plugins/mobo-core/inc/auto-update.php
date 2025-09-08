<?php

add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');
add_action('plugins_api', 'mobo_core_plugin_info', 10, 3);

function check_for_plugin_update($transient) {
    // GitHub repository details
    $repo = 'PedramDev/moboplugin.com';
    $api_url = 'https://api.github.com/repos/' . $repo . '/releases/latest';
    
    // Fetch the latest release information
    $response = wp_remote_get($api_url, [
        'headers' => [
            'User-Agent' => 'WordPress Plugin Updater'
        ]
    ]);

    if (is_wp_error($response)) {
        return $transient;
    }

    $release = json_decode(wp_remote_retrieve_body($response));
    
    if (isset($release->tag_name)) {
        // Create a new update object
        $plugin_slug = 'mobo-core';
        $transient->response[$plugin_slug] = (object) [
            'slug' => $plugin_slug,
            'new_version' => $release->tag_name,
            'url' => $release->html_url,
            'package' => $release->zipball_url // Update this to point to the zip file
        ];
    }

    return $transient;
}

function mobo_core_plugin_info($false, $action, $response) {
    // You can add additional info here if needed
    return $false;
}