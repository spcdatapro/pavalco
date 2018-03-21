<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para fuentes de caso
$app->get('/lstfuentescaso', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM fuentecaso ORDER BY descripcion");
});

$app->get('/getfuentecaso/:idfuentecaso', function($idfuentecaso){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM fuentecaso WHERE id = $idfuentecaso");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO fuentecaso(descripcion) VALUES('$d->descripcion')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE fuentecaso SET descripcion = '$d->descripcion' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM tipocaso WHERE idfuentecaso = ".$d->id);
    $db->doQuery("DELETE FROM fuentecaso WHERE id = ".$d->id);
});

//API para tipos de caso
$app->get('/lstalltiposcaso', function(){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idfuentecaso, b.descripcion AS fuentecaso, a.descripcion, a.mostrar ";
    $query.= "FROM tipocaso a INNER JOIN fuentecaso b ON b.id = a.idfuentecaso ";
    $query.= "ORDER BY b.descripcion, a.descripcion";
    print $db->doSelectASJson($query);
});

$app->get('/lsttiposcasobyfuente/:idfuentecaso', function($idfuentecaso){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idfuentecaso, b.descripcion AS fuentecaso, a.descripcion, a.mostrar ";
    $query.= "FROM tipocaso a INNER JOIN fuentecaso b ON b.id = a.idfuentecaso ";
    $query.= "WHERE a.idfuentecaso = $idfuentecaso ";
    $query.= "ORDER BY a.descripcion";
    print $db->doSelectASJson($query);
});

$app->get('/gettipocaso/:idtipocaso', function($idtipocaso){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idfuentecaso, b.descripcion AS fuentecaso, a.descripcion, a.mostrar ";
    $query.= "FROM tipocaso a INNER JOIN fuentecaso b ON b.id = a.idfuentecaso ";
    $query.= "WHERE a.id = $idtipocaso ";
    print $db->doSelectASJson($query);
});

$app->post('/cd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("INSERT INTO tipocaso(idfuentecaso, descripcion, mostrar) VALUES($d->idfuentecaso, '$d->descripcion', $d->mostrar)");
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/ud', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE tipocaso SET descripcion = '$d->descripcion', mostrar = $d->mostrar WHERE id = $d->id");
});

$app->post('/dd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM tipocaso WHERE id = ".$d->id);
});

$app->run();