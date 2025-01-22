<?php

if (!defined('ABSPATH')) exit; // Seguridad

// Función auxiliar para obtener datos de la API
function get_vehicles_data() {
    static $cached_data = null;
    
    if ($cached_data !== null) {
        return $cached_data;
    }

    $endpoint = 'https://api.motoraldia.com/wp-json/api-motor/v1/vehicles';
    $credentials = base64_encode('paulo:Paulo.5050!');

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'Authorization' => 'Basic ' . $credentials,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        error_log('Error en la API de Motor al día: ' . $response->get_error_message());
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!empty($data['vehicles'])) {
        $cached_data = $data;
    }

    return $data;
}
