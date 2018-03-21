<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para tecnicos
$app->get('/lsttecnicos', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, nombre, direccion, celular, email FROM tecnico ORDER BY nombre");
});

$app->get('/gettecnico/:idtecnico', function($idtecnico){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, nombre, direccion, celular, email FROM tecnico WHERE id = $idtecnico");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->direccion = $d->direccion == '' ? "NULL" : "'$d->direccion'";
    $d->celular = $d->celular == '' ? "NULL" : "'$d->celular'";
    $d->email = $d->email == '' ? "NULL" : "'$d->email'";
    $query = "INSERT INTO tecnico(nombre, direccion, celular, email) VALUES(";
    $query.= "'$d->nombre', $d->direccion, $d->celular, $d->email";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->direccion = $d->direccion == '' ? "NULL" : "'$d->direccion'";
    $d->celular = $d->celular == '' ? "NULL" : "'$d->celular'";
    $d->email = $d->email == '' ? "NULL" : "'$d->email'";
    $query = "UPDATE tecnico SET nombre = '$d->nombre', direccion = $d->direccion, celular = $d->celular, email = $d->email ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM tecnico WHERE id = ".$d->id);
});

$app->run();