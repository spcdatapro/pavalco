<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para cobro primera renta
$app->get('/lstcobroprimrenta', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT id, descripcion FROM cobroprimrenta ORDER BY descripcion";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getcobroprimrenta/:idcobroprimrenta', function($idcobroprimrenta){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT id, descripcion FROM cobroprimrenta WHERE id = ".$idcobroprimrenta;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO cobroprimrenta(descripcion) VALUES('".$d->descripcion."')";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE cobroprimrenta SET descripcion = '".$d->descripcion."' WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM cobroprimrenta WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();