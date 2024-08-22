<?php
include_once "reportes/dao_reportes.php";

class ci_asistencia_manual extends gafi_ci
{
	protected $s__id_licencia_x_cargo;

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__nueva_asistencia()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__ver_reporte()
	{
		$this->set_pantalla('pant_reporte');
	}

	//-----------------------------------------------------------------------------------
	//---- fomulario --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------


	function evt__fomulario__alta($datos)
	{
		try {
			$this->cn()->agregar_asistencia($datos);
			$this->set_pantalla('pant_inicial');
		} catch (Throwable $t) {
			toba::notificacion()->warning($t->getMessage());
		}
	}

	function evt__fomulario__modificacion($datos)
	{
		//nada aun
	}

	function evt__fomulario__cancelar()
	{
		$this->set_pantalla('pant_inicial');
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro)) {
			$datos = dao_reportes::get_fichadas_manuales(isset($this->s__filtro) ? $this->s__filtro : null);

			for ($i = 0; $i < count($datos); $i++) {
				$datos[$i]['evento'] = ($datos[$i]['evento'] == 1) ? 'Entrada' : 'Salida';
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
			toba::notificacion()->info('Seleccione alg�n FILTRO para continuar');
		}
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
	}
}
