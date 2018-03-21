<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para proveedores de equipos
$app->get('/lstprovseq', function(){
    $db = new dbcpm();
    $query = "SELECT a.id, a.nombre, a.idtitulosug, b.descripcion AS titulo, a.contactosug, a.telsug, a.correosug ";
    $query.= "FROM provequipo a LEFT JOIN titulo b ON b.id = a.idtitulosug ";
    $query.= "ORDER BY a.nombre";    
    print $db->doSelectAsJson($query);
});

$app->get('/getproveq/:idproveq', function($idproveq){
    $db = new dbcpm();
    $query = "SELECT a.id, a.nombre, a.idtitulosug, b.descripcion AS titulo, a.contactosug, a.telsug, a.correosug ";
    $query.= "FROM provequipo a LEFT JOIN titulo b ON b.id = a.idtitulosug ";
    $query.= "WHERE a.id = ".$idproveq;    
    print $db->doSelectAsJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();    
    $query = "INSERT INTO provequipo(nombre, idtitulosug, contactosug, telsug, correosug) VALUES(";
    $query.= "'$d->nombre', $d->idtitulosug, '$d->contactosug', '$d->telsug', '$d->correosug')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE provequipo SET nombre = '$d->nombre', idtitulosug = $d->idtitulosug, ";
    $query.= "contactosug = '$d->contactosug', telsug = '$d->telsug', correosug  = '$d->correosug' ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM provequipo WHERE id = $d->id");
});

$app->run();