<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para contratos
$app->get('/lstcontratos/:idempresa', function($idempresa){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idpromotor, a.idfinanciera, a.idcliente, e.id AS idtipofinanciera, g.id AS idpais, ";
    $query.= "a.fechacontrato, b.nombre AS promotor, b.telefono AS telpromotor, b.correoe AS correopromotor, ";
    $query.= "e.descripcion AS tipofinanciera, c.nombre AS financiera, f.descripcion AS titulo, ";
    $query.= "d.nombre AS cliente, d.telefono AS telcliente, d.puesto, d.empresa, d.direccion, ";
    $query.= "g.nombre AS pais, a.fhcreacion, a.creadopor, a.fhactualizacion, a.modificadopor, a.idempresa, a.correlativo, ";
    $query.= "CONCAT('GCF', LPAD(a.idcliente, 4, '0'), '-', LPAD(a.correlativo, 4, '0')) AS nocontrato ";
    $query.= "FROM contrato a INNER JOIN promotor b ON b.id = a.idpromotor INNER JOIN financiera c ON c.id = a.idfinanciera ";
    $query.= "INNER JOIN cliente d ON d.id = a.idcliente INNER JOIN tipofinanciera e ON e.id = c.idtipofinanciera ";
    $query.= "INNER JOIN titulo f ON f.id = d.idtitulo INNER JOIN pais g ON g.id = d.idpais ";
    $query.= "WHERE a.idempresa = ".$idempresa." ";
    $query.= "ORDER BY a.fechacontrato DESC, a.idcliente, a.correlativo";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/lstcontbyempcli/:idempresa/:idcliente', function($idempresa, $idcliente){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idpromotor, a.idfinanciera, a.idcliente, e.id AS idtipofinanciera, g.id AS idpais, ";
    $query.= "a.fechacontrato, b.nombre AS promotor, b.telefono AS telpromotor, b.correoe AS correopromotor, ";
    $query.= "e.descripcion AS tipofinanciera, c.nombre AS financiera, f.descripcion AS titulo, ";
    $query.= "d.nombre AS cliente, d.telefono AS telcliente, d.puesto, d.empresa, d.direccion, ";
    $query.= "g.nombre AS pais, a.fhcreacion, a.creadopor, a.fhactualizacion, a.modificadopor, a.idempresa, a.correlativo, ";
    $query.= "CONCAT('GCF', LPAD(a.idcliente, 4, '0'), '-', LPAD(a.correlativo, 4, '0')) AS nocontrato ";
    $query.= "FROM contrato a INNER JOIN promotor b ON b.id = a.idpromotor INNER JOIN financiera c ON c.id = a.idfinanciera ";
    $query.= "INNER JOIN cliente d ON d.id = a.idcliente INNER JOIN tipofinanciera e ON e.id = c.idtipofinanciera ";
    $query.= "INNER JOIN titulo f ON f.id = d.idtitulo INNER JOIN pais g ON g.id = d.idpais ";
    $query.= "WHERE a.idempresa = ".$idempresa." AND a.idcliente = ".$idcliente." ";
    $query.= "ORDER BY a.fechacontrato DESC, a.idcliente, a.correlativo";
    print $db->doSelectASJson($query);
});

