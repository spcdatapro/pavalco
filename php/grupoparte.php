<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para grupos de partes
$app->get('/lstgruposparte', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM grupoparte ORDER BY descripcion");
});

$app->get('/getgrupoparte/:idgrupoparte', function($idgrupoparte){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM grupoparte WHERE id = $idgrupoparte");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO grupoparte(descripcion) VALUES('$d->descripcion')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE grupoparte SET descripcion = '$d->descripcion' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM subgrupoparte WHERE idgrupoparte = ".$d->id);
    $db->doQuery("DELETE FROM grupoparte WHERE id = ".$d->id);
});

//API para subgrupos de partes
$app->get('/lstallsubgrupospartes', function(){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idgrupoparte, b.descripcion AS grupoparte, a.descripcion ";
    $query.= "FROM subgrupoparte a INNER JOIN grupoparte b ON b.id = a.idgrupoparte ";
    $query.= "ORDER BY b.descripcion, a.descripcion";
    //print $query;
    print $db->doSelectASJson($query);
});

$app->get('/lstgrupospartebygrupo/:idgrupoparte', function($idgrupoparte){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idgrupoparte, b.descripcion AS grupoparte, a.descripcion ";
    $query.= "FROM subgrupoparte a INNER JOIN grupoparte b ON b.id = a.idgrupoparte ";
    $query.= "WHERE a.idgrupoparte = $idgrupoparte ";
    $query.= "ORDER BY a.descripcion";
    print $db->doSelectASJson($query);
});

$app->get('/getsubgrupoparte/:idsubgrupoparte', function($idsubgrupoparte){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idgrupoparte, b.descripcion AS grupoparte, a.descripcion ";
    $query.= "FROM subgrupoparte a INNER JOIN grupoparte b ON b.id = a.idgrupoparte ";
    $query.= "WHERE a.id = $idsubgrupoparte ";
    print $db->doSelectASJson($query);
});

$app->post('/cd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("INSERT INTO subgrupoparte(idgrupoparte, descripcion) VALUES($d->idgrupoparte, '$d->descripcion')");
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/ud', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE subgrupoparte SET descripcion = '$d->descripcion' WHERE id = $d->id");
});

$app->post('/dd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM subgrupoparte WHERE id = ".$d->id);
});

$app->run();