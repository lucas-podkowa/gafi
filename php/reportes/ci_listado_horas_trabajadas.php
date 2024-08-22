<?php
include_once "reportes/dao_reportes.php";

class ci_listado_horas_trabajadas extends gafi_ci
{
	protected $s__filtro;
	protected $s__datos;
	protected $s__usuario;


	function ini()
	{
		unset($this->s__datos);
		$this->s__usuario = toba::usuario()->get_id();
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(gafi_ei_cuadro $cuadro)
	{
		// si viene algo en el filtro
		$reportes = new dao_reportes();

		if (isset($this->s__filtro)) {
			$this->s__datos = $reportes->get_listado_horas_extra(isset($this->s__filtro) ? $this->s__filtro : null);
			if (isset($this->s__datos) && count($this->s__datos) > 0) {
				//$this->s__datos['periodo'] = $this->s__filtro['periodo'];
				//$this->s__datos['agente'] .= ' (' . $this->s__datos['numero_documento'] . ')';

				// guarda los datos en una tabla para usarlo en jasperreport.
				$this->cn()->refrescar_tabla_horas_totales($this->s__datos);

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

			if (is_array($this->s__filtro) && count($this->s__filtro) > 0) {
				if ($this->s__filtro['fecha_hora']['condicion'] == 'entre') {
					$fini = new DateTime($this->s__filtro['fecha_hora']['valor']['desde']);
					$ffin = new DateTime($this->s__filtro['fecha_hora']['valor']['hasta']);

					$this->s__filtro['periodo'] = 'Desde ' . $fini->format('d/m/Y') . ' hasta ' . $ffin->format('d/m/Y');
				}
				if (isset($this->s__filtro['fecha_hora'])) {
					if ($this->s__filtro['fecha_hora']['condicion'] == 'es_igual_a') {
						$this->s__filtro['periodo'] = 'Solo ' . $this->s__filtro['fecha_hora']['valor'];
					}
					if ($this->s__filtro['fecha_hora']['condicion'] == 'es_distinto_de') {
						$this->s__filtro['periodo'] = 'Todo, excepto la fecha: ' . $this->s__filtro['fecha_hora']['valor'];
					}
					if ($this->s__filtro['fecha_hora']['condicion'] == 'hasta') {
						$this->s__filtro['periodo'] = 'Hasta ' . $this->s__filtro['fecha_hora']['valor'];
					}
					if ($this->s__filtro['fecha_hora']['condicion'] == 'desde') {
						$this->s__filtro['periodo'] = 'Desde ' . date('d/m/Y', strtotime($this->s__filtro['fecha_hora']['valor']));
					}
				}
			}
		}
	}

	function evt__filtro__filtrar($datos)
	{
		if (!empty($datos)) {
			$this->s__filtro = $datos;
			$this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND');
		} else {
			toba::notificacion()->info('Seleccione alg�n FILTRO para continuar');
		}
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
	}

	function extender_objeto_js()
	{
		echo "
			{$this->dep('cuadro')->objeto_js}.evt__imprimir = function(params) {
				location.href = vinculador.get_url(null, null, 'vista_jasperreports', {'id': params});
				return false;
			}
		";
	}

	function vista_jasperreports(toba_vista_jasperreports $report)
	{
		$report->set_parametro('periodo', 'S', $this->s__datos['periodo']);
		$report->set_parametro('usuario_id', 'S', $this->s__usuario);

		$path = toba::proyecto()->get_www();
		$report->set_parametro('img_fio', 'S', $path['path'] . '/img/logo-fio.jpg');
		$report->set_parametro('img_gafi', 'S', $path['path'] . '/img/gafi.jpg');

		$reporte = 'listado_horas_trabajadas.jasper';
		$path = toba::proyecto()->get_path() . '/exportaciones/jasper/' . $reporte;
		$report->set_path_reporte($path);
		$report->set_nombre_archivo('listado_horas_trabajadas.pdf');
	}
}
