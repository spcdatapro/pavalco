<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para clientes
$app->get('/lstallclientes', function(){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idpromotor, b.nombre AS promotor, a.idtitulo, c.descripcion AS titulo, a.nombre, a.telefono, a.puesto, ";
    $query.= "a.empresa, a.direccion, a.idpais, d.nombre AS pais, a.nit ";
    $query.= "FROM cliente a INNER JOIN promotor b ON b.id = a.idpromotor INNER JOIN titulo c ON c.id = a.idtitulo INNER JOIN pais d ON d.id = a.idpais ";
    $query.= "ORDER BY a.nombre";
    print $db->doSelectASJson($query);
});

$app->get('/lstclientesbypromotor/:idpromotor', function($idpromotor){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idpromotor, b.nombre AS promotor, a.idtitulo, c.descripcion AS titulo, a.nombre, a.telefono, a.puesto, ";
    $query.= "a.empresa, a.direccion, a.idpais, d.nombre AS pais, a.nit ";
    $query.= "FROM cliente a INNER JOIN promotor b ON b.id = a.idpromotor INNER JOIN titulo c ON c.id = a.idtitulo INNER JOIN pais d ON d.id = a.idpais ";
    $query.= "WHERE b.id = ".$idpromotor." ";
    $query.= "ORDER BY a.nombre";
    print $db->doSelectASJson($query);
});

$app->get('/getcliente/:idcliente', function($idcliente){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idpromotor, b.nombre AS promotor, a.idtitulo, c.descripcion AS titulo, a.nombre, a.telefono, a.puesto, ";
    $query.= "a.empresa, a.direccion, a.idpais, d.nombre AS pais, a.nit ";
    $query.= "FROM cliente a INNER JOIN promotor b ON b.id = a.idpromotor INNER JOIN titulo c ON c.id = a.idtitulo INNER JOIN pais d ON d.id = a.idpais ";
    $query.= "WHERE a.id = ".$idcliente;
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO cliente(idpromotor, idtitulo, nombre, telefono, puesto, empresa, direccion, idpais, nit) VALUES(";
    $query.= $d->idpromotor.", ".$d->idtitulo.", '".$d->nombre."', '".$d->telefono."', '".$d->puesto."', '".$d->empresa."', '".$d->direccion."', ";
    $query.= $d->idpais.", '".$d->nit."')";
    $db->doQuery($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE cliente SET idpromotor = ".$d->idpromotor.", idtitulo = ".$d->idtitulo.", nombre = '".$d->nombre."', telefono = '".$d->telefono."', ";
    $query.= "puesto = '".$d->puesto."', empresa = '".$d->empresa."', direccion = '".$d->direccion."', idpais = ".$d->idpais.", nit = '".$d->nit."' ";
    $query.= "WHERE id = ".$d->id;
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM cliente WHERE id = ".$d->id);
});

$app->run();