<?php

/**
* 
*/
class Ingreso extends Principal
{
	public $ingreso;
	public $usuario;
	public $bodega;
	protected $tabla = 'ingreso';
	protected $dtabla = 'detingreso';
	
	function __construct($id = '')
	{
		parent::__construct();

		if ($id) {
			$this->cargar($id);
		}
	}

	public function cargar($id)
	{
		$this->ingreso = (object)$this->db->get(
			$this->tabla, 
			['*'], 
			['id' => $id]
		);
	}

	public function cargar_bodega($id)
	{
		$this->bodega = (object)$this->db->get(
			'bodega', 
			['*'], 
			['id' => $this->ingreso->idbodega]
		);
	}	

	public function cargar_usuario($id)
	{
		$this->usuario = (object)$this->db->get(
			'usuario', 
			['*'], 
			['id' => $this->ingreso->idusrcrea]
		);
	}

	public function cargar_atributos()
	{
		$this->cargar_usuario();
		$this->cargar_bodega();
	}

	public function guardar($args=[])
	{
		if (is_array($args) && !empty($args)) {
			if (elemento($args, 'idbodega')) {
				$this->set_dato('idbodega', $args['idbodega']);
			}

			if (isset($args['confirmado'])) {
				$this->set_dato('confirmado', $args['confirmado']);

				if ($args['confirmado'] == 1) {
					$this->set_dato('#confirmadofecha', 'NOW()');
				}
			}

			if (elemento($args, 'descripcion')) {
				$this->set_dato('descripcion', $args['descripcion']);
			}

			if (elemento($args, 'documento')) {
				$this->set_dato('documento', $args['documento']);
			}

			if (elemento($args, 'fecha')) {
				$this->set_dato('fecha', fecha_angularjs($args['fecha']));
			}
		}

		if (!empty($this->datos)) {
			if ($this->ingreso) {
				if ($this->ingreso->confirmado) {
					$this->set_mensaje("Ingreso confirmado, no puedo editarlo.");
				} else {
					if ($this->db->update($this->tabla, $this->datos, ["id" => $this->ingreso->id])) {
						$this->cargar($this->ingreso->id);

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
				$this->set_dato('idusrcrea', $_SESSION['uid']);
				$this->set_dato('#fhcreacion', 'NOW()');

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

	public function agregar_detalle($args=[])
	{
		if ($this->ingreso->confirmado) {
			$this->set_mensaje("Ingreso confirmado, no puedo agregar items.");
		} else {
			$datos = [
				'idparte'       => $args['parte'], 
				'cantidad'      => $args['cantidad'], 
				'costounitario' => $args['costounitario'],
				'idingreso'     => $this->ingreso->id, 
				'idbodega'      => $this->ingreso->idbodega
			];

			$lid = $this->db->insert($this->dtabla, $datos);

			if ($lid) {
				$this->cargar($lid);

				return TRUE;
			} else {
				$this->set_mensaje('Error en la base de datos al guardar: ' . $this->db->error()[2]);
			}
		}
		return FALSE;
	}

	public function eliminar_detalle($id)
	{
		if ($this->ingreso->confirmado) {
			$this->set_mensaje("Ingreso confirmado, no puedo eliminar items.");
		} else {
			$data = $this->db->delete($this->dtabla, [
				'AND' => [
					'id'       => $id, 
					'idingreso' => $this->ingreso->id
				] 
			]);

			if ($data > 0) {
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
			a.*, 
			b.descripcion, 
			b.codigointerno
			FROM detingreso a 
			JOIN parte b on b.id = a.idparte 
			where a.idingreso = {$this->ingreso->id}
		")->fetchAll();
	}

	public function set_existencias()
	{
		$etabla = "existencia";

		if ($this->ingreso->existencia == 0) {
			foreach ($this->get_detalle() as $row) {
				$row = (object)$row;

				$ex = (object)$this->db->get(
					$etabla, 
					['*'], 
					[
						'AND' => [
							'idparte'  => $row->idparte, 
							'idbodega' => $this->ingreso->idbodega
						]
					]
				);

				if (isset($ex->scalar)) {
					$this->db->insert(
						$etabla, 
						[
							'idparte'  => $row->idparte, 
							'idbodega' => $this->ingreso->idbodega, 
							'cantidad' => $row->cantidad
						]
					);
				} else {
					$this->db->update(
						$etabla, 
						['cantidad' => ($row->cantidad + $ex->cantidad)], 
						["id" => $ex->id]
					);
				}
			}

			$this->set_dato('existencia', 1);
			$this->guardar();

			return TRUE;
		}

		return FALSE;
	}

	public function get_datos()
	{
		$datos = [
			'id'              => $this->ingreso->id, 
			'fecha'           => $this->ingreso->fecha, 
			'documento'       => $this->ingreso->documento, 
			'idbodega'		  => $this->ingreso->idbodega, 
			'fhcreacion'      => $this->ingreso->fhcreacion, 
			'descripcion'     => $this->ingreso->descripcion, 
			'confirmado'      => $this->ingreso->confirmado, 
			'confirmadofecha' => $this->ingreso->confirmadofecha
		];

		if ($this->ingreso->idbodega) {
			$this->cargar_bodega($this->ingreso->idbodega);
			$datos['nbodega'] = $this->bodega->nombre;
		}

		if ($this->ingreso->idusrcrea) {
			$this->cargar_usuario($this->ingreso->idusrcrea);
			$datos['nusuario'] = $this->usuario->nombre;
			$datos['correo']   = $this->usuario->correoe;
		}

		return $datos;
	}
}