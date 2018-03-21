<?php
require 'vendor/autoload.php';
require_once 'db.php';

//header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para transacciones bancarias
$app->get('/lsttranbanc/:idbanco', function($idbanco){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idbanco, CONCAT(b.nombre, ' (', b.nocuenta, ')') AS nombanco, a.tipotrans, a.numero, a.fecha, a.monto, ";
    $query.= "a.beneficiario, a.concepto, a.operado, a.anticipo, a.idbeneficiario, a.origenbene, a.anulado, a.fechaanula, a.tipocambio, a.impreso, a.fechaliquida, a.esajustedc ";
    $query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco ";
    $query.= "WHERE a.idbanco = ".$idbanco." ";
    $query.= "ORDER BY a.fecha DESC, a.operado, b.nombre, a.tipotrans, a.numero";
    print $db->doSelectASJson($query);
});

$app->get('/gettran/:idtran', function($idtran){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idbanco, CONCAT(b.nombre, ' (', b.nocuenta, ')') AS nombanco, a.tipotrans, a.numero, a.fecha, a.monto, ";
    $query.= "a.beneficiario, a.concepto, a.operado, a.anticipo, a.idbeneficiario, a.origenbene, a.anulado, c.razon, a.fechaanula, a.tipocambio, d.simbolo AS moneda, a.impreso, a.fechaliquida, a.esajustedc ";
    $query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco LEFT JOIN razonanulacion c ON c.id = a.idrazonanulacion LEFT JOIN moneda d ON d.id = b.idmoneda ";
    $query.= "WHERE a.id = ".$idtran;
    print $db->doSelectASJson($query);
});

function insertaDetalleContable($d, $idorigen){
    $db = new dbcpm();
    $origen = 1;
    //Inicia inserción automática de detalle contable de transacción bancaria
    //Si es C o B, va de la cuenta por liquidar o de la cuenta de proveedores en el debe al banco en el haber
    $idempresa = (int)$db->getOneField("SELECT idempresa FROM banco WHERE id = ".$d->idbanco);
    $ctabco = (int)$db->getOneField("SELECT idcuentac FROM banco WHERE id = ".$d->idbanco);
    //$tc = (float)$db->getOneField("SELECT a.tipocambio FROM moneda a INNER JOIN banco b ON a.id = b.idmoneda WHERE b.id = ".$d->idbanco);
    $cuenta = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = ".$idempresa." AND idtipoconfig = ".((int)$d->origenbene === 2 ? 5 : 3));

    if($cuenta > 0){
        $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
        $query.= $origen.", ".$idorigen.", ".$cuenta.", ".round(((float)$d->monto * (float)$d->tipocambio), 2).", 0.00, '".$d->concepto."')";
        $db->doQuery($query);
    };

    if($ctabco > 0){
        $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
        $query.= $origen.", ".$idorigen.", ".$ctabco.", 0.00, ".round(((float)$d->monto * (float)$d->tipocambio), 2).", '".$d->concepto."')";
        $db->doQuery($query);
    };
};

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $ttsalida = ['C', 'B'];
    $tentrada = ['D', 'R'];
    $query = "INSERT INTO tranban(idbanco, tipotrans, fecha, monto, beneficiario, concepto, numero, anticipo, idbeneficiario, origenbene, tipocambio, esajustedc) ";
    $query.= "VALUES($d->idbanco, '$d->tipotrans', '$d->fechastr', $d->monto, '$d->beneficiario', '$d->concepto', ";
    $query.= "$d->numero, $d->anticipo, $d->idbeneficiario, $d->origenbene, $d->tipocambio, $d->esajustedc)";
    //echo $query.'<br/>';
    $db->doQuery($query);
    $lastid = $db->getLastId();
    if(in_array($d->tipotrans, $ttsalida)){
        if($d->tipotrans === 'C'){ $db->doQuery("UPDATE banco SET correlativo = correlativo + 1 WHERE id = ".$d->idbanco); }
        //Inserta detalle contable
        if($db->getConConta()){ insertaDetalleContable($d, $lastid); }
    }elseif(in_array($d->tipotrans, $tentrada)){
        if($db->getConConta()) {
            $ctabco = (int)$db->getOneField("SELECT idcuentac FROM banco WHERE id = " . $d->idbanco);
            if ($ctabco > 0) {
                $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
                $query .= "1, " . $lastid . ", " . $ctabco . ", " . round(((float)$d->monto * (float)$d->tipocambio), 2) . ", 0.00, '" . $d->concepto . "')";
                $db->doQuery($query);
            };
        }
    }
    print json_encode(['lastid' => $lastid]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "UPDATE tranban SET tipotrans = '".$d->tipotrans."', ";
    $query.= "fecha = '".$d->fechastr."', monto = ".$d->monto.", beneficiario = '".$d->beneficiario."', concepto = '".$d->concepto."', ";
    $query.= "operado = ".$d->operado.", numero = ".$d->numero.", anticipo = ".$d->anticipo.", idbeneficiario = ".$d->idbeneficiario.", ";
    $query.= "origenbene = ".$d->origenbene.", tipocambio = ".$d->tipocambio.", esajustedc = $d->esajustedc ";
    $query.= "WHERE id = ".$d->id;
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    if($db->getConConta()){ $db->doQuery("DELETE FROM detallecontable WHERE origen = 1 AND idorigen = ".$d->id); }
    $db->doQuery("DELETE FROM tranban WHERE id = ".$d->id);
});

