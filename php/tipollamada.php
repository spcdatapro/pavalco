<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para tipos de llamada
$app->get('/lsttiposllamada', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM tipollamada ORDER BY descripcion");
});

$app->get('/gettipollamada/:idtipollamada', function($idtipollamada){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM tipollamada WHERE id = $idtipollamada");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO tipollamada(descripcion) VALUES('$d->descripcion')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE tipollamada SET descripcion = '$d->descripcion' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM tipollamada WHERE id = ".$d->id);
});

$app->run();