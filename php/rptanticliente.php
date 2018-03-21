<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/rptanticli', function(){
    //echo file_get_contents('php://input');

    $d = json_decode(file_get_contents('php://input'));

    //$d = $d->data;

    try{
        $db = new dbcpm();

        $sqlfields = '';
        $sqlgrp = '';
        $sqlord = '';
        $sqlwhr = "";
        if(!empty($d->clistr)){
            $sqlwhr = "where a.id = ".$d->clistr;
        }

        if($d->detalle == 1){
            $sqlfields = 'b.venta,b.factura,b.serie,b.fecha,';
            $sqlgrp = ',b.venta';
            $sqlord = ',b.fecha,b.serie,b.factura';
        }

        $query = "SELECT a.nit,a.nombre,".$sqlfields."
                        round(sum(if(b.dias < 15, b.monto,0)),2) as vigente,
                        round(sum(if(b.dias between 15 and 29, b.monto,0)),2) as a15,
                        round(sum(if(b.dias between 30 and 59, b.monto,0)),2) as a30,
                        round(sum(if(b.dias between 60 and 89, b.monto,0)),2) as a60,
                        round(sum(if(b.dias >= 90, b.monto,0)),2) as a90,
                        round(sum(ifnull(b.monto,0)),2) as total
                    from gcf.cliente a
                    inner join (

                        select a.orden,a.cliente,a.venta,a.fecha,a.factura,a.serie,
                            a.concepto,(a.monto-ifnull(sum(b.monto),0)) as monto,a.codigo,a.tc_cambio,a.fecpago,a.dias
                        from (
                            SELECT 1 as orden,c.idcliente as cliente,c.id as venta,c.fecha,c.numero as factura,c.serie,c.conceptomayor as concepto,
                                (c.total*c.tipocambio) as monto,e.simbolo as codigo,c.tipocambio as tc_cambio,
                                if(c.fechapago is not null, c.fechapago,c.fecha) as fecpago,datediff('".$d->falstr."',if(c.fechapago is not null, c.fechapago,c.fecha)) as dias
                            from gcf.factura c
                                inner join gcf.moneda e on c.idmoneda=e.id
                                where c.anulada=0
                                    and c.fecha<='".$d->falstr."'
                            order by c.fecha
                        ) as a
                        left join(
                            select orden,cliente,venta,fecha,documento,tipo,monto,codigo,tc_cambio from (

                                SELECT 2 as orden,a.idcliente as cliente,c.id as venta,c.fecha,d.numero as documento,'R' as tipo, (b.monto*d.tipocambio) as monto,
                                    'Q' as codigo,d.tipocambio as tc_cambio
                                from gcf.factura a
                                    inner join gcf.detcobroventa b on a.id=b.idfactura
                                    inner join gcf.recibocli c on b.idrecibocli=c.id
                                    inner join gcf.tranban d on c.idtranban=d.id
                                where c.anulado=0
                                    and c.fecha<='".$d->falstr."'
                            ) as b
                        ) as b on a.venta=b.venta
                        group by a.venta
                        having monto <> 0
                        order by a.fecha,a.serie,a.factura
                    ) as b on a.id=b.cliente ".$sqlwhr."
                    group by a.id".$sqlgrp." order by a.nombre ".$sqlord;

        if($d->detalle == 1) {
            $ancl = $db->getQuery($query);
            $cnt = count($ancl);
            $detrepo = array();
            $det = array();
            $ultnom = '';
            $idarray = 0;
            $sumvigente = 0.00;
            $suma15 = 0.00;
            $suma30 = 0.00;
            $suma60 = 0.00;
            $suma90 = 0.00;
            $sumatotal = 0.00;

            foreach ($ancl as $hac){

                if($hac->nombre != $ultnom){
                    $idarray++;
                    $ultnom = $hac->nombre;

                    /*$detrepo[$idarray] = [
                        'nit' => $hac->nit,
                        'nombre' => $hac->nombre,
                        'vigente' => 0.00,
                        'a15' => 0.00,
                        'a30' => 0.00,
                        'a60' => 0.00,
                        'a90' => 0.00,
                        'total' => 0.00
                    ];*/

                    $det = array();

                    $sumvigente = 0.00;
                    $suma15 = 0.00;
                    $suma30 = 0.00;
                    $suma60 = 0.00;
                    $suma90 = 0.00;
                    $sumatotal = 0.00;
                }

                $sumvigente += $hac->vigente;
                $suma15 += $hac->a15;
                $suma30 += $hac->a30;
                $suma60 += $hac->a60;
                $suma90 += $hac->a90;
                $sumatotal += $hac->total;

                array_push($det,
                    array(
                        'factura' => $hac->factura,
                        'serie' => $hac->serie,
                        'fecha' => $hac->fecha,
                        'vigente' => $hac->vigente,
                        'a15' => $hac->a15,
                        'a30' => $hac->a30,
                        'a60' => $hac->a60,
                        'a90' => $hac->a90
                    )
                );

                if($idarray > 0) {
                    $detrepo[$idarray] = [
                        'nit' => $hac->nit,
                        'nombre' => $hac->nombre,
                        'vigente' => $sumvigente,
                        'a15' => $suma15,
                        'a30' => $suma30,
                        'a60' => $suma60,
                        'a90' => $suma90,
                        'total' => $sumatotal,
                        'dac' => $det
                    ];
                }
            }

            $strjson = array();
            foreach($detrepo as $rdet){
                array_push($strjson,$rdet);
                //$strjson .= json_encode($rdet);
            }

            print json_encode($strjson);
            //print '['.json_encode($detrepo[]).']';
            //print $detrepo;

        }else{
            print $db->doSelectASJson($query);
        }

    }catch(Exception $e){
        $error = "Mensaje: ".$e->getMessage()." -- Linea: ".$e->getLine()." -- Objeto: ".json_encode($d);
        $query = "SELECT '' AS nit, '".$error."' AS nombre, 0 AS vigente, 0 AS a15, 0 AS a30, 0 AS a60, 0 AS a90, 0 AS total";
        print $db->doSelectASJson($query);
    }
});

$app->run();