$app->get('/getcontrato/:idcontrato', function($idcontrato){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idpromotor, a.idfinanciera, a.idcliente, e.id AS idtipofinanciera, g.id AS idpais, ";
    $query.= "a.fechacontrato, b.nombre AS promotor, b.telefono AS telpromotor, b.correoe AS correopromotor, ";
    $query.= "e.descripcion AS tipofinanciera, c.nombre AS financiera, f.descripcion AS titulo, ";
    $query.= "d.nombre AS cliente, d.telefono AS telcliente, d.puesto, d.empresa, d.direccion, ";
    $query.= "g.nombre AS pais, a.fhcreacion, a.creadopor, a.fhactualizacion, a.modificadopor, a.idempresa, a.correlativo ";
    $query.= "FROM contrato a INNER JOIN promotor b ON b.id = a.idpromotor INNER JOIN financiera c ON c.id = a.idfinanciera ";
    $query.= "INNER JOIN cliente d ON d.id = a.idcliente INNER JOIN tipofinanciera e ON e.id = c.idtipofinanciera ";
    $query.= "INNER JOIN titulo f ON f.id = d.idtitulo INNER JOIN pais g ON g.id = d.idpais ";
    $query.= "WHERE a.id = ".$idcontrato;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $correlativo = (int)$db->getOneField("SELECT IF(ISNULL(MAX(correlativo)), 0, MAX(correlativo)) + 1 AS correlativo FROM contrato WHERE idcliente = ".$d->idcliente);
    $query = "INSERT INTO contrato(idempresa, fechacontrato, idpromotor, idfinanciera, idcliente, fhcreacion, creadopor, correlativo) ";
    $query.= "VALUES(".$d->idempresa.", '".$d->fechacontratostr."', ".$d->idpromotor.", ".$d->idfinanciera.", ".$d->idcliente.", NOW(), '".$d->usuario."', ".$correlativo.")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE contrato SET fechacontrato = '".$d->fechacontratostr."', idpromotor = ".$d->idpromotor.", idfinanciera = ".$d->idfinanciera.", ";
    $query.= "idcliente = ".$d->idcliente.", fhactualizacion = NOW(), modificadopor = '".$d->usuario."'";
    $query.= "WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM contrato WHERE id = ".$d->id;
    $del = $conn->query($query);
});

//API para detalle de proveedores de equipo de contratos
$app->get('/lstdetproveq/:idcontrato', function($idcontrato){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcontrato, a.idprovequipo, b.nombre AS provequipo, a.cantidad, a.descequipo, a.preciounitario, a.preciotot, ";
    $query.= "a.idsector, c.descsector ";
    $query.= "FROM detequipo a INNER JOIN provequipo b ON b.id = a.idprovequipo INNER JOIN sector c ON c.id = a.idsector ";
    $query.= "WHERE a.idcontrato = ".$idcontrato." ";
    $query.= "ORDER BY c.descsector, b.nombre, a.descequipo";
    print $db->doSelectASJson($query);
});

$app->get('/getdetproveq/:iddetproveq', function($iddetproveq){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcontrato, a.idprovequipo, b.nombre AS provequipo, a.cantidad, a.descequipo, a.preciounitario, a.preciotot, ";
    $query.= "a.idsector, c.descsector ";
    $query.= "FROM detequipo a INNER JOIN provequipo b ON b.id = a.idprovequipo INNER JOIN sector c ON c.id = a.idsector ";
    $query.= "WHERE a.id = ".$iddetproveq;
    print $db->doSelectASJson($query);
});

$app->post('/ce', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO detequipo(idcontrato, idprovequipo, cantidad, descequipo, preciounitario, preciotot, idsector) ";
    $query.= "VALUES(".$d->idcontrato.", ".$d->idprovequipo.", ".$d->cantidad.", '".$d->descequipo."', ".$d->preciounitario.", ";
    $query.= $d->preciotot.", ".$d->idsector.")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/ue', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE detequipo SET idprovequipo = ".$d->idprovequipo.", cantidad = ".$d->cantidad.", descequipo = '".$d->descequipo."', ";
    $query.= "preciounitario = ".$d->preciounitario.", preciotot = ".$d->preciotot.", idsector = ".$d->idsector." ";
    $query.= "WHERE id = ".$d->id;
    $db->doQuery($query);
});

$app->post('/de', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM detequipo WHERE id = ".$d->id);
});

