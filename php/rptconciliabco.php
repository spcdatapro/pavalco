<?php
require 'vendor/autoload.php';
require_once 'db.php';

//header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/rptconciliabco', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conciliacion = new stdClass();

    //Datos del banco
    $query = "SELECT a.nombre, b.simbolo, $d->saldobco AS saldobco, a.nocuenta, c.codigo AS cuentacont ";
    $query.= "FROM banco a INNER JOIN moneda b ON b.id = a.idmoneda INNER JOIN cuentac c ON c.id = a.idcuentac ";
    $query.= "WHERE a.id = $d->idbanco";
    $conciliacion->banco = $db->getQuery($query)[0];
    $rango = $db->getQuery("SELECT DATE_FORMAT('$d->fdelstr', '%d/%m/%Y') AS del, DATE_FORMAT('$d->falstr', '%d/%m/%Y') AS al")[0];
    $conciliacion->banco->del = $rango->del;
    $conciliacion->banco->al = $rango->al;

    //Calculo de saldo inicial
    $query = "SELECT (SELECT IF(ISNULL(SUM(a.monto)), 0.00, SUM(a.monto)) FROM tranban a WHERE a.anulado = 0 AND a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('D','R')) - ";
    $query.= "(SELECT IF(ISNULL(SUM(a.monto)), 0.00, SUM(a.monto)) FROM tranban a ";
    $query.= "WHERE a.anulado = 0 AND a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('C','B')) AS saldoinicial";
    $conciliacion->saldoinicial = (float)$db->getOneField($query);

    //Documentos no operados => tranban.operado = 0 (que no aparecen en el estado de cuentas del banco)
    $query = "SELECT b.fecha, CONCAT('(', d.abreviatura,') ', d.descripcion) AS tipo, b.numero, b.beneficiario, b.concepto, ";
    $query.= "IF(b.tipotrans IN('D', 'R'), b.monto, 0.00) AS credito, IF(b.tipotrans IN('C', 'B'), b.monto, 0.00) AS debito, 0.00 AS saldo, c.id AS idbanco, ";
    $query.= "c.nombre AS banco, d.abreviatura, b.id AS idtran, d.descripcion AS tipodesc ";
    $query.= "FROM tranban b INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
    $query.= "WHERE b.anulado = 0 AND c.id = $d->idbanco AND (b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') AND ";
    $query.= "(ISNULL(b.fechaoperado) OR b.fechaoperado > '$d->falstr') ";
    $query.= "ORDER BY b.fecha";

    $conciliacion->entransito = $db->getQuery($query);

    $cant = count($conciliacion->entransito);
    $conciliacion->sfentransito = $conciliacion->saldoinicial;
    for($x = 0; $x < $cant; $x++){
        $conciliacion->sfentransito = in_array($conciliacion->entransito[$x]->abreviatura, ['D', 'R']) ? $conciliacion->sfentransito + (float)$conciliacion->entransito[$x]->credito : $conciliacion->sfentransito - (float)$conciliacion->entransito[$x]->debito;
        $conciliacion->entransito[$x]->saldo = $conciliacion->sfentransito;
    };

    //Documentos operados => tranban.operado = 1 (que ya aparecen en el estado de cuentas del banco)
    $query = "SELECT b.fecha, CONCAT('(', d.abreviatura,') ', d.descripcion) AS tipo, b.numero, b.beneficiario, b.concepto, ";
    $query.= "IF(b.tipotrans IN('D', 'R'), b.monto, 0.00) AS credito, IF(b.tipotrans IN('C', 'B'), b.monto, 0.00) AS debito, 0.00 AS saldo, c.id AS idbanco, ";
    $query.= "c.nombre AS banco, d.abreviatura, b.id AS idtran ";
    $query.= "FROM tranban b INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
    $query.= "WHERE b.anulado = 0 AND c.id = $d->idbanco AND ((b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') AND ";
    $query.= "(NOT ISNULL(b.fechaoperado) AND b.fechaoperado <= '$d->falstr')) OR ";
    $query.= "((b.fecha <= '$d->fdelstr' OR b.fecha >= '$d->falstr') AND (b.fechaoperado >= '$d->fdelstr' AND b.fechaoperado <= '$d->falstr')) ";
    $query.= "ORDER BY b.fecha";

    $conciliacion->operados = $db->getQuery($query);

    $cant = count($conciliacion->operados);
    $conciliacion->sfoperados = $conciliacion->sfentransito;
    for($x = 0; $x < $cant; $x++){
        $conciliacion->sfoperados = in_array($conciliacion->operados[$x]->abreviatura, ['D', 'R']) ? $conciliacion->sfoperados + (float)$conciliacion->operados[$x]->credito : $conciliacion->sfoperados - (float)$conciliacion->operados[$x]->debito;
        $conciliacion->operados[$x]->saldo = $conciliacion->sfoperados;
    };

    $conciliacion->saldofinal = $conciliacion->sfoperados;

    $conciliacion->comparacion = $conciliacion->saldofinal === (float)$d->saldobco ? 'El saldo según el banco cuadra con el saldo según contabilidad.' :
        'El saldo según el banco no cuadra con el saldo según contabilidad por '.$conciliacion->banco->simbolo.' '.round((float)$d->saldobco - $conciliacion->saldofinal, 2);

    print json_encode($conciliacion);
});

