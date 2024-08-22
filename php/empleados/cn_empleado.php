<?php
include_once 'empleados/dao_empleado.php';

class cn_empleado extends gafi_cn
{

    // ###################################################################################
    //----  L E G A J O  -----------------------------------------------------------------
    // ###################################################################################

    public function cargar_legajo($id)
    {
        $this->dep('legajo')->cargar($id);
        //$pais = $this->dep('pais')->get();
    }

    public function resetear_legajo()
    {
        $this->dep('legajo')->resetear();
    }

    public function get_legajos($filtro = null)
    {
        return $this->dep('legajo')->get_filas($filtro, false);
    }

    public function get_legajo($id)
    {
        $id_interno = $this->dep('legajo')->get_id_fila_condicion($id);
        $this->dep('legajo')->set_cursor($id_interno[0]);
        return $this->dep('legajo')->get();
    }

    public function agregar_legajo($registro)
    {

        $id = $this->dep('legajo')->nueva_fila($registro);
        $this->dep('legajo')->set_cursor($id);
        $this->dep('legajo')->sincronizar();
        $this->dep('legajo')->resetear();
    }

    public function modificar_legajo($registro)
    {
        if ($this->dep('legajo')->esta_cargada()) {
            $old = $this->dep('legajo')->get();
            if (dao_empleado::existe_legajo($registro['legajo'], $old['legajo'])) {
                throw new Exception('El Nº Legajo ' . $registro['legajo'] . ' ya existe en la Base de Datos');
            } else {
                if (dao_empleado::persona_valida_para_legajo($registro['persona_id'], $old['legajo'])) {
                    throw new Exception('La persona seleccionada ya posee un legajo activo');
                } else {
                    $this->dep('legajo')->set($registro);
                    $this->dep('legajo')->sincronizar();
                    $this->dep('legajo')->resetear();
                }
            }
        }
    }

    // ###################################################################################
    //----  C A R G O  -------------------------------------------------------------------
    // ###################################################################################

    public function resetear_cargo()
    {
        $this->dep('cargo')->resetear();
    }

    public function get_cargos($filtro = null)
    {
        return $this->dep('cargo')->get_filas($filtro, false);
    }

    public function get_cargo($id)
    {
        $this->dep('cargo')->cargar($id);
        return $this->dep('cargo')->get();
    }

    public function agregar_cargo($registro)
    {
        $id = $this->dep('cargo')->nueva_fila($registro);
        $this->dep('cargo')->set_cursor($id);
        $this->dep('cargo')->sincronizar();
        $this->dep('cargo')->resetear();
    }

    public function modificar_cargo($datos = array())
    {
        if ($this->dep('cargo')->esta_cargada()) {
            $old = $this->dep('cargo')->get();
            if (dao_empleado::verificar_cargo_duplicado($datos, $old)) {
                throw new Exception('Ya existe un cargo con los mismos datos que usted intenta guardar');
            } else {
                $nueva_carga_total = null;
                $carga_total_actual = dao_empleado::get_carga_horaria_legajo($old);
                $carga_actual = dao_empleado::get_carga_horaria_cargo($old);
                $carga_nueva = dao_empleado::get_carga_horaria_cargo($datos);
                if (is_null($old['fecha_baja']) and !is_null($datos['fecha_baja'])) {
                    $nueva_carga_total = ($carga_total_actual - $carga_actual);
                    $this->dep('cargo')->set($datos);
                    $this->dep('cargo')->sincronizar();
                    $this->dep('cargo')->resetear();
                } else {
                    $nueva_carga_total = ($carga_total_actual - $carga_actual + $carga_nueva);
                    if ($nueva_carga_total > 50) {
                        throw new Exception('La carga horaria total supera las 50 horas permitidas');
                    } else {
                        $this->dep('cargo')->set($datos);
                        $this->dep('cargo')->sincronizar();
                        $this->dep('cargo')->resetear();
                    }
                }
            }
        }
    }

