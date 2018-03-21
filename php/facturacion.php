<?php
ini_set('max_execution_time', 900);
require 'vendor/autoload.php';
require_once 'db.php';
require_once 'NumberToLetterConverter.class.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();

//API para facturacion
$app->post('/getcargos', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcontrato, a.fechacobro, e.simbolo AS moneda, a.monto, a.facturado, c.nombre AS cliente, c.telefono, ";
    $query.= "b.nit, b.emailenviofact, a.tipo, b.idcliente, e.codgface, d.idmoneda, b.correlativo, a.nocuota, ";
    $query.= "IF(a.nocuota = 0, 0, (SELECT MAX(nocuota) FROM cargo WHERE idcontrato = a.idcontrato)) AS ultimacuota ";
    $query.= "FROM cargo a INNER JOIN contrato b ON b.id = a.idcontrato INNER JOIN cliente c ON c.id = b.idcliente INNER JOIN detdatoesp d ON b.id = d.idcontrato ";
    $query.= "INNER JOIN moneda e ON e.id = d.idmoneda ";
    $query.= "WHERE d.autorizada = 1 AND a.facturado = 0 AND a.fechacobro <= '".$d->fal."' ";
    $query.= "ORDER BY a.fechacobro, c.nombre";
    print $db->doSelectASJson($query);
});

$app->get('/tst.xml', function(){

    $factdocgt = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><FactDocGT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.fact.com.mx/schema/gt http://www.mysuitemex.com/fact/schema/fx_2013_gt_3.xsd" xmlns="http://www.fact.com.mx/schema/gt"></FactDocGT>');
    //Nodo Version
    $factdocgt->addChild('Version', 3);

    //Nodo Procesamiento
    $procesamiento = $factdocgt->addChild('Procesamiento');
    $dictionary = $procesamiento->addChild('Dictionary');
    $dictionary->addAttribute('name', 'email');
    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'from');
    $entry->addAttribute('v', 'mash2k@gmail.com');
    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'to');
    $entry->addAttribute('v', 'mash2k@gmail.com');
    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'cc');
    $entry->addAttribute('v', 'aponcespc@gmail.com');
    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'formats');
    $entry->addAttribute('v', 'pdf');

    //Nodo Encabezado
    $encabezado = $factdocgt->addChild('Encabezado');
    $encabezado->addChild('TipoActivo', 'FACE63');
    $encabezado->addChild('CodigoDeMoneda', 'GTQ');
    $encabezado->addChild('TipoDeCambio', '1.0000');
    $encabezado->addChild('InformacionDeRegimenIsr', 'PAGO_TRIMESTRAL');


    Header('Content-type: text/xml');
    echo $factdocgt->asXML();

});

function getDescripcion($tipo, $fcobro, $nocuota = 0, $ultimacuota = 0){
    $descripcion = '';
    //$meses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
    switch($tipo){
        case 2 :
            $descripcion = 'Arrendamiento de equipo correspondiente al '.date('m', strtotime($fcobro)).'/'.date('Y', strtotime($fcobro)).", cuota $nocuota de $ultimacuota.";
            break;
        case 1 : $descripcion = 'Pago inicial de arrendamiento de equipo'; break;
        case 3 : $descripcion = 'Pago final de arrendamiento de equipo'; break;
    };
    return $descripcion;
};