//Agregado el 09022017 para compaginarlo mejor con la contabilidad y no según documentos
function saldoSegunContabilidad($d){
    $db = new dbcpm();
    $banco = $db->getQuery("SELECT a.idempresa, b.codigo FROM banco a INNER JOIN cuentac b ON b.id = a.idcuentac WHERE a.id = $d->idbanco LIMIT 1")[0];
    $d->idempresa = $banco->idempresa;
    $origenes = ['tranban' => 1, 'compra' => 2, 'venta' => 3, 'directa' => 4, 'reembolso' => 5, 'contrato' => 6, 'recprov' => 7, 'reccli' => 8, 'liquidadoc' => 9];
    $saldo = 0.00;
    foreach($origenes as $k => $v){
        $query = getSelect($v, $d, true, $banco->codigo);
        $result = $db->getQuery($query);
        if(count($result) > 0){
            $saldo += (float)$result[0]->anterior;
        }
    }
    return round($saldo, 2);
}

//Esta función es halada del reporte de balance general, que es el parámetro de comparación de GCF
function getSelect($cual, $d, $enrango, $ini){
    $query = "";
    switch($cual){
        case 1:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco INNER JOIN cuentac d ON d.id = a.idcuenta ";
            $query.= "WHERE a.origen = 1 AND a.activada = 1 AND FILTROFECHA AND c.idempresa = ".$d->idempresa." AND d.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ?
                "((b.anulado = 0 AND b.fecha <= '$d->falstr') OR (b.anulado = 1 AND b.fecha <= '$d->falstr' AND b.fechaanula > '$d->falstr'))" :
                "((b.anulado = 0 AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') OR (b.anulado = 1 AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr' AND b.fechaanula > '$d->falstr'))"
            ), $query);
            break;
        case 2:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN compra b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
            $query.= "WHERE a.origen = 2 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND b.idreembolso = 0 AND c.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ? "b.fechaingreso <= '".$d->falstr."'" : "b.fechaingreso >= '".$d->fdelstr."' AND b.fechaingreso <= '".$d->falstr."'"), $query);
            break;
        case 3:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN factura b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
            $query.= "WHERE a.origen = 3 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND c.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ? "b.fecha <= '".$d->falstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 4:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN directa b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
            $query.= "WHERE a.origen = 4 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND c.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ? "b.fecha <= '".$d->falstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 5:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN reembolso b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
            $query.= "WHERE a.origen = 5 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa."  AND b.estatus = 2 AND c.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ? "b.ffin <= '".$d->falstr."'" : "b.ffin >= '".$d->fdelstr."' AND b.ffin <= '".$d->falstr."'"), $query);
            break;
        case 6:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN contrato b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
            $query.= "WHERE a.origen = 6 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND c.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ? "b.fechacontrato <= '".$d->falstr."'" : "b.fechacontrato >= '".$d->fdelstr."' AND b.fechacontrato <= '".$d->falstr."'"), $query);
            break;
        case 7:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN reciboprov b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
            $query.= "WHERE a.origen = 7 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND c.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ? "b.fecha <= '".$d->falstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 8:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN recibocli b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
            $query.= "WHERE a.origen = 8 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND c.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ? "b.fecha <= '".$d->falstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 9:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco INNER JOIN cuentac d ON d.id = a.idcuenta ";
            $query.= "WHERE a.origen = 9 AND a.activada = 1 AND FILTROFECHA AND c.idempresa = ".$d->idempresa." AND d.codigo LIKE '".$ini."%' ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", ($enrango ?
                "((b.anulado = 0 AND b.fechaliquida <= '$d->falstr') OR (b.anulado = 1 AND b.fechaliquida <= '$d->falstr' AND b.fechaanula > '$d->falstr'))" :
                "((b.anulado = 0 AND b.fechaliquida >= '$d->fdelstr' AND b.fechaliquida <= '$d->falstr') OR (b.anulado = 1 AND b.fechaliquida >= '$d->fdelstr' AND b.fechaliquida <= '$d->falstr' AND b.fechaanula > '$d->falstr'))"
            ), $query);
            break;
    }
    return $query;
}
//Fin de lo agregado el 09022017