//API para detalle de datos específicos
$app->get('/lstdetesp/:idcontrato', function($idcontrato){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idcontrato, a.valorequipo, a.enganche, a.idperiodicidad, a.plazo, a.interes, a.idcalculopago, a.residual, ";
    $query.= "a.seguroventa, a.segurocosto, a.gastosapertura, a.deposegnrentas, a.depseg, a.otros, a.descuentoprov, a.iva, a.tasafondeo, ";
    $query.= "a.idcobroprimrenta, a.incluiriva, a.rentadiaria, a.floatingprov, a.opccompfija, a.idmoneda, b.descripcion AS periodicidad, ";
    $query.= "c.descripcion AS cobroprimrenta, CONCAT('(', d.simbolo, ') ', d.nommoneda) AS moneda, a.autorizada ";
    $query.= "FROM detdatoesp a LEFT JOIN periodicidad b ON b.id = a.idperiodicidad LEFT JOIN cobroprimrenta c ON c.id = a.idcobroprimrenta ";
    $query.= "LEFT JOIN moneda d ON d.id = a.idmoneda ";
    $query.= "WHERE a.idcontrato = ".$idcontrato." ";
    $query.= "ORDER BY a.id DESC";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getdetesp/:iddetesp', function($iddetesp){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idcontrato, a.valorequipo, a.enganche, a.idperiodicidad, a.plazo, a.interes, a.idcalculopago, a.residual, ";
    $query.= "a.seguroventa, a.segurocosto, a.gastosapertura, a.deposegnrentas, a.depseg, a.otros, a.descuentoprov, a.iva, a.tasafondeo, ";
    $query.= "a.idcobroprimrenta, a.incluiriva, a.rentadiaria, a.floatingprov, a.opccompfija, a.idmoneda, b.descripcion AS periodicidad, ";
    $query.= "c.descripcion AS cobroprimrenta, CONCAT('(', d.simbolo, ') ', d.nommoneda) AS moneda, a.autorizada ";
    $query.= "FROM detdatoesp a LEFT JOIN periodicidad b ON b.id = a.idperiodicidad LEFT JOIN cobroprimrenta c ON c.id = a.idcobroprimrenta ";
    $query.= "LEFT JOIN moneda d ON d.id = a.idmoneda ";
    $query.= "WHERE a.id = ".$iddetesp;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/cs', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO detdatoesp(";
    $query.= "idcontrato, valorequipo, enganche, idperiodicidad, plazo, interes, idcalculopago, residual, seguroventa, segurocosto, ";
    $query.= "gastosapertura, deposegnrentas, depseg, otros, descuentoprov, iva, tasafondeo, idcobroprimrenta, incluiriva, rentadiaria, ";
    $query.= "floatingprov, opccompfija, idmoneda";
    $query.= ") VALUES(";
    $query.= $d->idcontrato.", ".$d->valorequipo.", ".$d->enganche.", ".$d->idperiodicidad.", ".$d->plazo.", ";
    $query.= $d->interes.", ".$d->idcalculopago.", ".$d->residual.", ".$d->seguroventa.", ".$d->segurocosto.", ";
    $query.= $d->gastosapertura.", ".$d->deposegnrentas.", ".$d->depseg.", ".$d->otros.", ".$d->descuentoprov.", ";
    $query.= $d->iva.", ".$d->tasafondeo.", ".$d->idcobroprimrenta.", ".$d->incluiriva.", ".$d->rentadiaria.", ";
    $query.= $d->floatingprov.", ".$d->opccompfija.", ".$d->idmoneda;
    $query.= ")";
    $ins = $conn->query($query);
    $lastid = $conn->query("SELECT LAST_INSERT_ID()")->fetchColumn(0);
    print json_encode(['lastid' => $lastid]);
});

$app->post('/us', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE detdatoesp SET ";
    $query.= "idcontrato = ".$d->idcontrato.", valorequipo = ".$d->valorequipo.", enganche = ".$d->enganche.", idperiodicidad = ".$d->idperiodicidad.", plazo = ".$d->plazo.", ";
    $query.= "interes = ".$d->interes.", idcalculopago = ".$d->idcalculopago.", residual = ".$d->residual.", seguroventa = ".$d->seguroventa.", segurocosto = ".$d->segurocosto.", ";
    $query.= "gastosapertura = ".$d->gastosapertura.", deposegnrentas = ".$d->deposegnrentas.", depseg = ".$d->depseg.", otros = ".$d->otros.", descuentoprov = ".$d->descuentoprov.", ";
    $query.= "iva = ".$d->iva.", tasafondeo = ".$d->tasafondeo.", idcobroprimrenta = ".$d->idcobroprimrenta.", incluiriva = ".$d->incluiriva.", rentadiaria = ".$d->rentadiaria.", ";
    $query.= "floatingprov = ".$d->floatingprov.", opccompfija = ".$d->opccompfija.", idmoneda = ".$d->idmoneda." ";
    $query.= "WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/ds', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM detdatoesp WHERE id = ".$d->id;
    $del = $conn->query($query);
});

function rdt($porcentaje, $valor){ return (float)$porcentaje * (float)$valor / 100; }

function pmt($interest, $months, $loan) {
    $months = $months;
    $interest = $interest / 1200;
    $amount = $interest * -$loan * pow((1 + $interest), $months) / (1 - pow((1 + $interest), $months));
    //return number_format($amount, 2);
    return $amount;
}

