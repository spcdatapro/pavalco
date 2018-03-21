<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para titulos
$app->get('/lsttitulos', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('titulo',['id', 'descripcion'], ['ORDER' => 'descripcion']);
    print json_encode($data);
});

$app->get('/gettitulo/:idtitulo', function($idtitulo){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('titulo',['id', 'descripcion'], ['id'=>$idtitulo]);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO titulo(descripcion) VALUES('".$d->descripcion."')";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE titulo SET descripcion = '".$d->descripcion."' WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM titulo WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();