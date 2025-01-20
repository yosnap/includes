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
            $post_arr = array(
                'ID' => $vehicle['id'],
                'post_title' => $vehicle['titol'],
                'post_type' => 'motoraldia_vehicle',
                'post_status' => 'publish',
                'filter' => 'raw'
            );

            // Crear el post y establecer los datos del vehículo
            $post = new WP_Post((object)$post_arr);
            $post->vehicle_data = $vehicle;
            
            $posts[] = $post;
        }
    }

    // Establecer las propiedades de la consulta directamente
    if (property_exists($query_obj, 'posts')) {
        $query_obj->posts = $posts;
    }
    
    // Establecer propiedades de paginación usando @property
    /** @var int */
    $query_obj->found_posts = count($posts);
    /** @var int */
    $query_obj->post_count = count($posts);
    /** @var int */
    $query_obj->current_page = isset($data['current_page']) ? $data['current_page'] : 1;

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
