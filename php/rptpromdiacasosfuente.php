<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/pdcasosfuente', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $query = "SELECT a.id, a.descripcion, COUNT(c.id) AS cantidad, 0.00 AS porcentaje ";
    $query.= "FROM fuentecaso a LEFT JOIN tipocaso b ON a.id = b.idfuentecaso LEFT JOIN caso c ON b.id = c.idtipocaso ";
    $query.= "WHERE c.fhcierre >= '$d->fdelstr' AND c.fhcierre <= '$d->falstr' ";
    $query.= "GROUP BY a.id ";
    $query.= "ORDER BY a.descripcion";
    $promedio = $db->getQuery($query);

    $dh = $db->diasHabiles($d->fdelstr, $d->falstr);
    $cntProm = count($promedio);
    for($i = 0; $i < $cntProm; $i++){
        $p = $promedio[$i];
        $p->porcentaje = $dh > 0 ? round((int)$p->cantidad / $dh, 2) : 0.00;
    }

    print json_encode(['diashabiles' => $dh, 'promedios' => $promedio]);

});

$app->run();