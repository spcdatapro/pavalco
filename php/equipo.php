<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para equipos
$app->get('/lstequipos', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM equipo ORDER BY descripcion");
});

$app->get('/getequipo/:idequipo', function($idequipo){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM equipo WHERE id = $idequipo");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO equipo(descripcion) VALUES('$d->descripcion')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE equipo SET descripcion = '$d->descripcion' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM equipo WHERE id = ".$d->id);
});

$app->run();