<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para compras
$app->get('/lstcomras/:idempresa', function($idempresa){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idempresa, d.nomempresa, a.idproveedor, b.nombre AS nomproveedor, a.serie, a.documento, a.fechaingreso, a.mesiva, ";
    $query.= "a.fechafactura, a.idtipocompra, c.desctipocompra, a.conceptomayor, a.creditofiscal, a.extraordinario, a.fechapago, a.ordentrabajo, ";
    $query.= "a.totfact, a.noafecto, a.subtotal, a.iva, IF(ISNULL(e.cantpagos), 0, e.cantpagos) AS cantpagos, a.idmoneda, a.tipocambio, f.simbolo AS moneda, ";
    $query.= "a.idtipofactura, g.desctipofact AS tipofactura, a.isr, a.idtipocombustible, h.descripcion AS tipocombustible, a.galones, a.idp, ";
    $query.= "a.noformisr, a.noaccisr, a.fecpagoformisr, a.mesisr, a.anioisr ";
    $query.= "FROM compra a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN tipocompra c ON c.id = a.idtipocompra ";
    $query.= "INNER JOIN empresa d ON d.id = a.idempresa LEFT JOIN ( SELECT a.idcompra, COUNT(a.idtranban) AS cantpagos	";
    $query.= "FROM detpagocompra a INNER JOIN tranban b ON b.id = a.idtranban INNER JOIN banco c ON c.id = b.idbanco ";
    $query.= "INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans INNER JOIN moneda e ON e.id = c.idmoneda ";
    $query.= "GROUP BY a.idcompra) e ON a.id = e.idcompra LEFT JOIN moneda f ON f.id = a.idmoneda LEFT JOIN tipofactura g ON g.id = a.idtipofactura ";
    $query.= "LEFT JOIN tipocombustible h ON h.id = a.idtipocombustible ";
    $query.= "WHERE a.idempresa = ".$idempresa." AND a.idreembolso = 0 ";
    $query.= "ORDER BY a.fechapago, b.nombre";
    print $db->doSelectASJson($query);
});

$app->get('/getcompra/:idcompra', function($idcompra){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idempresa, d.nomempresa, a.idproveedor, b.nombre AS nomproveedor, a.serie, a.documento, a.fechaingreso, ";
    $query.= "a.mesiva, a.fechafactura, a.idtipocompra, c.desctipocompra, a.conceptomayor, a.creditofiscal, a.extraordinario, a.fechapago, ";
    $query.= "a.ordentrabajo, a.totfact, a.noafecto, a.subtotal, a.iva, a.idmoneda, a.tipocambio, f.simbolo AS moneda, ";
    $query.= "a.idtipofactura, g.desctipofact AS tipofactura, a.isr, a.idtipocombustible, h.descripcion AS tipocombustible, a.galones, a.idp, ";
    $query.= "a.noformisr, a.noaccisr, a.fecpagoformisr, a.mesisr, a.anioisr ";
    $query.= "FROM compra a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN tipocompra c ON c.id = a.idtipocompra ";
    $query.= "INNER JOIN empresa d ON d.id = a.idempresa LEFT JOIN moneda f ON f.id = a.idmoneda LEFT JOIN tipofactura g ON g.id = a.idtipofactura ";
    $query.= "LEFT JOIN tipocombustible h ON h.id = a.idtipocombustible ";
    $query.= "WHERE a.id = ".$idcompra;
    print $db->doSelectASJson($query);
});

