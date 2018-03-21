<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

function getQueryDetallePartidasDirectas($d){
    //Origen 4 = Partidas directas
    $query = "SELECT '' AS nombre, 0 AS idcontrato, 0 AS correlativo, a.id, a.origen, a.idorigen, a.idcuenta, a.debe, a.haber, a.conceptomayor, 0 AS idcliente, ";
    $query.= "CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(4, 2, '0'), LPAD(b.id, 7, '0')) AS poliza, c.codigo, c.nombrecta, b.fecha AS fregistro ";
    $query.= "FROM detallecontable a INNER JOIN directa b ON b.id = a.idorigen INNER JOIN cuentac c ON c.id = a.idcuenta ";
    $query.= "WHERE a.origen = 4 AND a.idcontrato = 0 AND a.idcuenta IN($d->idcuenta) AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr' ";
    $query.= "ORDER BY 1, 2, 3";
    return $query;
}

function getQueryDetalle($d, $idcliente = 0, $correlativo = 0){
    //Origen 6 = Contratos
    $query = "SELECT c.nombre, b.id AS idcontrato, b.correlativo, a.id, a.origen, a.idorigen, a.idcuenta, a.debe, a.haber, a.conceptomayor, c.id AS idcliente, ";
    $query.= "CONCAT('P', YEAR(b.fechacontrato), LPAD(MONTH(b.fechacontrato), 2, '0'), LPAD(DAY(b.fechacontrato), 2, '0'), LPAD(6, 2, '0'), LPAD(b.id, 7, '0')) AS poliza, ";
    $query.= "d.codigo, d.nombrecta, b.fechacontrato AS fregistro ";
    $query.= "FROM detallecontable a INNER JOIN contrato b ON b.id = a.idorigen INNER JOIN cliente c ON c.id = b.idcliente INNER JOIN cuentac d ON d.id = a.idcuenta ";
    $query.= "WHERE a.origen = 6 AND a.idcuenta IN($d->idcuenta) AND b.fechacontrato >= '$d->fdelstr' AND b.fechacontrato <= '$d->falstr' ";
    $query.= $idcliente > 0 ? "AND c.id = $idcliente " : "";
    $query.= $correlativo > 0 ? "AND b.correlativo = $correlativo " : "";
    $query.= "UNION ALL ";
    //Origen 3 = Ventas (Facturas de venta)
    $query.= "SELECT d.nombre, c.id AS idcontrato, c.correlativo, a.id, a.origen, a.idorigen, a.idcuenta, a.debe, a.haber, a.conceptomayor, d.id AS idcliente, ";
    $query.= "CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(3, 2, '0'), LPAD(b.id, 7, '0')) AS poliza, e.codigo, e.nombrecta, ";
    $query.= "b.fecha AS fregistro ";
    $query.= "FROM detallecontable a INNER JOIN factura b ON b.id = a.idorigen INNER JOIN contrato c ON c.id = b.idcontrato INNER JOIN cliente d ON d.id = c.idcliente ";
    $query.= "INNER JOIN cuentac e ON e.id = a.idcuenta ";
    $query.= "WHERE a.origen = 3 AND a.idcuenta IN($d->idcuenta) AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr' ";
    $query.= $idcliente > 0 ? "AND d.id = $idcliente " : "";
    $query.= $correlativo > 0 ? "AND c.correlativo = $correlativo " : "";
    $query.= "UNION ALL ";
    //Origen 8 = Recibos de clientes
    $query.= "SELECT f.nombre, e.id AS idcontrato, e.correlativo, a.id, a.origen, a.idorigen, a.idcuenta, a.debe, a.haber, a.conceptomayor, f.id AS idcliente, ";
    $query.= "CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(8, 2, '0'), LPAD(b.id, 7, '0')) AS poliza, g.codigo, g.nombrecta, ";
    $query.= "b.fecha AS fregistro ";
    $query.= "FROM detallecontable a INNER JOIN recibocli b ON b.id = a.idorigen INNER JOIN detcobroventa c ON b.id = c.idrecibocli INNER JOIN factura d ON d.id = c.idfactura ";
    $query.= "INNER JOIN contrato e ON e.id = d.idcontrato INNER JOIN cliente f ON f.id = e.idcliente INNER JOIN cuentac g ON g.id = a.idcuenta ";
    $query.= "WHERE a.origen = 8 AND a.idcuenta IN($d->idcuenta) AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr' ";
    $query.= $idcliente > 0 ? "AND f.id = $idcliente " : "";
    $query.= $correlativo > 0 ? "AND e.correlativo = $correlativo " : "";
    $query.= "UNION ALL ";
    //Origen 4 = Directas atadas a contratos
    $query.= "SELECT d.nombre, c.id AS idcontrato, c.correlativo, a.id, a.origen, a.idorigen, a.idcuenta, a.debe, a.haber, a.conceptomayor, c.idcliente, ";
    $query.= "CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(4, 2, '0'), LPAD(b.id, 7, '0')) AS poliza, e.codigo, e.nombrecta, ";
    $query.= "b.fecha AS fregistro ";
    $query.= "FROM detallecontable a INNER JOIN directa b ON b.id = a.idorigen INNER JOIN contrato c ON c.id = a.idcontrato INNER JOIN cliente d ON d.id = c.idcliente INNER JOIN cuentac e ON e.id = a.idcuenta ";
    $query.= "WHERE a.origen = 4 AND a.idcuenta IN($d->idcuenta) AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr' ";
    $query.= $idcliente > 0 ? "AND d.id = $idcliente " : "";
    $query.= $correlativo > 0 ? "AND c.correlativo = $correlativo " : "";
    $query.= "ORDER BY 1, 2, 3";
    return $query;
}

