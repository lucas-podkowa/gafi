<?php
include_once 'configuracion/dao_configuracion.php';

class cn_configuracion extends gafi_cn
{
    // ###################################################################################
    //----  C I C L O    L E C T I V O  --------------------------------------------------
    // ###################################################################################

    public function get_ciclo_lectivo($id = null)
    {

        //$id_interno = $this->dep('ciclo_lectivo')->get_id_fila_condicion($id);
        //$this->dep('ciclo_lectivo')->set_cursor($id_interno[0]);
        //return $this->dep('ciclo_lectivo')->get();

        $this->dep('ciclo_lectivo')->cargar($id);
        return $this->dep('ciclo_lectivo')->get();
    }

    public function agregar_ciclo_lectivo($registro)
    {
        $id = $this->dep('ciclo_lectivo')->nueva_fila($registro);
        $this->dep('ciclo_lectivo')->set_cursor($id);
        $this->dep('ciclo_lectivo')->sincronizar();
        $this->dep('ciclo_lectivo')->resetear();
    }

    public function modificar_ciclo_lectivo($registro)
    {

        if ($this->dep('ciclo_lectivo')->esta_cargada()) {
            $old = $this->dep('ciclo_lectivo')->get();
            if (dao_configuracion::existe_ciclo($registro['ciclo'], $old['ciclo'])) {
                throw new Exception('El ciclo ' . $registro['ciclo'] . ' ya existe en la Base de Datos');
            } else {

                $i = DateTime::createFromFormat('Y-m-d', $registro['desde']);
                $f = DateTime::createFromFormat('Y-m-d', $registro['hasta']);
                if (($i->format('Y') != $registro['ciclo']) || ($f->format('Y') != $registro['ciclo'])) {
                    throw new Exception('Las fechas de inicio y fin deben estar incluidos dentro del ciclo lectivo');
                } else {
                    $this->dep('ciclo_lectivo')->set($registro);
                    $this->dep('ciclo_lectivo')->sincronizar();
                    $this->dep('ciclo_lectivo')->resetear();
                }
            }
        }
    }

    public function resetear_ciclo_lectivo()
    {
        $this->dep('ciclo_lectivo')->resetear();
    }

    // ###################################################################################
    //----  R E C E S O  -----------------------------------------------------------------
    // ###################################################################################

    public function cargar_receso($id)
    {
        $this->dep('receso')->cargar($id);
    }

    public function resetear_receso()
    {
        $this->dep('receso')->resetear();
    }

    public function get_receso($id)
    {
        $id_interno = $this->dep('receso')->get_id_fila_condicion($id);
        $this->dep('receso')->set_cursor($id_interno[0]);
        return $this->dep('receso')->get();
    }

    public function agregar_receso($registro)
    {
        $id = $this->dep('receso')->nueva_fila($registro);
        $this->dep('receso')->set_cursor($id);
        $this->dep('receso')->sincronizar();
        $this->dep('receso')->resetear();
    }

    public function modificar_receso($registro)
    {
        if ($this->dep('receso')->esta_cargada()) {
            //$i = DateTime::createFromFormat('Y-m-d', $registro['inicio']);
            //$f = DateTime::createFromFormat('Y-m-d', $registro['fin']);

            $this->dep('receso')->set($registro);
            $this->dep('receso')->sincronizar();
            $this->dep('receso')->resetear();
        }
    }

    public function eliminar_receso()
    {
        if ($this->dep('receso')->esta_cargada() && $this->dep('receso')->hay_cursor()) {
            $this->dep('receso')->eliminar();
            $this->dep('receso')->sincronizar();
            $this->dep('receso')->resetear();
        }
    }

    // ###################################################################################
    // ---  D I A  N O  L A B O R A B L E  -----------------------------------------------
    // ###################################################################################

    public function cargar_dia_no_laborable($id)
    {
        $this->dep('dia_no_laborable')->cargar($id);
    }

    public function resetear_dia_no_laborable()
    {
        $this->dep('dia_no_laborable')->resetear();
    }

    public function get_dia_no_laborable($id)
    {
        //$id_interno = $this->dep('dia_no_laborable')->get_id_fila_condicion($id);
        //$this->dep('dia_no_laborable')->set_cursor($id_interno[0]);
        //return $this->dep('dia_no_laborable')->get();

        $this->dep('dia_no_laborable')->cargar($id);
        return $this->dep('dia_no_laborable')->get();
    }

    public function agregar_dia_no_laborable($registro)
    {
        if (dao_configuracion::existe_dia_no_laborable($registro, null)) {
            throw new Exception('Los datos cargados coinciden con una Día no Laborable existente');
        } else {
            $id = $this->dep('dia_no_laborable')->nueva_fila($registro);
            $this->dep('dia_no_laborable')->set_cursor($id);
            $this->dep('dia_no_laborable')->sincronizar();
            $this->dep('dia_no_laborable')->resetear();
        }
    }

