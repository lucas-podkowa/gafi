<?php
class ci_legajo extends gafi_ci
{
    protected $s__filtro;
    protected $s__id_legajo;
    protected $s__id_cargo;
    protected $s__id_afectacion;

    //-----------------------------------------------------------------------------------
    //---- Configuraciones --------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf()
    {
        if (isset($this->s__id_legajo)) {
            $this->cn()->cargar_legajo($this->s__id_legajo);
        } else {
            $this->cn()->resetear_legajo();
        }
        $this->dep('cuadro_detalles')->set_grupo_eventos_activo('no_cargado');
        $this->dep('cuadro_afectaciones')->set_grupo_eventos_activo('no_cargado');
    }

    // ###################################################################################
    //---- C U A D R O S ----------------------------------------------------------------
    // ###################################################################################

    //---- cuadro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__filtro)) {
            $cuadro->set_datos(dao_empleado::get_legajos(isset($this->s__filtro) ? $this->s__filtro : null));
        }
    }

    public function evt__cuadro__seleccion($seleccion)
    {
        //$cuadro_detalles->set_datos(dao_empleado::get_cargos_legajo($seleccion));
        $this->s__id_legajo = $seleccion;
        $this->dep('cuadro_detalles')->set_grupo_eventos_activo('cargado');
        $this->dep('cuadro_afectaciones')->set_grupo_eventos_activo('cargado');
    }

    public function evt__cuadro__agregar($datos)
    {
        unset($this->s__id_legajo);
        $this->set_pantalla('pant_edicion_legajo');
    }

    public function evt__cuadro__modificacion($seleccion)
    {
        $this->s__id_legajo = $seleccion;
        $this->set_pantalla('pant_edicion_legajo');
    }

    //---- cuadro_detalles (cargos y horarios) ------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro_detalles(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__id_legajo)) {
            $cuadro->set_datos(dao_empleado::get_cargos_y_horarios($this->s__id_legajo));
        }
    }

    public function evt__cuadro_detalles__seleccion($seleccion)
    {
        $this->s__id_cargo = $seleccion;

        $this->set_pantalla('pant_edicion_cargo');
    }

    public function evt__cuadro_detalles__agregar($datos)
    {
        if (isset($this->s__id_legajo)) {
            if ($this->cn()->puede_agregar_cargo($this->s__id_legajo)) {
                $this->set_pantalla('pant_edicion_cargo');
            } else {
                toba::notificacion()->warning('La persona seleccionada ya no puede tener más cargos activos');
            }
        }
    }

    //-----------------------------------------------------------------------------------
    //---- cuadro_afectaciones ----------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro_afectaciones(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__id_legajo)) {
            if ($this->cn()->tiene_cargos_activos($this->s__id_legajo)) {
                $cuadro->set_datos(dao_empleado::get_afectaciones_legajo($this->s__id_legajo));
            } else {
                $this->dep('cuadro_afectaciones')->eliminar_evento('agregar_afectacion');
            }
        }
    }

    public function evt__cuadro_afectaciones__modificacion($seleccion)
    {
        $this->s__id_afectacion = $seleccion;
        $this->set_pantalla('pant_edicion_afectacion');
    }

    public function evt__cuadro_afectaciones__agregar_afectacion($datos)
    {
        //$this->s__id_legajo = $seleccion;
        if (isset($this->s__id_legajo)) {
            $this->set_pantalla('pant_edicion_afectacion');
        }
    }

    // ###################################################################################
    //---- F O R M U L A R I O S  -------------------------------------------------------
    // ###################################################################################

    // ---- formulario_legajo ------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario_legajo(gafi_ei_formulario $form)
    {
        if (isset($this->s__id_legajo)) {

            $datos = $this->cn()->get_legajo($this->s__id_legajo);
            $form->set_datos($datos);
        }
    }

    public function evt__formulario_legajo__alta($datos)
    {
        $this->cn()->agregar_legajo($datos);
        unset($this->s__id_legajo);
        unset($this->s__filtro); // para que se vea el registro que cargo
        $this->set_pantalla('pant_inicial');
    }

    public function evt__formulario_legajo__modificacion($datos)
    {
        try {
            $this->cn()->modificar_legajo($datos);
            unset($this->s__id_legajo);
            unset($this->s__id_cargo);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }
    }

    public function evt__formulario_legajo__baja()
    {
    }

    public function evt__formulario_legajo__cancelar()
    {
        unset($this->s__id_legajo);
        unset($this->s__id_cargo);
        unset($this->s__filtro);
        $this->set_pantalla('pant_inicial');
    }

    //---- formulario_cargos ------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario_cargos(gafi_ei_formulario $form)
    {
        if (isset($this->s__id_cargo)) {
            $datos = $this->cn()->get_cargo($this->s__id_cargo);
            $form->set_datos($datos);
        }
        if (isset($this->s__id_legajo)) {
            $datos = array('legajo_id' => $this->s__id_legajo['legajo_id'], 'legajo' => dao_empleado::get_legajo_desc($this->s__id_legajo['legajo_id']));
            $form->set_datos($datos);
        }
    }

    public function evt__formulario_cargos__alta($datos)
    {
        $this->cn()->agregar_cargo($datos);
        unset($this->s__id_legajo);
        unset($this->s__id_cargo);
        $this->set_pantalla('pant_inicial');
    }

    public function evt__formulario_cargos__baja()
    {
    }

    public function evt__formulario_cargos__modificacion($datos)
    {
    }

    public function evt__formulario_cargos__cancelar()
    {
        unset($this->s__id_legajo);
        unset($this->s__id_cargo);
        $this->set_pantalla('pant_inicial');
    }

    //-----------------------------------------------------------------------------------
    //---- formulario_afectacion --------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario_afectacion(gafi_ei_formulario $form)
    {
        if (isset($this->s__id_legajo)) {
            if (isset($this->s__id_afectacion)) {
                $datos = $this->cn()->get_afectacion_legajo($this->s__id_afectacion);
                $datos['legajo'] = dao_empleado::get_legajo_desc($this->s__id_legajo['legajo_id']);
                $form->set_datos($datos);
            } else {
                $datos = array('legajo_id' => $this->s__id_legajo['legajo_id'], 'legajo' => dao_empleado::get_legajo_desc($this->s__id_legajo['legajo_id']));
                $form->set_datos($datos);
                $this->dep('formulario_afectacion')->set_grupo_eventos_activo('no_cargado');
            }
        }
    }

    public function evt__formulario_afectacion__alta($datos)
    {
        try {
            $this->cn()->agregar_afectacion_legajo($datos);
            unset($this->s__id_legajo);
            unset($this->s__id_cargo);
            unset($this->s__id_afectacion);
            unset($this->s__filtro); // para que se vea el registro que cargo
            $this->set_pantalla('pant_inicial');
        } catch (\Throwable $th) {
            toba::notificacion()->warning(mb_convert_encoding('La persona seleccionada ya posee dicha afectación', 'iso-8859-1', 'utf-8'));
        }

    }

    public function evt__formulario_afectacion__modificacion($datos)
    {
        try {
            $this->cn()->modificar_afectacion_legajo($datos);
            unset($this->s__id_cargo);
            unset($this->s__id_afectacion);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning('No se pueden guardar afectaciones repetidas');
        }
    }

    public function evt__formulario_afectacion__cancelar()
    {
        unset($this->s__id_cargo);
        unset($this->s__id_afectacion);
        $this->set_pantalla('pant_inicial');
    }

    //-----------------------------------------------------------------------------------
    //---- filtro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__filtro(gafi_ei_filtro $filtro)
    {
        if (isset($this->s__filtro)) {
            $filtro->set_datos($this->s__filtro);
        }
    }

    public function evt__filtro__filtrar($datos)
    {
        unset($this->s__filtro);
        unset($this->s__id_legajo);

        if (!empty($datos)) {
            $this->s__filtro = $datos;
            $this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND');
        } else {
            toba::notificacion()->info('Seleccione alg&uacuten FILTRO para continuar');
        }
    }

    public function evt__filtro__cancelar()
    {
        unset($this->s__filtro);
        unset($this->s__id_legajo);
    }
}
