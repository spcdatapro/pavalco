<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para empresas
$app->get('/lstempresas', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = 'SELECT a.id, a.nomempresa, a.idmoneda, b.nommoneda, b.simbolo, a.dectc ';
    $query.= 'FROM empresa a INNER JOIN moneda b ON b.id = a.idmoneda ';
    $query.= 'ORDER BY a.nomempresa, b.nommoneda';
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getemp/:idemp', function($idemp){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = 'SELECT a.id, a.nomempresa, a.idmoneda, b.nommoneda, b.simbolo, a.dectc ';
    $query.= 'FROM empresa a INNER JOIN moneda b ON b.id = a.idmoneda ';
    $query.= 'WHERE a.id = '.$idemp;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getdectc/:idemp', function($idemp){
    $db = new dbcpm();
    print json_encode(['dectc' => $db->getOneField("SELECT dectc FROM empresa WHERE id = ".$idemp)]);
});

$app->get('/pordefecto', function(){
    $db = new dbcpm();
    print json_encode(['pordefecto' => (int)$db->getOneField("SELECT id FROM empresa WHERE pordefecto = 1 LIMIT 1")]);
});

$app->get('/conconta/:idemp', function($idemp){
    $db = new dbcpm();
    print json_encode(['conconta' => $db->getOneField("SELECT conconta FROM empresa WHERE id = $idemp")]);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO empresa(nomempresa, idmoneda, dectc) VALUES('".$d->nomempresa."', ".$d->idmoneda.", ".$d->dectc.")";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE empresa SET nomempresa = '".$d->nomempresa."' , idmoneda = ".$d->idmoneda.", dectc = ".$d->dectc." WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM empresa WHERE id = ".$d->id;
    $del = $conn->query($query);
});

//API para la configuración contable de las empresas
$app->get('/lstconf/:idempresa', function($idempresa){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idempresa, a.idtipoconfig, b.desctipoconfconta, a.idcuentac, CONCAT('(',c.codigo,') ', c.nombrecta) AS cuentac ";
    $query.= "FROM detcontempresa a INNER JOIN tipoconfigconta b ON b.id = a.idtipoconfig INNER JOIN cuentac c ON c.id = a.idcuentac ";
    $query.= "WHERE a.idempresa = ".$idempresa." ";
    $query.= "ORDER BY b.desctipoconfconta";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getconf/:idconf', function($idconf){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idempresa, a.idtipoconfig, b.desctipoconfconta, a.idcuentac, CONCAT('(',c.codigo,') ', c.nombrecta) AS cuentac ";
    $query.= "FROM detcontempresa a INNER JOIN tipoconfigconta b ON b.id = a.idtipoconfig INNER JOIN cuentac c ON c.id = a.idcuentac ";
    $query.= "WHERE a.id = ".$idconf;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/cc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO detcontempresa(idempresa, idtipoconfig, idcuentac) VALUES(".$d->idempresa.", ".$d->idtipoconfig.", ".$d->idcuentac.")";
    $ins = $conn->query($query);
});

$app->post('/uc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE detcontempresa SET idtipoconfig = ".$d->idtipoconfig.", idcuentac = ".$d->idcuentac." WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/dc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM detcontempresa WHERE id = ".$d->id;
    $del = $conn->query($query);
});


$app->run();