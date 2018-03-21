<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para cuentas contables
$app->get('/lstctas/:idempresa', function($idempresa){
    $db = new dbcpm();
    $query = "SELECT id, idempresa, codigo, nombrecta, tipocuenta, CONCAT('(', codigo, ') ', nombrecta) AS codcta, integracliente ";
    $query.= "FROM cuentac ";
    $query.= "WHERE idempresa = $idempresa ";
    $query.= "ORDER BY codigo";
    print $db->doSelectASJson($query);
});

$app->get('/getcta/:idcta', function($idcta){
    $db = new dbcpm();
    $query = "SELECT id, idempresa, codigo, nombrecta, tipocuenta, CONCAT('(', codigo, ') ', nombrecta) AS codcta, integracliente FROM cuentac WHERE id = $idcta";
    print $db->doSelectASJson($query);
});

$app->get('/getbytipo/:idempresa/:tipo', function($idempresa, $tipo){
    $db = new dbcpm();
    $query = "SELECT id, idempresa, codigo, nombrecta, tipocuenta, CONCAT('(', codigo, ') ', nombrecta) AS codcta, integracliente ";
    $query.= "FROM cuentac WHERE idempresa = $idempresa AND tipocuenta = $tipo ORDER BY codigo";
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO cuentac(idempresa, codigo, nombrecta, tipocuenta, integracliente) ";
    $query.= "VALUES($d->idempresa, '$d->codigo', '$d->nombrecta', $d->tipocuenta, $d->integracliente)";
    $db->doQuery($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE cuentac SET idempresa = $d->idempresa, codigo = '$d->codigo', ";
    $query.= "nombrecta = '$d->nombrecta', tipocuenta = $d->tipocuenta, integracliente = $d->integracliente WHERE id = ".$d->id;
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM cuentac WHERE id = $d->id");
});

$app->run();