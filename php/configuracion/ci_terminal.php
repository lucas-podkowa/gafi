<?php
include "configuracion/dao_terminal.php";

class ci_terminal extends gafi_ci
{
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	protected $s__filtro;
	protected $s__id;
	
	
	function conf()
	{
		if (isset($this->s__id) ) {
			if( ! $this->cn()->esta_cargada_terminal()){
				$this->cn()->cargar_terminal($this->s__id);

			}
		} else {
			$this->cn()->resetear_terminal();
		}
	}

	//-----------------------------------------------------------------------------------
	//---- ei_formulario ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		if (isset($this->s__id)) {
			$datos = $this->cn()->get_terminal($this->s__id);
			return $datos;
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->cn()->agregar_terminal($datos);
		unset($this->s__id);
		$this->set_pantalla('pant_inicial');
	}

	function evt__formulario__baja()
	{
		if($this->cn()->esta_cargada_terminal()){
			$this->cn()->eliminar_terminal();
			unset($this->s__id);
			$this->set_pantalla('pant_inicial');
		}
	}

	function evt__formulario__modificacion($datos)
	{
		try {
			$this->cn()->modificar_terminal($datos);
			unset($this->s__id);
			$this->cn()->resetear_terminal();
			$this->set_pantalla('pant_inicial');
		} catch (Throwable $t) {
			toba::notificacion()->warning($t->getMessage());
		}
	}
	
	function evt__formulario__sinc_hora()
	{
		// sincroniza con la hora del servidor
		$this->cn()->terminal_sincronizar_hora($this->s__id);
	}
	
	function evt__formulario__set_password()
	{
		$this->set_pantalla('pant_password');
	}


	function evt__formulario__cancelar()
	{
		unset($this->s__id);
		$this->set_pantalla('pant_inicial');
	}

	//-----------------------------------------------------------------------------------
	//---- formulario_password ----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario_password(gafi_ei_formulario $form)
	{
	}

	function evt__formulario_password__modificacion($datos)
	{
		$this->cn()->terminal_set_password($this->s__id, $datos);
	}

	function evt__formulario_password__cancelar()
	{
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if(isset($this->s__filtro)){
			$cuadro->set_datos(dao_terminal::get_terminales($this->s__filtro));
		} else {
			$cuadro->set_datos(dao_terminal::get_terminales());
		}
	}

	function evt__cuadro__seleccion($seleccion)
	{
		$this->s__id = $seleccion;
		$this->set_pantalla('pant_edicion');
	}

	function evt__cuadro__agregar($datos)
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__cuadro__cancelar($datos)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- ei_filtro --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(gafi_ei_filtro $filtro)
	{
		if(isset($this->s__filtro)){
			$filtro->set_datos($this->s__filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__filtro = $datos;
		
		// envia el formato del nuevo filtro
		$this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND');
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
	}
}
?>
