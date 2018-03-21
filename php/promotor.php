<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para promotores
$app->get('/lstpromotores', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT id, nombre, telefono, correoe FROM promotor ORDER BY nombre";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getpromotor/:idpromotor', function($idpromotor){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT id, nombre, telefono, correoe FROM promotor WHERE id = ".$idpromotor;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO promotor(nombre, telefono, correoe) VALUES('".$d->nombre."', '".$d->telefono."', '".$d->correoe."')";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE promotor SET nombre = '".$d->nombre."', telefono = '".$d->telefono."', correoe = '".$d->correoe."' ";
    $query.= "WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM promotor WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();