function insertaDetalleContable($d, $idorigen){
    $db = new dbcpm();
    $origen = 2;
    //Inicia inserción automática de detalle contable de la factura
    $ctagastoprov = (int)$d->ctagastoprov;
    $ctaivaporpagar = (int)$db->getOneField("SELECT idcuentac FROM tipocompra WHERE id = ".$d->idtipocompra);
    if($ctaivaporpagar == 0){ $ctaivaporpagar = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = ".$d->idempresa." AND idtipoconfig = 2"); }
    $ctaproveedores = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = ".$d->idempresa." AND idtipoconfig = 3");
    $ctaisrretenido = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = ".$d->idempresa." AND idtipoconfig = 8");
    $ctaidp = (int)$db->getOneField("SELECT idcuentac FROM detcontempresa WHERE idempresa = ".$d->idempresa." AND idtipoconfig = 9");
    $d->conceptoprov.= ", ".$d->serie." - ".$d->documento;
    $d->idp = (float)$d->idp;

    if($ctagastoprov > 0){
        $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
        $query.= $origen.", ".$idorigen.", ".$ctagastoprov.", ".round((((float)$d->subtotal - $d->idp) * (float)$d->tipocambio), 2).", 0.00, '".$d->conceptomayor."')";
        $db->doQuery($query);
    };

    if($ctaivaporpagar > 0 && (float)$d->iva > 0){
        $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
        $query.= $origen.", ".$idorigen.", ".$ctaivaporpagar.", ".round(((float)$d->iva * (float)$d->tipocambio), 2).", 0.00, '".$d->conceptomayor."')";
        $db->doQuery($query);
    };

    if($ctaisrretenido > 0 && $d->isr > 0){
        $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
        $query.= $origen.", ".$idorigen.", ".$ctaisrretenido.", 0.00, ".round(($d->isr * (float)$d->tipocambio), 2).", '".$d->conceptomayor."')";
        $db->doQuery($query);
    }

    if($ctaidp > 0 && $d->idp > 0){
        $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
        $query.= $origen.", ".$idorigen.", ".$ctaidp.", ".round(($d->idp * (float)$d->tipocambio), 2).", 0.00, '".$d->conceptomayor."')";
        $db->doQuery($query);
    }

    if($ctaproveedores > 0){
        $query = "INSERT INTO detallecontable(origen, idorigen, idcuenta, debe, haber, conceptomayor) VALUES(";
        $query.= $origen.", ".$idorigen.", ".$ctaproveedores.", 0.00, ".round((((float)$d->totfact - $d->isr) * (float)$d->tipocambio), 2).", '".$d->conceptomayor."')";
        $db->doQuery($query);
    };
};

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $calcisr = (int)$db->getOneField("SELECT retensionisr FROM proveedor WHERE id = ".$d->idproveedor) === 1;
    $d->isr = !$calcisr ? 0.00 : $db->calculaISR((float)$d->subtotal, (float)$d->tipocambio);

    $query = "INSERT INTO compra(idempresa, idproveedor, serie, documento, fechaingreso, mesiva, fechafactura, idtipocompra, ";
    $query.= "conceptomayor, creditofiscal, extraordinario, fechapago, ordentrabajo, totfact, noafecto, subtotal, iva, idmoneda, tipocambio, ";
    $query.= "idtipofactura, isr, idtipocombustible, galones, idp) ";
    $query.= "VALUES(".$d->idempresa.", ".$d->idproveedor.", '".$d->serie."', ".$d->documento.", '".$d->fechaingresostr."', ".$d->mesiva.", '".$d->fechafacturastr."', ";
    $query.= $d->idtipocompra.", '".$d->conceptomayor."', ".$d->creditofiscal.", ".$d->extraordinario.", '".$d->fechapagostr."', ".$d->ordentrabajo.", ";
    $query.= $d->totfact.", ".$d->noafecto.", ".$d->subtotal.", ".$d->iva.", ".$d->idmoneda.", ".$d->tipocambio.", ".$d->idtipofactura.", ".$d->isr.", ";
    $query.= $d->idtipocombustible.", ".$d->galones.", ".$d->idp;
    $query.= ")";
    $db->doQuery($query);

    $lastid = $db->getLastId();
    if($db->getConConta()){
        //Inicia inserción automática de detalle contable de la factura
        insertaDetalleContable($d, $lastid);
        //Fin de inserción automática de detalle contable de la factura
    }

    print json_encode(['lastid' => $lastid]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $calcisr = (int)$db->getOneField("SELECT retensionisr FROM proveedor WHERE id = ".$d->idproveedor) === 1;
    $d->isr = !$calcisr ? 0.00 : $db->calculaISR((float)$d->subtotal);

    $query = "UPDATE compra SET ";
    $query.= "idproveedor = ".$d->idproveedor.", serie = '".$d->serie."', documento = ".$d->documento.", fechaingreso = '".$d->fechaingresostr."', ";
    $query.= "mesiva = ".$d->mesiva.", fechafactura = '".$d->fechafacturastr."', idtipocompra = ".$d->idtipocompra.", conceptomayor =  '".$d->conceptomayor."', ";
    $query.= "creditofiscal = ".$d->creditofiscal.", extraordinario = ".$d->extraordinario.", fechapago = '".$d->fechapagostr."', ordentrabajo = ".$d->ordentrabajo.", ";
    $query.= "totfact = ".$d->totfact.", noafecto = ".$d->noafecto.", subtotal = ".$d->subtotal.", iva = ".$d->iva.", ";
    $query.= "idmoneda = ".$d->idmoneda.", tipocambio = ".$d->tipocambio.", idtipofactura = ".$d->idtipofactura.", isr = ".$d->isr.", ";
    $query.= "idtipocombustible = ".$d->idtipocombustible.", galones = ".$d->galones.", idp = ".$d->idp." ";
    $query.= "WHERE id = ".$d->id;
    $db->doQuery($query);

    $origen = 2;
    $idorigen = (int)$d->id;
    if($db->getConConta()) {
        $query = "DELETE FROM detallecontable WHERE origen = " . $origen . " AND idorigen = " . $idorigen;
        $db->doQuery($query);

        //Inicia inserción automática de detalle contable de la factura
        insertaDetalleContable($d, $idorigen);
        //Fin de inserción automática de detalle contable de la factura
    }
    print json_encode(['lastid' => $idorigen]);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    if($db->getConConta()){ $db->doQuery("DELETE FROM detallecontable WHERE origen = 2 AND idorigen = ".$d->id); }
    $db->doQuery("DELETE FROM compra WHERE id = ".$d->id);
});

