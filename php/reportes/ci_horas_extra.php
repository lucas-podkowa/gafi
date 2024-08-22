<?php
include_once "reportes/dao_reportes.php";

class ci_horas_extra extends gafi_ci
{
	protected $s__filtro;
	protected $s__datos;
	
	
	function ini()
	{
		unset($this->s__datos);

	}
	
	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(gafi_ei_formulario $form)
	{
		// si viene algo en el filtro
		if(isset($this->s__filtro)){
			$this->s__datos = dao_reportes::horas_extra_de_un_agente_en_un_periodo(isset($this->s__filtro) ? $this->s__filtro : null);
			if(isset($this->s__datos) && count($this->s__datos) > 0){
				$this->s__datos['periodo'] = $this->s__filtro['periodo']; 
				$this->s__datos['agente'] .= ' (' . $this->s__datos['numero_documento'] . ')';
				return $this->s__datos;
			}
		}
	}
	
	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(gafi_ei_filtro $filtro)
	{
		if (isset($this->s__filtro)) {
			$filtro->set_datos($this->s__filtro);
			
			if(is_array($this->s__filtro) && count($this->s__filtro) > 0){
				if($this->s__filtro['fecha_hora']['condicion'] == 'entre'){
					$fini = new DateTime($this->s__filtro['fecha_hora']['valor']['desde']);
					$ffin = new DateTime($this->s__filtro['fecha_hora']['valor']['hasta']);

					$this->s__filtro['periodo'] = 'Desde ' . $fini->format('d/m/Y') . ' hasta ' . $ffin->format('d/m/Y');
				}
				if($this->s__filtro['fecha_hora']['condicion'] == 'es_igual_a'){
					$this->s__filtro['periodo'] = 'Solo ' . $this->s__filtro['fecha_hora']['valor'];
				}
				if($this->s__filtro['fecha_hora']['condicion'] == 'es_distinto_de'){
					$this->s__filtro['periodo'] = 'Todo, excepto la fecha: ' . $this->s__filtro['fecha_hora']['valor'];
				}
				if($this->s__filtro['fecha_hora']['condicion'] == 'hasta'){
					$this->s__filtro['periodo'] = 'Hasta ' . $this->s__filtro['fecha_hora']['valor'];
				}
				if($this->s__filtro['fecha_hora']['condicion'] == 'desde'){
					$this->s__filtro['periodo'] = 'Desde ' . date('d/m/Y', strtotime($this->s__filtro['fecha_hora']['valor']));
				}
			}
		}
		
			
	}

	function evt__filtro__filtrar($datos)
	{
		if (!empty($datos)) {
			$this->s__filtro = $datos;
			$this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND');
		}else{
			toba::notificacion()->info('Seleccione alg&uacuten FILTRO para continuar');
		}
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
	}
	
	function extender_objeto_js()
	{
		echo "
			{$this->dep('formulario')->objeto_js}.evt__imprimir = function(params) {
                location.href = vinculador.get_url(null, null, 'vista_jasperreports', {'id': params});
				return false;
			}
		";
	}

	function vista_jasperreports(toba_vista_jasperreports $report){

		$report->set_parametro('periodo', 'S', $this->s__datos['periodo']);
		$report->set_parametro('agente', 'S', $this->s__datos['agente']);
		
		$report->set_parametro('legajo', 'S', $this->s__datos['legajo']);
		
		
		$report->set_parametro('horas_normales', 'S', $this->s__datos['horas_normales'] . ' Hs.');
		$report->set_parametro('horas_extras', 'S', $this->s__datos['horas_extras'] . ' Hs.');
		$report->set_parametro('horas_totales', 'S', $this->s__datos['horas_totales'] . ' Hs.');
		
		$path = toba::proyecto()->get_www();
		$report->set_parametro('img_fio', 'S', $path['path'] . '/img/logo-fio.jpg');
		$report->set_parametro('img_gafi', 'S', $path['path'] . '/img/gafi.jpg');
		
		$reporte = 'horas_extras.jasper';
		$path = toba::proyecto()->get_path().'/exportaciones/jasper/'.$reporte;    
		$report->set_path_reporte($path);
		$report->set_nombre_archivo('horas_extras.pdf');
	}
}
?>
