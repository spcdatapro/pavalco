<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

$app->get('/rptlibventas/:idempresa/:mes/:anio', function($idempresa, $mes, $anio){
    $db = new dbcpm();
    $query = "SELECT a.fecha AS fechafactura, c.siglas AS tipodocumento, a.serie, a.numero AS documento, b.nit, ";
    $query.= "CONCAT(d.nombre, ' (GCF', LPAD(b.idcliente, 4, '0'), '-', LPAD(b.correlativo, 4, '0'), ')') AS cliente, ";
    $query.= "IF(a.idtipoventa IN(1, 2, 4), IF(c.generaiva = 0 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS exento, ";
    $query.= "IF(a.idtipoventa = 4, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS activo, ";
    $query.= "IF(a.idtipoventa = 1, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS bien, ";
    $query.= "IF(a.idtipoventa = 2, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS servicio, ";
    $query.= "ROUND(a.iva * a.tipocambio, 2) AS iva, ROUND(((a.subtotal - a.noafecto) + a.iva) * a.tipocambio, 2) AS totfact ";
    $query.= "FROM factura a INNER JOIN contrato b ON b.id = a.idcontrato INNER JOIN tipofactura c ON c.id = a.idtipofactura INNER JOIN cliente d ON d.id = a.idcliente ";
    $query.= "WHERE a.idtipoventa <> 5 AND c.id <> 5 AND b.idempresa = ".$idempresa." AND a.mesiva = ".$mes." AND YEAR(a.fecha) = ".$anio." ";
    $query.= "UNION ";
    $query.= "SELECT a.fecha AS fechafactura, c.siglas AS tipodocumento, a.serie, a.numero AS documento, d.nit, d.nombre AS cliente, ";
    $query.= "IF(a.idtipoventa IN(1, 2, 4), IF(c.generaiva = 0 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS exento, ";
    $query.= "IF(a.idtipoventa = 4, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS activo, ";
    $query.= "IF(a.idtipoventa = 1, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS bien, ";
    $query.= "IF(a.idtipoventa = 2, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS servicio, ";
    $query.= "ROUND(a.iva * a.tipocambio, 2) AS iva, ROUND(((a.subtotal - a.noafecto) + a.iva) * a.tipocambio, 2) AS totfact ";
    $query.= "FROM factura a INNER JOIN tipofactura c ON c.id = a.idtipofactura INNER JOIN cliente d ON d.id = a.idcliente ";
    $query.= "WHERE a.idtipoventa <> 5 AND c.id <> 5 AND a.idcontrato = 0 AND a.idempresa = ".$idempresa." AND a.mesiva = ".$mes." AND YEAR(a.fecha) = ".$anio." ";
    $query.= "ORDER BY 1, 2, 3, 4";
    print $db->doSelectASJson($query);
});

$app->run();