<?php
include_once "empleados/dao_empleado.php";
class ci_cargo extends gafi_ci
{
    protected $s__filtro;
    protected $s__id_cargo;
    protected $s__id_ddjj;
    protected $s__cabecera_ddjj;
    protected $s__horarios;
    protected $s__horarios_edicion;
    //-----------------------------------------------------------------------------------
    //---- Configuraciones --------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf()
    {
        if (isset($this->s__id_cargo)) {
            $this->cn()->get_cargo($this->s__id_cargo);
        } else {
            $this->cn()->resetear_cargo();
        }
        $this->dep('cuadro_DDJJ')->set_grupo_eventos_activo('no_cargado');
    }

    //-----------------------------------------------------------------------------------
    //---- cuadro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__filtro)) {
            $datos = dao_empleado::get_cargos(isset($this->s__filtro) ? $this->s__filtro : null);
            $cuadro->set_datos($datos);
        }
    }

    public function evt__cuadro__seleccion($seleccion)
    {
        $this->s__id_cargo = $seleccion;
        $this->dep('cuadro_DDJJ')->set_grupo_eventos_activo('cargado');
    }

    public function evt__cuadro__modificacion($seleccion)
    {
        $this->s__id_cargo = $seleccion;
        $this->set_pantalla('pant_edicion');
    }

    public function evt__cuadro__agregar($datos)
    {
        unset($this->s__filtro);
        unset($this->s__id_cargo);
        $this->set_pantalla('pant_edicion');
    }

    //-----------------------------------------------------------------------------------
    //---- formulario -------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario(gafi_ei_formulario $form)
    {
        if (isset($this->s__id_cargo)) {
            $datos = $this->cn()->get_cargo($this->s__id_cargo);
            $form->set_datos($datos);
        }
    }
    public function evt__formulario__alta($datos)
    {

        if (isset($datos['legajo_id'])) {
            if ($this->cn()->puede_agregar_cargo($datos)) {
                $this->cn()->agregar_cargo($datos);
                unset($this->s__id_legajo);
                unset($this->s__id_cargo);
                $this->set_pantalla('pant_inicial');
            } else {
                toba::notificacion()->warning('La persona seleccionada ya no puede tener más cargos activos');
            }
        }
    }

    public function evt__formulario__baja()
    {
        $this->cn()->eliminar_cargo();
        unset($this->s__id_cargo);
        $this->set_pantalla('pant_inicial');
    }

    public function evt__formulario__modificacion($datos)
    {
        try {
            $this->cn()->modificar_cargo($datos);
            unset($this->s__id_legajo);
            unset($this->s__id_cargo);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }
    }

    public function evt__formulario__cancelar()
    {
        unset($this->s__id_cargo);
        unset($this->s__id_ddjj);
        $this->set_pantalla('pant_inicial');
    }

    public function evt__formulario_ddjj__agregar($datos)
    {
    }

    //-----------------------------------------------------------------------------------
    //---- cuadro_DDJJ ------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro_DDJJ(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__id_cargo)) {
            $datos = dao_empleado::get_horarios_vigentes_del_cargo($this->s__id_cargo);
            $cuadro->set_datos($datos);
        }
    }

    public function evt__cuadro_DDJJ__agregar($datos)
    {
        $this->set_pantalla('pant_edicion_ddjj');
    }

    //funcion no activa en por el momento
    public function evt__cuadro_DDJJ__edicion($seleccion)
    {
        $this->s__id_ddjj = $seleccion;
        $this->set_pantalla('pant_edicion_ddjj');
    }

    //-----------------------------------------------------------------------------------
    //---- fomulario_cabecera_ddjj ------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__fomulario_cabecera_ddjj(gafi_ei_formulario $form)
    {
        $cargo = $cabecera = array();

        if (isset($this->s__id_cargo)) {
            $cargo = dao_empleado::get_cargo_desc($this->s__id_cargo);
        }

        if (isset($this->s__id_ddjj)) {
            $cabecera = dao_empleado::get_cabecera_ddjj($this->s__id_ddjj);
            if (empty($cabecera['estado'])) {
                $cabecera['estado'] = 0;
            }
        }

        $form->set_datos(array_merge($cargo, $cabecera));
    }

    //funcion no activa en por el momento
    public function evt__fomulario_cabecera_ddjj__modificacion($datos)
    {
        $this->s__cabecera_ddjj = $datos;
    }

    //-----------------------------------------------------------------------------------
    //---- ml_horarios_ddjj -------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__ml_horarios_ddjj(gafi_ei_formulario_ml $form_ml)
    {
        if (isset($this->s__id_ddjj)) {

            $datos = $this->cn()->get_horarios_ddjj($this->s__id_ddjj);
            if (isset($datos)) {
                $filas = array();
                foreach ($datos as $d) {
                    $d['dia_id'] = array($d['dia_id']);
                    array_push($filas, $d);
                }
                //$datos = dao_empleado::get_horarios_ddjj($this->s__id_ddjj);
                $form_ml->set_datos($filas);
            }
        }
    }

    public function evt__ml_horarios_ddjj__modificacion($datos)
    {
        $this->s__horarios = $datos;
    }

    //-----------------------------------------------------------------------------------
    //---- ml_horarios_edicion ----------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__ml_horarios_edicion(gafi_ei_formulario_ml $form_ml)
    {
        if (isset($this->s__id_ddjj)) {
            $datos = $this->cn()->get_horarios_ddjj($this->s__id_ddjj);
            //$datos = dao_empleado::get_horarios_ddjj($this->s__id_ddjj);
            if (isset($datos)) {
                $form_ml->set_datos($datos);
            }
        }
    }
    public function evt__ml_horarios_edicion__modificacion($datos)
    {
        $this->s__horarios_edicion = $datos;
    }

    //-----------------------------------------------------------------------------------
    //---- Eventos de la pantalla edicion ddjj ------------------------------------------
    //-----------------------------------------------------------------------------------

    public function evt__procesar()
    {
        if (isset($this->s__id_cargo)) {
            if (isset($this->s__horarios)) {
                try {
                    if (isset($this->s__id_ddjj)) {
                        toba::notificacion()->warning('funcionalidad deshabilitada');
                        //$this->cn()->modificar_declaracion_jurada($this->s__id_ddjj, $this->s__cabecera_ddjj, $this->s__horarios_edicion);
                    } else {
                        $this->cn()->agregar_declaracion_jurada($this->s__id_cargo, $this->s__horarios);
                    }
                    $this->set_pantalla('pant_inicial');
                } catch (Throwable $t) {
                    toba::notificacion()->warning($t->getMessage());
                }
            }
        }
        $this->set_pantalla('pant_inicial');
    }

    public function evt__cancelar()
    {
        unset($this->s__id_cargo);
        unset($this->s__id_ddjj);
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
    }
    //-----------------------------------------------------------------------------------
    //---- Configuraciones --------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__pant_edicion_ddjj(toba_ei_pantalla $pantalla)
    {
        if (isset($this->s__id_ddjj)) {
            $this->pantalla()->eliminar_dep('ml_horarios_ddjj');
        } else {
            $this->pantalla()->eliminar_dep('ml_horarios_edicion');
        }
    }
}
