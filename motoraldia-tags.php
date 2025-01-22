<?php

if (!defined('ABSPATH')) exit; // Seguridad

// Incluir el archivo de la API
require_once __DIR__ . '/motoraldia-api.php';

// Función para obtener el ID del vehículo actual
function get_current_vehicle_id() {
    // Obtener el ID del vehículo de la URL
    $vehicle_id = get_query_var('vehicle_id');
    error_log('Motor al día Tags - ID del vehículo desde URL: ' . $vehicle_id);
    return $vehicle_id;
}

// Función para obtener los datos del vehículo actual
function get_current_vehicle_data() {
    static $vehicle_data = null;
    
    if ($vehicle_data !== null) {
        return $vehicle_data;
    }
    
    $vehicle_id = get_current_vehicle_id();
    if (!$vehicle_id) {
        return null;
    }
    
    require_once get_stylesheet_directory() . '/includes/motoraldia-api.php';
    $vehicle_data = get_vehicle_by_id($vehicle_id);
    error_log('Motor al día Tags - Datos del vehículo cargados para ID ' . $vehicle_id);
    
    return $vehicle_data;
}

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
add_filter('bricks/dynamic_data/register_tags', function($tags) {
    // Tags para el listado de vehículos
    $tags['motoraldia'] = [
        'name' => 'Motor al día',
        'data' => function($tag) {
            global $post;
            if (!$post) return '';

            if (isset($post->{"vehicle_$tag"})) {
                return $post->{"vehicle_$tag"};
            }

            return '';
        },
        'tags' => [
            'id' => [
                'name' => 'ID',
                'description' => 'ID del vehículo',
            ],
            'titol-anunci' => [
                'name' => 'Título',
                'description' => 'Título del anuncio',
            ],
            'descripcio-anunci' => [
                'name' => 'Descripción',
                'description' => 'Descripción del anuncio',
            ],
            'preu' => [
                'name' => 'Precio',
                'description' => 'Precio del vehículo',
            ],
            'marca' => [
                'name' => 'Marca',
                'description' => 'Marca del vehículo',
            ],
            'model' => [
                'name' => 'Modelo',
                'description' => 'Modelo del vehículo',
            ],
            'any' => [
                'name' => 'Año',
                'description' => 'Año del vehículo',
            ],
            'quilometres' => [
                'name' => 'Kilómetros',
                'description' => 'Kilómetros del vehículo',
            ],
            'combustible' => [
                'name' => 'Combustible',
                'description' => 'Tipo de combustible',
            ],
            'canvi' => [
                'name' => 'Cambio',
                'description' => 'Tipo de cambio',
            ],
            'imatges' => [
                'name' => 'Imágenes',
                'description' => 'Galería de imágenes',
                'type' => 'array',
            ],
        ],
    ];

    // Tags para la página de detalle
    $tags['motoraldia_vehicle'] = [
        'name' => 'Motor al día - Detalle',
        'data' => function($tag) {
            $vehicle = get_current_vehicle_data();
            if (!$vehicle) {
                error_log('Motor al día Tags - No hay datos del vehículo disponibles');
                return '';
            }

            error_log('Motor al día Tags - Accediendo al campo: ' . $tag);
            
            switch ($tag) {
                case 'title':
                    return $vehicle['titol-anunci'] ?? '';
                case 'price':
                    return $vehicle['preu'] ?? '';
                case 'description':
                    return $vehicle['descripcio-anunci'] ?? '';
                case 'brand':
                    return $vehicle['marca'] ?? '';
                case 'model':
                    return $vehicle['model'] ?? '';
                case 'year':
                    return $vehicle['any'] ?? '';
                case 'kilometers':
                    return $vehicle['quilometres'] ?? '';
                case 'fuel':
                    return $vehicle['combustible'] ?? '';
                case 'transmission':
                    return $vehicle['canvi'] ?? '';
                case 'gallery':
                    return $vehicle['imatges'] ?? [];
                case 'location':
                    return $vehicle['localitzacio'] ?? '';
                case 'color':
                    return $vehicle['color'] ?? '';
                case 'doors':
                    return $vehicle['portes'] ?? '';
                case 'seats':
                    return $vehicle['places'] ?? '';
                case 'power':
                    return $vehicle['potencia'] ?? '';
                case 'engine':
                    return $vehicle['cilindrada'] ?? '';
                case 'publish_date':
                    return $vehicle['data-publicacio'] ?? '';
                default:
                    return $vehicle[$tag] ?? '';
            }
        },
        'tags' => [
            'title' => [
                'name' => 'Título',
                'description' => 'Título del anuncio del vehículo',
            ],
            'price' => [
                'name' => 'Precio',
                'description' => 'Precio del vehículo',
            ],
            'description' => [
                'name' => 'Descripción',
                'description' => 'Descripción completa del vehículo',
            ],
            'brand' => [
                'name' => 'Marca',
                'description' => 'Marca del vehículo',
            ],
            'model' => [
                'name' => 'Modelo',
                'description' => 'Modelo del vehículo',
            ],
            'year' => [
                'name' => 'Año',
                'description' => 'Año del vehículo',
            ],
            'kilometers' => [
                'name' => 'Kilómetros',
                'description' => 'Kilómetros del vehículo',
            ],
            'fuel' => [
                'name' => 'Combustible',
                'description' => 'Tipo de combustible',
            ],
            'transmission' => [
                'name' => 'Transmisión',
                'description' => 'Tipo de transmisión',
            ],
            'gallery' => [
                'name' => 'Galería',
                'description' => 'Galería de imágenes del vehículo',
                'type' => 'array',
            ],
            'location' => [
                'name' => 'Localización',
                'description' => 'Ubicación del vehículo',
            ],
            'color' => [
                'name' => 'Color',
                'description' => 'Color del vehículo',
            ],
            'doors' => [
                'name' => 'Puertas',
                'description' => 'Número de puertas',
            ],
            'seats' => [
                'name' => 'Plazas',
                'description' => 'Número de plazas',
            ],
            'power' => [
                'name' => 'Potencia',
                'description' => 'Potencia del motor',
            ],
            'engine' => [
                'name' => 'Cilindrada',
                'description' => 'Cilindrada del motor',
            ],
            'publish_date' => [
                'name' => 'Fecha de publicación',
                'description' => 'Fecha de publicación del anuncio',
            ],
        ],
    ];

    return $tags;
});

// Renderizado de los valores
add_filter('bricks/dynamic_data/render_content', function($content) {
    if (strpos($content, '{vehicle_data:') === false) {
        return $content;
    }

    $vehicle = get_current_vehicle_data();
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
            case 'quilometres':
                return number_format($vehicle[$field], 0, ',', '.') . ' km';
            case 'potencia':
                return $vehicle[$field] . ' CV';
            case 'combustible':
                return ucfirst(str_replace('combustible-', '', $vehicle[$field]));
            case 'imatges':
                if (is_array($vehicle[$field])) {
                    return implode(',', $vehicle[$field]);
                }
                return $vehicle[$field];
            default:
                return $vehicle[$field];
        }
    }, $content);
}, 10, 1);

// Función para procesar los shortcodes de vehículos
add_filter('the_content', function($content) {
    return preg_replace_callback('/\{motoraldia:(.*?)\}/', function($matches) {
        global $post;
        if (!$post || empty($matches[1])) return '';

        $field = $matches[1];
        if (isset($post->{"vehicle_$field"})) {
            return $post->{"vehicle_$field"};
        }

        return '';
    }, $content);
}, 10, 1);