    public function eliminar_cargo()
    {
        if ($this->dep('cargo')->esta_cargada() && $this->dep('cargo')->hay_cursor()) {

            $cargo = $this->dep('cargo')->get();
            $declaraciones = dao_empleado::get_ddjj_cargo($cargo);
            $asistencias = dao_empleado::get_cantidad_asistencias_registradas($cargo['legajo_id']);

            if ((int) $asistencias > 0) {
                # code... solo deberia desactivar
            } else {

                if (dao_empleado::posee_licencias_relacionadas_al_cargo($cargo)) {
                    throw new Exception('El agente posee licencias activas relacionadas al cargo');
                } else {
                    //si no tiene asistencias registradas se pasa a eliminar las declaraciones juradas y los horarios cargados
                    //foreach ($declaraciones as $dj) {
                    //    $this->dep('dr_declaracion_jurada')->cargar($dj);
                    //    // eliminar datos de tablas hija:los horarios de la ddjj.
                    //    $this->dep('dr_declaracion_jurada')->tabla('horario')->eliminar_todo();
                    //    // se elimina la ddjj.
                    //    $this->dep('dr_declaracion_jurada')->tabla('declaracion_jurada')->eliminar();
                    //    $this->dep('dr_declaracion_jurada')->sincronizar();
                    //    $this->dep('dr_declaracion_jurada')->resetear();
                    //}
                    //$this->dep('cargo')->eliminar();
                    //$this->dep('cargo')->sincronizar();
                    //$this->dep('cargo')->resetear();
                }
            }
        }
    }

    public function puede_agregar_cargo($legajo_id)
    {
        $carga_horaria = dao_empleado::get_carga_horaria_legajo($legajo_id);
        if ((int) $carga_horaria > 40) {
            return false;
        } else {
            return true;
        }
    }

    public function tiene_cargos_activos($legajo_id)
    {
        $activos = dao_empleado::cantidad_cargos_activos($legajo_id);
        if ($activos > 0) {
            return true;
        } else {
            return false;
        }
    }

    // ###################################################################################
    //----  C L A U S T R O  -------------------------------------------------------------
    // ###################################################################################

    public function cargar_claustro($id)
    {
        $this->dep('claustro')->cargar($id);
    }

    public function resetear_claustro()
    {
        $this->dep('claustro')->resetear();
    }

    public function get_claustros($filtro = null)
    {
        return $this->dep('claustro')->get_filas($filtro, false);
    }

    public function get_claustro($id)
    {
        $id_interno = $this->dep('claustro')->get_id_fila_condicion($id);
        $this->dep('claustro')->set_cursor($id_interno[0]);
        return $this->dep('claustro')->get();
    }

    // ###################################################################################
    // ************* A F E C T A C I O N E S   D E   H O R A R I O S ************* //
    // ###################################################################################
    public function get_afectacion_legajo($id)
    {
        $this->dep('afectacion_legajo')->cargar($id);
        return $this->dep('afectacion_legajo')->get();

        //$id_interno = $this->dep('afectacion_legajo')->get_id_fila_condicion($id);
        //$this->dep('afectacion_legajo')->set_cursor($id_interno[0]);
        //return $this->dep('afectacion_legajo')->get();
    }

    public function agregar_afectacion_legajo($registro)
    {

        $id = $this->dep('afectacion_legajo')->nueva_fila($registro);
        //$this->dep('afectacion_legajo')->set_cursor($id);
        $this->dep('afectacion_legajo')->sincronizar();
        //$nueva_al = $this->dep('afectacion_legajo')->get();
        $this->dep('afectacion_legajo')->resetear();
        //return $nueva_al;
    }

    public function modificar_afectacion_legajo($registro)
    {
        if ($this->dep('afectacion_legajo')->esta_cargada()) {
            $this->dep('afectacion_legajo')->set($registro);
            $this->dep('afectacion_legajo')->sincronizar();
            $this->dep('afectacion_legajo')->resetear();

        }
    }

    // ###################################################################################
    //----  D E C L A R A C I O N  J U R A D A  ------------------------------------------
    // ###################################################################################

    public function resetear_declaraciones_juradas()
    {
        $this->dep('declaracion_jurada')->resetear();
    }