$app->post('/autorizar', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE detdatoesp SET autorizada = 0 WHERE idcontrato = ".$d->idcontrato);
    $db->doQuery("UPDATE detdatoesp SET autorizada = 1 WHERE id = ".$d->id);


    //Calculo de cuota inicial, mensual y final
    $query = "SELECT valorequipo, gastosapertura, enganche, otros, plazo, seguroventa, interes ";
    $query.= "FROM detdatoesp ";
    $query.= "WHERE idcontrato = ".$d->idcontrato." AND autorizada = 1";
    $data = $db->getQuery($query)[0];
    $valorSinIVA = (float)$data->valorequipo / 1.12;
    $enganche = rdt($data->enganche, $valorSinIVA);
    $gastosApertura = rdt($data->gastosapertura, ($valorSinIVA - $enganche));
    $otros = rdt($data->otros, $valorSinIVA);

    $renta = pmt((float)$data->interes, (int)$data->plazo, ($valorSinIVA - $enganche));

    $cuotaFinalSinSeguro = fmod(($valorSinIVA - $enganche), (float)$data->plazo);
    $seguro = ($valorSinIVA * ((float)$data->seguroventa / 100)) / 12;

    $cuotaInicial = $gastosApertura + $enganche + $otros + (($gastosApertura + $enganche + $otros) * 0.12);
    $cuotaMensual = $renta + $seguro + (($renta + $seguro) * 0.12);
    $rentaFinal = pmt(((float)$data->interes / 12), 1, $cuotaFinalSinSeguro);
    $cuotaFinal = $rentaFinal + $seguro + (($rentaFinal + $seguro) * 0.12);

    //echo 'Valor sin IVA = '.$valorSinIVA.'<br/>Enganche = '.$enganche.'<br/>Gastos de apertura = '.$gastosApertura.'<br/>';
    //echo 'Otros = '.$otros.'<br/>Renta = '.$renta.'<br/>Seguro = '.$seguro.'<br/>';
    //echo 'Cuota mensual = '.$cuotaMensual;
    //echo 'Renta final = '.$rentaFinal.'<br/>Cuota final = '.$cuotaFinal;

    $query = "UPDATE contrato SET pagoinicial = ".$cuotaInicial.", cuotamensual = ".$cuotaMensual.", pagofinal = ".$cuotaFinal." WHERE id = ".$d->idcontrato;
    //$db->doQuery($query);
});

//API para datos de facturación
$app->get('/getfactdata/:idcontrato', function($idcontrato){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT id, nit, fechainiciofact, emailenviofact, pagoinicial, cuotamensual, pagofinal, fhactualizacion, modificadopor ";
    $query.= "FROM contrato ";
    $query.= "WHERE id = ".$idcontrato;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/uf', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE contrato SET nit = '".$d->nit."', fechainiciofact = '".$d->fechainiciofactstr."', emailenviofact = '".$d->emailenviofact."', ";
    $query.= "pagoinicial = ".$d->pagoinicial.", cuotamensual = ".$d->cuotamensual.", pagofinal = ".$d->pagofinal.", ";
    $query.= "fhactualizacion = NOW(), modificadopor = '".$d->usuario."' ";
    $query.= "WHERE id = ".$d->id;
    $db->doQuery($query);
    $query = "SELECT id, nit, fechainiciofact, emailenviofact, pagoinicial, cuotamensual, pagofinal, fhactualizacion, modificadopor ";
    $query.= "FROM contrato ";
    $query.= "WHERE id = ".$d->id;
    print $db->doSelectASJson($query);
});

$app->post('/df', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE contrato SET nit = NULL, fechainiciofact = NULL, emailenviofact = NULL, pagoinicial = 0.00, cuotamensual = 0.00, pagofinal = 0.00, ";
    $query.= "fhactualizacion = NOW(), modificadopor = '".$d->usuario."' ";
    $query.= "WHERE id = ".$d->id;
    $db->doQuery($query);
    $query = "SELECT id, nit, fechainiciofact, emailenviofact, pagoinicial, cuotamensual, pagofinal, fhactualizacion, modificadopor ";
    $query.= "FROM contrato ";
    $query.= "WHERE id = ".$d->id;
    print $db->doSelectASJson($query);
});