$app->get('/aconciliar/:idbanco/:afecha', function($idbanco, $afecha){
    try{
        $db = new dbcpm();
        $query = "SELECT a.id, a.idbanco, CONCAT(b.nombre, ' (', b.nocuenta, ')') AS nombanco, a.tipotrans, a.numero, a.fecha, a.monto, ";
        $query.= "a.beneficiario, a.concepto, a.operado, a.anticipo, a.idbeneficiario, a.origenbene, a.anulado, a.fechaanula, a.tipocambio, a.impreso, ";
        $query.= "IF(a.tipotrans IN('D', 'R'), ROUND(a.monto * a.tipocambio, 2), '') AS creditos, ";
        $query.= "IF(a.tipotrans IN('C', 'B'), ROUND(a.monto * a.tipocambio, 2), '') AS debitos ";
        $query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco ";
        $query.= "WHERE a.operado = 0 AND a.anulado = 0 AND a.idbanco = ".$idbanco." ";
        $query.= $afecha != '0' ? "AND a.fecha <= '$afecha' " : "";
        $query.= "ORDER BY a.fecha DESC, a.operado, b.nombre, a.tipotrans, a.numero";
        print $db->doSelectASJson($query);
    }catch(Exception $e ){
        print json_encode([]);
    }
});

$app->post('/o', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE tranban SET operado = ".$d->operado.", fechaoperado = '$d->foperado' WHERE id = ".$d->id);
});

$app->post('/anula', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("UPDATE tranban SET idrazonanulacion = ".$d->idrazonanulacion.", anulado = 1, fechaanula = '".$d->fechaanulastr."' WHERE id = ".$d->id);
    if($db->getConConta()){ $db->doQuery("UPDATE detallecontable SET anulado = 1 WHERE origen = 1 AND idorigen = ".$d->id); }
    $origenbene = (int)$db->getOneField("SELECT origenbene FROM tranban WHERE id = ".$d->id);
    switch($origenbene){
        case 1:
            $db->doQuery("DELETE FROM detpagocompra WHERE idtranban = ".$d->id); break;
        case 2:
            $db->doQuery("UPDATE reembolso SET idtranban = 0 WHERE idtranban = ".$d->id); break;
    }
});

$app->get('/lstbeneficiarios', function(){
    $db = new dbcpm();
    $query = "SELECT id, CONCAT(nombre, ' (', nit, ')') AS beneficiario, chequesa, 1 AS dedonde, 'Proveedor(es)' AS grupo FROM proveedor UNION ";
    $query.= "SELECT id, CONCAT(nombre, ' (', nit, ')') AS beneficiario, nombre AS chequesa, 2 AS dedonde, 'Beneficiario(s)' AS grupo FROM beneficiario ";
    $query.= "ORDER BY 4, 2";
    print $db->doSelectASJson($query);
});

