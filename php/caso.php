<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

//API para casos
$app->post('/lstabiertos', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "SELECT a.id, a.nocaso, a.idestatus, b.descripcion AS estatus, a.idequipo, c.descripcion AS equipo, a.idubicacion, d.descripcion AS ubicacion, a.idtipollamada, e.descripcion AS tipollamada, ";
    $query.= "a.fhapertura, a.idusuarioapertura, f.nombre AS usuarioapertura, a.comentario, a.idtipocaso, g.descripcion AS tipocaso, a.idtecnico, h.nombre AS tecnico, a.comentariocierre, a.serieequipouno, ";
    $query.= "a.serieequipodos, a.serieequipotres, a.fhcierre, a.idusuariocierra, i.nombre AS usuariocierra, ";
    $query.= "(SELECT COUNT(id) FROM bitacoracaso WHERE idcaso = a.id AND esvisita = 1) AS visitado, TIME_TO_SEC(TIMEDIFF(NOW(), a.fhapertura)) / 3600 AS horasaperturado ";
    $query.= "FROM caso a LEFT JOIN estatuscaso b ON b.id = a.idestatus LEFT JOIN equipo c ON c.id = a.idequipo LEFT JOIN ubicacion d ON d.id = a.idubicacion LEFT JOIN tipollamada e ON e.id = a.idtipollamada ";
    $query.= "LEFT JOIN usuario f ON f.id = a.idusuarioapertura LEFT JOIN tipocaso g ON g.id = a.idtipocaso LEFT JOIN tecnico h ON h.id = a.idtecnico LEFT JOIN usuario i ON i.id = a.idusuariocierra ";
    $query.= "WHERE a.idestatus <> 2 ";
    $query.= "ORDER BY a.fhapertura";
    print $db->doSelectASJson($query);
});

$app->post('/lstcerrados', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "SELECT a.id, a.nocaso, a.idestatus, b.descripcion AS estatus, a.idequipo, c.descripcion AS equipo, a.idubicacion, d.descripcion AS ubicacion, a.idtipollamada, e.descripcion AS tipollamada, ";
    $query.= "a.fhapertura, a.idusuarioapertura, f.nombre AS usuarioapertura, a.comentario, a.idtipocaso, g.descripcion AS tipocaso, a.idtecnico, h.nombre AS tecnico, a.comentariocierre, a.serieequipouno, ";
    $query.= "a.serieequipodos, a.serieequipotres, a.fhcierre, a.idusuariocierra, i.nombre AS usuariocierra, (SELECT COUNT(id) FROM bitacoracaso WHERE idcaso = a.id AND esvisita = 1) AS visitado, ";
    $query.= "a.idtiposolucion, j.descripcion AS tiposolucion, g.idfuentecaso, k.descripcion AS fuentecaso ";
    $query.= "FROM caso a LEFT JOIN estatuscaso b ON b.id = a.idestatus LEFT JOIN equipo c ON c.id = a.idequipo LEFT JOIN ubicacion d ON d.id = a.idubicacion LEFT JOIN tipollamada e ON e.id = a.idtipollamada ";
    $query.= "LEFT JOIN usuario f ON f.id = a.idusuarioapertura LEFT JOIN tipocaso g ON g.id = a.idtipocaso LEFT JOIN tecnico h ON h.id = a.idtecnico LEFT JOIN usuario i ON i.id = a.idusuariocierra ";
    $query.= "LEFT JOIN tiposolucion j ON j.id = a.idtiposolucion LEFT JOIN fuentecaso k ON k.id = g.idfuentecaso ";
    $query.= "WHERE a.idestatus = 2 ";
    $query.= $d->fdelstr != '' ? "AND a.fhcierre >= '$d->fdelstr' " : "";
    $query.= $d->falstr != '' ? "AND a.fhcierre <= '$d->falstr' " : "";
    $query.= $d->idtipocaso != '' ? "AND a.idtipocaso IN($d->idtipocaso) " : "";
    $query.= $d->idtiposolucion != '' ? "AND a.idtiposolucion IN($d->idtiposolucion) " : "";
    $query.= $d->idtecnico != '' ? "AND a.idtecnico IN($d->idtecnico) " : "";
    $query.= $d->idubicacion != '' ? "AND a.idubicacion IN($d->idubicacion) " : "";
    $query.= $d->idtipollamada != '' ? "AND a.idtipollamada IN($d->idtipollamada) " : "";
    $query.= $d->idfuentecaso != '' ? "AND g.idfuentecaso IN($d->idfuentecaso) " : "";
    $query.= "ORDER BY a.nocaso, c.descripcion, d.descripcion, e.descripcion, g.descripcion, j.descripcion, h.nombre, a.fhapertura, a.fhcierre";
    print $db->doSelectASJson($query);
});

