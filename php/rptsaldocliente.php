<?php
require 'vendor/autoload.php';
require_once 'db.php';

header('Content-Type: application/json');

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/rptsaldocli', function(){
    $d = json_decode(file_get_contents('php://input'));
    try{
        $db = new dbcpm();
        $query = "select c.nit,c.nombre,round(a.saldo,2) as anterior, round(b.cargos,2) as cargos,round(b.abonos,2) as abonos,round((a.saldo+b.cargos)-b.abonos,2) as saldo,c.id as cliente from
                gcf.cliente c
                left join (
                    SELECT a.id as cliente,
                        ifnull(b.cargos,0) as cargos,
                        ifnull(d.abonos,0) as abonos,
                        ifnull(b.cargos,0) -ifnull(d.abonos,0) as saldo
                        from gcf.cliente a
                        left join
                        (
                            select idcliente,sum(total*tipocambio) as cargos from gcf.factura where fecha < '".$d->fdelstr."' group by idcliente
                        ) as b on a.id = b.idcliente
                        left join
                        (
                            select a.idcliente,b.monto as abonos from gcf.recibocli a inner join gcf.detcobroventa b on a.id = b.idrecibocli where a.fecha < '".$d->fdelstr."' group by a.idcliente
                        ) as d on a.id = d.idcliente
                ) as a on a.cliente = c.id

                left join (
                    SELECT a.id as cliente,
                        ifnull(b.cargos,0) as cargos,
                        ifnull(d.abonos,0) as abonos,
                        ifnull(b.cargos,0) -ifnull(d.abonos,0) as saldo
                        from gcf.cliente a
                        left join
                        (
                            select idcliente,sum(total*tipocambio) as cargos from gcf.factura where fecha between '".$d->fdelstr."' and '".$d->falstr."' group by idcliente
                        )  as b on a.id = b.idcliente
                        left join
                        (
                            select a.idcliente,b.monto as abonos from gcf.recibocli a inner join gcf.detcobroventa b on a.id = b.idrecibocli where a.fecha between '".$d->fdelstr."' and '".$d->falstr."' group by a.idcliente
                        )  as d on a.id = d.idcliente
                ) as b on a.cliente=b.cliente
                group by c.id
                having saldo+cargos+abonos<>0
                order by c.nombre";
        print $db->doSelectASJson($query);
    }catch(Exception $e){
        $error = "Mensaje: ".$e->getMessage()." -- Linea: ".$e->getLine()." -- Objeto: ".json_encode($d);
        $query = "SELECT '' AS nit, '".$error."' AS nombre, 0 AS anterior, 0 AS cargos, 0 AS abonos, 0 AS saldo, 0 AS cliente";
        print $db->doSelectASJson($query);
    }
});

$app->run();
