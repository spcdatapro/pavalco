<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para bodegas
$app->get('/lstbodegas', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, nombre, direccion, telefono, contacto FROM bodega ORDER BY nombre");
});

$app->get('/getbodega/:idbodega', function($idbodega){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, nombre, direccion, telefono, contacto FROM bodega WHERE id = $idbodega");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->direccion = $d->direccion == '' ? "NULL" : "'$d->direccion'";
    $d->telefono = $d->telefono == '' ? "NULL" : "'$d->telefono'";
    $d->contacto = $d->contacto == '' ? "NULL" : "'$d->contacto'";
    $query = "INSERT INTO bodega(nombre, direccion, telefono, contacto) VALUES(";
    $query.= "'$d->nombre', $d->direccion, $d->telefono, $d->contacto";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->direccion = $d->direccion == '' ? "NULL" : "'$d->direccion'";
    $d->telefono = $d->telefono == '' ? "NULL" : "'$d->telefono'";
    $d->contacto = $d->contacto == '' ? "NULL" : "'$d->contacto'";
    $query = "UPDATE bodega SET nombre = '$d->nombre', direccion = $d->direccion, telefono = $d->telefono, contacto = $d->contacto ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM bodega WHERE id = ".$d->id);
});

$app->run();