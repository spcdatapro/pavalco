<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para facturas
$app->get('/lstfacturas/:todas', function($todas){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcontrato, a.idcliente, CONCAT(e.abreviatura, ' ', c.nombre) AS cliente, c.telefono, b.emailenviofact, ";
    $query.= "CONCAT(a.serie, '-', a.numero) AS factura, a.fecha, d.simbolo AS moneda, a.iva, a.total, a.pagada, a.tipocambio ";
    $query.= "FROM factura a INNER JOIN contrato b ON b.id = a.idcontrato INNER JOIN cliente c ON c.id = a.idcliente INNER JOIN moneda d ON d.id = a.idmoneda ";
    $query.= "INNER JOIN titulo e ON e.id = c.idtitulo ";
    $query.= (int)$todas === 0 ? "WHERE a.pagada = 0 " : "";
    $query.= "ORDER BY a.pagada, a.fecha";
    print $db->doSelectASJson($query);
});

$app->get('/getfactura/:idfactura', function($idfactura){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcontrato, a.idcliente, CONCAT(e.abreviatura, ' ', c.nombre) AS cliente, c.telefono, b.emailenviofact, ";
    $query.= "CONCAT(a.serie, '-', a.numero) AS factura, a.fecha, d.simbolo AS moneda, a.iva, a.total, a.pagada, a.tipocambio ";
    $query.= "FROM factura a INNER JOIN contrato b ON b.id = a.idcontrato INNER JOIN cliente c ON c.id = a.idcliente INNER JOIN moneda d ON d.id = a.idmoneda ";
    $query.= "INNER JOIN titulo e ON e.id = c.idtitulo ";
    $query.= "WHERE a.id = ".$idfactura;
    print $db->doSelectASJson($query);
});

$app->post('/pagar', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE factura SET pagada = 1 WHERE id = ".$d->id);
});

$app->post('/anular', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $testing = true;
    $query = "SELECT wsdl".($testing ? "tst" : "").", requestor, country, entity, usuario, username FROM confgface WHERE pordefecto = 1 LIMIT 1";
    $dataGFACE = $db->getQuery($query)[0];
    $params = [
        'Requestor' => $dataGFACE->requestor,
        'Transaction' => 'CANCEL_XML',
        'Country' => $dataGFACE->country,
        'Entity' => $dataGFACE->entity,
        'User' => $dataGFACE->usuario,
        'UserName' => $dataGFACE->username,
        'Data1' => $d->serie.'-'.$d->numero.'|'.$d->razonanula,
        'Data2' => 'PDF XML',
        'Data3' => $d->noautorizacion
    ];
    $wsdl = $testing ? $dataGFACE->wsdltst : $dataGFACE->wsdl;
    $client = new SoapClient($wsdl, array('trace' => 1));
    $resSoap = $client->RequestTransaction($params);
    $resAsStr = $client->__getLastResponse();
    $result = obj2array($resSoap);
    var_dump($result);
});

$app->run();