$app->post('/rptcerrados', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $query = "SELECT a.id, a.nocaso, a.idestatus, b.descripcion AS estatus, a.idequipo, c.descripcion AS equipo, a.idubicacion, d.descripcion AS ubicacion, a.idtipollamada, e.descripcion AS tipollamada, ";
    $query.= "DATE_FORMAT(a.fhapertura, '%d/%m/%Y %H:%i:%s') AS fhapertura, a.idusuarioapertura, f.nombre AS usuarioapertura, a.comentario, a.idtipocaso, g.descripcion AS tipocaso, a.idtecnico, h.nombre AS tecnico, a.comentariocierre, a.serieequipouno, ";
    $query.= "a.serieequipodos, a.serieequipotres, DATE_FORMAT(a.fhcierre, '%d/%m/%Y %H:%i:%s') AS fhcierre, a.idusuariocierra, i.nombre AS usuariocierra, (SELECT COUNT(id) FROM bitacoracaso WHERE idcaso = a.id AND esvisita = 1) AS visitado, ";
    $query.= "a.idtiposolucion, j.descripcion AS tiposolucion, g.idfuentecaso, k.descripcion AS fuentecaso ";
    $query.= "FROM caso a LEFT JOIN estatuscaso b ON b.id = a.idestatus LEFT JOIN equipo c ON c.id = a.idequipo LEFT JOIN ubicacion d ON d.id = a.idubicacion LEFT JOIN tipollamada e ON e.id = a.idtipollamada ";
    $query.= "LEFT JOIN usuario f ON f.id = a.idusuarioapertura LEFT JOIN tipocaso g ON g.id = a.idtipocaso LEFT JOIN tecnico h ON h.id = a.idtecnico LEFT JOIN usuario i ON i.id = a.idusuariocierra ";
    $query.= "LEFT JOIN tiposolucion j ON j.id = a.idtiposolucion LEFT JOIN fuentecaso k ON k.id = g.idfuentecaso ";
    $query.= "WHERE a.idestatus = 2 ";
    $query.= $d->fdelstr != '' ? "AND a.fhcierre >= '$d->fdelstr' " : "";
    $query.= $d->falstr != '' ? "AND a.fhcierre <= '$d->falstr' " : "";
    $query.= $d->idtipocaso != '' ? "AND a.idtipocaso IN($d->idtipocaso) " : "";
    $query.= $d->idtiposolucion != '' ? "AND a.idtiposolucion IN($d->idtiposolucion) " : "";
    $query.= $d->idtecnico != '' ? "AND a.idtecnico IN($d->idtecnico) " : "";
    $query.= $d->idubicacion != '' ? "AND a.idubicacion IN($d->idubicacion) " : "";
    $query.= $d->idtipollamada != '' ? "AND a.idtipollamada IN($d->idtipollamada) " : "";
    $query.= $d->idfuentecaso != '' ? "AND g.idfuentecaso IN($d->idfuentecaso) " : "";
    $query.= "ORDER BY a.nocaso, c.descripcion, d.descripcion, e.descripcion, g.descripcion, j.descripcion, h.nombre, a.fhapertura, a.fhcierre";
    $cerrados = $db->getQuery($query);

    $parametros = [];

    if($d->fdelstr != ''){ $parametros[] = ['param' => 'Del: '.$db->getOneField("SELECT DATE_FORMAT('$d->fdelstr', '%d/%m/%Y %H:%i:%s')")]; }
    if($d->falstr != ''){ $parametros[] = ['param' => 'Al: '.$db->getOneField("SELECT DATE_FORMAT('$d->falstr', '%d/%m/%Y %H:%i:%s')")]; }
    if($d->idtipocaso != ''){
        $query = "SELECT GROUP_CONCAT(DISTINCT CONCAT(b.descripcion, ' - ', a.descripcion) ORDER BY b.descripcion, a.descripcion SEPARATOR ', ') ";
        $query.= "FROM tipocaso a INNER JOIN fuentecaso b ON b.id = a.idfuentecaso ";
        $query.= "WHERE a.id IN ($d->idtipocaso)";
        $parametros[] = ['param' => 'Tipos de caso: '. $db->getOneField($query)];
    }
    if($d->idtiposolucion != ''){
        $parametros[] = ['param' => 'Tipos de caso: '. $db->getOneField("SELECT GROUP_CONCAT(DISTINCT a.descripcion ORDER BY a.descripcion SEPARATOR ', ') FROM tiposolucion a WHERE a.id IN ($d->idtiposolucion)")];
    }
    if($d->idtecnico != ''){
        $parametros[] = ['param' => 'Técnicos: '. $db->getOneField("SELECT GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') FROM tecnico a WHERE a.id IN ($d->idtecnico)")];
    }
    if($d->idubicacion != ''){
        $parametros[] = ['param' => 'Ubicaciones: '. $db->getOneField("SELECT GROUP_CONCAT(DISTINCT a.descripcion ORDER BY a.descripcion SEPARATOR ', ') FROM ubicacion a WHERE a.id IN ($d->idubicacion)")];
    }
    if($d->idtipollamada != ''){
        $parametros[] = ['param' => 'Tipos de llamada: '. $db->getOneField("SELECT GROUP_CONCAT(DISTINCT a.descripcion ORDER BY a.descripcion SEPARATOR ', ') FROM tipollamada a WHERE a.id IN ($d->idtipollamada)")];
    }
    if($d->idfuentecaso != ''){
        $parametros[] = ['param' => 'Fuentes: '. $db->getOneField("SELECT GROUP_CONCAT(DISTINCT a.descripcion ORDER BY a.descripcion SEPARATOR ', ') FROM fuentecaso a WHERE a.id IN ($d->idfuentecaso)")];
    }

    print json_encode(['parametros' => $parametros, 'cerrados' => $cerrados]);
});