function calcSaldoConta($d){
    $db = new dbcpm();

    $conciliacion = new stdClass();

    //Calculo de saldo inicial
    $query = "SELECT ";
    $query.= "(SELECT IF(ISNULL(SUM(a.monto * a.tipocambio)), 0.00, ROUND(SUM(a.monto * a.tipocambio), 2)) FROM tranban a ";    
	$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('D','R') AND (a.fechaoperado IS NOT NULL OR a.fechaoperado > '$d->falstr') AND (a.anulado = 0 OR a.fechaanula > '$d->falstr')) - ";
    $query.= "(SELECT IF(ISNULL(SUM(a.monto * a.tipocambio)), 0.00, ROUND(SUM(a.monto * a.tipocambio), 2)) FROM tranban a ";    
	$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('C','B') AND (a.fechaoperado IS NOT NULL OR a.fechaoperado > '$d->falstr') AND (a.anulado = 0 OR a.fechaanula > '$d->falstr')) AS saldoinicial";
    $conciliacion->saldoinicial = (float)$db->getOneField($query);

    //Documentos no operados => tranban.operado = 0 (que no aparecen en el estado de cuentas del banco)
    $query = "SELECT b.fecha, CONCAT('(', d.abreviatura,') ', d.descripcion) AS tipo, b.numero, b.beneficiario, b.concepto, ";
    $query.= "IF(b.tipotrans IN('D', 'R'), ROUND((b.monto * b.tipocambio), 2), 0.00) AS credito, ";
    $query.= "IF(b.tipotrans IN('C', 'B'), ROUND((b.monto * b.tipocambio), 2), 0.00) AS debito, 0.00 AS saldo, c.id AS idbanco, ";
    $query.= "c.nombre AS banco, d.abreviatura, b.id AS idtran ";
    $query.= "FROM tranban b INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
    $query.= "WHERE c.id = $d->idbanco AND (";

    $query.= "((b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') AND (ISNULL(b.fechaoperado) OR b.fechaoperado > '$d->falstr')) ";
    $query.= "OR ";
    $query.= "((b.fecha < '$d->fdelstr') AND (ISNULL(b.fechaoperado) OR b.fechaoperado > '$d->falstr')) ";

    //$query.= ") AND (b.anulado = 0 OR b.fechaanula >= '$d->fdelstr') ORDER BY b.fecha";
	$query.= ") AND (b.anulado = 0 OR b.fechaanula > '$d->falstr') ORDER BY b.fecha";
    $conciliacion->entransito = $db->getQuery($query);

    $cant = count($conciliacion->entransito);
    $conciliacion->sfentransito = $conciliacion->saldoinicial;
    for($x = 0; $x < $cant; $x++){
        $conciliacion->sfentransito = in_array($conciliacion->entransito[$x]->abreviatura, ['D', 'R']) ? $conciliacion->sfentransito + (float)$conciliacion->entransito[$x]->credito : $conciliacion->sfentransito - (float)$conciliacion->entransito[$x]->debito;
        $conciliacion->entransito[$x]->saldo = $conciliacion->sfentransito;
    };

    //Documentos operados => tranban.operado = 1 (que ya aparecen en el estado de cuentas del banco)
    $query = "SELECT b.fecha, CONCAT('(', d.abreviatura,') ', d.descripcion) AS tipo, b.numero, b.beneficiario, b.concepto, ";
    $query.= "IF(b.tipotrans IN('D', 'R'), ROUND((b.monto * b.tipocambio), 2), 0.00) AS credito, ";
    $query.= "IF(b.tipotrans IN('C', 'B'), ROUND((b.monto * b.tipocambio), 2), 0.00) AS debito, 0.00 AS saldo, c.id AS idbanco, ";
    $query.= "c.nombre AS banco, d.abreviatura, b.id AS idtran ";
    $query.= "FROM tranban b INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
    $query.= "WHERE c.id = $d->idbanco AND (((b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') AND ";
    $query.= "(NOT ISNULL(b.fechaoperado) AND b.fechaoperado <= '$d->falstr'))) AND (b.anulado = 0 OR b.fechaanula >= '$d->fdelstr') ";
    //$query.= "OR ";
    //$query.= "((b.fecha <= '$d->fdelstr' OR b.fecha >= '$d->falstr') AND (b.fechaoperado >= '$d->fdelstr' AND b.fechaoperado <= '$d->falstr'))) ";
    $query.= "ORDER BY b.fecha";

    $conciliacion->operados = $db->getQuery($query);

    $cant = count($conciliacion->operados);
    $conciliacion->sfoperados = $conciliacion->sfentransito;
    for($x = 0; $x < $cant; $x++){
        $conciliacion->sfoperados = in_array($conciliacion->operados[$x]->abreviatura, ['D', 'R']) ? $conciliacion->sfoperados + (float)$conciliacion->operados[$x]->credito : $conciliacion->sfoperados - (float)$conciliacion->operados[$x]->debito;
        $conciliacion->operados[$x]->saldo = $conciliacion->sfoperados;
    };

    $conciliacion->saldofinal = $conciliacion->sfoperados;

    //return ['docspend' => $conciliacion->entransito, 'saldoconta' => $conciliacion->saldofinal];
    return ['docspend' => $conciliacion->entransito, 'saldoconta' => saldoSegunContabilidad($d)];
}

