<?php
class ci_importar_personas extends gafi_ci
{
	protected $s__xml;
	protected $s__filas;

	//-----------------------------------------------------------------------------------
	//---- fomulario --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__fomulario__procesar($datos)
	{
		if (isset($datos['xml_personas'])) {
			try {
				$this->s__xml = $this->cn()->copiar_archivo_al_servidor($datos);
				$this->s__filas = $this->cn()->analizar_xml_direcciones_y_personas($this->s__xml);

				if (empty($this->s__filas)) {
					toba::notificacion()->warning('El archivo XML no posee el filtro de Dependencia (06 - Facultad de Ingeniería)');
				} else {
					$this->set_pantalla('pant_procesar');
				}
			} catch (Throwable $t) {
				toba::notificacion()->warning($t->getMessage());
			}
		} else {
			toba::notificacion()->warning('Debe seleccionar un archivo XML');
		}
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		if (isset($this->s__filas)) {
			$cuadro->set_datos($this->s__filas);
		}
	}

	function evt__cuadro__guardar($datos)
	{
		if (isset($this->s__filas)) {
			try {
				$this->cn()->procesar_direcciones_y_personas($this->s__filas);
				unset($this->s__filas);
				toba::notificacion()->info(mb_convert_encoding('Actualización realizada', 'iso-8859-1', 'utf-8'));
				$this->set_pantalla('pant_inicial');
			} catch (Throwable $t) {
				toba::notificacion()->warning($t->getMessage());
			}
		}
	}
	
}
