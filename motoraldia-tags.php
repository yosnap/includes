<?php

if (!defined('ABSPATH')) exit; // Seguridad

// Incluir el archivo de la API
require_once __DIR__ . '/motoraldia-api.php';

// Función para obtener el índice actual
function get_vehicle_index() {
    global $post;
    return isset($post->current_index) ? $post->current_index : 0;
}

// Función para obtener el vehículo actual
function get_current_vehicle() {
    $data = get_vehicles_data();
    if (empty($data['vehicles'])) {
        return null;
    }

    $vehicles = $data['vehicles'];
    $index = get_vehicle_index();
    
    return isset($vehicles[$index]) ? $vehicles[$index] : null;
}

// Registrar los dynamic tags
add_filter('bricks/dynamic_tags_list', function($tags) {
    $field_labels = [
        'titol-anunci' => 'Título',
        'preu' => 'Precio',
        'marques-cotxe' => 'Marca',
        'models-cotxe' => 'Modelo',
        'quilometratge' => 'Kilómetros',
        'any' => 'Año',
        'tipus-combustible' => 'Combustible',
        'potencia-cv' => 'Potencia',
        'estat-vehicle' => 'Estado',
        'color-vehicle' => 'Color',
        'portes-cotxe' => 'Puertas',
        'places-cotxe' => 'Plazas',
        'imatge-destacada-url' => 'Imagen destacada',
        'ad_gallery' => 'Galería',
        'venut' => 'Vendido',
        'thumbnail_id' => 'Miniatura'
    ];

    foreach ($field_labels as $field => $label) {
        $tags[] = [
            'name' => '{vehicle_data:' . $field . '}',
            'label' => $label,
            'group' => 'Motor al Día - Vehículo'
        ];
    }

    return $tags;
});

// Renderizado de los valores
add_filter('bricks/dynamic_data/render_content', function($content) {
    if (strpos($content, '{vehicle_data:') === false) {
        return $content;
    }

    $vehicle = get_current_vehicle();
    if (!$vehicle) {
        return $content;
    }

    return preg_replace_callback('/{vehicle_data:([^}]+)}/', function($matches) use ($vehicle) {
        $field = $matches[1];
        
        if (!isset($vehicle[$field])) {
            return '';
        }

        switch ($field) {
            case 'preu':
                return number_format($vehicle[$field], 0, ',', '.') . ' €';
            case 'quilometratge':
                return number_format($vehicle[$field], 0, ',', '.') . ' km';
            case 'potencia-cv':
                return $vehicle[$field] . ' CV';
            case 'tipus-combustible':
                return ucfirst(str_replace('combustible-', '', $vehicle[$field]));
            case 'venut':
                return filter_var($vehicle[$field], FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No';
            case 'ad_gallery':
                if (is_array($vehicle[$field])) {
                    return implode(',', $vehicle[$field]);
                }
                return $vehicle[$field];
            default:
                return $vehicle[$field];
        }
    }, $content);
}, 10, 1);
