<?php
set_time_limit(0);
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

function buscar($arr, $key, $valor){
    //var_dump($arr);
    foreach($arr as $k => $a){
        //var_dump($a);
        if((int)$a[$key] == (int)$valor){
            return (int)$k;
        }
    }
    return -1;
}

function sumaColumnas($arr, $sum, $mes){
    foreach($arr as $k => $v){
        foreach($v as $id => $value){
            if(in_array($id, $mes)){
                $sum[$id]+= $value;
            }
        }
    }
    return $sum;
}

function calcMix($arr, $mes){
    $lastIndex = count($arr) - 1;
    foreach($arr as $k => $v){
        foreach($v as $id => $value){
            if(in_array($id, $mes) && (int)$arr[$k]['idtipo'] > 0){
                $arr[$k][$id.'mix'] = $arr[$lastIndex][$id] > 0 ? round($value * 100 / $arr[$lastIndex][$id], 2) : 0.00;
            }
        }
    }
    return $arr;
}

function orderByAcuDesc($a, $b){ return $a['acu'] == $b['acu'] ? 0 : ($a['acu'] < $b['acu'] ? 1 : -1); }

$app->post('/rptintegra', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $mes = [1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic', 13 => 'acu'];

    $query = "(SELECT b.tipo, a.id AS idtipo, TRIM(a.descripcion) AS desctipo, b.anio, b.mes, b.cantidad ";
    $query.= "FROM tipollamada a LEFT JOIN rptintegracion b ON a.id = b.idtipo ";
    $query.= "WHERE b.tipo = 1 AND b.anio = $d->anio AND b.mes >= $d->dmes AND b.mes <= $d->ames ";
    //$query.= "LIMIT 10";
    $query.= ") ";
    $query.= "UNION ALL ";
    $query.= "(SELECT b.tipo, a.id AS idtipo, TRIM(a.descripcion) AS desctipo, b.anio, b.mes, b.cantidad ";
    $query.= "FROM tipocaso a LEFT JOIN rptintegracion b ON a.id = b.idtipo ";
    $query.= "WHERE b.tipo = 2 AND b.anio = $d->anio AND b.mes >= $d->dmes AND b.mes <= $d->ames ";
    //$query.= "LIMIT 10";
    $query.= ") ";
    $query.= "UNION ALL ";
    $query.= "(SELECT b.tipo, a.id AS idtipo, TRIM(a.descripcion) AS desctipo, b.anio, b.mes, b.cantidad ";
    $query.= "FROM ubicacion a LEFT JOIN rptintegracion b ON a.id = b.idtipo ";
    $query.= "WHERE b.tipo = 3 AND b.anio = $d->anio AND b.mes >= $d->dmes AND b.mes <= $d->ames ";
    //$query.= "LIMIT 10";
    $query.= ") ";
    $query.= "ORDER BY 1, 4, 5, 3";
    $rawdata = $db->getQuery($query);

    $reporte = new stdClass();
    $query = "SELECT COUNT(DISTINCT a.idubicacion) AS totMaquinasEnPeriodo FROM caso a WHERE YEAR(a.fhcierre) = $d->anio AND MONTH(a.fhcierre) >= $d->dmes AND MONTH(a.fhcierre) <= $d->ames";
    $reporte->maquinasperiodo = (int)$db->getOneField($query);
    $query = "SELECT COUNT(*) AS totMaquinasActivas FROM ubicacion WHERE debaja = 0";
    $reporte->maquinasactivas = (int)$db->getOneField($query);
    $reporte->tipollamada = []; $sumTipoLlamada = [];
    $reporte->tipocaso = []; $sumTipoCaso = [];
    $reporte->ubicacion = []; $sumUbicacion = [];

    $cnt = count($rawdata);
    for($i = 0; $i < $cnt; $i++){
        $rd = $rawdata[$i];
        switch((int)$rd->tipo){
            case 1:
                $k = count($reporte->tipollamada) > 0 ? buscar($reporte->tipollamada, 'idtipo', $rd->idtipo) : -1;
                if($k > -1){
                    $reporte->tipollamada[$k][$mes[(int)$rd->mes]] = (int)$rd->cantidad;
                    $reporte->tipollamada[$k][$mes[(int)$rd->mes].'mix'] = 0.00;
                    $reporte->tipollamada[$k]['acu']+= (int)$rd->cantidad;
                    $sumTipoLlamada[0][$mes[(int)$rd->mes]] = 0;
                    $sumTipoLlamada[0][$mes[(int)$rd->mes].'mix'] = '';
                }else{
                    $reporte->tipollamada[] = ['idtipo' => (int)$rd->idtipo, 'descripcion' => $rd->desctipo,
                        'acu' => (int)$rd->cantidad, 'acumix' => 0.00,
                        $mes[(int)$rd->mes] => (int)$rd->cantidad, $mes[(int)$rd->mes].'mix' => 0.00];
                    $sumTipoLlamada[] = ['idtipo' => '', 'descripcion' => '', 'acu' => 0, 'acumix' => '', $mes[(int)$rd->mes] => 0, $mes[(int)$rd->mes].'mix' => ''];
                }
                break;
            case 2:
                $k = count($reporte->tipocaso) > 0 ? buscar($reporte->tipocaso, 'idtipo', $rd->idtipo) : -1;
                if($k > -1){
                    $reporte->tipocaso[$k][$mes[(int)$rd->mes]] = (int)$rd->cantidad;
                    $reporte->tipocaso[$k][$mes[(int)$rd->mes].'mix'] = 0.00;
                    $reporte->tipocaso[$k]['acu']+= (int)$rd->cantidad;
                    $sumTipoCaso[0][$mes[(int)$rd->mes]] = 0;
                    $sumTipoCaso[0][$mes[(int)$rd->mes].'mix'] = '';
                }else{
                    $reporte->tipocaso[] = ['idtipo' => (int)$rd->idtipo, 'descripcion' => $rd->desctipo,
                        'acu' => (int)$rd->cantidad, 'acumix' => 0.00,
                        $mes[(int)$rd->mes] => (int)$rd->cantidad, $mes[(int)$rd->mes].'mix' => 0.00];
                    $sumTipoCaso[] = ['idtipo' => '', 'descripcion' => '', 'acu' => 0, 'acumix' => '', $mes[(int)$rd->mes] => 0, $mes[(int)$rd->mes].'mix' => ''];
                }
                break;
            case 3:
                $k = count($reporte->ubicacion) ? buscar($reporte->ubicacion, 'idtipo', $rd->idtipo) : -1;
                if($k > -1){
                    $reporte->ubicacion[$k][$mes[(int)$rd->mes]] = (int)$rd->cantidad;
                    $reporte->ubicacion[$k][$mes[(int)$rd->mes].'mix'] = 0.00;
                    $reporte->ubicacion[$k]['acu']+= (int)$rd->cantidad;
                    $sumUbicacion[0][$mes[(int)$rd->mes]] = 0;
                    $sumUbicacion[0][$mes[(int)$rd->mes].'mix'] = '';
                }else{
                    $reporte->ubicacion[] = ['idtipo' => (int)$rd->idtipo, 'descripcion' => $rd->desctipo,
                        'acu' => (int)$rd->cantidad, 'acumix' => 0.00,
                        $mes[(int)$rd->mes] => (int)$rd->cantidad, $mes[(int)$rd->mes].'mix' => 0.00];
                    $sumUbicacion[] = ['idtipo' => '', 'descripcion' => '', 'acu' => 0, 'acumix' => '', $mes[(int)$rd->mes] => 0, $mes[(int)$rd->mes].'mix' => ''];
                }
                break;
        }

    }

    usort($reporte->tipollamada, "orderByAcuDesc");
    usort($reporte->tipocaso, "orderByAcuDesc");
    usort($reporte->ubicacion, "orderByAcuDesc");

    //Si solo pide el top 10
    if((int)$d->tt == 1){
        $reporte->tipollamada = array_slice($reporte->tipollamada, 0 , 10, true);
        $reporte->tipocaso = array_slice($reporte->tipocaso, 0 , 10, true);
        $reporte->ubicacion = array_slice($reporte->ubicacion, 0 , 10, true);
    }

    array_push($reporte->tipollamada, sumaColumnas($reporte->tipollamada, $sumTipoLlamada[0], $mes));
    array_push($reporte->tipocaso, sumaColumnas($reporte->tipocaso, $sumTipoCaso[0], $mes));
    array_push($reporte->ubicacion, sumaColumnas($reporte->ubicacion, $sumUbicacion[0], $mes));

    $reporte->tipollamada = calcMix($reporte->tipollamada, $mes);
    $reporte->tipocaso = calcMix($reporte->tipocaso, $mes);
    $reporte->ubicacion = calcMix($reporte->ubicacion, $mes);

    print json_encode($reporte);
});

$app->run();