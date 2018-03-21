<?php
require 'vendor/autoload.php';
require_once 'db.php';

//header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para cuentas contables
$app->get('/lstbcos/:idempresa', function($idempresa){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, b.id AS idcuentac, CONCAT('(', b.codigo, ') ', b.nombrecta) AS nombrecta, ";
    $query.= "a.nombre, a.nocuenta, a.siglas, a.nomcuenta, a.idmoneda, CONCAT(c.nommoneda,' (',c.simbolo,')') AS descmoneda, ";
    $query.= "CONCAT(a.nombre, ' (', c.simbolo,')') AS bancomoneda, a.correlativo, c.tipocambio, CONCAT(a.nombre, ' (', c.simbolo,') (Sigue el No. ', a.correlativo,')') AS bancomonedacorrela, ";
    $query.= "CONCAT(a.nombre, ' ', c.simbolo, ' (', a.nocuenta,')') AS bmc ";
    $query.= "FROM banco a LEFT JOIN cuentac b ON b.id = a.idcuentac ";
    $query.= "LEFT JOIN moneda c ON c.id = a.idmoneda ";
    $query.= "WHERE a.idempresa = ".$idempresa." ORDER BY a.nombre";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getbco/:idbco', function($idbco){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id AS idbanco, b.id AS idcuentac, b.nombrecta, a.nombre, a.nocuenta, a.siglas, a.nomcuenta, a.idempresa, ";
    $query.= "a.idmoneda, CONCAT(c.nommoneda,' (',c.simbolo,')') AS descmoneda, ";
    $query.= "CONCAT(a.nombre, ' (', c.simbolo,')') AS bancomoneda, a.correlativo, c.tipocambio ";
    $query.= "FROM banco a LEFT JOIN cuentac b ON b.id = a.idcuentac ";
    $query.= "LEFT JOIN moneda c ON c.id = a.idmoneda ";
    $query.= "WHERE a.id = ".$idbco." ORDER BY a.nombre";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getcorrelabco/:idbco', function($idbco){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.correlativo ";
    $query.= "FROM banco a ";
    $query.= "WHERE a.id = ".$idbco;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/chkexists/:idbco/:ttrans/:num', function($idbco, $ttrans, $num){
    $db = new dbcpm();
    $data = $db->getQuery("SELECT idbanco, tipotrans, numero FROM tranban WHERE idbanco = ".$idbco." AND tipotrans = '".$ttrans."' AND numero = ".$num);
    print json_encode(['existe' => count($data) > 0 ? 1 : 0]);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "INSERT INTO banco(idempresa, idcuentac, nombre, nocuenta, siglas, nomcuenta, idmoneda, correlativo) ";
    $query.= "VALUES(".$d->idempresa.", ".$d->idcuentac.", '".$d->nombre."', '".$d->nocuenta."', '".$d->siglas."', '".$d->nomcuenta."', ".$d->idmoneda.", ".$d->correlativo.")";
    $ins = $conn->query($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE banco SET idempresa = ".$d->idempresa.", idcuentac = ".$d->idcuentac.", ";
    $query.= "nombre = '".$d->nombre."', siglas = '".$d->siglas."', nomcuenta = '".$d->nomcuenta."', idmoneda = ".$d->idmoneda.", correlativo = ".$d->correlativo." ";
    $query.= "WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM banco WHERE id = ".$d->id;
    $del = $conn->query($query);
});

#API para reportes
$app->post('/rptestcta', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $quetz = (int)$d->quetzalizado == 1;

    $query = "SELECT (SELECT IF(ISNULL(SUM(a.monto".($quetz ? " * a.tipocambio" : "").")), 0.00, SUM(a.monto".($quetz ? " * a.tipocambio" : "").")) FROM tranban a ";
	$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND ";
    $query.= "a.tipotrans IN('D','R')".(!$quetz ? " AND a.esajustedc = 0": "")." AND ";
    $query.= "(a.anulado = 0 OR a.fechaanula > '$d->falstr')) - ";
    $query.= "(SELECT IF(ISNULL(SUM(a.monto".($quetz ? " * a.tipocambio" : "").")), 0.00, SUM(a.monto".($quetz ? " * a.tipocambio" : "").")) FROM tranban a ";
	$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND ";
    $query.= "a.tipotrans IN('C','B')".(!$quetz ? " AND a.esajustedc = 0": "")." AND ";
    $query.= "(a.anulado = 0 OR a.fechaanula > '$d->falstr')) AS saldoinicial";
    $saldoinicial = $db->getOneField($query);

    $query = "SELECT b.fecha, CONCAT('(', d.abreviatura,') ', d.descripcion) AS tipo, b.numero, b.beneficiario, ";
    $query.= "IF(b.anulado = 0, b.concepto, CONCAT(b.concepto, ' (ANULADO)')) AS concepto, ";
    $query.= "IF(b.tipotrans IN('D', 'R'), IF((b.anulado = 0 OR b.fechaanula > '$d->falstr'), (b.monto".($quetz ? " * b.tipocambio" : "")."), 0.00), 0.00) AS credito, ";
    $query.= "IF(b.tipotrans IN('C', 'B'), IF((b.anulado = 0 OR b.fechaanula > '$d->falstr'), (b.monto".($quetz ? " * b.tipocambio" : "")."), 0.00), 0.00) AS debito, ";
    $query.= "0.00 AS saldo, c.id AS idbanco, ";
    $query.= "c.nombre AS banco, d.abreviatura, b.id AS idtran, b.tipocambio ";
    $query.= "FROM tranban b INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
    $query.= "WHERE c.id = ".$d->idbanco." AND b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."' ";
    $query.= !$quetz ? "AND b.esajustedc = 0 ": "";
    $query.= "ORDER BY b.fecha";
    $tran = $db->getQuery($query);

    $cant = count($tran);
    $tmp = $saldoinicial;
    for($x = 0; $x < $cant; $x++){
        $tmp = in_array($tran[$x]->abreviatura, ['D', 'R']) ? $tmp + (float)$tran[$x]->credito : $tmp - (float)$tran[$x]->debito;
        $tran[$x]->saldo = $tmp;
    };

    $query = "SELECT CONCAT('(', b.abreviatura,') ', b.descripcion) AS tipo, COUNT(a.tipotrans) AS cantidad, SUM(a.monto".($quetz ? " * a.tipocambio" : "").") AS monto, ";
    $query.= "IF(b.abreviatura IN ('D', 'R'), '(+)', '(-)') AS operacion ";
    $query.= "FROM tranban a INNER JOIN tipomovtranban b ON b.abreviatura = a.tipotrans ";
    $query.= "WHERE (a.anulado = 0 OR a.fechaanula > '$d->falstr') AND a.idbanco = ".$d->idbanco." AND a.fecha >= '".$d->fdelstr."' AND a.fecha <= '".$d->falstr."' ";
    $query.= "AND a.esajustedc = 0 ";
    $query.= "GROUP BY a.tipotrans ";
    $query.= "ORDER BY b.abreviatura";
    $resumen = $db->getQuery($query);

    if($quetz){
        $query = "SELECT CONCAT('(', b.abreviatura,') ', b.descripcion, ' - Ajuste por diferencial cambiario') AS tipo, COUNT(a.tipotrans) AS cantidad, ";
        $query.= "SUM(a.monto".($quetz ? " * a.tipocambio" : "").") AS monto, ";
        $query.= "IF(b.abreviatura IN ('D', 'R'), '(+)', '(-)') AS operacion ";
        $query.= "FROM tranban a INNER JOIN tipomovtranban b ON b.abreviatura = a.tipotrans ";
        $query.= "WHERE (a.anulado = 0 OR a.fechaanula > '$d->falstr') AND a.idbanco = ".$d->idbanco." AND a.fecha >= '".$d->fdelstr."' AND a.fecha <= '".$d->falstr."' ";
        $query.= "AND a.esajustedc = 1 ";
        $query.= "GROUP BY a.tipotrans ";
        $query.= "ORDER BY b.abreviatura";
        $ajustes = $db->getQuery($query);
        if(count($resumen) > 0){ $resumen = array_merge($resumen, $ajustes); };
    }

    $data = [
        'saldoinicial' => $saldoinicial,
        'tran' => $tran,
        'saldofinal' => $tmp,
        'resumen' => $resumen
    ];
    
    print json_encode($data);
});

$app->run();