$app->post('/rptconciliabcores', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conciliacion = new stdClass();

    //Datos del banco
    $query = "SELECT a.nombre, b.simbolo, $d->saldobco AS saldobco, a.nocuenta, c.codigo AS cuentacont ";
    $query.= "FROM banco a INNER JOIN moneda b ON b.id = a.idmoneda INNER JOIN cuentac c ON c.id = a.idcuentac ";
    $query.= "WHERE a.id = $d->idbanco";
    $conciliacion->banco = $db->getQuery($query)[0];
    $rango = $db->getQuery("SELECT DATE_FORMAT('$d->fdelstr', '%d/%m/%Y') AS del, DATE_FORMAT('$d->falstr', '%d/%m/%Y') AS al")[0];
    $conciliacion->banco->del = $rango->del;
    $conciliacion->banco->al = $rango->al;
    $conciliacion->banco->saldobco = round((float)$d->saldobco, 2);

    $temp = calcSaldoConta($d);
    $conciliacion->saldoconta = $temp['saldoconta'];
    $conciliacion->detdocspend = $temp['docspend'];

    //Calculo de saldo inicial
    $query = "SELECT ";
    $query.= "(SELECT IF(ISNULL(SUM(a.monto * a.tipocambio)), 0.00, ROUND(SUM(a.monto * a.tipocambio), 2)) FROM tranban a ";
    //$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('D','R') AND (a.anulado = 0 OR a.fechaanula >= '$d->fdelstr')) - ";
	$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('D','R') AND (a.anulado = 0 OR a.fechaanula > '$d->falstr')) - ";
    $query.= "(SELECT IF(ISNULL(SUM(a.monto * a.tipocambio)), 0.00, ROUND(SUM(a.monto * a.tipocambio), 2)) FROM tranban a ";
    //$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('C','B') AND (a.anulado = 0 OR a.fechaanula >= '$d->fdelstr')) AS saldoinicial";
	$query.= "WHERE a.idbanco = $d->idbanco AND a.fecha < '$d->fdelstr' AND a.tipotrans IN('C','B') AND (a.anulado = 0 OR a.fechaanula > '$d->falstr')) AS saldoinicial";
    $conciliacion->saldoinicial = (float)$db->getOneField($query);

    //Documentos no operados (que no aparecen en el estado de cuentas del banco) y que NO sean ajustes por diferencial cambiario
    $conciliacion->entransito = [];
    $query = "SELECT COUNT(b.tipotrans) AS cantTipo, CONCAT('(', d.abreviatura,') ', d.descripcion) AS tipo, ROUND(SUM(b.monto * b.tipocambio), 2) AS monto, c.id AS idbanco, c.nombre AS banco, ";
    $query.= "IF(b.tipotrans IN('D', 'R'), 1, 0) AS operacion ";
    $query.= "FROM tranban b INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
    $query.= "WHERE b.esajustedc = 0 AND c.id = $d->idbanco AND (";
    $query.= "((b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') AND (ISNULL(b.fechaoperado) OR b.fechaoperado > '$d->falstr')) ";
    $query.= "OR ";
    $query.= "((b.fecha < '$d->fdelstr') AND (ISNULL(b.fechaoperado) OR b.fechaoperado > '$d->falstr')) ";
	$query.= ") AND (b.anulado = 0 OR b.fechaanula > '$d->falstr') GROUP BY b.tipotrans ";
    $query.= "ORDER BY d.abreviatura";
    $conciliacion->entransito = $db->getQuery($query);

    //Documentos no operados (que no aparecen en el estado de cuentas del banco) y que SI sean ajustes por diferencial cambiario
    $query = "SELECT COUNT(b.tipotrans) AS cantTipo, CONCAT('(', d.abreviatura,') ', d.descripcion, ' - Ajuste por diferencial cambiario') AS tipo, ROUND(SUM(b.monto * b.tipocambio), 2) AS monto, ";
    $query.= "c.id AS idbanco, c.nombre AS banco, ";
    $query.= "IF(b.tipotrans IN('D', 'R'), 1, 0) AS operacion ";
    $query.= "FROM tranban b INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
    $query.= "WHERE b.esajustedc = 1 AND c.id = $d->idbanco AND (";
    $query.= "((b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') AND (ISNULL(b.fechaoperado) OR b.fechaoperado > '$d->falstr')) ";
    $query.= "OR ";
    $query.= "((b.fecha < '$d->fdelstr') AND (ISNULL(b.fechaoperado) OR b.fechaoperado > '$d->falstr')) ";
    $query.= ") AND (b.anulado = 0 OR b.fechaanula > '$d->falstr') GROUP BY b.tipotrans ";
    $query.= "ORDER BY d.abreviatura";
    $ajuste = $db->getQuery($query);

    if(count($ajuste) > 0){ $conciliacion->entransito = array_merge($conciliacion->entransito, $ajuste); }



    $cant = count($conciliacion->entransito);
    $conciliacion->saldofinal = $conciliacion->saldoinicial;
    $conciliacion->diferencia = $conciliacion->saldoconta;
    for($x = 0; $x < $cant; $x++){
        $conciliacion->saldofinal = (int)$conciliacion->entransito[$x]->operacion == 1 ?
            $conciliacion->saldofinal + (float)$conciliacion->entransito[$x]->monto :
            $conciliacion->saldofinal - (float)$conciliacion->entransito[$x]->monto;

        $conciliacion->diferencia = (int)$conciliacion->entransito[$x]->operacion == 1 ?
            $conciliacion->diferencia - (float)$conciliacion->entransito[$x]->monto :
            $conciliacion->diferencia + (float)$conciliacion->entransito[$x]->monto;
    };

    $conciliacion->diferencia -= $conciliacion->banco->saldobco;
    $conciliacion->diferencia = abs($conciliacion->diferencia);

    $conciliacion->comparacion = $conciliacion->saldofinal == $conciliacion->banco->saldobco ? 'Conciliación cuadrada' : 'Conciliación descuadrada';

    print json_encode($conciliacion);
});

