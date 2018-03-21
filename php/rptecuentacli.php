<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/rptecuentacli', function(){

    $d = json_decode(file_get_contents('php://input'));

    //$d = $d->data;

    try{
        $db = new dbcpm();

        $sqlh = "having saldo<>0";
        $sqlwhr = "";
        if(!empty($d->clistr)){
            $sqlwhr = "where a.id = ".$d->clistr;
        }

        if($d->detalle == 1){
            $sqlh = "";
        }

        $query = "SELECT a.nit,a.nombre,b.venta,b.factura,b.serie,b.fecha,
                round(b.monto,2) as saldo,round(b.totalfac,2) as totalfac
            from gcf.cliente a
            inner join (

                select a.orden,a.cliente,a.venta,a.fecha,a.factura,a.serie,
                    a.concepto,(a.monto-ifnull(sum(b.monto),0)) as monto,a.codigo,a.tc_cambio,a.fecpago,a.dias,a.monto as totalfac
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

                        SELECT 2 as orden,a.idcliente as cliente,a.id as venta,c.fecha,d.numero as documento,'R' as tipo, (b.monto*d.tipocambio) as monto,
                            'Q' as codigo,d.tipocambio as tc_cambio
                        from gcf.factura a
                            inner join gcf.detcobroventa b on a.id=b.idfactura
                            inner join gcf.recibocli c on b.idrecibocli=c.id
                            inner join gcf.tranban d on c.idtranban=d.id
                        where c.anulado=0
                            and c.fecha<='".$d->falstr."'
                    ) as b
                ) as b on a.venta=b.venta
                group by a.venta order by a.venta
            ) as b on a.id=b.cliente ".$sqlwhr."
            group by a.id,b.venta ".$sqlh." order by a.nombre,b.fecha,b.serie,b.factura";

        //echo $query;

        $ancl = $db->getQuery($query);

        $detrepo = array();
        $det = array();
        $detfac = array();
        $ultnom = '';
        $idarray = 0;
        $sumasaldo = 0.00;

        foreach ($ancl as $hac){

            if($hac->nombre != $ultnom){
                $idarray++;
                $ultnom = $hac->nombre;

                $det = array();

                $sumasaldo = 0.00;
            }

            $sumasaldo += $hac->saldo;

            if($d->detalle == 1){
                $detfac = array();

                $qdetpago = "SELECT a.idcliente as cliente,c.id as venta,c.fecha,d.numero as documento,'R' as tipotrans, round((b.monto*d.tipocambio),2) as monto
                        from gcf.factura a
                            inner join gcf.detcobroventa b on a.id=b.idfactura
                            inner join gcf.recibocli c on b.idrecibocli=c.id
                            inner join gcf.tranban d on c.idtranban=d.id
                        where c.anulado=0
                            and c.fecha<='".$d->falstr."' and a.id=".$hac->venta;

                $qdfac = $db->getQuery($qdetpago);
                //echo $qdetpago;
                foreach($qdfac as $row){

                    //var_dump($row);

                    array_push($detfac,
                        array(
                            'monto' => $row->monto,
                            'venta' => $row->venta,
                            'tipotrans' => $row->tipotrans,
                            'documento' => $row->documento,
                            'fecha' => $row->fecha
                        )
                    );
                }
                //$detfac = $db->doSelectASJson($qdetpago);
            }

            array_push($det,
                array(
                    'factura' => $hac->factura,
                    'serie' => $hac->serie,
                    'fecha' => $hac->fecha,
                    'saldo' => $hac->saldo,
                    'totalfac' => $hac->totalfac,
                    'dfac' => $detfac
                )
            );
            //
            if($idarray > 0) {
                $detrepo[$idarray] = [
                    'nit' => $hac->nit,
                    'nombre' => $hac->nombre,
                    'tsaldo' => $sumasaldo,
                    'dec' => $det
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

    }catch(Exception $e){
        $error = "Mensaje: ".$e->getMessage()." -- Linea: ".$e->getLine()." -- Objeto: ".json_encode($d);
        $query = "SELECT '' AS nit, '".$error."' AS nombre, 0 AS vigente, 0 AS a15, 0 AS a30, 0 AS a60, 0 AS a90, 0 AS total";
        print $db->doSelectASJson($query);
    }
});

$app->run();