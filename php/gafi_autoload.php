<?php
/**
 * Esta clase fue y será generada automáticamente. NO EDITAR A MANO.
 * @ignore
 */
class gafi_autoload 
{
	static function existe_clase($nombre)
	{
		return isset(self::$clases[$nombre]);
	}

	static function cargar($nombre)
	{
		if (self::existe_clase($nombre)) { 
			 require_once(dirname(__FILE__) .'/'. self::$clases[$nombre]); 
		}
	}

	static protected $clases = array(
		'gafi_ci' => 'extension_toba/componentes/gafi_ci.php',
		'gafi_cn' => 'extension_toba/componentes/gafi_cn.php',
		'gafi_datos_relacion' => 'extension_toba/componentes/gafi_datos_relacion.php',
		'gafi_datos_tabla' => 'extension_toba/componentes/gafi_datos_tabla.php',
		'gafi_ei_arbol' => 'extension_toba/componentes/gafi_ei_arbol.php',
		'gafi_ei_archivos' => 'extension_toba/componentes/gafi_ei_archivos.php',
		'gafi_ei_calendario' => 'extension_toba/componentes/gafi_ei_calendario.php',
		'gafi_ei_codigo' => 'extension_toba/componentes/gafi_ei_codigo.php',
		'gafi_ei_cuadro' => 'extension_toba/componentes/gafi_ei_cuadro.php',
		'gafi_ei_esquema' => 'extension_toba/componentes/gafi_ei_esquema.php',
		'gafi_ei_filtro' => 'extension_toba/componentes/gafi_ei_filtro.php',
		'gafi_ei_firma' => 'extension_toba/componentes/gafi_ei_firma.php',
		'gafi_ei_formulario' => 'extension_toba/componentes/gafi_ei_formulario.php',
		'gafi_ei_formulario_ml' => 'extension_toba/componentes/gafi_ei_formulario_ml.php',
		'gafi_ei_grafico' => 'extension_toba/componentes/gafi_ei_grafico.php',
		'gafi_ei_mapa' => 'extension_toba/componentes/gafi_ei_mapa.php',
		'gafi_servicio_web' => 'extension_toba/componentes/gafi_servicio_web.php',
		'gafi_comando' => 'extension_toba/gafi_comando.php',
		'gafi_modelo' => 'extension_toba/gafi_modelo.php',
	);
}
?>