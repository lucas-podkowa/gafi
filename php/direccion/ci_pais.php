<?php
include_once 'direccion/dao_direccion.php';
class ci_pais extends gafi_ci
{
	protected $s__id;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		if (isset($this->s__id)) {
			$this->cn()->cargar_pais($this->s__id);
		}
		
	}

	
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		
		$cuadro->set_datos(dao_direccion::get_paises(isset($this->s__filtro)?$this->s__filtro:null));
	}

	function evt__cuadro__seleccion($seleccion)
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
			$form->set_datos($this->cn()->get_pais($this->s__id));
		}
	}

	function evt__formulario__alta($datos)
	{
	}

	function evt__formulario__baja()
	{
	}

	function evt__formulario__modificacion($datos)
	{
	}

	function evt__formulario__cancelar()
	{
		unset($this->s__id);
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