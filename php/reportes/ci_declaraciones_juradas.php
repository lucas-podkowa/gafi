<?php
include_once "empleados/dao_empleado.php";
include_once 'persona/dao_persona.php';


class ci_declaraciones_juradas extends gafi_ci
{
	protected $s__filtro;

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro)) {

			// $persona = dao_persona::get_personas(isset($this->s__filtro) ? $this->s__filtro : null);
			// ei_arbol($persona);
			$datos = dao_empleado::get_cargos(isset($this->s__filtro) ? $this->s__filtro : null);
			$mostrar = array();
			if (!empty($datos)) {
				foreach ($datos as $cargo) {
					$horarios = dao_empleado::get_horarios_vigentes_del_cargo($cargo);
					foreach ($horarios as $h) {
						$h['persona'] = dao_empleado::get_legajo_desc($cargo['legajo_id']);
						$h['claustro'] = $cargo['claustro'];
						$h['cargo_desc'] = $cargo['cargo_desc'];
						array_push($mostrar, $h);
					}
				}
				$cuadro->set_datos($mostrar);
			}
			//$cuadro->set_titulo($persona[0]['persona_desc']);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(gafi_ei_filtro $filtro)
	{
		if (isset($this->s__filtro)) {
			$filtro->set_datos($this->s__filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		if (!empty($datos)) {
			$this->s__filtro = $datos;
			$this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND');
		} else {
			toba::notificacion()->info('Seleccione alg&uacuten FILTRO para continuar');
		}
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
	}
}
