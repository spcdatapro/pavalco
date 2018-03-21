<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para monedas
$app->get('/lstmonedas', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('moneda',['id', 'nommoneda', 'simbolo', 'tipocambio'], ['ORDER' => 'nommoneda']);
    print json_encode($data);
});

$app->get('/getmoneda/:idmoneda', function($idmoneda){
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('moneda',['id', 'nommoneda', 'simbolo', 'tipocambio'], ['id'=>$idmoneda]);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO moneda(nommoneda, simbolo, tipocambio) VALUES('".$d->nommoneda."', '".$d->simbolo."', ".$d->tipocambio.")";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE moneda SET nommoneda = '".$d->nommoneda."' , simbolo = '".$d->simbolo."', tipocambio = ".$d->tipocambio." WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM moneda WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();