$app->get('/factcomp/:idproveedor/:idtranban', function($idproveedor, $idtranban){
    $db = new dbcpm();
    $idmoneda = (int)$db->getOneField("SELECT b.idmoneda FROM tranban a INNER JOIN banco b ON b.id = a.idbanco WHERE a.id = $idtranban");
    $query = "SELECT a.id, a.idempresa, a.idproveedor, b.nombre AS proveedor, a.serie, a.documento, a.fechapago, a.conceptomayor, a.subtotal, a.totfact, ";
    $query.= "IFNULL(c.montopagado, 0.00) AS montopagado, (a.totfact - IFNULL(c.montopagado, 0.00)) AS saldo, ";
    $query.= "CONCAT(a.serie, ' ', a.documento, ' - Total: ', a.totfact, ' - Pendiente: ', (a.totfact - IFNULL(c.montopagado, 0.00))) AS cadena, ";
    $query.= "a.fechafactura ";
    $query.= "FROM compra a LEFT JOIN proveedor b ON b.id = a.idproveedor LEFT JOIN (";
    $query.= "SELECT idcompra, SUM(monto) AS montopagado FROM detpagocompra GROUP BY idcompra) c ON a.id = c.idcompra ";
    $query.= "WHERE (a.totfact - IFNULL(c.montopagado, 0.00)) > 0.00 AND a.idempresa = 1 AND a.idproveedor = ".$idproveedor." AND a.idmoneda = $idmoneda ";
    $query.= "ORDER BY a.serie, a.documento";
    print $db->doSelectASJson($query);
});

$app->get('/reem/:idbene', function($idbene){
    $db = new dbcpm();
    $query = "SELECT a.id, ";
    $query.= "CONCAT(b.desctiporeembolso,' - No. ',LPAD(a.id, 5, '0'), ' - ', DATE_FORMAT(a.finicio, '%d/%m/%Y'),  ' - ', c.nombre, ' - Q ', ";
    $query.= "IF(ISNULL(d.totreembolso), 0.00, d.totreembolso)) AS cadena, a.finicio AS fechafactura, 'REE' AS serie, a.id AS documento, ";
    $query.= "IF(ISNULL(d.totreembolso), 0.00, d.totreembolso) AS totfact ";
    $query.= "FROM reembolso a INNER JOIN tiporeembolso b ON b.id = a.idtiporeembolso INNER JOIN beneficiario c ON c.id = a.idbeneficiario LEFT JOIN (";
    $query.= "SELECT idreembolso, SUM(totfact) AS totreembolso FROM compra WHERE idreembolso > 0 GROUP BY idreembolso) d ON a.id = d.idreembolso ";
    $query.= "WHERE a.idtranban = 0 AND a.idbeneficiario = ".$idbene." ";
    $query.= "ORDER BY a.id";
    print $db->doSelectASJson($query);
});

