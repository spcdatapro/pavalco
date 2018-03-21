<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para ubicacions
$app->get('/lstubicaciones', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion, direccion, telefono, contacto, debaja FROM ubicacion ORDER BY debaja, descripcion");
});

$app->get('/getubicacion/:idubicacion', function($idubicacion){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion, direccion, telefono, contacto, debaja FROM ubicacion WHERE id = $idubicacion");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->telefono = $d->telefono == '' ? "NULL" : "'$d->telefono'";
    $d->contacto = $d->contacto == '' ? "NULL" : "'$d->contacto'";
    $query = "INSERT INTO ubicacion(descripcion, direccion, telefono, contacto, debaja) VALUES(";
    $query.= "'$d->descripcion', '$d->direccion', $d->telefono, $d->contacto, $d->debaja";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->telefono = $d->telefono == '' ? "NULL" : "'$d->telefono'";
    $d->contacto = $d->contacto == '' ? "NULL" : "'$d->contacto'";
    $query = "UPDATE ubicacion SET descripcion = '$d->descripcion', direccion = '$d->direccion', telefono = $d->telefono, contacto = $d->contacto, debaja = $d->debaja ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM ubicacion WHERE id = ".$d->id);
});

$app->run();