//API para la configuración de generación de cargos
$app->get('/lstconfcargos/:idcontrato', function($idcontrato){
    $db = new dbcpm();
    $query = "SELECT id, idcontrato, cantidad, monto, tipocargo, ordengenera FROM confcargocontrato WHERE idcontrato = $idcontrato ORDER BY ordengenera";
    $config = $db->getQuery($query);
    $cntCnf = count($config);
    if($cntCnf > 0){
        for($i = 0; $i < $cntCnf; $i++){
            $cnf = $config[$i];
            $query = "SELECT id, idconfcargocont, cantidad, descripcion, preciounitario, precio FROM detconfcargocontrato WHERE idconfcargocont = $cnf->id";
            $cnf->detalle = $db->getQuery($query);
        }
    }
    print json_encode($config);
});

$app->get('/getconfcargos/:idconfcargo', function($idconfcargo){
    $db = new dbcpm();
    $query = "SELECT id, idcontrato, cantidad, monto, tipocargo, ordengenera FROM confcargocontrato WHERE id = $idconfcargo";
    $config = $db->getQuery($query);
    $cntCnf = count($config);
    if($cntCnf > 0){
        for($i = 0; $i < $cntCnf; $i++){
            $cnf = $config[$i];
            $query = "SELECT id, idconfcargocont, cantidad, descripcion, preciounitario, precio FROM detconfcargocontrato WHERE idconfcargocont = $cnf->id";
            $cnf->detalle = $db->getQuery($query);
        }
    }
    print json_encode($config);
});

$app->post('/cc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO confcargocontrato(idcontrato, cantidad, monto, tipocargo, ordengenera) VALUES(";
    $query.= "$d->idcontrato, $d->cantidad, $d->monto, $d->tipocargo, $d->ordengenera";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/uc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE confcargocontrato SET cantidad = $d->cantidad, monto = $d->monto, tipocargo = $d->tipocargo, ordengenera = $d->ordengenera WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/dc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "DELETE FROM detconfcargocontrato WHERE idconfcargocont = $d->id";
    $db->doQuery($query);
    $query = "DELETE FROM confcargocontrato WHERE id = $d->id";
    $db->doQuery($query);
});

$app->get('/lstdetconfcargo/:idconfcargo', function($idconfcargo){
    $db = new dbcpm();
    $query = "SELECT id, idconfcargocont, cantidad, descripcion, preciounitario, precio FROM detconfcargocontrato WHERE idconfcargocont = $idconfcargo";
    print $db->doSelectASJson($query);
});

$app->get('/getdetconfcargo/:iddetconfcargo', function($iddetconfcargo){
    $db = new dbcpm();
    $query = "SELECT id, idconfcargocont, cantidad, descripcion, preciounitario, precio FROM detconfcargocontrato WHERE id = $iddetconfcargo";
    print $db->doSelectASJson($query);
});

$app->post('/cdc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO detconfcargocontrato(idconfcargocont, cantidad, descripcion, preciounitario, precio) VALUES(";
    $query.= "$d->idconfcargocont, $d->cantidad, '$d->descripcion', $d->preciounitario, $d->precio";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/udc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE detconfcargocontrato SET cantidad = $d->cantidad, descripcion = '$d->descripcion', preciounitario = $d->preciounitario, precio = $d->precio WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/ddc', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "DELETE FROM detconfcargocontrato WHERE id = $d->id";
    $db->doQuery($query);
});

//API para generación de cargos
function procDataGen($d){
    $d->id = (int)$d->id;
    $d->fechainiciofact = new DateTime($d->fechainiciofact, new DateTimeZone('America/Guatemala'));
    $d->pagoinicial = (float)$d->pagoinicial;
    $d->cuotamensual = (float)$d->cuotamensual;
    $d->pagofinal = (float)$d->pagofinal;
    $d->plazo = (int)$d->plazo;
    $d->dias = (int)$d->dias;
    return $d;
};

function insertaCargo($idcontrato, $fechacobro, $monto, $tipo, $idconfcargo, $nocuota){
    $db = new dbcpm();
    $query = "INSERT INTO cargo(idcontrato, fgeneracion, fechacobro, monto, tipo, idconfcargo, nocuota) VALUES(";
    $query.= "$idcontrato, NOW(), '$fechacobro', $monto, $tipo, $idconfcargo, $nocuota)";
    $db->doQuery($query);
};

