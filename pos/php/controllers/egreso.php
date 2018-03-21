<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION)) {
    session_start();
}

require dirname(dirname(dirname(__DIR__))) . '/php/vendor/autoload.php';
require dirname(dirname(dirname(__DIR__))) . '/php/ayuda.php';
require dirname(dirname(dirname(__DIR__))) . '/php/NumberToLetterConverter.class.php';

require dirname(__DIR__) . '/Principal.php';
require dirname(__DIR__) . '/models/General.php';
require dirname(__DIR__) . '/models/Serie.php';
require dirname(__DIR__) . '/models/Venta.php';


$app = new \Slim\Slim();

$app->get('/buscar', function(){
	$b = new General();

	$resultados = $b->buscar_venta($_GET);
	
	enviar_json([
		'cantidad'   => count($resultados), 
		'resultados' => $resultados, 
		'maximo'     => get_limite()
	]);
});

$app->get('/buscar_producto', function(){
	$b = new General();

	$resultados = $b->buscar_producto($_GET);
	
	enviar_json([
		'cantidad'   => count($resultados), 
		'productos' => $resultados
	]);
});

$app->get('/buscar_nit/:nit', function($nit){
    $e = new General();

    enviar_json($e->buscar_nit(['nit' => $nit]));
});

$app->get('/get_bodegas', function(){
	$e = new General();

	enviar_json($e->get_bodegas());
});


$app->post('/iniciar_venta', function(){
	$v = new Venta();

	$data = ['exito' => 0];

	if (elemento($_POST, 'id')) {
		$v->cargar($_POST['id']);
	}

	if (elemento($_POST, 'id') && elemento($_POST, 'confirmada')) {
		if ($v->generar_factura()) {
			if ($v->guardar($_POST)) {
				$data['exito']   = 1;
				$data['mensaje'] = 'Se ha guardado con éxito.';
				$data['venta']   = $v->get_datos();
			} else {
				$data['mensaje'] = $v->get_mensaje();
				$data['venta']   = $_POST;
			}
		} else {
			$data['mensaje'] = $v->get_mensaje();
		}
	} else {
		if ($v->guardar($_POST)) {
			$data['exito']   = 1;
			$data['mensaje'] = 'Se ha guardado con éxito.';
			$data['venta']   = $v->get_datos();
		} else {
			$data['mensaje'] = $v->get_mensaje();
			$data['venta']   = $_POST;
		}
	}

		

    enviar_json($data);
});

$app->post('/agregar_detalle/:venta', function($venta){
	$v = new Venta($venta);

	$datos = ['exito' => 1];

	if ($v->agregar_detalle($_POST)) {
		$datos['mensaje'] = "Se agregó con éxito.";
	} else {
		$datos['exito'] = 0;
		$datos['mensaje'] = $v->get_mensaje();
	}

	enviar_json($datos);
});

$app->post('/eliminar_detalle/:venta', function($venta){
	$data = ['exito' => 0];

	if (elemento($_POST, 'id')) {
		$v = new Venta($venta);

		if ($v->eliminar_detalle($_POST['id'])) {
			$data['exito']   = 1;
			$data['mensaje'] = "Se eliminó con éxito";
		} else {
			$data['mensaje'] = $v->get_mensaje();
		}
	} else {
		$data['mensaje'] = "Faltan datos obligatorios para eliminar";
	}

	enviar_json($data);
});

$app->get('/ver_detalle/:venta', function($venta){
	$v = new Venta($venta);

	enviar_json($v->get_detalle());
});


$app->get('/get_metodopago', function(){
	$g = new General();

	enviar_json($g->get_metodopago());
});

$app->get('/factura/:venta', function($venta)
{
	require dirname(dirname(dirname(__DIR__))) . '/libs/tcpdf/tcpdf.php';	

	$v = new Venta($venta);
	
	$v->cargar_atributos();

	$pdf = new TCPDF();
	
	$pdf = generar_formato($pdf, $v);

	$pdf->Output("factura.pdf", 'I');
	die();
});

$app->run();