$app->get('/getcaso/:idcaso', function($idcaso){
    $db = new dbcpm();
    $query = "SELECT a.id, a.nocaso, a.idestatus, b.descripcion AS estatus, a.idequipo, c.descripcion AS equipo, a.idubicacion, d.descripcion AS ubicacion, a.idtipollamada, e.descripcion AS tipollamada, ";
    $query.= "a.fhapertura, a.idusuarioapertura, f.nombre AS usuarioapertura, a.comentario, a.idtipocaso, g.descripcion AS tipocaso, a.idtecnico, h.nombre AS tecnico, a.comentariocierre, a.serieequipouno, ";
    $query.= "a.serieequipodos, a.serieequipotres, a.fhcierre, a.idusuariocierra, i.nombre AS usuariocierra, ";
    $query.= "(SELECT COUNT(id) FROM bitacoracaso WHERE idcaso = a.id AND esvisita = 1) AS visitado, TIME_TO_SEC(TIMEDIFF(NOW(), a.fhapertura)) / 3600 AS horasaperturado ";
    $query.= "FROM caso a LEFT JOIN estatuscaso b ON b.id = a.idestatus LEFT JOIN equipo c ON c.id = a.idequipo LEFT JOIN ubicacion d ON d.id = a.idubicacion LEFT JOIN tipollamada e ON e.id = a.idtipollamada ";
    $query.= "LEFT JOIN usuario f ON f.id = a.idusuarioapertura LEFT JOIN tipocaso g ON g.id = a.idtipocaso LEFT JOIN tecnico h ON h.id = a.idtecnico LEFT JOIN usuario i ON i.id = a.idusuariocierra ";
    $query.= "WHERE a.id = $idcaso";
    print $db->doSelectASJson($query);
});

$app->post('/c', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->idubicacion = $d->idubicacion == '' ? "0" : "$d->idubicacion";
    $d->idtipollamada = $d->idtipollamada == '' ? "0" : "$d->idtipollamada";
    $d->comentario = $d->comentario == '' ? "NULL" : "'$d->comentario'";
    $query = "INSERT INTO caso(nocaso, idestatus, idequipo, idubicacion, idtipollamada, fhapertura, idusuarioapertura, comentario) VALUES(";
    $query.= "'$d->nocaso', $d->idestatus, $d->idequipo, $d->idubicacion, $d->idtipollamada, '$d->fhaperturastr', $d->idusuarioapertura, $d->comentario";
    $query.= ")";
    $db->doQuery($query);
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/u', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->idubicacion = $d->idubicacion == '' ? "0" : "$d->idubicacion";
    $d->idtipollamada = $d->idtipollamada == '' ? "0" : "$d->idtipollamada";
    $d->comentario = $d->comentario == '' ? "NULL" : "'$d->comentario'";
    $query = "UPDATE caso SET idequipo = $d->idequipo, idubicacion = $d->idubicacion, idtipollamada = $d->idtipollamada, comentario = $d->comentario ";
    $query.= "WHERE id = $d->id";
    //print $query;
    $db->doQuery($query);
});

