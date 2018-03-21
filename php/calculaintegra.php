<?php
set_time_limit(0);
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->get('/calcula', function(){
    $db = new dbcpm();

    $query = "DELETE FROM rptintegracion";
    $db->doQuery($query);
    $query = "ALTER TABLE rptintegracion AUTO_INCREMENT = 1";
    $db->doQuery($query);

    $query = "SELECT MIN(YEAR(fhcierre)) AS delAnio, MAX(YEAR(fhcierre)) AS alAnio FROM caso";
    $rangoAnios = $db->getQuery($query)[0];
    //$queries = [];
    $query = "SELECT id, descripcion, tabla, sqlconteo FROM tiporptintegracion ORDER BY id";
    $tiposReporte = $db->getQuery($query);
    foreach($tiposReporte as $tr){
        $query = "SELECT id, descripcion FROM $tr->tabla ORDER BY descripcion";
        $tipos = $db->getQuery($query);
        $cntTipos = count($tipos);
        for($anio = $rangoAnios->delAnio; $anio <= $rangoAnios->alAnio; $anio++){
            for($mes = 1; $mes <= 12; $mes++){
                for($i = 0; $i < $cntTipos; $i++){
                    $tipo = $tipos[$i];
                    $idtipo = $tipo->id;
                    eval("\$query = \"$tr->sqlconteo\";");
                    //$queries[] = $query;
                    $cantidad = $db->getOneField($query);
                    $query = "INSERT INTO rptintegracion(tipo, idtipo, anio, mes, cantidad) VALUES(";
                    $query.= "$tr->id, $idtipo, $anio, $mes, $cantidad";
                    $query.= ")";
                    $db->doQuery($query);
                }
            }
        }
    }

    $query = "SELECT DATE_FORMAT(NOW(), '%d/%m/%Y %H:%i:%s')";

    print json_encode(['ultact' => $db->getOneField($query)]);
});

$app->run();