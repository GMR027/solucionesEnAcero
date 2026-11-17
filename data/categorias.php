<?php

/**
 * Categorias del catalogo.
 *
 * En la BD de cotizaciones la categoria es texto libre (productos.categoria);
 * aqui se le asigna slug, portada y textos. Temporal hasta el CRUD de categorias.
 * `nombre` debe coincidir exactamente con productos.categoria.
 */
return [
    [
        'slug' => 'registros-angulo',
        'nombre' => 'Registros de ángulo',
        'resumen' => 'Marco y contramarco de ángulo con armado de Varillatec. Para instalaciones eléctricas, sanitarias y cisternas.',
        'portada' => 'img-productos/Ra3-P.png',
    ],
    [
        'slug' => 'registros-solera',
        'nombre' => 'Registros de solera',
        'resumen' => 'Solera de 2" para cargas mayores. La opción para cisternas y tráfico pesado.',
        'portada' => 'img-productos/Rs1-P.png',
    ],
    [
        'slug' => 'tapas-metalicas',
        'nombre' => 'Tapas metálicas',
        'resumen' => 'Tapas sencillas y reforzadas de 60x60 y 70x70 cm para cisternas, incluidas Rotoplas.',
        'portada' => 'img-productos/TM-N-P.png',
    ],
    [
        'slug' => 'bases',
        'nombre' => 'Bases',
        'resumen' => 'Base de lámina con soportes de ángulo para boilers y bombas hidráulicas.',
        'portada' => 'img-productos/BB-600x600.png',
    ],
];
