<?php
class ci_ciclo_lectivo extends gafi_ci
{
	protected $s__filtro;
	protected $s__id;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro)) {
		$cuadro->set_datos(dao_configuracion::get_ciclos_lectivos(isset($this->s__filtro) ? $this->s__filtro : null));
		}
	}

	function evt__cuadro__agregar($datos)
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__cuadro__editar($seleccion)
	{
		$this->s__id = $seleccion;
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		if (isset($this->s__id)) {
			$datos = $this->cn()->get_ciclo_lectivo($this->s__id);
			$form->set_datos($datos);
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->cn()->agregar_ciclo_lectivo($datos);
		unset($this->s__id);
		$this->set_pantalla('pant_inicial');
	}

	function evt__formulario__baja()
	{
	}

	function evt__formulario__modificacion($datos)
	{
		try {
			$this->cn()->modificar_ciclo_lectivo($datos);
			unset($this->s__id);
			$this->cn()->resetear_ciclo_lectivo();
			$this->set_pantalla('pant_inicial');
		} catch (Throwable $t) {
			toba::notificacion()->warning($t->getMessage());
		}
	}

	function evt__formulario__cancelar()
	{
		unset($this->s__id);
		unset($this->s__filtro);
		$this->cn()->resetear_ciclo_lectivo();
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
		unset($this->s__id);

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
		unset($this->s__id);
	}
}
