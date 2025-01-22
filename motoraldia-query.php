<?php

if (!defined('ABSPATH')) exit; // Seguridad

// Incluir el archivo de la API
require_once __DIR__ . '/motoraldia-api.php';

// Registrar el tipo de consulta personalizado
add_filter('bricks/setup/control_options', function($control_options) {
    if (!empty($control_options['queryTypes'])) {
        $control_options['queryTypes']['motoraldia_api'] = 'Motor al día - coches';
    }
    return $control_options;
});

// Implementar el Query Loop
add_filter('bricks/query/run', function($results, $query_obj) {
    if ($query_obj->object_type !== 'motoraldia_api') {
        return $results;
    }

    // Obtener los datos de la API
    $data = get_vehicles_data();
    if (empty($data['vehicles'])) {
        return [];
    }

    // Obtener el número de items a mostrar
    $total_vehicles = count($data['vehicles']);
    $number_of_items = isset($query_obj->query_vars['number_of_items']) ? min($query_obj->query_vars['number_of_items'], $total_vehicles) : $total_vehicles;
    
    // Crear posts para el loop
    $posts = [];
    for ($i = 0; $i < $number_of_items; $i++) {
        $post = new stdClass();
        $post->ID = $i;
        $post->post_type = 'motoraldia_vehicle';
        $post->current_index = $i;
        $posts[] = $post;
    }

    return $posts;
}, 10, 2);
