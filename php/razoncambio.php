<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para razones de cambio
$app->get('/lstrazonescambio', function(){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM razoncambio ORDER BY descripcion");
});

$app->get('/getrazoncambio/:idrazoncambio', function($idrazoncambio){
    $db = new dbcpm();
    print $db->doSelectASJson("SELECT id, descripcion FROM razoncambio WHERE id = $idrazoncambio");
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO razoncambio(descripcion) VALUES('$d->descripcion')";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE razoncambio SET descripcion = '$d->descripcion' WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM razoncambio WHERE id = ".$d->id);
});

$app->run();