<?php

if (!defined('ABSPATH')) exit; // Seguridad

// Función auxiliar para obtener datos de la API
function get_vehicles_data() {
    $endpoint = 'https://api.motoraldia.com/wp-json/api-motor/v1/vehicles';
    $credentials = base64_encode('paulo:Paulo.5050!');

    // Implementar caché para evitar múltiples llamadas a la API
    $cache_key = 'motor_vehicles_data';
    $cached_data = wp_cache_get($cache_key);

    if (false !== $cached_data) {
        return $cached_data;
    }

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

    // Guardar en caché por 1 minuto
    wp_cache_set($cache_key, $data, '', 60);

    return $data;
}

// Registrar los dynamic tags
add_filter('bricks/dynamic_tags_list', function($tags) {
    // Obtener datos de la API
    $data = get_vehicles_data();

    if (!empty($data['vehicles']) && is_array($data['vehicles'])) {
        $first_vehicle = current($data['vehicles']);

        // Etiquetas en español
        $field_labels = [
            'id' => 'ID',
            'titol' => 'Título',
            'tipus' => 'Tipo',
            'preu' => 'Precio'
        ];

        // Registrar cada campo disponible
        foreach ($first_vehicle as $field => $value) {
            $label = isset($field_labels[$field]) ? $field_labels[$field] : ucfirst($field);
            $tags[] = [
                'name' => '{vehicle_data:' . $field . '}',
                'label' => $label,
                'group' => 'Motor al Día - Vehículo'
            ];
        }
    }

    return $tags;
});

// Renderizado de los valores
add_filter('bricks/dynamic_data/render_content', function($content, $post = null) {
    if (strpos($content, '{vehicle_data:') === false) {
        return $content;
    }

    // Obtener datos del vehículo
    $vehicle = null;
    
    if ($post instanceof WP_Post) {
        // Intentar obtener datos del post actual
        if (isset($post->vehicle_data)) {
            $vehicle = $post->vehicle_data;
        } elseif (isset($post->meta['vehicle_data'])) {
            $vehicle = $post->meta['vehicle_data'];
        }
    }

    // Si no hay datos en el post, obtener el vehículo específico de la API
    if (!$vehicle && $post instanceof WP_Post) {
        $data = get_vehicles_data();
        if (!empty($data['vehicles'])) {
            // Buscar el vehículo correspondiente por ID
            foreach ($data['vehicles'] as $v) {
                if ($v['id'] == $post->ID) {
                    $vehicle = $v;
                    break;
                }
            }
        }
    }

    if (!$vehicle) {
        return $content;
    }

    return preg_replace_callback('/{vehicle_data:([^}]+)}/', function($matches) use ($vehicle) {
        $field = $matches[1];
        return isset($vehicle[$field]) ? $vehicle[$field] : '';
    }, $content);
}, 10, 2);