$app->post('/uisr', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->noformisr = $d->noformisr == '' ? 'NULL' : "'".$d->noformisr."'";
    $d->noaccisr = $d->noaccisr == '' ? 'NULL' : "'".$d->noaccisr."'";
    $d->fecpagoformisrstr = $d->fecpagoformisrstr == '' ? 'NULL' : "'".$d->fecpagoformisrstr."'";
    $d->mesisr = (int)$d->mesisr == 0 ? 'NULL' : $d->mesisr;
    $d->anioisr = (int)$d->anioisr == 0 ? 'NULL' : $d->anioisr;
    $query = "UPDATE compra SET noformisr = ".$d->noformisr.", noaccisr = ".$d->noaccisr.", fecpagoformisr = ".$d->fecpagoformisrstr.", mesisr = ".$d->mesisr.", anioisr = ".$d->anioisr." WHERE id = ".$d->id;
    $db->doQuery($query);
});

$app->get('/tranpago/:idcompra', function($idcompra){
    $db = new dbcpm();
    $query = "SELECT a.idtranban, CONCAT('(', d.abreviatura, ') ', d.descripcion) AS tipodoc, b.numero, CONCAT(c.nombre, ' (', e.simbolo, ')') AS banco, b.monto ";
    $query.= "FROM detpagocompra a INNER JOIN tranban b ON b.id = a.idtranban INNER JOIN banco c ON c.id = b.idbanco ";
    $query.= "INNER JOIN tipomovtranban d ON d.abreviatura = b.tipotrans INNER JOIN moneda e ON e.id = c.idmoneda ";
    $query.= "WHERE a.idcompra = ".$idcompra." AND a.esrecprov = 0 ";
    $query.= "UNION ALL ";
    $query.= "SELECT a.idtranban, 'Recibo' AS tipodoc, LPAD(b.id, 5, '0') AS numero, '' AS banco, c.arebajar AS monto ";
    $query.= "FROM detpagocompra a INNER JOIN reciboprov b ON b.id = a.idtranban INNER JOIN detrecprov c ON b.id = c.idrecprov ";
    $query.= "WHERE a.idcompra = $idcompra AND a.esrecprov = 1 AND c.origen = 2 AND c.idorigen = $idcompra ";
    $query.= "ORDER BY 2, 3";
    print $db->doSelectASJson($query);
});

