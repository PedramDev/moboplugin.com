<?php

add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');
add_action('plugins_api', 'mobo_core_plugin_info', 10, 3);
define('PLUGIN_SLUG', 'mobo-core');
define('GITHUB_REPO', 'PedramDev/moboplugin.com');


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
        $plugin_slug = 'mobo-core';
        $transient->response[$plugin_slug] = (object) [
            'slug' => $plugin_slug,
            'new_version' => $release->tag_name,
            'url' => $release->html_url,
            'package' => $release->zipball_url // Updated to use the zipball_url
        ];
    } else {
        error_log('No tag name found in release: ' . print_r($release, true));
    }


    return $transient;
}

function plugin_info_api($false, $action, $response) {
    if ($action === 'plugin_information' && $response->slug === 'your-plugin-slug') {
        $repo_url = 'https://api.github.com/repos/username/repo'; // Change to your repo URL
        $response = wp_remote_get($repo_url);
        $data = json_decode(wp_remote_retrieve_body($response));

        return (object) [
            'name' => $data->name,
            'slug' => $data->slug,
            'version' => $data->tag_name,
            'description' => $data->description,
            'homepage' => $data->homepage,
            'sections' => [
                'description' => $data->description,
            ],
        ];
    }
    return $false;
}