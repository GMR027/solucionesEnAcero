<?php

/**
 * Catalogo publico de Soluciones en Acero.
 *
 * Copia de la tabla `productos` de la BD de cotizaciones (empresa_id = 1),
 * SOLO con las columnas publicas: id, nombre, sku, categoria, descripcion, imagen.
 * Nunca agregar precio, costo ni stock aqui.
 *
 * Temporal: en la siguiente fase el modelo Producto leera estas filas desde la BD.
 */
return [
    ['id' => 36, 'nombre' => 'Registro de ángulo 40x40 cm', 'sku' => 'RA-1', 'categoria' => 'Registros de ángulo', 'imagen' => 'producto-ra1.jpg',
     'descripcion' => "Registro compacto de ángulo.\r\nDimensión: 40x40 cm. Marco: 1 1/2\". Contramarco: 1 1/4\". Armado interior: Varillatec 1/4\". Acabado: primer rojo.\r\nPatas de anclaje en la parte posterior; división de armado: 13.3 cm.\r\nTipo de uso: ideal para instalaciones eléctricas o de telecomunicaciones."],
    ['id' => 37, 'nombre' => 'Registro de ángulo 40x60 cm', 'sku' => 'RA-3', 'categoria' => 'Registros de ángulo', 'imagen' => 'producto-ra3.jpg',
     'descripcion' => "Registro reforzado de ángulo.\r\nDimensión: 40x60 cm. Marco: 1 1/4\". Contramarco: 1\". Armado interior: Varillatec 1/4\". Acabado: primer rojo.\r\nArmado de largos: 13.3 cm, cortos: 15 cm.\r\nTipo de uso: ideal para instalaciones sanitarias."],
    ['id' => 38, 'nombre' => 'Registro de ángulo 40x60 cm con coladera', 'sku' => 'RA-4', 'categoria' => 'Registros de ángulo', 'imagen' => 'producto-ra4.jpg',
     'descripcion' => "Registro reforzado de ángulo con coladera.\r\nDimensión: 40x60 cm. Marco: 1 1/4\". Contramarco: 1\". Armado interior: Varillatec 1/4\". Acabado: primer rojo.\r\nColadera armada con varillatec; armado de largos: 13.3 cm, cortos: 15 cm.\r\nTipo de uso: ideal para áreas de desagüe de líquidos."],
    ['id' => 39, 'nombre' => 'Registro de ángulo 47x67 cm', 'sku' => 'RA-5', 'categoria' => 'Registros de ángulo', 'imagen' => 'producto-ra5.jpg',
     'descripcion' => "Registro grande de ángulo.\r\nDimensión: 47x67 cm. Marco: 1 1/2\". Contramarco: 1 1/4\". Armado interior: Varillatec 1/4\". Acabado: primer gris.\r\nArmado de largos: 15.5 cm, cortos: 13.4 cm.\r\nTipo de uso: ideal para instalaciones sanitarias donde el registro de block requiera una medida interior libre de 40x60 cm."],
    ['id' => 40, 'nombre' => 'Registro de ángulo 60x60 cm', 'sku' => 'RA-6', 'categoria' => 'Registros de ángulo', 'imagen' => 'producto-ra6.jpg',
     'descripcion' => "Registro cuadrado de ángulo.\r\nDimensión: 60x60 cm. Marco: 1 1/4\". Contramarco: 1\". Armado interior: Varillatec 1/4\". Acabado: primer rojo.\r\nSeparación de armado: 12 cm.\r\nTipo de uso: ideal para instalaciones sanitarias y para cisternas (no de la marca Rotoplas) con tráfico ligero."],
    ['id' => 42, 'nombre' => 'Registro de solera 40x60 cm', 'sku' => 'RS-1', 'categoria' => 'Registros de solera', 'imagen' => 'producto-rs1.jpg',
     'descripcion' => "Registro de solera.\r\nDimensión: 40x60 cm. Marco: 2\". Contramarco: 2\". Armado interior: Varillatec 1/4\". Acabado: primer rojo.\r\nArmado de largos: 13.3 cm, cortos: 15 cm.\r\nTipo de uso: ideal para instalaciones sanitarias."],
    ['id' => 43, 'nombre' => 'Registro de solera 65x65 cm', 'sku' => 'RS-2', 'categoria' => 'Registros de solera', 'imagen' => 'producto-rs2.jpg',
     'descripcion' => "Registro de solera para tráfico pesado.\r\nDimensión: 65x65 cm. Marco: 2\". Contramarco: 2\". Armado interior: Varillatec 5/16\". Acabado: primer rojo.\r\nSeparación de armado interior: 13 cm.\r\nTipo de uso: ideal para cisternas (no de la marca Rotoplas) e instalaciones sanitarias con tráfico pesado."],
    ['id' => 44, 'nombre' => 'Registro de solera 75x75 cm', 'sku' => 'RS-3', 'categoria' => 'Registros de solera', 'imagen' => 'producto-rs3.jpg',
     'descripcion' => "Registro de solera de gran tamaño.\r\nDimensión: 75x75 cm. Marco: 2\". Contramarco: 2\". Armado interior: Varillatec 5/16\". Acabado: primer gris.\r\nSeparación de armado: 12.5 cm.\r\nTipo de uso: ideal para cisternas de la marca Rotoplas e instalaciones sanitarias con tráfico pesado."],
    ['id' => 45, 'nombre' => 'Tapa metálica 60x60 cm sencilla', 'sku' => 'TM-1', 'categoria' => 'Tapas metálicas', 'imagen' => 'producto-tm1.jpg',
     'descripcion' => "Tapa metálica sencilla.\r\nDimensión: 60x60 cm. Color: gris. Acabado: primer gris.\r\nTipo de uso: ideal para cisternas (no de la marca Rotoplas) con tráfico ligero."],
    ['id' => 46, 'nombre' => 'Tapa metálica 60x60 cm reforzada', 'sku' => 'TM-2', 'categoria' => 'Tapas metálicas', 'imagen' => 'producto-tm2.jpg',
     'descripcion' => "Tapa metálica reforzada.\r\nDimensión: 60x60 cm. Color: negra. Acabado: primer negro.\r\nTipo de uso: ideal para cisternas (no de la marca Rotoplas) con tráfico pesado."],
    ['id' => 47, 'nombre' => 'Tapa metálica 70x70 cm sencilla', 'sku' => 'TM-3', 'categoria' => 'Tapas metálicas', 'imagen' => 'producto-tm3.jpg',
     'descripcion' => "Tapa metálica sencilla.\r\nDimensión: 70x70 cm. Color: gris. Acabado: primer gris.\r\nTipo de uso: ideal para cisternas de la marca Rotoplas con tráfico ligero."],
    ['id' => 48, 'nombre' => 'Tapa metálica 70x70 cm reforzada', 'sku' => 'TM-4', 'categoria' => 'Tapas metálicas', 'imagen' => 'producto-tm4.jpg',
     'descripcion' => "Tapa metálica reforzada.\r\nDimensión: 70x70 cm. Color: negra. Acabado: primer negro.\r\nTipo de uso: ideal para cisternas de la marca Rotoplas con tráfico pesado."],
    ['id' => 49, 'nombre' => 'Base para boiler 40x40 cm', 'sku' => 'BB-1', 'categoria' => 'Bases', 'imagen' => 'producto-bb1.jpg',
     'descripcion' => "Base de lámina para boiler y bombas hidráulicas.\r\nDimensión: 40x40 cm. Base de lámina; soportes de ángulo. Acabado: primer gris (color gris).\r\nTipo de uso: ideal para recibir bases de boiler y bombas."],
];
