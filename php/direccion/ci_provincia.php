<?php
class ci_provincia extends gafi_ci
{
	protected $s__id;
	protected $s__filtro;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		if (isset($this->s__id)) {
			$this->cn()->cargar_provincia($this->s__id);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(gafi_ei_filtro $filtro)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		if (isset($this->s__id)) {
			$form->set_datos($this->cn()->get_provincia($this->s__id));
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->cn()->agregar_provincia($datos);
		unset($this->s__id);
		unset($this->s__filtro); // para que se vea el registro que cargo
		$this->set_pantalla('pant_inicial');
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
		unset($this->s__filtro);
		$this->set_pantalla('pant_inicial');
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		$cuadro->set_datos(dao_direccion::get_provincias_con_paises(isset($this->s__filtro)?$this->s__filtro:null));
	}

	function evt__cuadro__seleccion($seleccion)
	{
		$this->s__id = $seleccion;
		$this->set_pantalla('pant_edicion');
	}

	function evt__cuadro__agregar($datos)
	{
		unset($this->s__id);
		$this->set_pantalla('pant_edicion');
	}



}
?>