    public function modificar_dia_no_laborable($registro, $id)
    {
        if ($this->dep('dia_no_laborable')->esta_cargada()) {
            if (dao_configuracion::existe_dia_no_laborable($registro, $id)) {
                throw new Exception('Los datos cargados coinciden con una Día no Laborable existente');
            } else {
                $this->dep('dia_no_laborable')->set($registro);
                $this->dep('dia_no_laborable')->sincronizar();
                $this->dep('dia_no_laborable')->resetear();
            }
        }
    }

    // ###################################################################################
    //----  PREFERENCIA  -----------------------------------------------------------------
    // ###################################################################################

    public function cargar_preferencia($id)
    {
        $this->dep('preferencia')->cargar($id);
    }

    public function esta_cargada_preferencia()
    {
        return $this->dep('preferencia')->esta_cargada();
    }

    public function resetear_preferencia()
    {
        $this->dep('preferencia')->resetear();
    }

    public function get_preferencia($id)
    {
        $id_interno = $this->dep('preferencia')->get_id_fila_condicion($id);
        $this->dep('preferencia')->set_cursor($id_interno[0]);
        return $this->dep('preferencia')->get();
    }

    public function agregar_preferencia($registro)
    {
        $id = $this->dep('preferencia')->nueva_fila($registro);
        $this->dep('preferencia')->set_cursor($id);
        $this->dep('preferencia')->sincronizar();
        $this->dep('preferencia')->resetear();
    }

    public function modificar_preferencia($registro)
    {
        if ($this->dep('preferencia')->esta_cargada()) {
            $this->dep('preferencia')->set($registro);
            $this->dep('preferencia')->sincronizar();
            $this->dep('preferencia')->resetear();
        }
    }

    public function eliminar_preferencia()
    {
        $this->dep('preferencia')->eliminar_fila($this->dep('preferencia')->get_cursor());
        $this->dep('preferencia')->sincronizar();
        $this->dep('preferencia')->resetear();
    }

    // ###################################################################################
    //----  S E C T O R ------------------------------------------------------------------
    // ###################################################################################
    public function get_sector($id)
    {
        //$id_interno = $this->dep('sector')->get_id_fila_condicion($id);
        //$this->dep('sector')->set_cursor($id_interno[0]);
        //return $this->dep('sector')->get();
        $this->dep('sector')->cargar($id);
        return $this->dep('sector')->get();
    }

    public function agregar_sector($registro)
    {
        $id = $this->dep('sector')->nueva_fila($registro);
        $this->dep('sector')->set_cursor($id);
        $this->dep('sector')->sincronizar();

        $filas = array();
        $sector_id = $this->dep('sector')->get()['sector_id'];

        foreach ($registro['personas'] as $persona) {
            $a = array('persona_id' => $persona, 'sector_id' => $sector_id);
            array_push($filas, $a);
        }
        self::actualizar_personas_x_sector($sector_id, $filas);

        $this->dep('sector')->resetear();
    }

    public function modificar_sector($registro)
    {
        if ($this->dep('sector')->esta_cargada()) {
            $this->dep('sector')->set($registro);
            $this->dep('sector')->sincronizar();

            $filas = array();
            $sector_id = $this->dep('sector')->get()['sector_id'];

            foreach ($registro['personas'] as $persona) {
                $a = array('persona_id' => $persona, 'sector_id' => $sector_id);
                array_push($filas, $a);
            }
            self::actualizar_personas_x_sector($sector_id, $filas);
            $this->dep('sector')->resetear();
        }
    }

    public function actualizar_personas_x_sector($sector_id, $filas)
    {
        $anteriores = dao_configuracion::get_personas_sector($sector_id);
        if (empty($filas)) {
            foreach ($anteriores as $a) {
                $this->dep('persona_x_sector')->cargar($a);
                $this->dep('persona_x_sector')->eliminar_fila($this->dep('persona_x_sector')->get_cursor());
                $this->dep('persona_x_sector')->sincronizar();
            }
            //funcion alternativa no utilizada "dao_configuracion::quitar_personas_sector($sector_id);"
        } else {
            foreach ($filas as $f) {
                if (!in_array($f, $anteriores)) {
                    $this->dep('persona_x_sector')->nueva_fila($f);
                    $this->dep('persona_x_sector')->sincronizar();
                }
            }
            foreach ($anteriores as $a) {
                if (!in_array($a, $filas)) {
                    $this->dep('persona_x_sector')->cargar($a);
                    $this->dep('persona_x_sector')->eliminar_fila($this->dep('persona_x_sector')->get_cursor());
                    $this->dep('persona_x_sector')->sincronizar();
                    //funcion alternativa no utilizada "dao_configuracion::quitar_persona_de_sector($a);"
                }
            }
        }
        $this->dep('persona_x_sector')->resetear();
    }

    public function eliminar_sector()
    {
        $this->dep('sector')->eliminar_fila($this->dep('preferencia')->get_cursor());
        $this->dep('sector')->sincronizar();
        $this->dep('sector')->resetear();
    }

}
