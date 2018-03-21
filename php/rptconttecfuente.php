<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

function conteoFuenteTecnicoResumen($d, $db, $idtec, $idfuente){
    $query = "SELECT COUNT(a.id) AS conteo ";
    $query.= "FROM caso a INNER JOIN tipocaso b ON b.id = a.idtipocaso ";
    $query.= "WHERE a.fhcierre >= '$d->fdelstr' AND a.fhcierre <= '$d->falstr' AND a.idtecnico = $idtec AND b.idfuentecaso = $idfuente";
    return (int)$db->getOneField($query);
}

$app->post('/resumen', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $tecnicos = $db->getQuery("SELECT id, nombre FROM tecnico ORDER BY nombre");
    $cntTecs = count($tecnicos);
    $fuentes = $db->getQuery("SELECT id, descripcion FROM fuentecaso ORDER BY descripcion");
    $cntFuentes = count($fuentes);

    $sumas = ['id' => 0, 'descripcion' => 'Totales', 'tecnicos' => []];
    for($i = 0; $i < $cntFuentes; $i++){
        $fuente = $fuentes[$i];
        $acumulado = 0;
        for($j = 0; $j < $cntTecs; $j++){
            $tecnico = $tecnicos[$j];
            $conteo = conteoFuenteTecnicoResumen($d, $db, $tecnico->id, $fuente->id);
            $acumulado += $conteo;
            $fuente->tecnicos[] = ['idtecnico' => (int)$tecnico->id, 'tecnico' => $tecnico->nombre, 'conteo' => $conteo];
            $key = array_search((int)$tecnico->id, array_column($sumas['tecnicos'], 'idtecnico'));
            if($key === false){ $sumas['tecnicos'][] = ['idtecnico' => (int)$tecnico->id, 'tecnico' => $tecnico->nombre, 'conteo' => $conteo]; }else{ $sumas['tecnicos'][$key]['conteo'] += $conteo; }
        }
        $fuente->tecnicos[] = ['idtecnico' => '0', 'tecnico' => 'Acumulado', 'conteo' => $acumulado];
        $key = array_search(0, array_column($sumas['tecnicos'], 'idtecnico'));
        if($key === false){ $sumas['tecnicos'][] = ['idtecnico' => 0, 'tecnico' => 'Acumulado', 'conteo' => $acumulado]; }else{ $sumas['tecnicos'][$key]['conteo'] += $acumulado; }
    }

    //print json_encode($sumas);
    array_push($fuentes, $sumas);
    print json_encode($fuentes);
});

function conteoFuenteTecnicoMensual($idfuente, $idtec, $mes, $anio){
    $db = new dbcpm();
    $query = "SELECT COUNT(a.id) FROM caso a INNER JOIN tipocaso b ON b.id = a.idtipocaso WHERE MONTH(a.fhcierre) = $mes AND YEAR(a.fhcierre) = $anio AND b.idfuentecaso = $idfuente AND a.idtecnico = $idtec";
    $cnt = $db->getOneField($query);
    //print json_encode((int)$cnt);
    return (int)$cnt;
}

$app->post('/mensual', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $fuentes = $db->getQuery("SELECT id, descripcion FROM fuentecaso ORDER BY descripcion");
    $cntFuentes = count($fuentes);
    $tecnicos = $db->getQuery("SELECT id, nombre FROM tecnico ORDER BY nombre");
    $cntTecs = count($tecnicos);
    $meses = $db->getQuery("SELECT id, nombrecorto AS mes, CAST(0 AS UNSIGNED) AS conteo FROM mes WHERE id >= $d->dmes AND id <= $d->ames ORDER BY id");

    $tst = [];
    //$idfuente = 0; $idtecnico = 0; $idmes = 0;
    for($i = 0; $i < $cntFuentes; $i++){
        $fuentes[$i]->tecnicos = $tecnicos;
        for($j = 0; $j < $cntTecs; $j++){
            $fuentes[$i]->tecnicos[$j]->meses = $meses;
        }
    }

    $cntA = count($fuentes);
    for($a = 0; $a < $cntA; $a++){
        $cntB = count($fuentes[$a]->tecnicos);
        //print json_encode($cntB);
        for($b = 0; $b < $cntB; $b++){
            $cntC = count($fuentes[$a]->tecnicos[$b]->meses);
            //print json_encode($cntC);
            for($c = 0; $c < $cntC; $c++){
                //print json_encode([ 'idfuente' => $fuentes[$a]->id, 'idtecnico' => $fuentes[$a]->tecnicos[$b]->id, 'idmes' => $fuentes[$a]->tecnicos[$b]->meses[$c]->id, 'anio' => $d->anio, 'conteo' => $fuentes[$a]->tecnicos[$b]->meses[$c]->conteo ]);
                $dato = conteoFuenteTecnicoMensual($fuentes[$a]->id, $fuentes[$a]->tecnicos[$b]->id, $fuentes[$a]->tecnicos[$b]->meses[$c]->id, $d->anio);
                //print json_encode($dato);
                $fuentes[$a]->tecnicos[$b]->meses[$c]->conteo = $dato;
                //var_dump($fuentes[$a]->tecnicos[$b]->meses[$c]);
                //print json_encode($fuentes[$a]->tecnicos[$b]->meses[$c]->conteo);
            }
            //var_dump($fuentes[$a]->tecnicos[$b]);
        }
        var_dump($fuentes[$a]);
    }



    //var_dump($fuentes);
    //print json_encode($cntA);
    //print json_encode($fuentes, JSON_NUMERIC_CHECK);
});

$app->run();