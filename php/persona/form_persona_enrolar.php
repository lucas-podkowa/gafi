<?php
class form_persona_enrolar extends gafi_ei_formulario
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__mi__procesar = function(es_inicial)
		{
			// para que no pueda chequear los dos radio a la vez
			this.ef('md').resetear_estado();
			
			if(es_inicial){
				//var userid = this.ef('userid').get_estado();
				this.controlador.ajax('get_hubicacion_huella', null, this, this.setear_dedos_enrolados);	
			}
		}
		
		{$this->objeto_js}.setear_dedos_enrolados = function(datos){
			for(j = 0; j < datos.length; j++) {
				if(datos[j] >= 0 && datos[j] <= 4){
					var radios = document.querySelectorAll('input[name=ef_form_103000014_formulario_enrolarmi]')
					radios[datos[j]].parentNode.style.background = 'green';
				}
				if(datos[j] >= 5 && datos[j] <= 9){
					var radios = document.querySelectorAll('input[name=ef_form_103000014_formulario_enrolarmd]')
					radios[datos[j]-5].parentNode.style.background = 'green';
				}
			}
		}
		
		{$this->objeto_js}.evt__md__procesar = function(es_inicial)
		{
			// para que no pueda chequear los dos radio a la vez
			this.ef('mi').resetear_estado();
		}
		
		//---- Validacion de EFs -----------------------------------
		
		{$this->objeto_js}.evt__mi__validar = function()
		{
			return true;
		}
		
		{$this->objeto_js}.evt__md__validar = function()
		{
			return true;
		}
		";
	}

}
?>
