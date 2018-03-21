<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/rptlibmay', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM rptlibromayor");
    $db->doQuery("ALTER TABLE rptlibromayor AUTO_INCREMENT = 1");
    $db->doQuery("INSERT INTO rptlibromayor(idcuentac, codigo, nombrecta, tipocuenta) SELECT id, codigo, nombrecta, tipocuenta FROM cuentac ORDER BY codigo");
    $origenes = ['tranban' => 1, 'compra' => 2, 'venta' => 3, 'directa' => 4, 'reembolso' => 5, 'contrato' => 6, 'recprov' => 7, 'reccli' => 8, 'liquidadoc' => 9];
    foreach($origenes as $k => $v){
        $query = "UPDATE rptlibromayor a INNER JOIN (".getSelectHeader($v, $d, false).") b ON a.idcuentac = b.idcuenta SET a.anterior = a.anterior + b.anterior";
        $db->doQuery($query);
        $query = "UPDATE rptlibromayor a INNER JOIN (".getSelectHeader($v, $d, true).") b ON a.idcuentac = b.idcuenta SET a.debe = a.debe + b.debe, a.haber = a.haber + b.haber";
        $db->doQuery($query);
    }
    $db->doQuery("UPDATE rptlibromayor SET actual = anterior + debe - haber");

    //Calculo de datos para cuentas de totales
    $tamnivdet = [4 => 6, 2 => 6, 1 => 6];
    $query = "SELECT DISTINCT LENGTH(codigo) AS tamnivel FROM rptlibromayor WHERE tipocuenta = 1 ORDER BY 1 DESC";
    //echo $query."<br/><br/>";
    $tamniveles = $db->getQuery($query);
    foreach($tamniveles as $t){
        //echo "Tamaño del nivel = ".$t->tamnivel."<br/><br/>";
        $query = "SELECT id, idcuentac, codigo FROM rptlibromayor WHERE tipocuenta = 1 AND LENGTH(codigo) = ".$t->tamnivel." ORDER BY codigo";
        //echo $query."<br/><br/>";
        $niveles = $db->getQuery($query);
        foreach($niveles as $n){
            //echo "LENGTH(codigo) = ".$tamnivdet[(int)$t->tamnivel]."<br/><br/>";
            //echo "Codigo = ".$n->codigo."<br/><br/>";
            $query = "SELECT SUM(anterior) AS anterior, SUM(debe) AS debe, SUM(haber) AS haber, SUM(actual) AS actual ";
            $query.= "FROM rptlibromayor ";
            $query.= "WHERE tipocuenta = 0 AND LENGTH(codigo) = ".$tamnivdet[(int)$t->tamnivel]." AND codigo LIKE '".$n->codigo."%'";
            //echo $query."<br/><br/>";
            $sumas = $db->getQuery($query)[0];
            $query = "UPDATE rptlibromayor SET anterior = ".$sumas->anterior.", debe = ".$sumas->debe.", haber = ".$sumas->haber.", actual = ".$sumas->actual." ";
            $query.= "WHERE tipocuenta = 1 AND id = ".$n->id." AND idcuentac = ".$n->idcuentac;
            //echo $query."<br/><br/>";
            $db->doQuery($query);
        }
    }

    $where = "";
    if($d->codigo != ''){
        $where = "WHERE ";
        switch(true){
            case strpos($d->codigo, ','):
                $where.= "codigo IN(".$d->codigo.") ";
                break;
            case strpos($d->codigo, '-'):
                $rango = explode('-', $d->codigo);
                $where.= "codigo >= ".$rango[0]." AND codigo <= ".$rango[1]." ";
                break;
            default:
                $where.= "codigo = ".$d->codigo." ";
                break;
        }
    }

    $query = "SELECT id, idcuentac, codigo, nombrecta, tipocuenta, anterior, debe, haber, actual ";
    $query.= "FROM rptlibromayor ";
    $query.= $where;
    $query.= "ORDER BY codigo";
    $lm = $db->getQuery($query);
    $cntLm = count($lm);
    for($i = 0; $i < $cntLm; $i++){
        $lm[$i]->dlm = $db->getQuery(getSelectDetail(1, $d, $lm[$i]->idcuentac));
    }
    //print $db->doSelectASJson("SELECT id, idcuentac, codigo, nombrecta, tipocuenta, anterior, debe, haber, actual FROM rptlibromayor ORDER BY codigo");
    print json_encode($lm);
});

