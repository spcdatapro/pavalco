<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para tipos de solucion
$app->get('/lsttipossolucion', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM tiposolucion ORDER BY descripcion");
});

$app->get('/gettiposolucion/:idtiposolucion', function($idtiposolucion){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM tiposolucion WHERE id = $idtiposolucion");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO tiposolucion(descripcion) VALUES('$d->descripcion')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE tiposolucion SET descripcion = '$d->descripcion' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM tiposolucion WHERE id = ".$d->id);
});

$app->run();