$app->post('/lstcompisr', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $where = "";
    if($d->fdelstr != ''){ $where.= "AND a.fechafactura >= '".$d->fdelstr."' "; }
    if($d->falstr != ''){ $where.= "AND a.fechafactura <= '".$d->falstr."' "; }
    switch((int)$d->cuales){
        case 1:
            $where.= "AND LENGTH(a.noformisr) > 0 ";
            break;
        case 2:
            $where.= "AND (ISNULL(a.noformisr) OR LENGTH(a.noformisr) = 0) ";
            break;
    }

    $query = "SELECT a.id, b.nit, b.nombre AS nomproveedor, a.serie, a.documento, a.fechafactura, c.desctipocompra, a.tipocambio, f.simbolo AS moneda, g.desctipofact AS tipofactura, ";
    $query.= "a.totfact, a.isr, a.noformisr, a.noaccisr, a.fecpagoformisr, a.mesisr, a.anioisr, ROUND((a.isr * a.tipocambio), 2) AS isrlocal ";
    $query.= "FROM compra a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN tipocompra c ON c.id = a.idtipocompra INNER JOIN empresa d ON d.id = a.idempresa INNER JOIN moneda f ON f.id = a.idmoneda ";
    $query.= "INNER JOIN tipofactura g ON g.id = a.idtipofactura ";
    $query.= "WHERE a.idempresa = ".$d->idempresa." AND a.idreembolso = 0 AND a.isr > 0 ";
    $query.= $where;
    $query.= "UNION ";
    $query.= "SELECT a.id, a.nit, a.proveedor, a.serie, a.documento, a.fechafactura, c.desctipocompra, a.tipocambio, f.simbolo AS moneda, g.desctipofact AS tipofactura, ";
    $query.= "a.totfact, a.isr, a.noformisr, a.noaccisr, a.fecpagoformisr, a.mesisr, a.anioisr, ROUND((a.isr * a.tipocambio), 2) AS isrlocal ";
    $query.= "FROM compra a INNER JOIN tipocompra c ON c.id = a.idtipocompra INNER JOIN empresa d ON d.id = a.idempresa INNER JOIN moneda f ON f.id = a.idmoneda INNER JOIN tipofactura g ON g.id = a.idtipofactura ";
    $query.= "WHERE a.idempresa = ".$d->idempresa." AND a.idreembolso > 0 AND a.isr > 0 ";
    $query.= $where;
    $query.= "ORDER BY 13, 3, 6";
    print $db->doSelectASJson($query);
});

$app->get('/getcompisr/:idcomp', function($idcomp){
    $db = new dbcpm();
    $query = "SELECT a.id, b.nit, b.nombre AS nomproveedor, a.serie, a.documento, a.fechafactura, c.desctipocompra, a.tipocambio, f.simbolo AS moneda, g.desctipofact AS tipofactura, ";
    $query.= "a.totfact, a.isr, a.noformisr, a.noaccisr, a.fecpagoformisr, a.mesisr, a.anioisr, ROUND((a.isr * a.tipocambio), 2) AS isrlocal ";
    $query.= "FROM compra a INNER JOIN proveedor b ON b.id = a.idproveedor INNER JOIN tipocompra c ON c.id = a.idtipocompra INNER JOIN empresa d ON d.id = a.idempresa INNER JOIN moneda f ON f.id = a.idmoneda ";
    $query.= "INNER JOIN tipofactura g ON g.id = a.idtipofactura ";
    $query.= "WHERE a.id = ".$idcomp." AND a.idreembolso = 0 AND a.isr > 0 ";
    $query.= "UNION ";
    $query.= "SELECT a.id, a.nit, a.proveedor, a.serie, a.documento, a.fechafactura, c.desctipocompra, a.tipocambio, f.simbolo AS moneda, g.desctipofact AS tipofactura, ";
    $query.= "a.totfact, a.isr, a.noformisr, a.noaccisr, a.fecpagoformisr, a.mesisr, a.anioisr, ROUND((a.isr * a.tipocambio), 2) AS isrlocal ";
    $query.= "FROM compra a INNER JOIN tipocompra c ON c.id = a.idtipocompra INNER JOIN empresa d ON d.id = a.idempresa INNER JOIN moneda f ON f.id = a.idmoneda INNER JOIN tipofactura g ON g.id = a.idtipofactura ";
    $query.= "WHERE a.id = ".$idcomp." AND a.idreembolso > 0 AND a.isr > 0 ";
    $query.= "ORDER BY 13, 3, 6";
    print $db->doSelectASJson($query);
});

$app->run();