<?php
require_once 'fpdf.php';
require_once 'db.php';
require_once 'NumberToLetterConverter.class.php';

class PDF extends FPDF{
    // Tabla simple
    function BasicTable($header, $data){
        // Cabecera
        foreach($header as $col)
            $this->Cell(40,7,$col,1);
        $this->Ln();
        // Datos
        foreach($data as $row)
        {
            foreach($row as $col)
                $this->Cell(40,6,$col,1);
            $this->Ln();
        }
    }
    // Una tabla más completa
    function ImprovedTable($header, $data, $w, $h = null)
    {
        // Anchuras de las columnas
        // Cabeceras
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C');
        $this->Ln();
        // Datos
        $h = is_null($h) ? 6 : $h;
        $mov = 15.5;
        //$numItems = count($data);
        //$cont = 0;
        foreach($data as $row){
            $this->Cell($mov);
            $this->Cell($w[0],$h,$row[0],'LR',0,'C');
            $this->Cell($w[1],$h,$row[1],'LR',0,'L');
            $this->Cell($w[2],$h,$row[2],'LR',0,'R');
            $this->Cell($w[3],$h,$row[3],'LR',0,'R');
            $this->Ln();
        }
        // Línea de cierre
        $this->Cell($mov);
        $this->Cell(array_sum($w),0,'','T');
    }
}

$meses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
$db = new dbcpm();
$numCheque = (int)$_GET['c'];
$query = "SELECT a.id, a.numero, a.fecha, DAY(a.fecha) AS dia, MONTH(a.fecha) AS mes, YEAR(a.fecha) AS anio, FORMAT(a.monto, 2) AS montostr, a.monto, a.beneficiario, a.concepto, ";
$query.= "CONCAT(b.nombre, ' / ', b.nomcuenta, ' / ', c.nommoneda, ' / ', b.nocuenta, ' / Cheque No. ', a.numero) AS banco, d.nomempresa AS empresa ";
$query.= "FROM tranban a INNER JOIN banco b ON b.id = a.idbanco INNER JOIN moneda c ON c.id = b.idmoneda INNER JOIN empresa d ON d.id = b.idempresa ";
$query.= "WHERE a.id = ".$numCheque;
$cheque = $db->getQuery($query)[0];
//var_dump($cheque);
$n2l = new NumberToLetterConverter();

$query = "SELECT b.codigo, b.nombrecta, a.debe, a.haber FROM detallecontable a INNER JOIN cuentac b ON b.id = a.idcuenta WHERE a.origen = 1 AND a.idorigen = ".$numCheque." ORDER BY a.debe DESC";
$detcont = $db->getQueryAsArray($query);
$query = "SELECT '' AS codigo, 'TOTALES' AS nombrecta, SUM(a.debe) AS debe, SUM(a.haber) AS haber FROM detallecontable a INNER JOIN cuentac b ON b.id = a.idcuenta WHERE a.origen = 1 AND a.idorigen = ".$numCheque;
$totdet = $db->getQueryAsArray($query);
array_push($detcont, $totdet[0]);
//var_dump($detcont);

$query = "UPDATE tranban SET impreso = 1 WHERE id = ".$numCheque;
$db->doQuery($query);

//Creación del PDF
$um = 'mm';
//$pdf = new FPDF('P', $um, 'Letter');
$pdf = new PDF('P', $um, 'Letter');
$conv = $um == 'mm' ? 10 : 1;
$pdf->SetMargins(0, 2.15 * $conv, 0);
$pdf->AddPage();
$pdf->SetFont('Arial','', 9);
$borde = 0;
//Generación del cheque
$pdf->Cell(2.2 * $conv);
$pdf->Cell(9 * $conv, 0.7 * $conv, 'Guatemala, '.$cheque->dia.' de '.$meses[(int)$cheque->mes].' de '.$cheque->anio, $borde, 0);
$pdf->Cell(2.3 * $conv);
$pdf->Cell(4.4 * $conv, 0.7 * $conv, $cheque->montostr, $borde, 0);
$pdf->Ln();
$pdf->Cell(2.2 * $conv);
$pdf->Cell(14 * $conv, 0.65 * $conv, iconv('UTF-8', 'windows-1252', $cheque->beneficiario), $borde, 0);
$pdf->Ln();
$pdf->Cell(1.8 * $conv);
$pdf->Cell(14.5 * $conv, 0.65 * $conv, $n2l->to_word_int($cheque->monto), $borde, 0);
$pdf->Ln(1.3 * $conv);
$pdf->Cell(3.5 * $conv);
$pdf->Cell(4 * $conv, 0.65 * $conv, 'NO NEGOCIABLE', $borde, 0);

$pdf->AddPage();
$pdf->SetFont('Arial','', 9);
$borde = 0;
//Generación del cheque
$pdf->Cell(2.2 * $conv);
$pdf->Cell(9 * $conv, 0.7 * $conv, 'LUGAR Y FECHA: Guatemala, '.$cheque->dia.' de '.$meses[(int)$cheque->mes].' de '.$cheque->anio, $borde, 0);
$pdf->Cell(2.3 * $conv);
$pdf->Cell(4.4 * $conv, 0.7 * $conv, 'POR: '.$cheque->montostr, $borde, 0);
$pdf->Ln();
$pdf->Cell(2.2 * $conv);
$pdf->Cell(14 * $conv, 0.65 * $conv, 'PAGO A LA ORDEN DE: '.iconv('UTF-8', 'windows-1252', $cheque->beneficiario), $borde, 0);
$pdf->Ln();
$pdf->Cell(1.8 * $conv);
$pdf->Cell(14.5 * $conv, 0.65 * $conv, 'POR: '.$n2l->to_word_int($cheque->monto), $borde, 0);
$pdf->Ln(1.3 * $conv);
$pdf->Cell(3.5 * $conv);
$pdf->Cell(4 * $conv, 0.65 * $conv, 'NO NEGOCIABLE', $borde, 0);
$pdf->Ln(1.3 * $conv);
$pdf->Cell(1.8 * $conv);
$pdf->Cell(10 * $conv, 0.65 * $conv, iconv('UTF-8', 'windows-1252', $cheque->concepto), $borde, 0);
//Generación del boucher
$pdf->Ln(30);

$pdf->Cell(1.55 * $conv);
$pdf->Cell(20 * $conv, 0.7 * $conv, $cheque->empresa, 0, 2);

$pdf->Cell(20 * $conv, 0.7 * $conv, $cheque->banco, 0, 2);
$pdf->SetFont('Arial','', 8);

$header = [iconv('UTF-8', 'windows-1252', 'CÓDIGO'), 'CUENTA', 'Cargos', 'Abonos'];
$anchura = [20, 100, 30, 30];
$pdf->ImprovedTable($header, $detcont, $anchura);

$pdf->Cell(1.5 * $conv, 1 * $conv, '', 0, 2);
$pdf->Cell(-18 * $conv);

$anchura = [45, 45, 45, 45];
$header = ['Hecho por', 'Revisado por', 'Autorizado por', iconv('UTF-8', 'windows-1252', 'Recibí conforme')];
$data = [['', '', '', '']];
$pdf->ImprovedTable($header, $data, $anchura, 15);

$pdf->Output();