<?php
class ei_cuadro_ddjj_horarios extends gafi_ei_cuadro
{
    public function vista_pdf(toba_vista_pdf $salida)
    {
        $salida->set_nombre_archivo("Reporte de Declaración Jurada de Horarios.pdf");
        $salida->titulo("Reporte de Declaración Jurada de Horarios");
        $salida->separacion();
        $salida->separacion();
        $salida->separacion();
        //Cambio lo mï¿½rgenes accediendo directamente a la librerï¿½a PDF
        $pdf = $salida->get_pdf();

        $pdf->addText(480, 20, 8, date('d/m/Y h:i:s a'));

        $pdf->ezSetMargins(60, 30, 30, 30); //top, bottom, left, right

        //Pie de pï¿½gina
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
