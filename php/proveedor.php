<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para encabezado de proveedores
$app->get('/lstprovs', function(){
    $db = new dbcpm();
    $query = "SELECT a.id, a.nit, a.nombre, a.direccion, a.telefono, a.correo, a.concepto, a.chequesa, a.retensionisr, a.diascred, a.limitecred, ";
    $query.= "a.pequeniocont, CONCAT('(', a.nit, ') ', a.nombre, ' (', b.simbolo, ')') AS nitnombre, a.idmoneda, b.nommoneda AS moneda, a.tipocambioprov ";
    $query.= "FROM proveedor a INNER JOIN moneda b ON b.id = a.idmoneda ";
    $query.= "ORDER BY a.nombre";
    print $db->doSelectASJson($query);
});

$app->get('/getprov/:idprov', function($idprov){
    $db = new dbcpm();
    $query = "SELECT a.id, a.nit, a.nombre, a.direccion, a.telefono, a.correo, a.concepto, a.chequesa, a.retensionisr, a.diascred, a.limitecred, ";
    $query.= "a.pequeniocont, CONCAT('(', a.nit, ') ', a.nombre, ' (', b.simbolo, ')') AS nitnombre, a.idmoneda, b.nommoneda AS moneda, a.tipocambioprov ";
    $query.= "FROM proveedor a INNER JOIN moneda b ON b.id = a.idmoneda ";
    $query.= "WHERE a.id = ".$idprov;
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO proveedor(nit, nombre, direccion, telefono, correo, concepto, chequesa, ";
    $query.= "retensionisr, diascred, limitecred, pequeniocont, idmoneda, tipocambioprov) ";
    $query.= "VALUES('".$d->nit."', '".$d->nombre."', '".$d->direccion."', '".$d->telefono."', '".$d->correo."', '".$d->concepto."', '".$d->chequesa."', ";
    $query.= $d->retensionisr.", ".$d->diascred.", ".$d->limitecred.", ".$d->pequeniocont.", ".$d->idmoneda.", ".$d->tipocambioprov.")";
    $db->doQuery($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE proveedor SET nit = '".$d->nit."', nombre = '".$d->nombre."', direccion = '".$d->direccion."', ";
    $query.= "telefono = '".$d->telefono."', correo = '".$d->correo."', concepto = '".$d->concepto."', ";
    $query.= "chequesa = '".$d->chequesa."', retensionisr = ".$d->retensionisr.", diascred = ".$d->diascred.", ";
    $query.= "limitecred = ".$d->limitecred.", pequeniocont = ".$d->pequeniocont.", idmoneda = ".$d->idmoneda.", tipocambioprov = ".$d->tipocambioprov." ";
    $query.= "WHERE id = ".$d->id;
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "DELETE FROM proveedor WHERE id = ".$d->id;
    $db->doQuery($query);
    $query = "DELETE FROM detcontprov WHERE idproveedor = ".$d->id;
    $db->doQuery($query);
});

//API para detalle contable de proveedores
$app->get('/detcontprov/:idprov', function($idprov){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idproveedor, b.nombre, c.idempresa, d.nomempresa, a.idcuentac, c.codigo, c.nombrecta ";
    $query.= "FROM detcontprov a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN cuentac c ON c.id = a.idcuentac ";
    $query.= "INNER JOIN empresa d ON d.id = c.idempresa ";
    $query.= "WHERE a.idproveedor = ".$idprov." ";
    $query.= "ORDER BY d.nomempresa, c.codigo, c.nombrecta";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getdetcontprov/:iddetcont', function($iddetcont){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idproveedor, b.nombre, c.idempresa, d.nomempresa, a.idcuentac, c.codigo, c.nombrecta ";
    $query.= "FROM detcontprov a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN cuentac c ON c.id = a.idcuentac ";
    $query.= "INNER JOIN empresa d ON d.id = c.idempresa ";
    $query.= "WHERE a.id = ".$iddetcont;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/lstdetcontprov/:idprov', function($idprov){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.idcuentac, CONCAT('(', b.codigo,') ', b.nombrecta) as cuentac ";
    $query.= "FROM detcontprov a INNER JOIN cuentac b ON b.id = a.idcuentac ";
    $query.= "WHERE a.idproveedor = ".$idprov." ";
    $query.= "ORDER BY b.codigo";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/cd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO detcontprov(idproveedor, idcuentac) ";
    $query.= "VALUES(".$d->idproveedor.", ".$d->idcuentac.")";
    $ins = $conn->query($query);
});

$app->post('/ud', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE detcontprov SET idcuentac = ".$d->idcuentac." WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/dd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM detcontprov WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();