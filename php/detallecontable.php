<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para detallecontable
$app->get('/lstdetcont/:origen/:idorigen', function($origen, $idorigen){
    $db = new dbcpm();

    $query = "SELECT a.id, a.origen, a.idorigen, a.idcuenta, CONCAT('(', b.codigo, ') ', b.nombrecta) AS desccuentacont, a.debe, a.haber, a.conceptomayor, a.activada, a.idcontrato, ";
    $query.= "CONCAT('GCF', LPAD(c.idcliente, 4, '0'), '-', LPAD(c.correlativo, 4, '0')) AS nocontrato ";
    $query.= "FROM detallecontable a INNER JOIN cuentac b ON b.id = a.idcuenta ";
    $query.= "LEFT JOIN contrato c ON c.id = a.idcontrato ";
    $query.= "WHERE a.origen = ".$origen." AND a.idorigen = ".$idorigen." ";
    $query.= "ORDER BY a.debe DESC, b.precedencia DESC";
    $res1 = $db->getQuery($query);

    $query = "SELECT 0 AS id, origen, idorigen, IF(SUM(debe) = SUM(haber), 0, -1) AS idcuenta, 'Total de partida' AS desccuentacont, ";
    $query.= "SUM(debe) AS debe, SUM(haber) AS haber, IF(SUM(debe) = SUM(haber), 'Partida cuadrada', 'Partida descuadrada') AS conceptomayor, 1 AS activada, 0 as idcontrato, '' AS nocontrato ";
    $query.= "FROM detallecontable WHERE origen = ".$origen." AND idorigen = ".$idorigen." ";
    $query.= "GROUP BY origen, idorigen";
    $res2 = $db->getQuery($query);

    if(count($res1) > 0){ array_push($res1, $res2[0]); }
    print json_encode($res1);
});

$app->get('/getdetcont/:iddetcont', function($iddetcont){
    $db = new dbcpm();
    $query = "SELECT a.id, a.origen, a.idorigen, a.idcuenta, CONCAT('(', b.codigo, ') ', b.nombrecta) AS desccuentacont, ";
    $query.= "a.debe, a.haber, a.conceptomayor, a.activada, a.idcontrato, ";
    $query.= "CONCAT('GCF', LPAD(c.idcliente, 4, '0'), '-', LPAD(c.correlativo, 4, '0')) AS nocontrato ";
    $query.= "FROM detallecontable a INNER JOIN cuentac b ON b.id = a.idcuenta ";
    $query.= "LEFT JOIN contrato c ON c.id = a.idcontrato ";
    $query.= "WHERE a.id = ".$iddetcont;
    print $db->doSelectASJson($query);
});

$app->get('/cuadrada/:origen/:idorigen', function($origen, $idorigen){
    $db = new dbcpm();
    print json_encode(['cuadrada' => (int)$db->getOneField("SELECT IF(SUM(debe) = SUM(haber), 0, -1) FROM detallecontable WHERE origen = $origen AND idorigen = $idorigen GROUP BY origen, idorigen")]);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor".(isset($d->activada) ? ", activada" : "").(isset($d->idcontrato) ? ", idcontrato" : "").") ";
    $query.= "VALUES($d->origen, $d->idorigen, $d->idcuenta, $d->debe, $d->haber, '$d->conceptomayor'";
    $query.= isset($d->activada) ? ", ".$d->activada : "";
    $query.= isset($d->idcontrato) ? ", ".$d->idcontrato: "";
    $query.=")";
    $db->doQuery($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE detallecontable SET idcuenta = ".$d->idcuenta.", debe = ".$d->debe.", haber = ".$d->haber.", ";
    $query.= "conceptomayor = '".$d->conceptomayor."' ";
    $query.= isset($d->activada) ? ", activada = ".$d->activada." " : "";
    $query.="WHERE id = ".$d->id;
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM detallecontable WHERE id = ".$d->id);
});

$app->post('/rptdetcontfact', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();

    $fProv = "AND c.idproveedor = ".$d->idprov." ";
    $fSerie = "AND c.serie = '".$d->serie."' ";
    $fDoc = "AND c.documento = ".$d->documento." ";
    $fDel = "AND c.fechaingreso >= '".$d->fdelstr."' ";
    $fAl = "AND c.fechaingreso <= '".$d->falstr."' ";

    $query = "SELECT d.id AS idproveedor, d.nit, d.nombre, c.id AS idcompra, CONCAT(c.serie, '-',c.documento) AS documento, ";
    $query.= "c.fechaingreso, c.fechapago, c.totfact, b.codigo, b.nombrecta,  a.debe, a.haber, a.conceptomayor ";
    $query.= "FROM detallecontable a INNER JOIN cuentac b ON b.id = a.idcuenta INNER JOIN compra c ON c.id = a.idorigen ";
    $query.= "INNER JOIN proveedor d ON d.id = c.idproveedor ";
    $query.= "WHERE a.origen = 2 ";
    $query.= (int)$d->idprov > 0 ? $fProv : "";
    $query.= $d->serie != '' ? $fSerie : "";
    $query.= (int)$d->documento > 0 ? $fDoc : "";
    $query.= $d->fdelstr != '' ? $fDel : "";
    $query.= $d->falstr != '' ? $fAl : "";
    $query.= "ORDER BY d.nombre, c.id, a.debe DESC, b.codigo";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/rptdetcontdocs', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();

    $fBco = "AND c.id = ".$d->idbanco." ";
    $fTipo = "AND d.abreviatura = '".$d->abreviatura."' ";
    $fDel = "AND b.fecha >= '".$d->fdelstr."' ";
    $fAl = "AND b.fecha <= '".$d->falstr."' ";

    $query = "SELECT c.id AS idbanco, c.nombre AS banco, d.abreviatura, CONCAT('(', d.abreviatura,') ', d.descripcion) AS tipo, ";
    $query.= "b.id AS idtran, b.fecha, b.numero, b.beneficiario, b.concepto, b.monto, e.codigo, e.nombrecta, a.debe, a.haber, a.conceptomayor ";
    $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco ";
    $query.= "INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans INNER JOIN cuentac e ON e.id = a.idcuenta ";
    $query.= "WHERE a.origen = 1 AND c.idempresa = ".$d->idempresa." ";
    $query.= (int)$d->idbanco > 0 ? $fBco : "";
    $query.= $d->abreviatura != '' ? $fTipo : "";
    $query.= $d->fdelstr != '' ? $fDel : "";
    $query.= $d->falstr != '' ? $fAl : "";
    $query.= "ORDER BY c.nombre, d.abreviatura, b.numero, a.debe DESC";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});


$app->run();