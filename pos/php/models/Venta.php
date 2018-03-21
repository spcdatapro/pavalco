<?php

/**
* 
*/
class Venta extends Principal
{
	public $venta;
	public $cliente;
	public $bodega;
	public $mp;
	public $sr;
	protected $tabla = 'factura';
	protected $dtabla = 'detfact';
	
	function __construct($id = '')
	{
		parent::__construct();

		if ($id) {
			$this->cargar($id);
		}
	}

	public function cargar($id)
	{
		$this->venta = (object)$this->db->get(
			$this->tabla, 
			['*'], 
			['id' => $id]
		);
	}

	public function cargar_bodega()
	{
		$this->bodega = (object)$this->db->get(
			'bodega', 
			['*'], 
			['id' => $this->venta->idbodega]
		);
	}

	public function cargar_mp()
	{
		$this->mp = (object)$this->db->get(
			'metodopago', 
			['*'], 
			['id' => $this->venta->idmetodopago]
		);
	}

	public function cargar_cliente()
	{
		$this->cliente = (object)$this->db->get(
			"cliente", 
			['*'], 
			['id' => $this->venta->idcliente]
		);
	}

	public function cargar_serie()
	{
		$this->sr = new Serie($this->venta->idserie);
	}

	public function cargar_atributos()
	{
		$this->cargar_cliente();
		$this->cargar_serie();
		$this->cargar_bodega();
		$this->cargar_mp();
	}

	public function guardar($args=[])
	{
		if (is_array($args) && !empty($args)) {
			if (elemento($args, 'idbodega')) {
				$this->set_dato('idbodega', $args['idbodega']);
			}

			if (elemento($args, 'idmetodopago')) {
				$this->set_dato('idmetodopago', $args['idmetodopago']);
			}

			if (elemento($args, 'nit')) {
				$tmp = $this->db->count('cliente', [
					'AND' => [
						'nit' => $args['nit']
					]
				]);

				if ($tmp == 0) {
					$cliente = $this->db->insert(
						'cliente', 
						[
							'nombre' => $args['nombre'], 
							'nit' => $args['nit'], 
							'direccion' => $args['direccion']
						]
					);
				} else {
					$cliente = (object)$this->db->get(
						'cliente', 
						['*'], 
						['nit' => $args['nit']]
					);

					$this->set_dato('idcliente', $cliente->id);
				}
			}

			if (isset($args['confirmada'])) {
				$this->set_dato('confirmada', $args['confirmada']);
			}

			if (elemento($args, 'conceptomayor')) {
				$this->set_dato('conceptomayor', $args['conceptomayor']);
			}
		}

		if (!empty($this->datos)) {
			if ($this->venta) {
				if ($this->venta->confirmada) {
					$this->set_mensaje("Venta confirmada, no puedo editarla.");
				} else {
					$this->set_dato('total', $this->get_total());
					if ($this->db->update($this->tabla, $this->datos, ["id" => $this->venta->id])) {
						$this->cargar($this->venta->id);

						return TRUE;
					} else {
						if ($this->db->error()[0] == 0) {
							$this->set_mensaje('Nada que actualizar.');
						} else {
							$this->set_mensaje('Error en la base de datos al actualizar: ' . $this->db->error()[2]);
						}
					}
				}
			} else {
				$this->set_dato('idtipofactura', 7); # Cambiar posteriormente a algo automático
				$this->set_dato('idempresa', $_SESSION['workingon']);
				$this->set_dato('idmoneda', 1); # Cambiar posteriormente a algo automático

				$lid = $this->db->insert($this->tabla, $this->datos);

				if ($lid) {
					$this->cargar($lid);

					return TRUE;
				} else {
					$this->set_mensaje('Error en la base de datos al guardar: ' . $this->db->error()[2]);
				}
			}
		} else {
			$this->set_mensaje('No hay datos que guardar o actualizar.');
		}

		return FALSE;
	}

	public function get_total()
	{
		return $this->db->sum($this->dtabla, "preciotot", ['idfactura' => $this->venta->id]);
	}

	public function generar_factura()
	{
		# Estoy usando la serie con id 1, tenemos que automatizar esto
		$idserie = 1;
		$serie   = (object)$this->db->get('serie', ['*'], ['id' => $idserie]);
		$factura = ($serie->correlativo+1);

		$this->set_dato('idserie', $idserie);
		$this->set_dato('numero', $factura);
		$this->set_dato('#fecha', 'CURDATE()');

		if ($this->guardar()) {
			unset($this->datos['numero']);

			if ($this->db->update('serie', ['correlativo' => $factura], ['id' => 1])) {
				return TRUE;
			} else {
				$this->set_mensaje('Error en la base de datos al actualizar de serie: ' . $this->db->error()[2]);
			}
		} else {
			$this->set_mensaje("\nError al generar factura.");
		}

		return FALSE;
	}