$app->post('/d', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM bitacoracaso WHERE idcaso = ".$d->id);
    $db->doQuery("DELETE FROM caso WHERE id = ".$d->id);
});

$app->post('/cierre', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $d->serieequipouno = $d->serieequipouno == '' ? "NULL" : "'$d->serieequipouno'";
    $d->serieequipodos = $d->serieequipodos == '' ? "NULL" : "'$d->serieequipodos'";
    $d->serieequipotres = $d->serieequipotres == '' ? "NULL" : "'$d->serieequipotres'";
    $d->comentariocierre = $d->comentariocierre == '' ? "NULL" : "'$d->comentariocierre'";

    $query = "UPDATE caso SET ";
    $query.= "idestatus = 2, idtecnico = $d->idtecnico, idtipocaso = $d->idtipocaso, idtiposolucion = $d->idtiposolucion, ";
    $query.= "serieequipouno = $d->serieequipouno, serieequipodos = $d->serieequipodos, serieequipotres = $d->serieequipotres, ";
    $query.= "comentariocierre = $d->comentariocierre, fhcierre = '$d->fhcierrestr', idusuariocierra = $d->idusuariocierra ";
    $query.= "WHERE id = $d->id";
    $db->doQuery($query);
});

$app->get('/visitado/:idcaso', function($idcaso){
    $db = new dbcpm();
    print (int)$db->getOneField("SELECT COUNT(id) FROM bitacoracaso WHERE idcaso = $idcaso AND esvisita = 1") > 0 ? 1 : 0;
});

//API para bitácoras de caso
$app->get('/lstbitacoras/:idcaso', function($idcaso){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcaso, a.fechahora, a.esvisita, a.enviara, a.comentario, a.idusuario, b.nombre AS usuario ";
    $query.= "FROM bitacoracaso a INNER JOIN usuario b ON b.id = a.idusuario ";
    $query.= "WHERE a.idcaso = $idcaso ";
    $query.= "ORDER BY a.fechahora DESC";
    print $db->doSelectASJson($query);
});

$app->get('/getbitacora/:idbitacora', function($idbitacora){
    $db = new dbcpm();
    $query = "SELECT a.id, a.idcaso, a.fechahora, a.esvisita, a.enviara, a.comentario, a.idusuario, b.nombre AS usuario ";
    $query.= "FROM bitacoracaso a INNER JOIN usuario b ON b.id = a.idusuario ";
    $query.= "WHERE a.id = $idbitacora ";
    print $db->doSelectASJson($query);
});

function esVisita($db, $idcaso){ $db->doQuery("UPDATE caso SET idestatus = 4 WHERE id = $idcaso AND idestatus <> 2"); }

$app->post('/cb', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->enviara = $d->enviara == '' ? "NULL" : "'$d->enviara'";
    $query = "INSERT INTO bitacoracaso(idcaso, fechahora, esvisita, enviara, comentario, idusuario) VALUES(";
    $query.= "$d->idcaso, '$d->fechahorastr', $d->esvisita, $d->enviara, '$d->comentario', $d->idusuario";
    $query.= ")";
    $db->doQuery($query);
    if((int)$d->esvisita == 1){ esVisita($db, $d->idcaso); }
    print json_encode(['lastid' => $db->getLastId()]);
});

$app->post('/ub', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $d->enviara = $d->enviara == '' ? "NULL" : "'$d->enviara'";
    $query = "UPDATE bitacoracaso SET fechahora = '$d->fechahorastr', esvisita = $d->esvisita, enviara = $d->enviara, comentario = '$d->comentario', idusuario = $d->idusuario ";
    $query.= "WHERE id = $d->id";
    if((int)$d->esvisita == 1){ esVisita($db, $d->idcaso); }
    $db->doQuery($query);
});

$app->post('/db', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();
    $db->doQuery("DELETE FROM bitacoracaso WHERE id = ".$d->id);
});

$app->run();