function getSelectHeader($cual, $d, $enrango){
    $query = "";
    switch($cual){
        case 1:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco ";
            $query.= "WHERE a.origen = 1 AND a.activada = 1 AND FILTROFECHA AND c.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ?
                "((b.anulado = 0 AND b.fecha < '$d->fdelstr') OR (b.anulado = 1 AND b.fecha < '$d->fdelstr' AND b.fechaanula >= '$d->fdelstr'))" :
                "((b.anulado = 0 AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') OR (b.anulado = 1 AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr' AND b.fechaanula > '$d->falstr'))"
            ), $query);
            break;
        case 2:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN compra b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 2 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fechaingreso < '".$d->fdelstr."'" : "b.fechaingreso >= '".$d->fdelstr."' AND b.fechaingreso <= '".$d->falstr."'"), $query);
            break;
        case 3:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN factura b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 3 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 4:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN directa b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 4 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 5:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN reembolso b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 5 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa."  AND b.estatus = 2 ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.ffin < '".$d->fdelstr."'" : "b.ffin >= '".$d->fdelstr."' AND b.ffin <= '".$d->falstr."'"), $query);
            break;
        case 6:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN contrato b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 6 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fechacontrato < '".$d->fdelstr."'" : "b.fechacontrato >= '".$d->fdelstr."' AND b.fechacontrato <= '".$d->falstr."'"), $query);
            break;
        case 7:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN reciboprov b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 7 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 8:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN recibocli b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 8 AND a.activada = 1 AND a.anulado = 0 AND FILTROFECHA AND b.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ? "b.fecha < '".$d->fdelstr."'" : "b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."'"), $query);
            break;
        case 9:
            $query = "SELECT a.idcuenta, SUM(a.debe) AS debe, SUM(a.haber) AS haber, (SUM(a.debe) - SUM(a.haber)) AS anterior ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco ";
            $query.= "WHERE a.origen = 9 AND a.activada = 1 AND FILTROFECHA AND c.idempresa = ".$d->idempresa." ";
            $query.= "GROUP BY a.idcuenta ORDER BY a.idcuenta";
            $query = str_replace("FILTROFECHA", (!$enrango ?
                "((b.anulado = 0 AND b.fechaliquida < '$d->fdelstr') OR (b.anulado = 1 AND b.fechaliquida < '$d->fdelstr' AND b.fechaanula >= '$d->fdelstr'))" :
                "((b.anulado = 0 AND b.fechaliquida >= '$d->fdelstr' AND b.fechaliquida <= '$d->falstr') OR (b.anulado = 1 AND b.fechaliquida >= '$d->fdelstr' AND b.fechaliquida <= '$d->falstr' AND b.fechaanula > '$d->falstr'))"
            ), $query);
            break;
    }
    return $query;
}