function genXML($obj){
    $db = new dbcpm();
    //$conn = $db->getConn();
    $query = "SELECT nit, idioma, codigoestablecimiento, dispositivoelectronico FROM confgface WHERE pordefecto = 1 LIMIT 1";
    $dataVendor = $db->getQuery($query)[0];
    //$dataVendor = $conn->query($query)->fetchAll(5)[0];
    $n2l = new NumberToLetterConverter();

    $factdocgt = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><FactDocGT xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.fact.com.mx/schema/gt http://www.mysuitemex.com/fact/schema/fx_2013_gt_3.xsd" xmlns="http://www.fact.com.mx/schema/gt"></FactDocGT>');
    //Nodo Version
    $factdocgt->addChild('Version', 3);

    //Nodo Procesamiento
    $procesamiento = $factdocgt->addChild('Procesamiento');
    $dictionary = $procesamiento->addChild('Dictionary');
    $dictionary->addAttribute('name', 'email');
    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'from');
    $entry->addAttribute('v', 'contabilidad@gcfleasing.com');
    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'to');
    $entry->addAttribute('v', $obj->emailenviofact);
    //$entry->addAttribute('v', 'aponcespc@gmail.com; mash2k@gmail.com');

    /*
    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'cc');
    $entry->addAttribute('v', 'mash2k@gmail.com');
    */

    //$entry = $dictionary->addChild('Entry');
    //$entry->addAttribute('k', 'bcc');
    //$entry->addAttribute('v', 'mash2k@gmail.com');

    $entry = $dictionary->addChild('Entry');
    $entry->addAttribute('k', 'formats');
    $entry->addAttribute('v', 'pdf');

    //Nodo Encabezado
    $encabezado = $factdocgt->addChild('Encabezado');
    $encabezado->addChild('TipoActivo', 'FACE63');
    $encabezado->addChild('CodigoDeMoneda', $obj->codgface);
    $encabezado->addChild('TipoDeCambio', $obj->tcdia);
    $encabezado->addChild('InformacionDeRegimenIsr', 'PAGO_TRIMESTRAL');

    //Nodo Vendedor
    $vendedor = $factdocgt->addChild('Vendedor');
    $vendedor->addChild('Nit', preg_replace('/[^0-9]/', '', $dataVendor->nit));
    $vendedor->addChild('Idioma', $dataVendor->idioma);
    $vendedor->addChild('CodigoDeEstablecimiento', $dataVendor->codigoestablecimiento);
    $vendedor->addChild('DispositivoElectronico', $dataVendor->dispositivoelectronico);

    //Nodo Comprador
    $comprador = $factdocgt->addChild('Comprador');
    $comprador->addChild('Nit', preg_replace('/[^0-9]/', '', $obj->nit));
    $comprador->addChild('Idioma', $dataVendor->idioma);

    //Agregado el 20022017 para los que no son cuotas
    $tipocargo = (int)$db->getOneField("SELECT tipocargo FROM confcargocontrato WHERE id = (SELECT idconfcargo FROM cargo WHERE id = $obj->id)");
    $dets = [];
    $maxLengthDescripcion = 70;
    if($tipocargo == 0){
        $dets[] = [
            'descripcion' => substr(getDescripcion((int)$obj->tipo, $obj->fechacobro, (int)$obj->nocuota, (int)$obj->ultimacuota), 0, $maxLengthDescripcion),
            'cantidad' => 1,
            'preciosinimpuesto' => (float)$obj->monto / 1.12,
            'iva' => (float)$obj->monto - ((float)$obj->monto / 1.12),
            'categoria' => 'Servicio'
        ];
    }else{
        $query = "SELECT cantidad, descripcion, preciounitario, precio FROM detconfcargocontrato WHERE idconfcargocont = (SELECT id FROM confcargocontrato WHERE id = (SELECT idconfcargo FROM cargo WHERE id = $obj->id))";
        $detcargo = $db->getQuery($query);
        $cntDetCargo = count($detcargo);
        for($x = 0; $x < $cntDetCargo; $x++){
            $dc = $detcargo[$x];
            $dets[] = [
                'descripcion' => substr($dc->descripcion, 0, $maxLengthDescripcion),
                'cantidad' => $dc->cantidad,
                'preciosinimpuesto' => (float)$dc->preciounitario / 1.12,
                'iva' => (float)$dc->preciounitario - ((float)$dc->preciounitario / 1.12),
                'categoria' => 'Bien'
            ];
        }
    }

    $suma = new stdClass();
    $suma->precioSinImpuesto = 0.00; $suma->iva = 0.00;
    $detalles = $factdocgt->addChild('Detalles');
    $cntDets = count($dets);
    for($i = 0; $i < $cntDets; $i++){
        $ed = $dets[$i];
        $detalle = $detalles->addChild('Detalle');

        $detalle->addChild('Descripcion', $ed['descripcion']);
        $detalle->addChild('CodigoEAN', '00000000000000');
        $detalle->addChild('UnidadDeMedida', 'UNI');
        $detalle->addChild('Cantidad', $ed['cantidad']);
        $valorsindr = $detalle->addChild('ValorSinDR');

        $valorsindr->addChild('Precio', $ed['preciosinimpuesto']);
        $valorsindr->addChild('Monto', $ed['preciosinimpuesto'] * $ed['cantidad']);
        $valorcondr = $detalle->addChild('ValorConDR');
        $valorcondr->addChild('Precio', $ed['preciosinimpuesto']);
        $valorcondr->addChild('Monto', $ed['preciosinimpuesto'] * $ed['cantidad']);
        //Subnodo del IVA
        $impuestos = $detalle->addChild('Impuestos');
        $impuestos->addChild('TotalDeImpuestos', $ed['iva']);
        $impuestos->addChild('IngresosNetosGravados', $ed['preciosinimpuesto']);
        $impuestos->addChild('TotalDeIVA', $ed['iva']);
        $impuesto = $impuestos->addChild('Impuesto');
        $impuesto->addChild('Tipo', 'IVA');
        $impuesto->addChild('Base', $ed['preciosinimpuesto']);
        $impuesto->addChild('Tasa', 12);
        $impuesto->addChild('Monto', $ed['iva']);
        $detalle->addChild('Categoria', $ed['categoria']);

        $suma->precioSinImpuesto += $ed['preciosinimpuesto'];
        $suma->iva += $ed['iva'];
    }
    //Fin de lo agregado el 20022017

    //Nodo Detalles

    //Nodo Totales
    $totales = $factdocgt->addChild('Totales');
    $totales->addChild('SubTotalSinDR', $suma->precioSinImpuesto);
    $totales->addChild('SubTotalConDR', $suma->precioSinImpuesto);
    $impstots = $totales->addChild('Impuestos');
    $impstots->addChild('TotalDeImpuestos', $suma->iva);
    $impstots->addChild('IngresosNetosGravados', $suma->precioSinImpuesto);
    $impstots->addChild('TotalDeIVA', $suma->iva);
    $imptot = $impstots->addChild('Impuesto');
    $imptot->addChild('Tipo', 'IVA');
    $imptot->addChild('Base', $suma->precioSinImpuesto);
    $imptot->addChild('Tasa', 12);
    $imptot->addChild('Monto', $suma->iva);
    $totales->addChild('Total', ($suma->precioSinImpuesto + $suma->iva));
    $totales->addChild('TotalLetras', $n2l->to_word(($suma->precioSinImpuesto + $suma->iva), $obj->codgface));

    //Nodo Texto de pie
    $textosdepie = $factdocgt->addChild('TextosDePie');
    $textosdepie->addChild('Texto', 'Esta es una prueba...');
    //Header('Content-type: text/xml');
    return $factdocgt->asXML();
};

