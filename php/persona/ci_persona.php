<?php
class ci_persona extends gafi_ci
{
	protected $s__filtro;
	protected $s__id;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		if (isset($this->s__id)) {
			$this->cn('cn_persona')->cargar_persona($this->s__id);
		} else {
			$this->cn('cn_persona')->resetear_persona();
		}
	}

	// ###################################################################################
	//---- C U A D R O S ----------------------------------------------------------------
	// ###################################################################################


	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro)) {
			$cuadro->set_datos(dao_persona::get_personas(isset($this->s__filtro) ? $this->s__filtro : null));
		}
	}

	function evt__cuadro__seleccion($seleccion)
	{
		$this->s__id = $seleccion;
	}

	function evt__cuadro__edicion($seleccion)
	{
		$this->s__id = $seleccion;
		$this->set_pantalla('pant_edicion_persona');
	}

	function evt__cuadro__agregar($datos)
	{
		unset($this->s__id);
		$this->set_pantalla('pant_edicion_persona');
	}




	//---- cuadro_legajos ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_legajos(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__id)) {
			$cuadro->set_datos(dao_persona::get_legajos_persona($this->s__id));
		}
	}


	// ###################################################################################
	//---- F O R M U L A R I O S  -------------------------------------------------------
	// ###################################################################################


	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		if (isset($this->s__id)) {
			$datos = $this->cn('cn_persona')->get_persona($this->s__id);
			$datos['cantidad_huellas'] = $this->cn()->dep('cn_terminal')->terminal_cantidad_huellas_usuario($this->s__id['persona_id']);
			$form->set_datos($datos);
		}
	}

	function evt__formulario__desconectar()
	{
		$this->cn()->dep('cn_terminal')->terminal_desconectar();
	}

	function evt__formulario__enrolar($datos)
	{
		//actualiza la tabla usuario en la base de datos
		//$this->cn()->dep('cn_persona')->actualizar_cantidad_huellas_persona($datos);

		// se modifican el nombre de la persona, si hay cambios... en el reloj
		$this->cn()->dep('cn_terminal')->terminal_set_usuario(array_merge($this->s__id, $datos));
		$this->set_pantalla('pant_enrolar');
	}

	function evt__formulario__sincronizar($datos)
	{
		$this->cn()->dep('cn_terminal')->terminal_sincronizar_huellas($this->s__id['persona_id'], $datos);
	}

	function evt__formulario__alta($datos)
	{

		$id_persona = $this->cn('cn_persona')->agregar_persona($datos);

		if ($id_persona) {
			$this->s__id[persona_id] = $id_persona['persona_id'];
			//unset($this->s__filtro); // comento esto para que se vea el registro que cargo

			// dar de alta la persona en el terminal. Evaldo
			//id_persona trae toda la fila de la nueva persona con todos sus datos.

			$this->cn()->dep('cn_terminal')->terminal_set_usuario($id_persona);

			// no me cambio de pantalla al dar de alta para que pueda enrolar.
			//$this->set_pantalla('pant_inicial'); // vuelve a la pantalla de la que vino

		} else {
			toba::notificacion()->warning('No puede ingresar un Numero de Identificacion o CUIT duplicado');
		}
	}

	function evt__formulario__baja()
	{
		toba::notificacion()->warning('Función no habilitada');
		// dar de baja el usuario en el reloj maestro
		//$this->cn()->dep('cn_terminal')->terminal_eliminar_usuario($id_persona);
	}

	function evt__formulario__modificacion($datos)
	{
		$id = $this->cn('cn_persona')->modificar_persona($datos);

		// se modifican el nombre de la persona, si hay cambios... en el reloj
		$this->cn()->dep('cn_terminal')->terminal_set_usuario(array_merge($this->s__id, $datos));

		unset($this->s__id);
		$this->cn('cn_persona')->resetear_persona();
		$this->set_pantalla('pant_inicial'); // vuelve a la pantalla de la que vino
	}

	function evt__formulario__cancelar()
	{
		unset($this->s__id);
		$this->set_pantalla('pant_inicial');
	}

	//-----------------------------------------------------------------------------------
	//---- formulario_enrolar -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario_enrolar(gafi_ei_formulario $form)
	{
	}

	function evt__formulario_enrolar__enrolar($datos)
	{
		// $datos['md'] viene el indice del dedo que se marcó para enrolar. md = mano derecha. mi = mano izquierda
		// siempre viene uno solo. o de la mano derecha o de la izquierda
		// los valores posibles son [0-9]. 0-4 mano izquierda (pulgar, indice, mayor, anular, meñique) 
		// mano derecha 5-9 en el mismo orden (pulgar, indice, mayor, anular, meñique)
		$this->cn()->dep('cn_terminal')->terminal_enrolar($this->s__id['persona_id'], $datos['md'] ? $datos['md'] : $datos['mi']);
	}

	function evt__formulario_enrolar__actualizar()
	{
		// no hace nada.. solo para recargar la página.-
	}

	function evt__formulario_enrolar__cancelar()
	{
		$this->set_pantalla('pant_edicion_persona');
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
			unset($this->s__id);
			$edad = " AND (1=1)";
			$huellas = " AND (1=1)";
			$this->s__filtro = $datos;
			$clausulas = $this->dep('filtro')->get_sql_clausulas();

			if (isset($datos['huellas'])) {
				unset($clausulas['huellas']);
				if ($datos['huellas']['valor']) {
					$huellas = " AND p.cantidad_huellas > '0'";
				} else {
					$huellas = " AND p.cantidad_huellas = '0'";
				}
			}

			if (isset($datos['edad'])) {
				unset($clausulas['edad']);

				$fechaactual = date('Y-m-d');
				//se resta el valor ingresado como si fueran años desde la fecha actual, con esa nueva fecha comparamos las fechas de nacimiento
				$referencia = date('Y-m-d', strtotime('-' . $datos['edad']['valor'] . ' year', strtotime($fechaactual)));
				$referencia_anterior = date('Y-m-d', strtotime('-1 year', strtotime($referencia)));

				switch ($datos['edad']['condicion']) {
					case 'es_mayor_que':
						$edad = " AND p.fecha_nacimiento <= '" . $referencia_anterior . "'";
						break;
					case 'es_mayor_o_igual_que':
						$edad = " AND p.fecha_nacimiento <= '" . $referencia . "'";
						break;
					case 'es_menor_que':
						$edad = " AND p.fecha_nacimiento > '" . $referencia . "'";
						break;
					case 'es_menor_o_igual_que':
						$edad = " AND p.fecha_nacimiento > '" . $referencia_anterior . "'";
						break;
					case 'es_igual_a':
						$edad = " AND (p.fecha_nacimiento > '" . $referencia_anterior . "' AND p.fecha_nacimiento <= '" . $referencia . "')";
						break;
					case 'es_distinto_de':
						$edad = " AND (p.fecha_nacimiento <= '" . $referencia_anterior . "' OR p.fecha_nacimiento > '" . $referencia . "')";
						break;
					case 'entre':
						$desde = date('Y-m-d', strtotime('-' . $datos['edad']['valor']['desde'] . ' year', strtotime($fechaactual)));
						$hasta = date('Y-m-d', strtotime('-' . $datos['edad']['valor']['hasta'] . ' year', strtotime($fechaactual)));
						$edad = " AND (p.fecha_nacimiento <= '" . $desde . "' AND p.fecha_nacimiento > '" . $hasta . "')";
						break;
				}
			}

			$this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND', $clausulas);
			$this->s__filtro['where'] .= $huellas;
			$this->s__filtro['where'] .= $edad;
		} else {
			toba::notificacion()->info('Seleccione alg&uacuten FILTRO para continuar');
		}
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
		unset($this->s__id);
	}


	function ajax__get_hubicacion_huella($parametro, toba_ajax_respuesta $respuesta)
	{
		$huellas = array();
		$huellas = $this->cn()->dep('cn_terminal')->terminal_get_ubicacion_huellas_usuario($this->s__id['persona_id']);
		$respuesta->set($huellas);
	}
}
