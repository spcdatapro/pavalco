<?php
require 'vendor/autoload.php';
require_once 'db.php';

//header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para recibos de clientes
$app->get('/lstreciboscli/:idempresa', function($idempresa){
    $db = new dbcpm();
    $query = "SELECT a.id, a.fecha, a.fechacrea, a.idcliente, a.espropio, a.idtranban, a.anulado, a.idrazonanulacion, a.fechaanula, b.nombre AS cliente, c.tipotrans, c.numero, e.nombre, ";
    $query.= "f.simbolo, c.monto, a.idempresa, d.razon ";
    $query.= "FROM recibocli a INNER JOIN cliente b ON b.id = a.idcliente LEFT JOIN tranban c ON c.id = a.idtranban LEFT JOIN razonanulacion d ON d.id = a.idrazonanulacion ";
    $query.= "LEFT JOIN banco e ON e.id = c.idbanco LEFT JOIN moneda f ON f.id = e.idmoneda ";
    $query.= "WHERE a.idempresa = $idempresa ";
    $query.= "ORDER BY a.fecha DESC";
    print $db->doSelectASJson($query);
});

$app->get('/getrecibocli/:idrecibo', function($idrecibo){
    $db = new dbcpm();
    $query = "SELECT a.id, a.fecha, a.fechacrea, a.idcliente, a.espropio, a.idtranban, a.anulado, a.idrazonanulacion, a.fechaanula, b.nombre AS cliente, c.tipotrans, c.numero, e.nombre, ";
    $query.= "f.simbolo, c.monto, a.idempresa, d.razon ";
    $query.= "FROM recibocli a INNER JOIN cliente b ON b.id = a.idcliente LEFT JOIN tranban c ON c.id = a.idtranban LEFT JOIN razonanulacion d ON d.id = a.idrazonanulacion ";
    $query.= "LEFT JOIN banco e ON e.id = c.idbanco LEFT JOIN moneda f ON f.id = e.idmoneda ";
    $query.= "WHERE a.id = $idrecibo";
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO recibocli(idempresa, fecha, fechacrea, idcliente, espropio, idtranban) VALUES($d->idempresa,'$d->fechastr', NOW(), $d->idcliente, $d->espropio, $d->idtranban)";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE recibocli SET fecha = '$d->fechastr', idcliente = $d->idcliente, espropio = $d->espropio, idtranban = $d->idtranban WHERE id = $d->id");
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM detallecontable WHERE origen = 8 AND idorigen = $d->id");
    $db->doQuery("DELETE FROM detcobroventa WHERE idrecibocli = $d->id");
    $db->doQuery("DELETE FROM recibocli WHERE id = ".$d->id);
});

$app->post('/anula', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE recibocli SET anulado = 1, idrazonanulacion = $d->idrazonanulacion, fechaanula = '$d->fechaanulastr' WHERE id = $d->id";
    $db->doQuery($query);
    $query = "UPDATE detallecontable SET activada = 0, anulado = 1 WHERE origen = 8 AND idorigen = $d->id";
    $db->doQuery($query);
});

$app->get('/lsttranban/:idempresa', function($idempresa){
    $db = new dbcpm();
    $query = "SELECT a.id, a.fecha, b.nombre, a.tipotrans, a.numero, c.simbolo, a.monto ";
    $query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco INNER JOIN moneda c ON c.id = b.idmoneda ";
    $query.= "WHERE a.tipotrans IN('D', 'R') AND b.idempresa = $idempresa ";
    $query.= "ORDER BY a.fecha, b.nombre, a.tipotrans, a.numero";
    //echo $query;
    print $db->doSelectASJson($query);
});

