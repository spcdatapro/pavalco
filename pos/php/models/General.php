<?php

/**
* Clase para  realizar búsquedas
*/
class General extends Principal
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function buscar_venta($args=[])
	{
		$condicion = [];

		if (elemento($args, 'termino')) {
			$condicion["numero[~]"] = $args['termino'];
		}

		$condicion["LIMIT"] = [elemento($args, 'inicio', 0), get_limite()];

		$tmp = $this->db->select('factura', ['id'], $condicion);

		$resultados = [];

		foreach ($tmp as $row) {
			$v = new Venta($row['id']);
			$resultados[] = $v->get_datos();
		}

		return $resultados;
	}

	public function buscar_ingreso($args=[])
	{
		$condicion = [];

		if (elemento($args, 'termino')) {
			$condicion["documento[~]"] = $args['termino'];
		}

		$condicion["LIMIT"] = [elemento($args, 'inicio', 0), get_limite()];

		$tmp = $this->db->select('ingreso', ['id'], $condicion);

		$resultados = [];

		foreach ($tmp as $row) {
			$i = new Ingreso($row['id']);

			$resultados[] = $i->get_datos();
		}

		return $resultados;
	}

	public function buscar_producto($args=[])
	{
		$condicion = " AND a.idbodega = " . $args['idbodega'];

		if (elemento($args, 'termino')) {
            $termino = $args['termino'];
            $condicion = " and (b.descripcion like '%{$termino}%' or b.codigointerno = '{$termino}')";
		}

		return $this->db->query("SELECT 
			a.cantidad, 
			b.*, 
			c.descripcion AS marca 
			FROM existencia a
			join parte b on b.id = a.idparte
			JOIN marca c on c.id = b.idmarca  
			where a.cantidad > 0 {$condicion}")
		->fetchAll();
	}

	public function buscar_nit(Array $args)
	{
		return $this->db->get('cliente', 
			['id', 'nombre', 'direccion'], 
			['nit' => $args['nit']]
		);
	}

	public function get_bodegas()
	{
		# SELECT * FROM pavalco.bodega
		return $this->db->select('bodega', ['id', 'nombre']);
	}

	public function get_metodopago()
	{
		return $this->db->select('metodopago', ['id', 'descripcion']);
	}
}