function obj2array($obj) {
    $out = array();
    foreach ($obj as $key => $val) {
        switch(true) {
            case is_object($val):
                $out[$key] = obj2array($val);
                break;
            case is_array($val):
                $out[$key] = obj2array($val);
                break;
            default:
                $out[$key] = $val;
        }
    }
    return $out;
};

$app->post('/facturar', function(){
    $db = new dbcpm();
    $conn = $db->getConn();
    $testing = true;
    $query = "SELECT wsdl".($testing ? "tst" : "").", requestor, country, entity, usuario, username FROM confgface WHERE pordefecto = 1";
    $dataGFACE = $conn->query($query)->fetchAll(5)[0];
    $params = [
        'Requestor' => $dataGFACE->requestor,
        'Transaction' => 'CONVERT_NATIVE_XML',
        'Country' => $dataGFACE->country,
        'Entity' => $dataGFACE->entity,
        'User' => $dataGFACE->usuario,
        'UserName' => $dataGFACE->username,
        'Data1' => '',
        'Data2' => 'PDF',
        'Data3' => ''
    ];
    $wsdl = $testing ? $dataGFACE->wsdltst : $dataGFACE->wsdl;
    $d = json_decode(file_get_contents('php://input'));
    $contAFacturar = count($d);
    $n2l = new NumberToLetterConverter();
    $client = new SoapClient($wsdl, array('trace' => 1));
    $factGeneradas = '';
    $errores = '';
    for($x = 0; $x < $contAFacturar; $x++){
        $params['Data1'] = genXML($d[$x]);
        $resSoap = $client->RequestTransaction($params);
        $resAsStr = $client->__getLastResponse();
        $result = obj2array($resSoap);
        //var_dump($result['RequestTransactionResult']['ResponseData']['ResponseData1']);
        //return;
        if($result['RequestTransactionResult']['Response']['Result']){
            $res = $result['RequestTransactionResult']['Response'];
            $query = "SELECT idcliente FROM contrato WHERE id = ".$d[$x]->idcontrato;
            $idcontrato = $conn->query($query)->fetchColumn(0);
            //Insert encabezado de factura
            $query = "INSERT INTO factura(idcliente, serie, numero, fecha, iva, total, totalletras, firmaelectronica, respuestagface, ";
            $query.= "idmoneda, tipocambio, idcontrato, noautorizacion, ";
            $query.= "idempresa, idtipofactura, fechaingreso, mesiva, idtipoventa) VALUES(";
            $query.= $idcontrato.", '".$res['Identifier']['Batch']."', '".$res['Identifier']['Serial']."', ";
            $query.= "'".substr($res['TimeStamp'], 0, 10)."', ".((float)$d[$x]->monto - ((float)$d[$x]->monto / 1.12)).", ";
            $query.= (float)$d[$x]->monto.", '".$n2l->to_word((float)$d[$x]->monto, $d[$x]->codgface)."', ";
            $query.= "'".$result['RequestTransactionResult']['ResponseData']['ResponseData1']."', '".$resAsStr."', ".$d[$x]->idmoneda.", ";
            $query.= $d[$x]->tcdia.", ".$d[$x]->idcontrato.", '".$res['Identifier']['ANumber']."', ";
            $query.= "1, 1, NOW(), MONTH(NOW()), 2";
            $query.= ")";
            $conn->query($query);
            $lastIdFact = (int)$conn->query("SELECT LAST_INSERT_ID()")->fetchColumn(0);

            if($factGeneradas !== ''){ $factGeneradas.= ', '; };
            $factGeneradas.= $res['Identifier']['Batch'].'-'.$res['Identifier']['Serial'];

            //Inserta detalle de factura
            $query = "INSERT INTO detfact(idfactura, cantidad, descripcion, preciounitario, preciotot) VALUES(";
            $query.= $lastIdFact.", 1, '".getDescripcion($d[$x]->tipo, $d[$x]->fechacobro)."', ".(float)$d[$x]->monto.", ".(float)$d[$x]->monto * 1;
            $query.= ")";
            $conn->query($query);
            //Actualiza cargo
            $query = "UPDATE cargo SET facturado = 1, idfactura = ".$lastIdFact." WHERE id = ".$d[$x]->id;
            $conn->query($query);
        }else{
            if($errores !== ''){ $errores.= ','; };
            $errores.= $result['RequestTransactionResult']['Response']['Description'];
            //var_dump($result);
        };
    }; //final del for para recorrer todos los documentos a facturar.

    print json_encode(['factgen' => $factGeneradas, 'errores' => $errores]);

});

$app->run();