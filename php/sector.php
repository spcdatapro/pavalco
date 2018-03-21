<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para sectores (tipo de equipo)
$app->get('/lstsectores', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descsector FROM sector ORDER BY descsector");
});

$app->get('/getsector/:idsector', function($idsector){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descsector FROM sector WHERE id = ".$idsector);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("INSERT INTO sector(descsector) VALUES('".$d->descsector."')");
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE sector SET descsector = '".$d->descsector."' WHERE id = ".$d->id);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM sector WHERE id = ".$d->id);
});

$app->run();