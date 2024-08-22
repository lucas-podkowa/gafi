<?php
class ci_dia_no_laborable extends gafi_ci
{
	protected $s__id_dia_no_laborable;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		if (isset($this->s__id_dia_no_laborable)) {
			$this->cn()->cargar_dia_no_laborable($this->s__id_dia_no_laborable);
		} else {
			$this->cn()->resetear_dia_no_laborable();
		}
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		//if (isset($this->s__filtro)) {
			$cuadro->set_datos(dao_configuracion::get_dias_no_laborables(isset($this->s__filtro) ? $this->s__filtro : null));
		//}
	}

	function evt__cuadro__edicion($seleccion)
	{
		$this->s__id_dia_no_laborable = $seleccion;
		$this->set_pantalla('pant_edicion');
	}

	function evt__cuadro__agregar($datos)
	{
		unset($this->s__id_dia_no_laborable);
		$this->set_pantalla('pant_edicion');
	}



	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		if (isset($this->s__id_dia_no_laborable)) {
			$datos = $this->cn()->get_dia_no_laborable($this->s__id_dia_no_laborable);
			$form->set_datos($datos);
		}
	}

	function evt__formulario__alta($datos)
	{
		try {
			$this->cn()->agregar_dia_no_laborable($datos);
			unset($this->s__id_dia_no_laborable);
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
			$this->cn()->modificar_dia_no_laborable($datos, $this->s__id_dia_no_laborable);
			unset($this->s__id_dia_no_laborable);
			$this->set_pantalla('pant_inicial');
		} catch (Throwable $t) {
			toba::notificacion()->warning($t->getMessage());
		}
	}

	function evt__formulario__cancelar()
	{
		unset($this->s__id_dia_no_laborable);
		$this->set_pantalla('pant_inicial');
	}

	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(gafi_ei_filtro $filtro)
	{
	}


}
?>