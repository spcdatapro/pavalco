<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/rptecuentaprov', function(){

    $d = json_decode(file_get_contents('php://input'));

    //$d = $d->data;

    try{
        $db = new dbcpm();

        $sqlwhr = "";
        if(!empty($d->provstr)){
            $sqlwhr = "where a.id = ".$d->provstr;
        }

        $query = "SELECT a.nit,a.nombre,b.compra,b.factura,b.serie,b.fecha,
                round(b.monto,2) as saldo,round(b.totalfac,2) as totalfac
            from gcf.proveedor a
            inner join (

                select a.orden,a.proveedor,a.compra,a.fecha,a.factura,a.serie,
                    a.concepto,(a.monto-ifnull(sum(b.monto),0)) as monto,a.codigo,a.tc_cambio,a.fecpago,a.dias,a.monto as totalfac
                from (
                    SELECT 1 as orden,c.idproveedor as proveedor,c.id as compra,c.fechafactura as fecha,c.documento as factura,c.serie,c.conceptomayor as concepto,
                        (c.totfact*c.tipocambio) as monto,e.simbolo as codigo,c.tipocambio as tc_cambio,
                        if(c.fechapago is not null, c.fechapago,c.fechafactura) as fecpago,datediff('".$d->falstr."',if(c.fechapago is not null, c.fechapago,c.fechafactura)) as dias
                    from gcf.compra c
                        inner join gcf.moneda e on c.idmoneda=e.id
                        where c.fechafactura<='".$d->falstr."'
                    order by c.fechafactura
                ) as a
                left join(
                    select c.idproveedor as proveedor,b.monto,c.id as compra
                    from gcf.tranban a
                    inner join gcf.detpagocompra b on a.id = b.idtranban and esrecprov=0
                    inner join gcf.compra c on b.idcompra=c.id
                    where a.fecha <= '".$d->falstr."'
                    union all
                    select d.idproveedor as proveedor,c.monto as abonos,d.id as compra
                    from gcf.tranban a
                    inner join  gcf.reciboprov b on a.id=b.idtranban
                    inner join gcf.detpagocompra c on b.id = c.idtranban and esrecprov=1
                    inner join gcf.compra d on c.idcompra=d.id
                    where a.fecha <= '".$d->falstr."'
                    group by 1,3
                ) as b on a.compra=b.compra
                group by a.compra having monto<>0 order by a.compra
            ) as b on a.id=b.proveedor ".$sqlwhr."
            group by a.id ,b.compra order by a.nombre,b.fecha,b.serie,b.factura";

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

                $qdetpago = "select c.idproveedor as proveedor,b.monto,c.id as compra,a.tipotrans,a.numero as documento,a.fecha
                    from gcf.tranban a
                    inner join gcf.detpagocompra b on a.id = b.idtranban and esrecprov=0
                    inner join gcf.compra c on b.idcompra=c.id
                    where a.fecha <= '".$d->falstr."' and c.id=".$hac->compra."
                    union all
                    select d.idproveedor as proveedor,c.monto as abonos,d.id as compra,a.tipotrans,a.numero as documento,a.fecha
                    from gcf.tranban a
                    inner join  gcf.reciboprov b on a.id=b.idtranban
                    inner join gcf.detpagocompra c on b.id = c.idtranban and esrecprov=1
                    inner join gcf.compra d on c.idcompra=d.id
                    where a.fecha <= '".$d->falstr."' and d.id=".$hac->compra."
                    group by 1,3";

                $qdfac = $db->getQuery($qdetpago);
                //echo $qdetpago;
                foreach($qdfac as $row){

                    //var_dump($row);

                    array_push($detfac,
                        array(
                            'monto' => $row->monto,
                            'compra' => $row->compra,
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