function getSelectDetail($cual, $d, $idcuenta){
    $query = "";
    switch($cual){
        case 1:
            //Transacciones bancarias -> origen = 1
            $query = "SELECT CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fecha, CONCAT(d.descripcion, ' ', b.numero, ' ', c.nombre) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
            $query.= "WHERE a.origen = 1 AND a.activada = 1 AND a.idcuenta = ".$idcuenta." AND ";
            $query.= "((b.anulado = 0 AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr') OR (b.anulado = 1 AND b.fecha >= '$d->fdelstr' AND b.fecha <= '$d->falstr' AND b.fechaanula > '$d->falstr')) AND ";
            $query.= "c.idempresa = ".$d->idempresa." ";
            $query.= "UNION ALL ";
            //Compras -> origen = 2
            $query.= "SELECT CONCAT('P', YEAR(b.fechaingreso), LPAD(MONTH(b.fechaingreso), 2, '0'), LPAD(DAY(b.fechaingreso), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fechaingreso AS fecha, CONCAT('Compra', ' ', b.serie, '-', b.documento, ' ') AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN compra b ON b.id = a.idorigen INNER JOIN proveedor c ON c.id = b.idproveedor ";
            $query.= "WHERE a.origen = 2 AND a.activada = 1 AND a.anulado = 0 AND b.idreembolso = 0 AND a.idcuenta = ".$idcuenta." AND b.fechaingreso >= '".$d->fdelstr."' AND b.fechaingreso <= '".$d->falstr."' ";
            $query.= "AND b.idempresa = ".$d->idempresa." ";
            $query.= "UNION ALL ";
            //Ventas -> origen = 3
            $query.= "SELECT CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fecha, CONCAT('Venta', ' ', b.serie, '-', b.numero) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN factura b ON b.id = a.idorigen INNER JOIN contrato c ON c.id = b.idcontrato INNER JOIN cliente d ON d.id = b.idcliente ";
            $query.= "WHERE a.origen = 3 AND a.activada = 1 AND a.anulado = 0 AND a.idcuenta = ".$idcuenta." AND b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."' AND c.idempresa = ".$d->idempresa." ";
            $query.= "UNION ALL ";
            $query.= "SELECT CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fecha, CONCAT('Venta', ' ', b.serie, '-', b.numero) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN factura b ON b.id = a.idorigen INNER JOIN cliente d ON d.id = b.idcliente ";
            $query.= "WHERE a.origen = 3 AND a.activada = 1 AND a.anulado = 0 AND a.idcuenta = ".$idcuenta." AND b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."' AND b.idempresa = ".$d->idempresa." ";
            $query.= "AND b.idcontrato = 0 ";
            $query.= "UNION ALL ";
            //Directas -> origen = 4
            $query.= "SELECT CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fecha, CONCAT('Directa No.', LPAD(b.id, 5, '0')) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN directa b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 4 AND a.activada = 1 AND a.anulado = 0 AND a.idcuenta = ".$idcuenta." AND b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."' AND b.idempresa = ".$d->idempresa." ";
            $query.= "UNION ALL ";
            //Reembolsos -> origen = 5
            $query.= "SELECT CONCAT('P', YEAR(b.ffin), LPAD(MONTH(b.ffin), 2, '0'), LPAD(DAY(b.ffin), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.ffin AS fecha, CONCAT(c.desctiporeembolso, ' No.', LPAD(b.id, 5, '0')) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN reembolso b ON b.id = a.idorigen INNER JOIN tiporeembolso c ON c.id = b.idtiporeembolso ";
            $query.= "WHERE a.origen = 5 AND a.activada = 1 AND a.anulado = 0 AND a.idcuenta = ".$idcuenta." AND b.ffin >= '".$d->fdelstr."' AND b.ffin <= '".$d->falstr."' AND b.idempresa = ".$d->idempresa." AND b.estatus = 2 ";
            $query.= "UNION ALL ";
            //Contratos -> origen = 6
            $query.= "SELECT CONCAT('P', YEAR(b.fechacontrato), LPAD(MONTH(b.fechacontrato), 2, '0'), LPAD(DAY(b.fechacontrato), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fechacontrato AS fecha, CONCAT('Contrato', ' ', 'GCF', LPAD(b.idcliente, 4, '0'), '-', LPAD(b.correlativo, 4, '0')) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN contrato b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 6 AND a.activada = 1 AND a.anulado = 0 AND a.idcuenta = ".$idcuenta." AND b.fechacontrato >= '".$d->fdelstr."' AND b.fechacontrato <= '".$d->falstr."' AND b.idempresa = ".$d->idempresa." ";
            $query.= "UNION ALL ";
            //Recibos de proveedores -> origen = 7
            $query.= "SELECT CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fecha AS fecha, CONCAT('Recibo de proveedores No. ', LPAD(b.id, 5, '0')) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN reciboprov b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 7 AND a.activada = 1 AND a.anulado = 0 AND a.idcuenta = ".$idcuenta." AND b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."' AND b.idempresa = ".$d->idempresa." ";
            $query.= "UNION ALL ";
            //Recibos de proveedores -> origen = 8
            $query.= "SELECT CONCAT('P', YEAR(b.fecha), LPAD(MONTH(b.fecha), 2, '0'), LPAD(DAY(b.fecha), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fecha AS fecha, CONCAT('Recibo de clientes No. ', LPAD(b.id, 5, '0')) AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN recibocli b ON b.id = a.idorigen ";
            $query.= "WHERE a.origen = 8 AND a.activada = 1 AND a.anulado = 0 AND a.idcuenta = ".$idcuenta." AND b.fecha >= '".$d->fdelstr."' AND b.fecha <= '".$d->falstr."' AND b.idempresa = ".$d->idempresa." ";
            $query.= "UNION ALL ";
            //Liquidación de documentos -> origen = 9
            $query.= "SELECT CONCAT('P', YEAR(b.fechaliquida), LPAD(MONTH(b.fechaliquida), 2, '0'), LPAD(DAY(b.fechaliquida), 2, '0'), LPAD(a.origen, 2, '0'), LPAD(a.idorigen, 7, '0')) AS poliza, ";
            $query.= "b.fechaliquida AS fecha, CONCAT(d.descripcion, ' ', b.numero, ' ', c.nombre, '(Liquidación)') AS referencia, a.conceptomayor, a.debe, a.haber, a.idorigen, a.origen ";
            $query.= "FROM detallecontable a INNER JOIN tranban b ON b.id = a.idorigen INNER JOIN banco c ON c.id = b.idbanco INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans ";
            $query.= "WHERE a.origen = 9 AND a.activada = 1 AND a.idcuenta = ".$idcuenta." AND ";
            $query.= "((b.anulado = 0 AND b.fechaliquida >= '$d->fdelstr' AND b.fechaliquida <= '$d->falstr') OR (b.anulado = 1 AND b.fechaliquida >= '$d->fdelstr' AND b.fechaliquida <= '$d->falstr' AND b.fechaanula > '$d->falstr')) AND ";
            $query.= "c.idempresa = ".$d->idempresa." ";
            $query.= "ORDER BY 1, 2";
            break;
    }
    return $query;
}

$app->run();