$app->get('/docspend/:idempresa/:idcliente', function($idempresa, $idcliente){
    $db = new dbcpm();
    $query = "SELECT a.id, c.siglas, a.serie, a.numero, a.fecha, b.simbolo, a.total, IF(ISNULL(d.cobrado), 0.00, d.cobrado) AS cobrado, (a.total - IF(ISNULL(d.cobrado), 0.00, d.cobrado)) AS saldo, ";
    $query.= "CONCAT(c.siglas, ' - ', a.serie, ' ', a.numero, ' - ', DATE_FORMAT(a.fecha, '%d/%m/%Y'), ' - Total: ', b.simbolo, ' ', a.total,  ' - Abonado: ', ";
    $query.= "IF(ISNULL(d.cobrado), 0.00, d.cobrado),  ' - Saldo: ',(a.total - IF(ISNULL(d.cobrado), 0.00, d.cobrado))) AS cadena ";
    $query.= "FROM factura a INNER JOIN moneda b ON b.id = a.idmoneda INNER JOIN tipofactura c ON c.id = a.idtipofactura ";
    $query.= "LEFT JOIN (SELECT a.idfactura, SUM(a.monto) AS cobrado FROM detcobroventa a INNER JOIN recibocli b ON b.id = a.idrecibocli WHERE b.anulado = 0 GROUP BY a.idfactura) d ON a.id = d.idfactura ";
    $query.= "WHERE a.idempresa = $idempresa AND a.pagada = 0 AND (a.total - IF(ISNULL(d.cobrado), 0.00, d.cobrado)) > 0 AND a.idcliente = $idcliente ";
    $query.= "ORDER BY a.fecha";
    print $db->doSelectASJson($query);
});

//API para detalle de recibos de clientes
$app->get('/lstdetreccli/:idrecibo', function($idrecibo){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idfactura, a.idrecibocli, d.siglas, b.serie, b.numero, b.fecha, c.simbolo, b.total, a.monto, a.interes ";
    $query.= "FROM detcobroventa a INNER JOIN factura b ON b.id = a.idfactura INNER JOIN moneda c ON c.id = b.idmoneda INNER JOIN tipofactura d ON d.id = b.idtipofactura ";
    $query.= "WHERE a.idrecibocli = $idrecibo ";
    $query.= "ORDER BY b.fecha";
    print $db->doSelectASJson($query);
});

$app->get('/getdetreccli/:iddetrec', function($iddetrec){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idfactura, a.idrecibocli, d.siglas, b.serie, b.numero, b.fecha, c.simbolo, b.total, a.monto, a.interes ";
    $query.= "FROM detcobroventa a INNER JOIN factura b ON b.id = a.idfactura INNER JOIN moneda c ON c.id = b.idmoneda INNER JOIN tipofactura d ON d.id = b.idtipofactura ";
    $query.= "WHERE a.id = $iddetrec";
    print $db->doSelectASJson($query);
});

$app->post('/cd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO detcobroventa(idfactura, idrecibocli, monto, interes, esrecprov) VALUES($d->idfactura, $d->idrecibocli, $d->monto, $d->interes, 1)";
    $db->doQuery($query);

    //Poner como pagada la factura si su saldo es 0.00
    $query = "SELECT (a.total - IF(ISNULL(d.cobrado), 0.00, d.cobrado)) AS saldo FROM factura a ";
    $query.= "LEFT JOIN (SELECT a.idfactura, SUM(a.monto) AS cobrado FROM detcobroventa a INNER JOIN recibocli b ON b.id = a.idrecibocli WHERE b.anulado = 0 GROUP BY a.idfactura) d ON a.id = d.idfactura ";
    $query.= "WHERE a.id = $d->idfactura LIMIT 1";
    $haypendiente = (float)$db->getOneField($query) > 0.00;
    if(!$haypendiente){
        $query = "UPDATE factura SET pagada = 1, fechapago = (SELECT fecha FROM recibocli WHERE id = $d->idrecibocli) WHERE id = $d->idfactura";
        $db->doQuery($query);
    }
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/ud', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE detcobroventa SET monto = $d->monto, interes = $d->interes WHERE id = $d->id");
});

$app->post('/dd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM detcobroventa WHERE id = $d->id");

    //Poner como NO pagada la factura
    $query = "SELECT (a.total - IF(ISNULL(d.cobrado), 0.00, d.cobrado)) AS saldo FROM factura a ";
    $query.= "LEFT JOIN (SELECT a.idfactura, SUM(a.monto) AS cobrado FROM detcobroventa a INNER JOIN recibocli b ON b.id = a.idrecibocli WHERE b.anulado = 0 GROUP BY a.idfactura) d ON a.id = d.idfactura ";
    $query.= "WHERE a.id = $d->idfactura LIMIT 1";
    $haypendiente = (float)$db->getOneField($query) > 0.00;
    if($haypendiente){
        $query = "UPDATE factura SET pagada = 0, fechapago = NULL WHERE id = $d->idfactura";
        $db->doQuery($query);
    }
});

$app->run();