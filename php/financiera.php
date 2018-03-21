<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para financieras
$app->get('/lstallfinan', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idtipofinanciera, b.descripcion AS tipofinanciera, a.nombre, CONCAT(b.descripcion, ' - ', a.nombre) AS tipofinanfinan ";
    $query.= "FROM financiera a INNER JOIN tipofinanciera b ON b.id = a.idtipofinanciera ";
    $query.= "ORDER BY b.descripcion, a.nombre";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/lstfinanbytipo/:idtipofinan', function($idtipofinan){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idtipofinanciera, b.descripcion AS tipofinanciera, a.nombre, CONCAT(b.descripcion, ' - ', a.nombre) AS tipofinanfinan  ";
    $query.= "FROM financiera a INNER JOIN tipofinanciera b ON b.id = a.idtipofinanciera ";
    $query.= "WHERE b.id = ".$idtipofinan." ";
    $query.= "ORDER BY a.nombre";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getfinan/:idfinan', function($idfinan){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idtipofinanciera, b.descripcion AS tipofinanciera, a.nombre, CONCAT(b.descripcion, ' - ', a.nombre) AS tipofinanfinan  ";
    $query.= "FROM financiera a INNER JOIN tipofinanciera b ON b.id = a.idtipofinanciera ";
    $query.= "WHERE a.id = ".$idfinan;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO financiera(idtipofinanciera, nombre) VALUES(".$d->idtipofinanciera.", '".$d->nombre."')";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE financiera SET idtipofinanciera = ".$d->idtipofinanciera.", nombre = '".$d->nombre."' WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM financiera WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();