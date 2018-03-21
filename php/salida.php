<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para salidas de partes
$app->get('/lstallsalidas', function(){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcaso, a.fecha, a.razon, a.despachado, a.fhcreacion, a.idusrcrea, b.nombre AS usrcrea, a.fhmodifica, a.idusrmodifica, d.nombre AS usrmodifica, a.escambio, ";
    $query.= "a.correlativo, a.idrazoncambio, c.descripcion AS razoncambio ";
    $query.= "FROM salida a INNER JOIN usuario b ON b.id = a.idusrcrea INNER JOIN razoncambio c ON c.id = a.idrazoncambio LEFT JOIN usuario d ON d.id = a.idusrmodifica ";
    $query.= "ORDER BY fecha DESC";
    print $db->doSelectASJson($query);
});

$app->get('/lstsalidasporcaso/:idcaso', function($idcaso){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcaso, a.fecha, a.razon, a.despachado, a.fhcreacion, a.idusrcrea, b.nombre AS usrcrea, a.fhmodifica, a.idusrmodifica, d.nombre AS usrmodifica, a.escambio, ";
    $query.= "a.correlativo, a.idrazoncambio, c.descripcion AS razoncambio ";
    $query.= "FROM salida a INNER JOIN usuario b ON b.id = a.idusrcrea INNER JOIN razoncambio c ON c.id = a.idrazoncambio LEFT JOIN usuario d ON d.id = a.idusrmodifica ";
    $query.= "WHERE a.idcaso = $idcaso ";
    $query.= "ORDER BY fecha DESC";
    print $db->doSelectASJson($query);
});

$app->get('/getsalida/:idsalida', function($idsalida){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcaso, a.fecha, a.razon, a.despachado, a.fhcreacion, a.idusrcrea, b.nombre AS usrcrea, a.fhmodifica, a.idusrmodifica, d.nombre AS usrmodifica, a.escambio, ";
    $query.= "a.correlativo, a.idrazoncambio, c.descripcion AS razoncambio ";
    $query.= "FROM salida a INNER JOIN usuario b ON b.id = a.idusrcrea INNER JOIN razoncambio c ON c.id = a.idrazoncambio LEFT JOIN usuario d ON d.id = a.idusrmodifica ";
    $query.= "WHERE a.id = $idsalida";
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO salida(idcaso, fecha, fhcreacion, idusrcrea, idrazoncambio, despachado) VALUES(";
    $query.= "$d->idcaso, '$d->fechastr', '$d->fhcreacionstr', $d->idusrcrea, $d->idrazoncambio, 1";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE salida SET fecha = '$d->fechastr', fhmodifica = '$d->fhmodificastr', idusrmodifica = $d->idusrmodifica, idrazoncambio = $d->idrazoncambio ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM detsalida WHERE idsalida = ".$d->id);
    $db->doQuery("DELETE FROM salida WHERE id = ".$d->id);
});

//API para detalle de salidas
$app->get('/lstdetsalida/:idsalida', function($idsalida){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idsalida, a.idbodega, b.nombre AS bodega, a.idparte, a.cantidad, c.codigointerno AS codigointernoparte, d.descripcion AS marca, e.descripcion AS subgrupo, ";
    $query.= "f.descripcion AS grupo, c.descripcion AS parte ";
    $query.= "FROM detsalida a INNER JOIN bodega b ON b.id = a.idbodega INNER JOIN parte c ON c.id = a.idparte INNER JOIN marca d ON d.id = c.idmarca INNER JOIN subgrupoparte e ON e.id = c.idsubgrupo ";
    $query.= "INNER JOIN grupoparte f ON f.id = e.idgrupoparte ";
    $query.= "WHERE a.idsalida = $idsalida ";
    $query.= "ORDER BY b.nombre, f.descripcion, e.descripcion, d.descripcion, c.codigointerno, c.descripcion";
    print $db->doSelectASJson($query);
});

$app->get('/getdetsalida/:iddetsalida', function($iddetsalida){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idsalida, a.idbodega, b.nombre AS bodega, a.idparte, a.cantidad, c.codigointerno AS codigointernoparte, d.descripcion AS marca, e.descripcion AS subgrupo, ";
    $query.= "f.descripcion AS grupo, c.descripcion AS parte ";
    $query.= "FROM detsalida a INNER JOIN bodega b ON b.id = a.idbodega INNER JOIN parte c ON c.id = a.idparte INNER JOIN marca d ON d.id = c.idmarca INNER JOIN subgrupoparte e ON e.id = c.idsubgrupo ";
    $query.= "INNER JOIN grupoparte f ON f.id = e.idgrupoparte ";
    $query.= "WHERE a.id = $iddetsalida ";
    print $db->doSelectASJson($query);
});

$app->post('/cd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO detsalida(idsalida, idbodega, idparte, cantidad) VALUES(";
    $query.= "$d->idsalida, $d->idbodega, $d->idparte, $d->cantidad";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/ud', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE detsalida SET idbodega = $d->idbodega, idparte = $d->idparte, cantidad = $d->cantidad ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/dd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM detsalida WHERE id = ".$d->id);
});

$app->run();