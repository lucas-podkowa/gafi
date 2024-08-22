<?php
class ci_analisis_dedicacion extends gafi_ci
{
	protected $s__xml;
	protected $s__filas;
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filas)) {
			$cuadro->set_datos($this->s__filas);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__formulario__analizar($datos)
	{
		if (isset($datos['xml'])) {
			try {
				$this->s__xml = $this->cn()->copiar_archivo_al_servidor($datos);
				//$this->s__filas = $this->cn()->analizar_porcentaje_dedicacion($this->s__xml);
				$this->s__filas = $this->cn()->analizar_porcentaje_dedicacion_con_repeticion($this->s__xml);
				if (!empty($this->s__filas)) {
					echo ("tengo los datos");
					$this->set_pantalla('pant_inicial');
				}
			} catch (Throwable $t) {
				toba::notificacion()->warning($t->getMessage());
			}
		} else {
			toba::notificacion()->warning('Debe seleccionar un archivo XML');
		}
	}
}
