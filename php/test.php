<?php
require 'vendor/autoload.php';
require_once 'db.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$app->get('/lstmodulos', function () {
    $db = new dbcpm();
    $conn = $db->getConn();
    $data = $conn->select('modulo',['id', 'descmodulo']);
    print json_encode($data);
});

$app->get('/pdf', function(){
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(40,10,'¡Hola, Mundo!');
    $pdf->Output();
});

function procDataGen($d){
    $d->fechainiciofact = new DateTime($d->fechainiciofact, new DateTimeZone('America/Guatemala'));
    $d->dias = (int)$d->dias;
    return $d;
};

$app->get('/fixcargos', function(){
    $db = new dbcpm();

    $query = "SELECT DISTINCT a.idcontrato FROM cargo a INNER JOIN confcargocontrato b on b.id = a.idconfcargo ORDER BY a.idcontrato, a.fechacobro, a.nocuota";
    $contratos = $db->getQuery($query);
    $cntContratos = count($contratos);
    for($i = 0; $i < $cntContratos; $i++){
        $contrato = $contratos[$i];

        $query = "SELECT a.fechainiciofact, c.dias, DATE_FORMAT(a.fechainiciofact, '%d') AS dia FROM contrato a INNER JOIN detdatoesp b ON a.id = b.idcontrato INNER JOIN periodicidad C ON c.id = b.idperiodicidad ";
        $query.= "WHERE a.id = ".$contrato->idcontrato." AND b.autorizada = 1";
        $dg = procDataGen($db->getQuery($query)[0]);

        $query = "SELECT a.id, a.idcontrato, a.idconfcargo, a.fechacobro, a.nocuota, b.tipocargo ";
        $query.= "FROM cargo a INNER JOIN confcargocontrato b on b.id = a.idconfcargo ";
        $query.= "WHERE a.idcontrato = $contrato->idcontrato ";
        $query.= "ORDER BY a.idcontrato, a.fechacobro, a.nocuota";
        $cargos = $db->getQuery($query);
        $cntCargos = count($cargos);
        $hubocuota = false;
        for($j = 0; $j < $cntCargos; $j++){
            $cargo = $cargos[$j];
            $escuota = (int)$cargo->tipocargo == 0;
            if($escuota){ $hubocuota = true; }
            $query = "UPDATE cargo SET fechacobro = '".$dg->fechainiciofact->format('Y-m-'.$dg->dia)."' WHERE id = $cargo->id";
            $db->doQuery($query);
            //if($escuota || $hubocuota){ $dg->fechainiciofact->add(new DateInterval('P' . $dg->dias . 'D')); }
            if($escuota || $hubocuota){ $dg->fechainiciofact->add(new DateInterval('P1M')); }
        }
    }

    print 'Proceso terminado...';
});

$app->run();







