<?php
include_once 'reportes/dao_reportes.php';
include_once 'persona/dao_persona.php';


class cn_reporte extends gafi_cn
{

    public function refrescar_tabla_horas_totales($datos)
    {
        $usuario_id = toba::usuario()->get_id();
        dao_reportes::limpiar_tabla_horas_totales($usuario_id);
        foreach ($datos as $horario) {
            $horario['usuario_id'] = $usuario_id;
            $this->insertar_horas_totales($horario);
        }
    }

    public function insertar_horas_totales($registro)
    {
        $id = $this->dep('horas_totales')->nueva_fila($registro);
        $this->dep('horas_totales')->set_cursor($id);
        $this->dep('horas_totales')->sincronizar();
        $this->dep('horas_totales')->resetear();
    }

    public function filtrar_personas_ausentes($filtro)
    {

        $fechas = array();
        if (isset($filtro['fecha_hora'])) {
            $intervalo = new DateInterval('P1D');
            //$where .= " AND tl.claustro_id = '{$filtro['claustro_id']}' ";
            switch ($filtro['fecha_hora']['condicion']) {
                case 'es_igual_a':
                    array_push($fechas, $filtro['fecha_hora']['valor']);
                    break;

                case 'entre':
                    $desde = new DateTime($filtro['fecha_hora']['valor']['desde']);
                    $hasta = new DateTime($filtro['fecha_hora']['valor']['hasta']);

                    // Agregar un día al final para incluir la fecha final en el rango
                    $hasta->modify('+1 day');
                    $periodo = new DatePeriod($desde, $intervalo, $hasta);

                    foreach ($periodo as $fecha) {
                        $fechas[] = $fecha->format('Y-m-d');
                    }
                    break;
            }
        }

        //filtro solamente las personas con cargos activos que coicidan con el filtro
        $personas = dao_persona::filtrar_personas($filtro);
        $listado = array();

        foreach ($fechas as $fecha) {
            // obtener las fichadas de ese día
            $filtro['where'] = "date(fecha_hora) = '" . $fecha . "'";
            $fichadas = dao_reportes::get_fichadas_persona($filtro);

            // Obtener los valores en $personas pero no en $fichadas basado en el campo 'persona_id'
            // aqui tengo las personas sin fichaje en el dia seleccionado
            $ausentes = $this->getDiferencias($personas, $fichadas, 'persona_id');

            //analizar los horarios de las declaraciones jurandas solamente de los ausentes
            // se busca separar aquellas personas sin declaracion jurada y ademas filtrar las que no tengan horarios

            $dia_buscado = $this->obtenerDiaSemana($fecha);
            $no_laboral = dao_configuracion::es_dia_no_laborable($fecha);
            if ($no_laboral) {
                $motivo = mb_convert_encoding('Día no laborable: ', 'iso-8859-1', 'utf-8') . $no_laboral['motivo'];
                toba::notificacion()->info($motivo);
            } else {
                foreach ($ausentes as $ausente) {

                    $ausente['fecha'] = $fecha . ' (' . $dia_buscado . ')';
                    $horario_cargo = dao_empleado::get_horarios_vigentes_del_cargo(array('cargo_id' => $ausente['cargo_id']));
                    if (empty($horario_cargo)) {
                        $ausente['motivo'] = 'Persona sin DDJJ decladara';
                        array_push($listado, $ausente);
                    } else {
                        if ($this->buscar_dia_en_horarios($horario_cargo, $dia_buscado)) {
                            //si una persona declara no viene ese día, buscar el motivo
                            $licencia = dao_licencia::get_licencia_x_fecha_persona($ausente['persona_id'], $fecha);

                            $ausente['motivo'] = (!empty($licencia)) ? $licencia['nombre_tipo_lic'] . ' (' . $licencia['nombre_licencia'] . ')' : 'No Justifica';
                            array_push($listado, $ausente);
                        }
                    }
                }
            }
        }
        return $listado;
    }

    function buscar_dia_en_horarios($horarios, $dia)
    {
        foreach ($horarios as $horario) {
            if (strtolower($horario['dia_semana']) == strtolower($dia)) {
                return true;
            }
        }
        return false; // este return solo se ejecutará si no encontro el dia entre los horarios
    }

    public function obtenerDiaSemana($fecha)
    {
        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $f = new DateTime($fecha);
        $num = $f->format('w'); // Obtiene el número del día de la semana (0 para Domingo, 1 para Lunes, etc.)
        $dia_semana = $dias[$num];
        return $dia_semana;
    }

    public function getDiferencias($personas, $fichadas, $campo)
    {
        //extraigo los valores del campo 'persona_id' del segundo array (fichadas)
        $ids = array_column($fichadas, $campo);

        //filtro los elementos del array1 (personas) que no están presentes en array2 (fichadas).
        return array_filter($personas, function ($item) use ($ids, $campo) {
            return !in_array($item[$campo], $ids);
        });
    }
}