	public function verificar_existencia($args=[])
	{
		$etabla = "existencia";

		$ex = (object)$this->db->get(
			$etabla, 
			['*'], 
			[
				'AND' => [
					'idparte'  => $args['id'], 
					'idbodega' => $this->venta->idbodega
				]
			]
		);

		if (!isset($ex->scalar)) {
			if ($args['accion'] == 'restar') {
				if ($ex->cantidad >= $args['cantidad']) {
					$cantidad = ($ex->cantidad-$args['cantidad']);
				} else {
					$this->set_mensaje("Ya no hay existencias disponibles.");
					return FALSE;
				}
			} else if ($args['accion'] == 'sumar') {
				$cantidad = ($ex->cantidad+$args['cantidad']);
			} else {
				return FALSE;
			}

			$this->db->update(
				$etabla, 
				['cantidad' => $cantidad], 
				["id" => $ex->id]
			);

			return TRUE;
				
		} else {
			$this->set_mensaje("No hay existencias disponibles.");
			return FALSE;
		}

		return FALSE;
	}

	public function agregar_detalle($args=[])
	{
		if ($this->venta->confirmada) {
			$this->set_mensaje("Venta confirmada, no puedo agregar items.");
		} else {
			$args['accion'] = 'restar';

			if ($this->verificar_existencia($args)) {
				$datos = [
					'idfactura'      => $this->venta->id, 
					'idparte'        => $args['id'], 
					'cantidad'       => $args['cantidad'], 
					'preciounitario' => $args['precio'], 
					'preciotot'      => ($args['cantidad'] * $args['precio'])
				];

				$this->db->insert($this->dtabla, $datos);

				return TRUE;
			}
		}
		return FALSE;
	}

	public function eliminar_detalle($id)
	{
		if ($this->venta->confirmada) {
			$this->set_mensaje("Venta confirmada, no puedo eliminar items.");
		} else {
			
			$det = (object)$this->db->get(
				$this->dtabla, 
				['*'], 
				[
					'AND' => [
						'id'        => $id, 
						'idfactura' => $this->venta->id
					]
				]
					
			);

			$data = $this->db->delete($this->dtabla, [
				'AND' => [
					'id'        => $id, 
					'idfactura' => $this->venta->id
				] 
			]);

			if ($data > 0) {
				$args = [
					'accion'   => 'sumar', 
					'cantidad' => $det->cantidad, 
					'id'       => $det->idparte
				];

				$this->verificar_existencia($args);
				return TRUE;
			} else {
				$this->set_mensaje("Nada que eliminar");
			}
		}

		return FALSE;
	}

	public function get_detalle()
	{
		return $this->db->query("SELECT
					a.*, b.descripcion, b.precio, b.codigointerno
				FROM {$this->dtabla} a 
				JOIN parte b on b.id = a.idparte 
				where a.idfactura = {$this->venta->id}
				")->fetchAll();
	}

	public function get_datos_formato()
	{
		$detalle = [];

		foreach ($this->get_detalle() as $row) {
			$detalle[] = [
				[
					'campo' => 'cantidad', 
					'valor' => $row['cantidad']
				],
				[
					'campo' => 'codigo', 
					'valor' => $row['codigointerno']
				],
				[
					'campo' => 'descripcion', 
					'valor' => $row['descripcion']
				], 
				[
					'campo' => 'preciounitario', 
					'valor' => $row['preciounitario']
				],
				[
					'campo' => 'preciotot', 
					'valor' => $row['preciotot']
				]
			];
		}

		$letras = new NumberToLetterConverter();

		$datos = [
			[
				'campo' => 'factura', 
				'valor' => $this->venta->numero
			], 
			[
				'campo' => 'serie', 
				'valor' => $this->sr->serie->serie
			], 
			[
				'campo' => 'fecha', 
				'valor' => $this->venta->fecha
			], 
			[
				'campo' => 'conceptomayor', 
				'valor' => $this->venta->conceptomayor
			], 
			[
				'campo' => 'total', 
				'valor' => $this->venta->total
			], 
			[
				'campo' => 'cliente', 
				'valor' => $this->cliente->nombre
			], 
			[
				'campo' => 'nit', 
				'valor' => $this->cliente->nit
			], 
			[
				'campo' => 'direccion', 
				'valor' => $this->cliente->direccion
			], 
			[
				'campo' => 'detalle', 
				'valor' => $detalle
			], 
			[
				'campo' => 'montoletras', 
				'valor' => $letras->to_word($this->venta->total, 'GTQ')
			]
		];

		return $datos;
	}

	public function get_datos()
	{
		$datos = [
			'id'            => $this->venta->id, 
			'confirmada'    => $this->venta->confirmada, 
			'total'         => $this->venta->total, 
			'fecha'         => $this->venta->fecha, 
			'conceptomayor' => $this->venta->conceptomayor, 
			'numero'        => $this->venta->numero, 
			'idmetodopago'  => $this->venta->idmetodopago, 
			'idbodega'		=> $this->venta->idbodega
		];

		if ($this->venta->idcliente) {
			$this->cargar_cliente();
			$datos['nombre']    = $this->cliente->nombre;
			$datos['nit']       = $this->cliente->nit;
			$datos['direccion'] = $this->cliente->direccion;
		}

		if ($this->venta->idserie) {
			$this->cargar_serie();
			$datos['serie'] = $this->sr->serie->serie;
		}

		if ($this->venta->idbodega) {
			$this->cargar_bodega();
			$datos['nbodega'] = $this->bodega->nombre;
		}

		if ($this->venta->idmetodopago) {
			$this->cargar_mp();
			$datos['metodopago'] = $this->mp->descripcion;
		}

		return $datos;
	}
}