$app->post('/integracli', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $cuenta = $db->getQuery("SELECT codigo, nombrecta, ".getSaldoAnterior($d)." AS saldoinicial, 0.00 AS sumdebe, 0.00 AS sumhaber, 0.00 AS saldofinal FROM cuentac WHERE id IN($d->idcuenta) ORDER BY codigo")[0];

    $directas = $db->getQuery(getQueryDetallePartidasDirectas($d));
    $cntDir = count($directas);
    $sumPoliDebe = 0.00;
    $sumPoliHaber = 0.00;
    if($cntDir > 0){
        for($l = 0; $l < $cntDir; $l++){
            $directa = $directas[$l];
            $sumPoliDebe += (float)$directa->debe;
            $sumPoliHaber += (float)$directa->haber;
        }
    }
    array_push($directas, ['conceptomayor' => 'TOTAL', 'debe' => round($sumPoliDebe, 2), 'haber' => round($sumPoliHaber, 2)]);
    $cuenta->sumdebe += $sumPoliDebe;
    $cuenta->sumhaber += $sumPoliHaber;

    $qrDet = getQueryDetalle($d);
    $query = "SELECT DISTINCT a.idcliente, a.nombre FROM($qrDet) a ORDER BY a.nombre, a.idcliente";
    $clientes = $db->getQuery($query);
    $cntCli  = count($clientes);
    if($cntCli > 0){
        for($i = 0; $i < $cntCli; $i++){
            $cliente = $clientes[$i];
            $query = "SELECT DISTINCT a.idcliente, a.correlativo, CONCAT('GCF', LPAD(a.idcliente, 4, '0'), '-', LPAD(a.correlativo, 4, '0')) AS nocontrato ";
            $query.= "FROM (".getQueryDetalle($d, (int)$cliente->idcliente).") a ORDER BY idcliente, correlativo";
            $cliente->contratos = $db->getQuery($query);
            $cntCont = count($cliente->contratos);
            if($cntCont > 0){
                for($j = 0; $j < $cntCont; $j++){
                    $contrato = $cliente->contratos[$j];
                    $contrato->polizas = $db->getQuery(getQueryDetalle($d, (int)$cliente->idcliente, (int)$contrato->correlativo));
                    $cntPoli = count($contrato->polizas);
                    $sumPoliDebe = 0.00;
                    $sumPoliHaber = 0.00;
                    if($cntPoli > 0){
                        for($k = 0; $k < $cntPoli; $k++){
                            $poliza = $contrato->polizas[$k];
                            $sumPoliDebe += (float)$poliza->debe;
                            $sumPoliHaber += (float)$poliza->haber;
                        }
                    }
                    array_push($contrato->polizas, ['conceptomayor' => 'TOTAL', 'debe' => $sumPoliDebe, 'haber' => $sumPoliHaber]);
                    $cuenta->sumdebe += $sumPoliDebe;
                    $cuenta->sumhaber += $sumPoliHaber;
                }
            }
        }
    }

    $cuenta->saldofinal = round((float)$cuenta->saldoinicial + (float)$cuenta->sumdebe - (float)$cuenta->sumhaber, 2);

    print json_encode(['cuenta' => $cuenta, 'directas' => $directas, 'clientes' => $clientes]);
});

function getSaldoAnterior($d){
    $db = new dbcpm();
    $anterior = 0.00;
    $origenes = ['tranban' => 1, 'compra' => 2, 'venta' => 3, 'directa' => 4, 'reembolso' => 5, 'contrato' => 6, 'recprov' => 7, 'reccli' => 8, 'liquidadoc' => 9];
    foreach($origenes as $v){ $anterior += (float)$db->getOneField(getSelectHeader($v, $d, false)); }
    return round($anterior, 2);
}

function getSelectHeader($cual, $d, $enrango){
    $query = "";
    switch($cual){
        case 1:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco ";
            $query.= "WHERE a.origen = 1 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND c.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 2:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN compra b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 2 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fechaingreso < '".$d->fdelstr."'" : "b.fechaingreso >= '".$d->fdelstr."' AND b.fechaingreso <= '".$d->falstr."'"), $query);
            break;
        case 3:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN factura b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 3 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 4:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN directa b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 4 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 5:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN reembolso b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 5 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa."  AND b.estatus = 2 AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.ffin < '".$d->fdelstr."'" : "b.ffin >= '".$d->fdelstr."' AND b.ffin <= '".$d->falstr."'"), $query);
            break;
        case 6:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN contrato b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 6 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fechacontrato < '".$d->fdelstr."'" : "b.fechacontrato >= '".$d->fdelstr."' AND b.fechacontrato <= '".$d->falstr."'"), $query);
            break;
        case 7:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN reciboprov b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 7 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 8:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN recibocli b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 8 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 9:
            $query = "SELECT (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco ";
            $query.= "WHERE a.origen = 9 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND c.idempresa = ".$d->idempresa." AND a.idcuenta IN($d->idcuenta) ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fechaliquida < '".$d->fdelstr."'" : "b.fechaliquida >= '".$d->fdelstr."' AND b.fechaliquida <= '".$d->falstr."'"), $query);
            break;
    }
    return $query;
}




$app->run();