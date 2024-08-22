<?php
class ci_inasistencias extends gafi_ci
{
	protected $s__filtro;

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro)) {

			//ei_arbol($this->s__filtro);
			$datos = $this->cn()->filtrar_personas_ausentes($this->s__filtro);
			$cuadro->set_datos($datos);
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
		unset($this->s__filtro);

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
		//unset($this->s__id_licencia);
	}
}
