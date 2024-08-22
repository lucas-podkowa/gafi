<?php
class ci_licencia extends gafi_ci
{
	protected $s__id_licencia;

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		if (isset($this->s__id_licencia)) {
			$this->cn()->cargar_licencia($this->s__id_licencia);
		} else {
			$this->cn()->resetear_licencia();
		}
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro)) {
			$cuadro->set_datos(dao_licencia::get_licencias(isset($this->s__filtro) ? $this->s__filtro : null));
		}
	}

	function evt__cuadro__edicion($seleccion)
	{
		$this->s__id_licencia = $seleccion;
		$this->set_pantalla('pant_edicion');
	}

	function evt__cuadro__agregar($datos)
	{
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		if (isset($this->s__id_licencia)) {
			$datos = $this->cn()->get_licencia($this->s__id_licencia);
			$form->set_datos($datos);
		}
	}

	function evt__formulario__alta($datos)
	{
		try {
			$this->cn()->agregar_licencia($datos);
			unset($this->s__id_licencia);
			$this->set_pantalla('pant_inicial');
		} catch (Throwable $t) {
			toba::notificacion()->warning($t->getMessage());
		}
	}

	function evt__formulario__baja()
	{
	}

	function evt__formulario__modificacion($datos)
	{
		try {
			$this->cn()->modificar_licencia($datos, $this->s__id_licencia);
			unset($this->s__id_licencia);
			$this->set_pantalla('pant_inicial');
		} catch (Throwable $t) {
			toba::notificacion()->warning($t->getMessage());
		}
	}

	function evt__formulario__cancelar()
	{
		unset($this->s__id_licencia);
		$this->set_pantalla('pant_inicial');
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
		unset($this->s__id_licencia);

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
		unset($this->s__id_licencia);
	}



}