//API Documentos de soporte
$app->get('/lstdocsop/:idtran', function($idtran){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idtranban, a.idtipodoc, b.desctipodoc, a.documento, a.fechadoc, a.monto, a.serie, a.iddocto ";
    $query.= "FROM doctotranban a INNER JOIN tipodocsoptranban b ON b.id = a.idtipodoc ";
    $query.= "WHERE a.idtranban = ".$idtran." ";
    $query.= "ORDER BY a.fechadoc DESC";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getdocsop/:iddoc', function($iddoc){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT a.id, a.idtranban, a.idtipodoc, b.desctipodoc, a.documento, a.fechadoc, a.monto, a.serie, a.iddocto ";
    $query.= "FROM doctotranban a INNER JOIN tipodocsoptranban b ON b.id = a.idtipodoc ";
    $query.= "WHERE a.id = ".$iddoc;
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->get('/getsumdocssop/:idtranban', function($idtranban){
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "SELECT SUM(monto) AS totMonto FROM doctotranban WHERE idtranban = ".$idtranban;
    $data = $conn->query($query)->fetchColumn(0);
    print json_encode(['totmonto' => $data]);
});

function cierreReembolso($db, $d){
    $estatus = (int)$db->getOneField("SELECT estatus FROM reembolso WHERE id = ".$d->iddocto);
    if($estatus == 2){
        $query = "UPDATE reembolso SET idtranban = ".$d->idtranban." WHERE id = ".$d->iddocto;
        $db->doQuery($query);
    }else{
        $query = "UPDATE reembolso SET estatus = 2, idtranban = ".$d->idtranban.", ffin = NOW() WHERE id = ".$d->iddocto;
        $db->doQuery($query);
        if($db->getConConta()) {
            //Generación del detalle contable del reembolso Origen = 5
            $query = "INSERT INTO detallecontable (origen, idorigen, idcuenta, debe, haber, conceptomayor) ";
            $query .= "SELECT 5 AS origen, a.idreembolso AS idorigen, b.idcuenta, SUM(b.debe) AS debe, 0.00 AS haber, GROUP_CONCAT(b.conceptomayor SEPARATOR ', ') AS conceptomayor ";
            $query .= "FROM compra a INNER JOIN detallecontable b ON a.id = b.idorigen AND b.origen = 2 INNER JOIN cuentac d ON d.id = b.idcuenta ";
            $query .= "WHERE a.idreembolso = " . $d->iddocto . " ";
            $query .= "GROUP BY b.idcuenta ";
            $query .= "ORDER BY d.precedencia DESC, d.nombrecta";
            $db->doQuery($query);
            $ctaporliquidar = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = " . $d->idempresa . " AND idtipoconfig = 5");
            if ($ctaporliquidar > 0) {
                $query = "SELECT SUM(b.debe) AS debe FROM compra a INNER JOIN detallecontable b ON a.id = b.idorigen AND b.origen = 2 WHERE a.idreembolso = " . $d->iddocto;
                $haber = (float)$db->getOneField($query);
                $query = "INSERT INTO detallecontable (origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
                $query .= "5, " . $d->iddocto . ", " . $ctaporliquidar . ", 0.00, " . $haber . ", 'Reembolso No. " . $d->iddocto . "'";
                $query .= ")";
                $db->doQuery($query);
            }
        }
    }
}

$app->post('/cd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "INSERT INTO doctotranban(idtranban, idtipodoc, documento, fechadoc, monto, serie, iddocto) ";
    $query.= "VALUES(".$d->idtranban.", ".$d->idtipodoc.", ".$d->documento.", '".$d->fechadocstr."', ".$d->monto.", '".$d->serie."', ".$d->iddocto.")";
    $db->doQuery($query);
    $tipodoc = (int)$d->idtipodoc;

    //Inserta abono a la factura
    $query = "INSERT INTO detpagocompra (idcompra, idtranban, monto) VALUES(".$d->iddocto.", ".$d->idtranban.", ".$d->monto.")";
    $db->doQuery($query);

    switch($tipodoc){
        case 1:
            if($d->fechaliquidastr != ''){
                //Inserta la tercera partida contable...
                //Origen = 9 -> liquidaciones de cheques
                $query = "UPDATE tranban SET fechaliquida = '$d->fechaliquidastr' WHERE id = $d->idtranban";
                $db->doQuery($query);
                if($db->getConConta()) {
                    $idempresa = (int)$db->getOneField("SELECT b.idempresa FROM tranban a INNER JOIN banco b ON b.id = a.idbanco WHERE a.id = " . $d->idtranban);
                    $cxp = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = " . $idempresa . " AND idtipoconfig = 6");
                    $cxc = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = " . $idempresa . " AND idtipoconfig = 7");
                    $tc = (float)$db->getOneField("SELECT tipocambio FROM tranban WHERE id = $d->idtranban");
                    $tcf = (float)$db->getOneField("SELECT tipocambio FROM compra WHERE id = $d->iddocto");
                    $cdc = 0;

                    $montoSegunCompra = round(($d->monto * $tcf), 2);
                    $montoSegunTransaccion = round(($d->monto * $tc), 2);

                    $diferencial = round(($montoSegunCompra - $montoSegunTransaccion), 2);

                    if ($diferencial < 0) {
                        $cdc = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = " . $idempresa . " AND idtipoconfig = 10");
                    }
                    if ($diferencial > 0) {
                        $cdc = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = " . $idempresa . " AND idtipoconfig = 11");
                    }

                    if ($cxp > 0) {
                        $query = "INSERT INTO detallecontable (origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
                        $query .= "9, " . $d->idtranban . ", " . $cxp . ", " . $montoSegunCompra . ", 0.00, 'Pago de factura " . $d->serie . " " . $d->documento . "'";
                        $query .= ")";
                        $db->doQuery($query);
                    }

                    if ($cdc > 0 && $diferencial != 0) {
                        $query = "INSERT INTO detallecontable (origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
                        $query .= "9, " . $d->idtranban . ", " . $cdc . ", ";
                        $query .= ($diferencial < 0 ? abs($diferencial) : "0.00") . ", " . ($diferencial > 0 ? abs($diferencial) : "0.00") . ", ";
                        $query .= "'Pago de factura " . $d->serie . " " . $d->documento . "'";
                        $query .= ")";
                        $db->doQuery($query);
                    }

                    if ($cxc > 0) {
                        $query = "INSERT INTO detallecontable (origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
                        $query .= "9, " . $d->idtranban . ", " . $cxc . ", 0.00, " . $montoSegunTransaccion . ", 'Pago de factura " . $d->serie . " " . $d->documento . "'";
                        $query .= ")";
                        $db->doQuery($query);
                    }
                }
            }
            break;
        case 2:
            cierreReembolso($db, $d);
            break;
    };
});

$app->post('/ud', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "UPDATE doctotranban SET idtipodoc = ".$d->idtipodoc.", documento = ".$d->documento.", fechadoc = '".$d->fechadocstr."', ";
    $query.= "monto = ".$d->monto.", serie = '".$d->serie."', iddocto = ".$d->iddocto." ";
    $query.= "WHERE id = ".$d->id;
    $upd = $conn->query($query);
});

