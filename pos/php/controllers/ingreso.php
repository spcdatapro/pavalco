<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION)) {
    session_start();
}

require dirname(dirname(dirname(__DIR__))) . '/php/vendor/autoload.php';
require dirname(dirname(dirname(__DIR__))) . '/php/ayuda.php';
require dirname(__DIR__) . '/Principal.php';
require dirname(__DIR__) . '/models/General.php';
require dirname(__DIR__) . '/models/Serie.php';
require dirname(__DIR__) . '/models/Ingreso.php';


$app = new \Slim\Slim();

$app->get('/buscar', function(){
	$b = new General();

	$resultados = $b->buscar_ingreso($_GET);
	
	enviar_json([
		'cantidad'   => count($resultados), 
		'resultados' => $resultados, 
		'maximo'     => get_limite()
	]);
});

$app->post('/guardar', function(){
	$i = new Ingreso();

	$data = ['exito' => 0];

	if (elemento($_POST, 'id')) {
		$i->cargar($_POST['id']);
	}

	if (elemento($_POST, 'id') && elemento($_POST, 'confirmado')) {
		if (count($i->get_detalle()) > 0) {
			$i->set_existencias();
			
			if ($i->guardar($_POST)) {
				$data['exito']   = 1;
				$data['mensaje'] = 'Se ha guardado con éxito.';
				$data['ingreso'] = $i->get_datos();
			} else {
				$data['mensaje'] = $i->get_mensaje();
				$data['ingreso'] = $_POST;
			}
		} else {
			$data['mensaje'] = "No puedo confirmar un ingreso sin detalle.";
		}
	} else {
		if ($i->guardar($_POST)) {
			$data['exito']   = 1;
			$data['mensaje'] = 'Se ha guardado con éxito.';
			$data['ingreso'] = $i->get_datos();
		} else {
			$data['mensaje'] = $i->get_mensaje();
			$data['ingreso'] = $_POST;
		}
	}
	
    enviar_json($data);
});

$app->get('/buscar_producto', function(){
	$b = new General();

	$resultados = $b->buscar_producto($_GET);
	
	enviar_json([
		'cantidad'   => count($resultados), 
		'productos' => $resultados
	]);
});


$app->post('/agregar_detalle/:ingreso', function($ingreso){
	$i = new Ingreso($ingreso);

	$datos = ['exito' => 0];

	if ($i->agregar_detalle($_POST)) {
		$datos['exito']   = 1;
		$datos['mensaje'] = "Se agregó con éxito.";
	} else {
		$datos['mensaje'] = $i->get_mensaje();
	}

	enviar_json($datos);
});

$app->post('/eliminar_detalle/:ingreso', function($ingreso){
	$data = ['exito' => 0];

	if (elemento($_POST, 'id')) {
		$v = new Ingreso($ingreso);

		if ($v->eliminar_detalle($_POST['id'])) {
			$data['exito']  = 1;
			$data['mensaje'] = "Se eliminó con éxito";
		} else {
			$data['mensaje'] = $v->get_mensaje();
		}
	} else {
		$data['mensaje'] = "Faltan datos obligatorios para eliminar";
	}

	enviar_json($data);
});

$app->get('/ver_detalle/:ingreso', function($ingreso){
	$i = new Ingreso($ingreso);

	enviar_json($i->get_detalle());
});

$app->run();