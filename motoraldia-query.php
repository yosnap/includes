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
        error_log('Motor al día API: No hay vehículos disponibles');
        return [];
    }

    // Obtener el número de items a mostrar
    $total_vehicles = count($data['vehicles']);
    $number_of_items = isset($query_obj->query_vars['number_of_items']) ? min($query_obj->query_vars['number_of_items'], $total_vehicles) : $total_vehicles;
    
    error_log("Motor al día API: Mostrando $number_of_items de $total_vehicles vehículos");
    
    // Crear posts para el loop
    $posts = [];
    for ($i = 0; $i < $number_of_items; $i++) {
        $vehicle = $data['vehicles'][$i];
        
        // Crear un objeto post con todos los datos necesarios
        $post = new stdClass();
        $post->ID = $vehicle['id']; // Usar el ID real del vehículo
        $post->post_type = 'motoraldia_vehicle';
        $post->post_title = $vehicle['titol-anunci'];
        $post->post_content = $vehicle['descripcio-anunci'];
        $post->permalink = get_vehicle_permalink($vehicle['id']); // Agregar la URL amigable
        
        // Agregar todos los campos del vehículo como meta
        foreach ($vehicle as $key => $value) {
            $post->{"vehicle_$key"} = $value;
        }
        
        // Campos específicos para Bricks
        $post->image = $vehicle['imatge-destacada-url'];
        $post->price = $vehicle['preu'];
        $post->year = $vehicle['any'];
        $post->brand = $vehicle['marques-cotxe'];
        $post->model = $vehicle['models-cotxe'];
        $post->version = $vehicle['versio'];
        
        $posts[] = $post;
        
        error_log("Motor al día API: Procesando vehículo {$vehicle['id']} - {$vehicle['titol-anunci']}");
    }

    // Debug - Imprimir en el frontend
    add_action('wp_footer', function() use ($posts) {
        echo '<script>
        console.log("Motor al día - Debug Info:");
        console.log("Total posts:", ' . json_encode(count($posts)) . ');
        console.log("Posts Data:", ' . json_encode($posts) . ');
        </script>';
    }, 999);

    return $posts;
}, 10, 2);