$app->post('/dd', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();
    $query = "DELETE FROM doctotranban WHERE id = ".$d->id;
    $del = $conn->query($query);
});

//API de reportería
$app->post('/rptcorrch', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();

    $fBco = "AND b.id = ".$d->idbanco." ";
    $fDel = "AND a.fecha >= '".$d->fdelstr."' ";
    $fAl = "AND a.fecha <= '".$d->falstr."' ";

    $query = "SELECT b.id AS idbanco, b.nombre AS banco, a.numero, a.fecha, a.beneficiario, a.concepto, a.monto ";
    $query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco ";
    $query.= "WHERE a.tipotrans = 'C' AND b.idempresa = ".$d->idempresa." ";
    $query.= (int)$d->idbanco > 0 ? $fBco : "";
    $query.= $d->fdelstr != '' ? $fDel : "";
    $query.= $d->falstr != '' ? $fAl : "";
    $query.= "ORDER BY b.nombre, a.numero, a.fecha";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->post('/rptdocscircula', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $conn = $db->getConn();

    $fBco = "AND b.id = ".$d->idbanco." ";
    $fAl = "AND a.fecha <= '".$d->falstr."' ";

    $query = "SELECT b.id AS idbanco, b.nombre AS banco, c.abreviatura, c.descripcion, a.fecha, a.numero, a.beneficiario, a.concepto, a.monto ";
    $query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco INNER JOIN tipomovtranban c ON c.abreviatura = a.tipotrans ";
    $query.= "WHERE a.operado = 0 AND b.idempresa = ".$d->idempresa." ";
    $query.= (int)$d->idbanco > 0 ? $fBco : "";
    $query.= $d->falstr != "" ? $fAl : "";
    $query.= "UNION ";
    $query.= "SELECT 0, 'zzzzz', c.abreviatura, c.descripcion, '', 0, '', '', SUM(a.monto) AS totportipo ";
    $query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco INNER JOIN tipomovtranban c ON c.abreviatura = a.tipotrans ";
    $query.= "WHERE a.operado = 0 AND b.idempresa = ".$d->idempresa." ";
    $query.= (int)$d->idbanco > 0 ? $fBco : "";
    $query.= $d->falstr != "" ? $fAl : "";
    $query.= "GROUP BY a.tipotrans ";
    $query.= "ORDER BY 2, 3, 5, 6";
    $data = $conn->query($query)->fetchAll(5);
    print json_encode($data);
});

$app->run();