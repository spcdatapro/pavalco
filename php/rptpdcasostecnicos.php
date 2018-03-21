<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->post('/pdcasostecs', function(){
    $d = json_decode(file_get_contents('php://input'));
    $db = new dbcpm();

    $query = "SELECT a.id, a.nombre, COUNT(b.id) AS cantidad, 0.00 AS porcentaje ";
    $query.= "FROM tecnico a LEFT JOIN caso b ON a.id = b.idtecnico ";
    $query.= "WHERE b.fhcierre >= '$d->fdelstr' AND b.fhcierre <= '$d->falstr' ";
    $query.= "GROUP BY a.id ";
    $query.= "ORDER BY a.nombre";
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