$app->get('/histocon/:idempresa', function($idempresa){
    $db = new dbcpm();
    $query = "SELECT a.id, a.descripcion, CONCAT(b.nombre, ' ', c.simbolo, ' (', b.nocuenta, ') - ', e.codigo) AS banco, a.saldobco, a.fdel, a.fal, ";
    $query.= "a.resumido, IF(a.resumido = 1, 'Resumido', 'Detallado') AS resumidostr ";
    $query.= "FROM histoconciliacion a INNER JOIN banco b ON b.id = a.idbanco INNER JOIN moneda c ON c.id = b.idmoneda ";
    $query.= "INNER JOIN usuario d ON d.id = a.guardadopor INNER JOIN cuentac e ON e.id = b.idcuentac ";
    $query.= "WHERE b.idempresa = $idempresa ";
    $query.= "ORDER BY a.fguardado DESC";
    print $db->doSelectASJson($query);
});

$app->get('/getcon/:idhisto', function($idhisto){
    $db = new dbcpm();
    $query = "SELECT a.id, a.descripcion, a.idbanco, CONCAT(b.nombre, ' ', c.simbolo, ' (', b.nocuenta, ') - ', e.codigo) AS banco, a.saldobco, a.fdel, a.fal, ";
    $query.= "a.resumido, IF(a.resumido = 1, 'Resumido', 'Detallado') AS resumidostr, a.guardadopor, b.idempresa ";
    $query.= "FROM histoconciliacion a INNER JOIN banco b ON b.id = a.idbanco INNER JOIN moneda c ON c.id = b.idmoneda ";
    $query.= "INNER JOIN usuario d ON d.id = a.guardadopor INNER JOIN cuentac e ON e.id = b.idcuentac ";
    $query.= "WHERE a.id = $idhisto";
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO histoconciliacion(descripcion, idbanco, saldobco, fdel, fal, resumido, fguardado, guardadopor) VALUES(";
    $query.= "'$d->descripcion', $d->idbanco, $d->saldobco, '$d->fdelstr', '$d->falstr', $d->resumido, NOW(), $d->idusuario";
    $query.= ")";
    $db->doQuery($query);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE histoconciliacion SET ";
    $query.= "descripcion = '$d->descripcion', idbanco = $d->idbanco, saldobco = $d->saldobco, fdel = '$d->fdelstr', fal = '$d->falstr', ";
    $query.= "resumido = $d->resumido, fguardado =  NOW(), guardadopor = $d->idusuario ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM histoconciliacion WHERE id = ".$d->id);
});


$app->run();