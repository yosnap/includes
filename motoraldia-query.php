<?php

if (!defined('ABSPATH')) exit; // Seguridad

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

    $endpoint = 'https://api.motoraldia.com/wp-json/api-motor/v1/vehicles';
    $credentials = base64_encode('paulo:Paulo.5050!');

    // Obtener parámetros de la consulta
    $number_of_items = isset($query_obj->query_vars['number_of_items']) ? $query_obj->query_vars['number_of_items'] : 10;
    $order = isset($query_obj->query_vars['order']) ? $query_obj->query_vars['order'] : 'ASC';

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

    // Convertir cada vehículo en un objeto post
    $posts = [];
    if (!empty($data['vehicles'])) {
        foreach ($data['vehicles'] as $index => $vehicle) {
            if ($index >= $number_of_items) break;

            // Asegurarnos de que el ID esté en vehicle_data
            $vehicle_data = $vehicle;
            $vehicle_data['id'] = $vehicle['id'];
            
            // Crear un post object con los datos del vehículo
            $post = new stdClass();
            $post->ID = $vehicle['id'];
            $post->post_title = $vehicle['titol-anunci'] ?? '';
            $post->post_type = 'vehicle';
            $post->vehicle_data = $vehicle_data;
            
            // Establecer los datos globalmente para este post
            global $current_post_vehicle;
            $current_post_vehicle = $vehicle_data;
            
            $posts[] = $post;
        }

        // Ordenar los posts
        if ($order === 'DESC') {
            $posts = array_reverse($posts);
        }
    }

    // Establecer las propiedades de la consulta directamente
    if (property_exists($query_obj, 'posts')) {
        $query_obj->posts = $posts;
    }
    
    // Establecer propiedades de paginación
    if (method_exists($query_obj, 'set_found_posts')) {
        $query_obj->set_found_posts(count($posts));
    }
    
    if (property_exists($query_obj, 'post_count')) {
        $query_obj->post_count = count($posts);
    }
    
    if (property_exists($query_obj, 'current_page')) {
        $query_obj->current_page = isset($data['current_page']) ? $data['current_page'] : 1;
    }

    return $posts;
}, 10, 2);

// Función auxiliar para establecer datos del vehículo
function set_vehicle_data($post, $vehicle_data) {
    // Establecer los datos como propiedad pública
    $post->vehicle_data = $vehicle_data;
    
    // También almacenar en post_meta virtual
    $post->meta = array(
        'vehicle_data' => $vehicle_data
    );
    
    return $post;
}
