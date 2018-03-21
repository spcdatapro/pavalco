<?php

/**
* 
*/
class Serie extends Principal
{
	public $serie;
	protected $tabla = 'serie';
	
	function __construct($id = '')
	{
		parent::__construct();

		if (!empty($id)) {
			$this->cargar($id);
		}
	}

	public function cargar($id)
	{
		$this->serie = (object)$this->db->get(
			$this->tabla, 
			['*'], 
			['id' => $id]
		);
	}

	public function get_campo_formato($campo)
	{
		return (object)$this->db->get(
			'serieformato', 
			['*'], 
			[
				'AND' => [
					'campo'   => $campo, 
					'idserie' => $this->serie->id
				]
			]
		);
	}
}