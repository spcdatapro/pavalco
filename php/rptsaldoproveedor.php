<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/rptsaldoprov', function(){
    $d = json_decode(file_get_contents('php://input'));
    try{
        $db = new dbcpm();
        $query = "select c.nit,c.nombre,round(a.saldo,2) as anterior, round(b.cargos,2) as cargos,round(b.abonos,2) as abonos,round((a.saldo+b.cargos)-b.abonos,2) as saldo,c.id as proveedor from
            gcf.proveedor c
            left join (
                SELECT a.id as proveedor
                    ,ifnull(b.cargos,0) as cargos
                    ,ifnull(d.abonos,0) as abonos
                    ,ifnull(b.cargos,0) -ifnull(d.abonos,0) as saldo
                    from gcf.proveedor a
                    left join
                    (
                        select idproveedor,sum(totfact*tipocambio) as cargos from gcf.compra where fechafactura < '".$d->fdelstr."' group by idproveedor
                    ) as b on a.id = b.idproveedor
                    left join
                    (
                        select idproveedor,sum(abonos) as abonos from (
                            select c.idproveedor as idproveedor,b.monto as abonos
                            from gcf.tranban a
                            inner join gcf.detpagocompra b on a.id = b.idtranban and esrecprov=0
                            inner join gcf.compra c on b.idcompra=c.id
                            where a.fecha < '".$d->fdelstr."'
                            union all
                            select d.idproveedor as idproveedor,c.monto as abonos
                            from gcf.tranban a
                            inner join  gcf.reciboprov b on a.id=b.idtranban
                            inner join gcf.detpagocompra c on b.id = c.idtranban and esrecprov=1
                            inner join gcf.compra d on c.idcompra=d.id
                            where a.fecha < '".$d->fdelstr."'
                        ) as a group by idproveedor
                    ) as d on a.id = d.idproveedor
            ) as a on a.proveedor = c.id

            left join (
                SELECT a.id as proveedor
                    ,ifnull(b.cargos,0) as cargos
                    ,ifnull(d.abonos,0) as abonos
                    ,ifnull(b.cargos,0) -ifnull(d.abonos,0) as saldo
                    from gcf.proveedor a
                    left join
                    (
                        select idproveedor,sum(totfact*tipocambio) as cargos from gcf.compra where fechafactura between '".$d->fdelstr."' and '".$d->falstr."' group by idproveedor
                    )  as b on a.id = b.idproveedor
                    left join
                    (
                        select idproveedor,sum(abonos) as abonos from (
                            select c.idproveedor as idproveedor,b.monto as abonos
                            from gcf.tranban a
                            inner join gcf.detpagocompra b on a.id = b.idtranban and esrecprov=0
                            inner join gcf.compra c on b.idcompra=c.id
                            where a.fecha between '" . $d->fdelstr . "' and '" . $d->falstr . "'
                            union all
                            select d.idproveedor as idproveedor,c.monto as abonos
                            from gcf.tranban a
                            inner join  gcf.reciboprov b on a.id=b.idtranban
                            inner join gcf.detpagocompra c on b.id = c.idtranban and esrecprov=1
                            inner join gcf.compra d on c.idcompra=d.id
                            where a.fecha between '" . $d->fdelstr . "' and '" . $d->falstr . "'
                        ) as a group by idproveedor
                    )  as d on a.id = d.idproveedor
            ) as b on a.proveedor=b.proveedor
            group by c.id
            having saldo+cargos+abonos<>0
            order by c.nombre";

        print $db->doSelectASJson($query);
    }catch(Exception $e){
        $error = "Mensaje: ".$e->getMessage()." -- Linea: ".$e->getLine()." -- Objeto: ".json_encode($d);
        $query = "SELECT '' AS nit, '".$error."' AS nombre, 0 AS anterior, 0 AS cargos, 0 AS abonos, 0 AS saldo, 0 AS proveedor";
        print $db->doSelectASJson($query);
    }
});

$app->run();
