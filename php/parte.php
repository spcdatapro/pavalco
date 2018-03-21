<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para partes
$app->get('/lstpartes', function(){
    $db = new dbcpm();
    $query = "SELECT a.id, a.codigo, a.codigointerno, a.idproveedor, b.nombre AS proveedor, a.idmarca, c.descripcion AS marca, a.descripcion, e.id AS idgrupo, e.descripcion AS grupoparte, ";
    $query.= "a.idsubgrupo, d.descripcion AS subgrupoparte, a.minimo, a.maximo, a.saldo ";
    $query.= "FROM parte a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN marca c ON c.id = a.idmarca INNER JOIN subgrupoparte d ON d.id = a.idsubgrupo ";
    $query.= "INNER JOIN grupoparte e ON e.id = d.idgrupoparte ";
    $query.= "ORDER BY e.descripcion, d.descripcion, a.codigo, a.codigointerno";
    print $db->doSelectASJson($query);
});

$app->get('/getparte/:idparte', function($idparte){
    $db = new dbcpm();
    $query = "SELECT a.id, a.codigo, a.codigointerno, a.idproveedor, b.nombre AS proveedor, a.idmarca, c.descripcion AS marca, a.descripcion, e.id AS idgrupo, e.descripcion AS grupoparte, ";
    $query.= "a.idsubgrupo, d.descripcion AS subgrupoparte, a.minimo, a.maximo, a.saldo ";
    $query.= "FROM parte a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN marca c ON c.id = a.idmarca INNER JOIN subgrupoparte d ON d.id = a.idsubgrupo ";
    $query.= "INNER JOIN grupoparte e ON e.id = d.idgrupoparte ";
    $query.= "WHERE a.id = $idparte";
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->codigointerno = $d->codigointerno == '' ? "NULL" : "'$d->codigointerno'";
    $query = "INSERT INTO parte(codigo, codigointerno, idproveedor, idmarca, descripcion, idsubgrupo, minimo, maximo) VALUES(";
    $query.= "'$d->codigo', $d->codigointerno, $d->idproveedor, $d->idmarca, '$d->descripcion', $d->idsubgrupo, $d->minimo, $d->maximo";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->codigointerno = $d->codigointerno == '' ? "NULL" : "'$d->codigointerno'";
    $query = "UPDATE parte SET codigo = '$d->codigo', codigointerno = $d->codigointerno, idproveedor = $d->idproveedor, idmarca = $d->idmarca, descripcion = '$d->descripcion', ";
    $query.= "idsubgrupo = $d->idsubgrupo, minimo = $d->minimo, maximo = $d->maximo WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM imagenparte WHERE idparte = ".$d->id);
    $db->doQuery("DELETE FROM parte WHERE id = ".$d->id);
});

//API para las imágenes de partes
$app->get('/lstimgparte/:idparte', function($idparte){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idparte, a.descripcion, a.url FROM imagenparte a WHERE a.idparte = $idparte ORDER BY a.descripcion";
    print $db->doSelectASJson($query);
});

$app->get('/getimgparte/:idimg', function($idimg){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idparte, a.descripcion, a.url FROM imagenparte a WHERE a.id = $idimg";
    print $db->doSelectASJson($query);
});

$app->post('/cd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO imagenparte(idparte, descripcion, url) VALUES(";
    $query.= "$d->idparte, '$d->descripcion', '$d->url'";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/ud', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE imagenparte SET descripcion = '$d->descripcion', url = '$d->url' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/dd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM imagenparte WHERE id = ".$d->id);
});

$app->run();