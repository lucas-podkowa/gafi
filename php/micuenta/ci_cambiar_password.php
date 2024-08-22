<?php
class ci_cambiar_password extends gafi_ci
{
	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		$datos = array( 'usuario' => toba::usuario()->get_id(),
				'nombre' => toba::usuario()->get_nombre() );
		return $datos;
		// prueba
	}

	function evt__formulario__modificacion($datos)
	{
		$id_usuario = toba::usuario()->get_id();
		if (!(toba_usuario_basico::autenticar($id_usuario, $datos['clave_vieja']))) {
			toba::notificacion()->agregar('La clave actual ingresada no es la correcta.');
			return;
		}
		
		// si ya se utilizo la clave esta función devuelve un error y no se ejecuta las lineas siguientes.		
		toba::usuario()->verificar_clave_no_utilizada($datos['clave_nueva'], $id_usuario, $no_repetidas=null);
		
		toba::usuario()->set_clave_usuario ($datos['clave_nueva'], $id_usuario);
		$this->pantalla()->set_descripcion('La clave fue actualizada correctamente.<br>');
	}

	function evt__formulario__cancelar()
	{
		toba::vinculador()->navegar_a(null, 2, null);
	}

}
?>
