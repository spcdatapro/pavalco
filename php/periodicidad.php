<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para periodicidad
$app->get('/lstperiodicidad', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('periodicidad',['id', 'descripcion', 'dias'], ['ORDER' => 'dias']);
    print json_encode($data);
});

$app->get('/getperiodicidad/:idperiodicidad', function($idperiodicidad){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('periodicidad',['id', 'descripcion', 'dias'], ['id'=>$idperiodicidad]);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO periodicidad(descripcion, dias) VALUES('".$d->descripcion."', ".$d->dias.")";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE periodicidad SET descripcion = '".$d->descripcion."', dias = ".$d->dias." WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM periodicidad WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();