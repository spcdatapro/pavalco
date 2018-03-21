<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para partidas directas
$app->get('/lstdirectas/:idempresa', function($idempresa){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT id, idempresa, fecha FROM directa WHERE idempresa = ".$idempresa." ORDER BY fecha, id";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getdirecta/:iddirecta', function($iddirecta){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT id, idempresa, fecha FROM directa WHERE id = ".$iddirecta;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO directa(idempresa, fecha) VALUES(".$d->idempresa.",'".$d->fechastr."')";
    $ins = $conn->query($query);
    $lastid = $conn->query("SELECT LAST_INSERT_ID()")->fetchColumn(0);
    print json_encode(['lastid' => $lastid]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE directa SET fecha = '".$d->fechastr."' WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM directa WHERE id = ".$d->id;
    $del = $conn->query($query);
});

$app->run();