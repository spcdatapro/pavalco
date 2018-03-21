<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para tipos de financiera
$app->get('/lsttiposfinan', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('tipofinanciera',['id', 'descripcion'], ['ORDER' => 'descripcion']);
    print json_encode($data);
});

$app->get('/gettipofinan/:idtipofinan', function($idtipofinan){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('tipofinanciera',['id', 'descripcion'], ['id'=>$idtipofinan]);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO tipofinanciera(descripcion) VALUES('".$d->descripcion."')";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE tipofinanciera SET descripcion = '".$d->descripcion."' WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM tipofinanciera WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();