<?php

if (!defined('ABSPATH')) exit; // Seguridad

// Función auxiliar para obtener datos de la API
function get_vehicles_data() {
    static $cached_data = null;
    
    if ($cached_data !== null) {
        error_log('Motor al día API: Retornando datos cacheados');
        return $cached_data;
    }

    $endpoint = 'https://api.motoraldia.com/wp-json/api-motor/v1/vehicles';
    $credentials = base64_encode('paulo:Paulo.5050!');

    error_log('Motor al día API: Haciendo petición a ' . $endpoint);

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
    $status = wp_remote_retrieve_response_code($response);
    
    error_log('Motor al día API: Código de respuesta HTTP: ' . $status);
    error_log('Motor al día API: Respuesta raw: ' . $body);

    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Motor al día API: Error decodificando JSON: ' . json_last_error_msg());
        return [];
    }

    if (!empty($data['vehicles'])) {
        $cached_data = $data;
        error_log('Motor al día API: Datos guardados en caché');
    } else {
        error_log('Motor al día API: No se encontraron vehículos en la respuesta');
    }

    return $data;
}

// Función para obtener un vehículo específico por ID
function get_vehicle_by_id($vehicle_id) {
    $data = get_vehicles_data();
    
    if (empty($data['vehicles'])) {
        error_log('Motor al día API: No hay vehículos disponibles');
        return null;
    }
    
    foreach ($data['vehicles'] as $vehicle) {
        if ($vehicle['id'] == $vehicle_id) {
            // Log para ver todos los campos disponibles
            error_log('Motor al día API - Campos disponibles para el vehículo ' . $vehicle_id . ':');
            error_log(print_r(array_keys($vehicle), true));
            error_log('Motor al día API - Datos completos del vehículo:');
            error_log(print_r($vehicle, true));
            return $vehicle;
        }
    }
    
    error_log('Motor al día API: Vehículo no encontrado con ID ' . $vehicle_id);
    return null;
}
