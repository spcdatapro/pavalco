<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

$app->get('/rptlibcomp/:idempresa/:mes/:anio', function($idempresa, $mes, $anio){
    $db = new dbcpm();
    $query = "SELECT a.fechafactura, c.siglas AS tipodocumento, a.serie, a.documento, b.nit, b.nombre AS proveedor, ";
    $query.= "IF(a.idtipocompra = 3, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - (a.noafecto + a.idp)) * a.tipocambio, 2), 0.00), 0.00) AS combustible, ";
    $query.= "IF(a.idtipocompra = 1, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS bien, ";
    $query.= "IF(a.idtipocompra = 2, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS servicio, ";
    $query.= "IF(a.idtipocompra = 1, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND(a.noafecto * a.tipocambio, 2), 0.00), 0.00) AS bienex, ";
    $query.= "IF(a.idtipocompra = 2, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND(a.noafecto * a.tipocambio, 2), 0.00), 0.00) AS servicioex, ";
    $query.= "IF(a.idtipocompra <> 5, IF(c.generaiva = 1 AND a.idtipofactura = 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS importaciones, ";
    //$query.= "ROUND(a.iva * a.tipocambio, 2) AS iva, ROUND(((a.subtotal - (a.noafecto + a.idp)) + a.iva) * a.tipocambio, 2) AS totfact ";
    $query.= "ROUND(a.iva * a.tipocambio, 2) AS iva, ROUND(a.totfact * a.tipocambio, 2) AS totfact ";
    $query.= "FROM compra a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN tipofactura c ON c.id = a.idtipofactura ";
    $query.= "WHERE a.idtipocompra <> 5 AND c.id <> 5 AND a.idempresa = ".$idempresa." AND a.idreembolso = 0 AND a.mesiva = ".$mes." AND YEAR(a.fechafactura) = ".$anio." ";
    $query.= "UNION ";
    $query.= "SELECT a.fechafactura, c.siglas AS tipodocumento, a.serie, a.documento, a.nit, a.proveedor, ";
    $query.= "IF(a.idtipocompra = 3, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - (a.noafecto + a.idp)) * a.tipocambio, 2), 0.00), 0.00) AS combustible, ";
    $query.= "IF(a.idtipocompra = 1, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS bien, ";
    $query.= "IF(a.idtipocompra = 2, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS servicio, ";
    $query.= "IF(a.idtipocompra = 1, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND(a.noafecto * a.tipocambio, 2), 0.00), 0.00) AS bienex, ";
    $query.= "IF(a.idtipocompra = 2, IF(c.generaiva = 1 AND a.idtipofactura <> 6, ROUND(a.noafecto * a.tipocambio, 2), 0.00), 0.00) AS servicioex, ";
    $query.= "IF(a.idtipocompra <> 5, IF(c.generaiva = 1 AND a.idtipofactura = 6, ROUND((a.subtotal - a.noafecto) * a.tipocambio, 2), 0.00), 0.00) AS importaciones, ";
    //$query.= "ROUND(a.iva * a.tipocambio, 2) AS iva, ROUND(((a.subtotal - (a.noafecto + a.idp)) + a.iva) * a.tipocambio, 2) AS totfact ";
    $query.= "ROUND(a.iva * a.tipocambio, 2) AS iva, ROUND(a.totfact * a.tipocambio, 2) AS totfact ";
    $query.= "FROM compra a INNER JOIN tipofactura c ON c.id = a.idtipofactura ";
    $query.= "WHERE a.idtipocompra <> 5 AND c.id <> 5 AND a.idempresa = ".$idempresa." AND a.idreembolso > 0 AND a.mesiva = ".$mes." AND YEAR(a.fechafactura) = ".$anio." ";
    $query.= "ORDER BY 1, 2, 3, 4";
    print $db->doSelectASJson($query);
});

$app->run();