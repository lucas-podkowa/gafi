<?php
include_once 'licencias/dao_licencia.php';
include_once 'persona/dao_persona.php';

class cn_licencia extends gafi_cn
{

	// ###################################################################################
	//----  L I C E N C I A  -------------------------------------------------------------
	// ###################################################################################

	function cargar_licencia($id)
	{
		$this->dep('licencia')->cargar($id);
		//$pais = $this->dep('pais')->get();
	}

	function resetear_licencia()
	{
		$this->dep('licencia')->resetear();
	}

	function get_licencias($filtro = null)

	{
		return $this->dep('licencia')->get_filas($filtro, false);
	}

	function get_licencia($id)
	{
		$this->dep('licencia')->cargar($id);
		$datos = $this->dep('licencia')->get();
		$datos['claustro_id'] = dao_licencia::get_claustro_licencia($datos);
		return $datos;
	}

	function agregar_licencia($registro)
	{
		if (dao_licencia::existe_licencia($registro, null)) {
			throw new Exception('Los datos cargados coinciden con una Licencia existente');
		} else if (dao_licencia::equivalencia_no_valida($registro)) {
			throw new Exception('La licencia equivalente no debe pertenecer al mismo claustro');
		} else {
			$this->dep('licencia')->nueva_fila($registro);
			$this->dep('licencia')->sincronizar();
			$this->dep('licencia')->resetear();
		}
	}

	function modificar_licencia($registro, $licencia_id)
	{
		if (dao_licencia::existe_licencia($registro, $licencia_id['licencia_id'])) {
			throw new Exception('Los datos cargados coinciden con una Licencia existente');
		} else if (dao_licencia::equivalencia_no_valida($registro)) {
			throw new Exception('La licencia equivalente no debe pertenecer al mismo claustro');
		} else {
			$this->dep('licencia')->set($registro);
			$this->dep('licencia')->sincronizar();
			$this->dep('licencia')->resetear();
		}
	}


	// ###################################################################################
	//----  L I C E N C I A   X   C A R G O   --------------------------------------------
	// ###################################################################################

	function resetear_licencia_x_cargo()
	{
		$this->dep('licencia_x_cargo')->resetear();
	}

	function get_licencia_x_cargo($id)
	{
		$this->dep('licencia_x_cargo')->cargar($id);
		$datos = $this->dep('licencia_x_cargo')->get();
		$detalles = dao_licencia::get_licencia_x_cargo($datos);
		$datos['claustro_id'] = $detalles['claustro_id'];
		//$datos['claustro_id'] = dao_licencia::get_claustro_licencia($datos);
		return $datos;
	}


	function agregar_licencia_x_cargo($registro)
	{
		$claustros = dao_persona::get_claustros_persona($registro['persona_id']);
		if (empty($claustros)) {
			throw new Exception('La Persona seleccionada no posee cargos activos');
		} else {
			$claustro_licencia = dao_licencia::get_claustro_licencia($registro);

			if (in_array($claustro_licencia, $claustros)) {
				$this->dep('licencia_x_cargo')->nueva_fila($registro);
				$this->dep('licencia_x_cargo')->sincronizar();
				$this->dep('licencia_x_cargo')->resetear();
				
				/* 
				//esto era cuando se asignaban varios cargos mediante checks en el fomulario, ya no se utiliza pero queda el codigo
				if (!empty($registro['cargos'])) {
					foreach ($registro['cargos'] as $cargo) {
						$nueva_fila = array();
						$nueva_fila['persona_id'] = $registro['persona_id'];
						$nueva_fila['claustro_id'] = $registro['claustro_id'];
						$nueva_fila['licencia_id'] = $registro['licencia_id'];
						$nueva_fila['cargo_id'] = $cargo;
						$nueva_fila['fecha_alta'] = $registro['fecha_alta'];
						$nueva_fila['fecha_baja'] = $registro['fecha_baja'];
						$nueva_fila['anulado'] = $registro['anulado'];
						
						$this->dep('licencia_x_cargo')->nueva_fila($nueva_fila);
						$this->dep('licencia_x_cargo')->sincronizar();
						$this->dep('licencia_x_cargo')->resetear();		
					}
				} else {
					throw new Exception('Debe seleccionar al menos uno de los cargos disponibles');
				}
				*/
			} else {
				throw new Exception('La licencia y el claustro seleccionados son incompatibles');
			}
		}
	}

	function modificar_licencia_x_cargo($registro, $lxp_id)
	{
		if (dao_licencia::existe_licencia_x_cargo($registro, $lxp_id['licencia_x_cargo_id'])) {
			throw new Exception('Los datos coinciden con una Licencia activa');
		} else if (isset($registro['fecha_baja']) and $registro['fecha_alta'] >= $registro['fecha_baja']) {
			throw new Exception('La fecha de Baja debe ser posterior a la fecha de Alta');
		} else {
			$this->dep('licencia_x_cargo')->set($registro);
			$this->dep('licencia_x_cargo')->sincronizar();
			$this->dep('licencia_x_cargo')->resetear();
		}
	}

	function anular_licencia_x_cargo($motivo, $lxp_id)
	{
		$this->dep('licencia_x_cargo')->cargar($lxp_id);
		$registro = $this->dep('licencia_x_cargo')->get();

		$registro['motivo_anulacion'] = $motivo['motivo_anulacion'];
		$registro['anulado'] = 1;

		//if (dao_licencia::existe_licencia_x_cargo($registro, $lxp_id['licencia_x_cargo_id'])) {
		//	throw new Exception('Los datos coinciden con una Licencia activa');
		//} else if (isset($registro['fecha_baja']) and $registro['fecha_alta'] >= $registro['fecha_baja']) {
		//	throw new Exception('La fecha de Baja debe ser posterior a la fecha de Alta');
		//} else {
			$this->dep('licencia_x_cargo')->set($registro);
			$this->dep('licencia_x_cargo')->sincronizar();
			$this->dep('licencia_x_cargo')->resetear();
		//}
	}
}
