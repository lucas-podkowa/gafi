<?php
include_once "reportes/dao_reportes.php";
class ci_fichadas_por_empleado extends gafi_ci
{
	protected $s__filtro;

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro)) {
			$datos = dao_reportes::get_fichadas_persona(isset($this->s__filtro) ? $this->s__filtro : null);
			for ($i = 0; $i < count($datos); $i++) {
				$datos[$i]['evento'] = ($datos[$i]['evento'] == 1) ? 'Entrada' : 'Salida';
				if ($datos[$i]['en_reloj']) {
					$datos[$i]['fuente'] = '<font color = green>Reloj: </font>' . $datos[$i]['terminal_nombre'];
				} else {
					$datos[$i]['fuente'] = '<font color = red><b>Manual: </b></font>' . $datos[$i]['terminal_nombre'];
				}
			}
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