$app->get('/chkforcargos/:idcontrato', function($idcontrato){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT COUNT(id) FROM cargo WHERE idcontrato = ".$idcontrato." GROUP BY idcontrato";
    $data = $conn->query($query)->fetchColumn(0);
    print json_encode(['yagenero' => (int)$data > 0 ? 1 : 0]);
});

/*
$app->post('/gencargos', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.fechainiciofact, a.pagoinicial, a.cuotamensual, a.pagofinal, b.plazo, c.dias ";
    $query.= "FROM contrato a INNER JOIN detdatoesp b ON a.id = b.idcontrato INNER JOIN periodicidad C ON c.id = b.idperiodicidad ";
    $query.= "WHERE a.id = ".$d->idcontrato." AND b.autorizada = 1";
    $dg = procDataGen($conn->query($query)->fetchAll(5)[0]);
    $restar = 0;

    if($dg->pagoinicial > 0){
        insertaCargo($dg->id, $dg->fechainiciofact->format('Y-m-d'), $dg->pagoinicial, 1);
        $dg->fechainiciofact->add(new DateInterval('P'.$dg->dias.'D'));
    };

    if($dg->cuotamensual > 0) {
        for ($i = 1; $i <= ($dg->plazo - $restar); $i++) {
            insertaCargo($dg->id, $dg->fechainiciofact->format('Y-m-d'), $dg->cuotamensual, 2);
            $dg->fechainiciofact->add(new DateInterval('P' . $dg->dias . 'D'));
        };
    }

    if($dg->pagofinal > 0){ insertaCargo($dg->id, $dg->fechainiciofact->format('Y-m-d'), $dg->pagofinal, 3); };

    print json_encode(['generados' => $dg->plazo, 'yagenero' => 1]);

});
*/

$app->post('/gencargos', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $query = "SELECT a.id, a.fechainiciofact, a.pagoinicial, a.cuotamensual, a.pagofinal, b.plazo, c.dias, DATE_FORMAT(a.fechainiciofact, '%d') AS dia ";
    $query.= "FROM contrato a INNER JOIN detdatoesp b ON a.id = b.idcontrato INNER JOIN periodicidad C ON c.id = b.idperiodicidad ";
    $query.= "WHERE a.id = ".$d->idcontrato." AND b.autorizada = 1";
    $dg = procDataGen($db->getQuery($query)[0]);

    $query = "SELECT id, idcontrato, cantidad, monto, tipocargo FROM confcargocontrato WHERE idcontrato = $d->idcontrato ORDER BY ordengenera, id";
    $config = $db->getQuery($query);
    $generados = 0;
    $ncuota = 1;
    $hubocuota = false;
    foreach($config as $cnf){
        $cntCobros = (int)$cnf->cantidad;
        $generados += $cntCobros;
        $escuota = (int)$cnf->tipocargo == 0;
        if($escuota){ $hubocuota = true; }
        for($i = 1; $i <= $cntCobros; $i++){
            //insertaCargo($dg->id, $dg->fechainiciofact->format('Y-m-d'), $cnf->monto, 2, $cnf->id, $escuota ? $ncuota : 0);
            insertaCargo($dg->id, $dg->fechainiciofact->format('Y-m-'.$dg->dia), $cnf->monto, 2, $cnf->id, $escuota ? $ncuota : 0);
            if($escuota || $hubocuota){ 
                $ncuota += 1;
                //$dg->fechainiciofact->add(new DateInterval('P' . $dg->dias . 'D'));
                $dg->fechainiciofact->add(new DateInterval('P1M'));
            }            
        }
    }

    print json_encode(['generados' => $generados, 'yagenero' => 1]);
});

$app->get('/lstcargos/:idcontrato', function($idcontrato){
    $db = new dbcpm();

    $query = "SELECT a.nocuota AS norenta, a.id, a.fechacobro, c.simbolo AS moneda, a.monto, a.facturado, a.idfactura ";
    $query.= "FROM cargo a INNER JOIN detdatoesp b ON a.idcontrato = b.idcontrato INNER JOIN moneda c ON c.id = b.idmoneda ";
    $query.= "WHERE a.idcontrato = $idcontrato AND b.autorizada = 1 ";
    $query.= "ORDER BY a.fechacobro";
    print $db->doSelectASJson($query);
});

$app->post('/setpagado', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $query = "UPDATE cargo SET facturado = $d->facturado WHERE id = $d->id";
    $db->doQuery($query);
});


$app->run();