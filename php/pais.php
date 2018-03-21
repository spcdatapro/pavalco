<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para paises
$app->get('/lstpaises', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('pais',['id', 'abreviatura', 'nombre'], ['ORDER' => 'nombre']);
    print json_encode($data);
});

$app->get('/getpais/:idpais', function($idpais){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('pais',['id', 'abreviatura', 'nombre'], ['id'=>$idpais]);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO pais(abreviatura, nombre) VALUES('".$d->abreviatura."', '".$d->nombre."')";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE pais SET abreviatura = '".$d->abreviatura."' , nombre = '".$d->nombre."' WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM pais WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();