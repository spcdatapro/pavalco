<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para marcas
$app->get('/lstmarcas', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM marca ORDER BY descripcion");
});

$app->get('/getmarca/:idmarca', function($idmarca){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM marca WHERE id = $idmarca");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO marca(descripcion) VALUES('$d->descripcion')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE marca SET descripcion = '$d->descripcion' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM marca WHERE id = ".$d->id);
});

$app->run();