    public function modificar_declaracion_jurada($id_ddjj, $cabecera, $horarios)
    {
        if (isset($id_ddjj)) {
            $permitido = true;

            if ($cabecera['estado'] == true) {
                $permitido = dao_empleado::puede_activar_declaracion_jurada($id_ddjj);
            }

            if ($permitido) {
                self::actualizar_declaracion_jurada($id_ddjj, $cabecera);
                self::actualizar_horarios_declaracion_jurada($id_ddjj, $horarios);
            } else {
                throw new Exception(mb_convert_encoding('El cargo solo puede poseer una declaración vigente al mismo tiempo', 'iso-8859-1', 'utf-8'));
            }
        }
    }

    public function agregar_declaracion_jurada($cargo, $horarios)
    {

        //    $declaradas = 0;
        //    foreach ($horarios as $h) {
        //        foreach ($h['dia_id'] as $dia) {
        //            $entrada = strtotime($h['desde']);
        //            $salida = strtotime($h['hasta']);
        //            $declaradas = $declaradas + ($salida - $entrada);
        //        }
        //    }
        //    $declaradas = $declaradas / 3600;
        //    $reglamentarias = dao_empleado::get_carga_horaria_cargo($cargo);

        if (isset($cargo) && isset($horarios)) {
            $dj = array(
                'persona_id' => dao_empleado::get_persona_cargo($cargo['cargo_id']),
                'cargo_id' => $cargo['cargo_id'],
                'fecha' => date('Y-m-d'),
            );
            dao_empleado::desactivar_declaraciones_vigentes($cargo);

            $id = $this->dep('declaracion_jurada')->nueva_fila($dj);
            $this->dep('declaracion_jurada')->set_cursor($id);
            $this->dep('declaracion_jurada')->sincronizar();
            $nueva_ddjj = $this->dep('declaracion_jurada')->get();
            $this->dep('declaracion_jurada')->resetear();

            foreach ($horarios as $horario) {
                foreach ($horario['dia_id'] as $dia) {
                    $h['desde'] = $horario['desde'];
                    $h['hasta'] = $horario['hasta'];
                    $h['dia_id'] = $dia;
                    $h['declaracion_jurada_id'] = $nueva_ddjj['declaracion_jurada_id'];
                    self::agregar_horario($h);
                }
            }
        }
    }

    public function actualizar_declaracion_jurada($id, $registro)
    {
        $this->dep('dr_declaracion_jurada')->dep('declaracion_jurada')->cargar($id);
        $this->dep('dr_declaracion_jurada')->dep('declaracion_jurada')->set($registro);
        $this->dep('dr_declaracion_jurada')->dep('declaracion_jurada')->sincronizar();
    }

    public function get_horarios_ddjj($filtro = null)
    {
        $this->dep('dr_declaracion_jurada')->tabla('declaracion_jurada')->cargar($filtro);
        $dj = $this->dep('dr_declaracion_jurada')->tabla('declaracion_jurada')->get_id_fila_condicion($filtro);
        $this->dep('dr_declaracion_jurada')->tabla('declaracion_jurada')->set_cursor($dj[0]);
        $this->dep('dr_declaracion_jurada')->tabla('horario')->cargar();
        return $this->dep('dr_declaracion_jurada')->tabla('horario')->get_filas();
    }

    public function actualizar_horarios_declaracion_jurada($ddjj_id, $registros)
    {
        $tuplas = array();
        foreach ($registros as $reg) {
            $reg['declaracion_jurada_id'] = $ddjj_id['declaracion_jurada_id'];
            array_push($tuplas, $reg);
        }
        $this->dep('dr_declaracion_jurada')->dep('horario')->procesar_filas($tuplas);
        $this->dep('dr_declaracion_jurada')->dep('horario')->sincronizar();
        $this->dep('dr_declaracion_jurada')->dep('horario')->resetear();
    }

    public function agregar_horario($registro)
    {
        $this->dep('horario')->nueva_fila($registro);
        $this->dep('horario')->sincronizar();
        $this->dep('horario')->resetear();
    }
}
