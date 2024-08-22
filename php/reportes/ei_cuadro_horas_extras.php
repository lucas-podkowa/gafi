<?php
class ei_cuadro_horas_extras extends gafi_ei_cuadro
{
	public function vista_pdf(toba_vista_pdf $salida)
	{

		$datos = $this->get_datos();
		$filtro = $this->controlador->dep('filtro')->get_datos();

		$salida->titulo('INFORME DE HORAS TRABAJADAS');
		$salida->separacion();
		$salida->separacion();
		$salida->separacion();

		$pdf = $salida->get_pdf();

		if (is_array($filtro) && count($filtro) > 0) {
			if ($filtro['fecha_hora']['condicion'] == 'entre') {
				$pdf->addText(40, 750, 8, 'Periodo: Desde ' . $filtro['fecha_hora']['valor']['desde'] . ' hasta ' . $filtro['fecha_hora']['valor']['desde']);
			}
			if ($filtro['fecha_hora']['condicion'] == 'es_igual_a') {
				$pdf->addText(40, 750, 8, 'Periodo: Solo ' . $filtro['fecha_hora']['valor']);
			}
			if ($filtro['fecha_hora']['condicion'] == 'es_distinto_de') {
				$pdf->addText(40, 750, 8, 'Periodo: Todo, excepto la fecha: ' . $filtro['fecha_hora']['valor']);
			}
			if ($filtro['fecha_hora']['condicion'] == 'hasta') {
				$pdf->addText(40, 750, 8, 'Periodo: Hasta ' . $filtro['fecha_hora']['valor']);
			}
			if ($filtro['fecha_hora']['condicion'] == 'desde') {
				$pdf->addText(40, 750, 8, 'Periodo: Desde ' . date('d/m/Y', strtotime($filtro['fecha_hora']['valor'])));
			}
		}
		$pdf->addText(40, 740, 8, date('d/m/Y h:i:s a'));

		$pdf->addText(480, 20, 8, date('d/m/Y h:i:s a'));

		//margenes
		$pdf->ezSetMargins(60, 30, 30, 30); //top, bottom, left, right

		//Pie de página
		$formato = 'Pag. {PAGENUM} de {TOTALPAGENUM}';
		$pdf->ezStartPageNumbers(300, 20, 8, 'left', $formato, 1); //x, y, size, pos, texto, pagina inicio

		//Invoco la salida pdf original del cuadro
		parent::vista_pdf($salida);

		//Encabezado
		foreach ($pdf->ezPages as $pageNum => $id) {
			$pdf->addText(480, 20, 8, date('d/m/Y h:i:s a'));
			$pdf->reopenObject($id);
			$gafi = toba::proyecto()->get_path() . '/www/img/gafi.jpg';
			$pdf->addJpegFromFile($gafi, 30, 785, 80, 40); //imagen, x, y, ancho, alto

			$fio = toba::proyecto()->get_path() . '/www/img/logo-fio.jpg';
			$pdf->addJpegFromFile($fio, 500, 785, 55, 40); //imagen, x, y, ancho, alto
			$pdf->closeObject();
		}
	}
}
