<?php 

if ( ! function_exists('elemento')) {
	function elemento($arreglo, $indice, $return = NULL)
	{
		if (is_array($arreglo) && isset($arreglo[$indice]) && !empty($arreglo[$indice])) {
			return $arreglo[$indice];
		}

		return $return;
	}
}

if ( ! function_exists('depurar')) {
	function depurar($datos)
	{
		echo "<pre>";
		print_r($datos, 1);
		echo "</pre>";
	}
}

if ( ! function_exists('get_limite')) {
	function get_limite()
	{
		return 10;
	}
}

if ( ! function_exists('enviar_json')) {
	function enviar_json($arreglo)
	{
		header('Content-Type: application/json');
		echo json_encode($arreglo);
	}
}

if ( ! function_exists('mostrar_errores')) {
	function mostrar_errores()
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
}

if ( ! function_exists('generar_formato')) {
	/**
	 * Permite generar el pdf de un formato de factura
	 * @param  [object] $pdf objeto de la clase TCPDF
	 * @param  [object] $v objecto de la clase Venta, que incluye toda los datos de la factura
	 * @return [object] TCPDF
	 */
	function generar_formato($pdf, $v)
	{
		$pdf->AddPage($v->sr->serie->orientacion, $v->sr->serie->hoja);

		foreach ($v->get_datos_formato() as $row) {
			$conf = $v->sr->get_campo_formato($row['campo']);

			if (!isset($conf->scalar) && $conf->visible == 1) {
				if ($conf->detalle == 1) {
					foreach ($row['valor'] as $key => $det) {
						$pdf->SetY($conf->psy+($key*$conf->espacio));

						foreach ($det as $item) {
							$dconf = $v->sr->get_campo_formato($item['campo']);
							if (!isset($dconf->scalar) && $dconf->visible == 1) {
								$pdf->SetX($dconf->psx);

								if ($dconf->multilinea == 1) {
									$pdf->MultiCell(
										$dconf->ancho, 
										$dconf->espacio, 
										$item['valor'], 
										$v->sr->serie->borde, 
										$dconf->alineacion 
									);
								} else {
									$pdf->Cell(
										$dconf->ancho, 
										$dconf->espacio, 
										$item['valor'], 
										$v->sr->serie->borde, 
										$dconf->alineacion 
									);
								}
							}
						}
					}
				} else {
					$pdf->SetY($conf->psy);
					$pdf->SetX($conf->psx);
					$pdf->SetFont($conf->letra, $conf->estilo, $conf->tamanio);
					

					if ($conf->multilinea == 1) {
						$pdf->MultiCell(
							$conf->ancho, 
							$conf->espacio, 
							$row['valor'], 
							$v->sr->serie->borde, 
							$conf->alineacion 
						);
					} else {
						$pdf->Cell(
							$conf->ancho, 
							$conf->espacio, 
							$row['valor'], 
							$v->sr->serie->borde, 
							$conf->alineacion 
						);
					}
				}
			}
		}

		return $pdf;
	}
}

if ( ! function_exists('fecha_angularjs')) {
	function fecha_angularjs($fecha, $tipo='')
	{
		$fecha = substr($fecha, 0, strpos($fecha, '('));
		
		if ($fecha !== false) {
			switch ($tipo) {
				case 1: # 
					return date('Y-m-d h:i:s', strtotime($fecha));
					break;
				
				default:
					return date('Y-m-d', strtotime($fecha));
					break;
			}
		} else {
			return NULL;
		}
	}
}