<?php
include_once "empleados/dao_empleado.php";
class ci_licencia_x_cargo extends gafi_ci
{
    protected $s__filtro;
    protected $s__id_licencia_x_cargo;
    //-----------------------------------------------------------------------------------
    //---- Configuraciones --------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf()
    {
        if (isset($this->s__id_licencia_x_cargo)) {
            $this->cn()->get_licencia_x_cargo($this->s__id_licencia_x_cargo);
        } else {
            $this->cn()->resetear_licencia_x_cargo();
        }
    }

    //-----------------------------------------------------------------------------------
    //---- cuadro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro(gafi_ei_cuadro $cuadro)
    {
        $cadena = "Licencias asignadas";
        if (isset($this->s__filtro)) {
            $cuadro->set_datos(dao_licencia::get_licencias_x_cargo(isset($this->s__filtro) ? $this->s__filtro : null));

            if (isset($this->s__filtro['anulado'])) {
                if ($this->s__filtro['anulado']['valor'] == 1) {
                    $this->dep('cuadro')->eliminar_evento('anular');
                }
            }



            if (isset($this->s__filtro['anulado'])) {
                if ($this->s__filtro['anulado']['valor'] == 1) {
                    $cadena .= " Anuladas";
                } else {
                    $cadena .= " No Anuladas";
                }
            }
            if (isset($this->s__filtro['vigencia'])) {
                switch ($this->s__filtro['vigencia']['condicion']) {
                    case 'es_igual_a':
                        $cadena .= " vigentes en " . date("d/m/Y", strtotime($this->s__filtro['vigencia']['valor']));
                        break;
                    case 'es_distinto_de':
                        $cadena .= " no vigentes en " . date("d/m/Y", strtotime($this->s__filtro['vigencia']['valor']));
                        break;
                    case 'desde':
                        $cadena .= " vigentes desde " . date("d/m/Y", strtotime($this->s__filtro['vigencia']['valor']));
                        break;
                    case 'hasta':
                        $cadena .= " vigentes hasta " . date("d/m/Y", strtotime($this->s__filtro['vigencia']['valor']));
                        break;
                    case 'entre':
                        $cadena .= " vigentes entre " . date("d/m/Y", strtotime($this->s__filtro['vigencia']['valor']['desde'])) . " y " .
                            date("d/m/Y", strtotime($this->s__filtro['vigencia']['valor']['hasta']));
                        break;

                    default:
                        $cadena .= " y fecha de fin " . $this->s__filtro['fecha_baja']['condicion'] . " " . $this->s__filtro['fecha_baja']['valor'];
                        break;
                }
            }

            if (isset($this->s__filtro['fecha_alta'])) {
                switch ($this->s__filtro['fecha_alta']['condicion']) {
                    case 'es_igual_a':
                        $cadena .= " con fecha de inicio igual a " . date("d/m/Y", strtotime($this->s__filtro['fecha_alta']['valor']));
                        break;
                    case 'es_distinto_de':
                        $cadena .= " con fecha de inicio distinta de " . date("d/m/Y", strtotime($this->s__filtro['fecha_alta']['valor']));
                        break;
                    case 'entre':
                        $cadena .= " con fecha de inicio entre " . date("d/m/Y", strtotime($this->s__filtro['fecha_alta']['valor']['desde'])) . " y " .
                            date("d/m/Y", strtotime($this->s__filtro['fecha_alta']['valor']['hasta']));
                        break;
                    default:
                        $cadena .= " con fecha de inicio " . $this->s__filtro['fecha_alta']['condicion'] . " " . date("d/m/Y", strtotime($this->s__filtro['fecha_alta']['valor']));
                        break;
                }
            }

            if (isset($this->s__filtro['fecha_baja'])) {
                if (isset($this->s__filtro['fecha_alta'])) {
                    switch ($this->s__filtro['fecha_baja']['condicion']) {
                        case 'es_igual_a':
                            $cadena .= " y fecha de fin igual a " . date("d/m/Y", strtotime($this->s__filtro['fecha_baja']['valor']));
                            break;
                        case 'es_distinto_de':
                            $cadena .= " y fecha de fin distinta de " . date("d/m/Y", strtotime($this->s__filtro['fecha_baja']['valor']));
                            break;
                        case 'entre':
                            $cadena .= " y fecha de fin entre " . $this->s__filtro['fecha_baja']['valor']['desde'] . " y " . $this->s__filtro['fecha_baja']['valor']['hasta'];
                            break;
                        default:
                            $cadena .= " y fecha de fin " . $this->s__filtro['fecha_baja']['condicion'] . " " . $this->s__filtro['fecha_baja']['valor'];
                            break;
                    }
                } else {
                    switch ($this->s__filtro['fecha_baja']['condicion']) {
                        case 'es_igual_a':
                            $cadena .= " con fecha de fin igual a " . $this->s__filtro['fecha_baja']['valor'];
                            break;
                        case 'es_distinto_de':
                            $cadena .= " con fecha de fin distinta de " . $this->s__filtro['fecha_baja']['valor'];
                            break;
                        case 'entre':
                            $cadena .= " con fecha de fin entre " . $this->s__filtro['fecha_baja']['valor']['desde'] . " y " . $this->s__filtro['fecha_baja']['valor']['hasta'];
                            break;
                        default:
                            $cadena .= " con fecha de fin " . $this->s__filtro['fecha_baja']['condicion'] . " " . $this->s__filtro['fecha_baja']['valor'];
                            break;
                    }
                }
            }
        }
        $cuadro->set_titulo($cadena);
    }

    public function evt__cuadro__edicion($seleccion)
    {
        $this->s__id_licencia_x_cargo = $seleccion;
        $this->set_pantalla('pant_edicion');
    }

    public function evt__cuadro__agregar($datos)
    {
        //toba::notificacion()->warning('Se activará en los proximos dias');
        unset($this->s__filtro);
        unset($this->s__id_licencia_x_cargo);
        $this->set_pantalla('pant_edicion');
    }

    public function evt__cuadro__anular($seleccion)
    {
        $this->s__id_licencia_x_cargo = $seleccion;
        $this->set_pantalla('pant_anulacion');
    }

    //-----------------------------------------------------------------------------------
    //---- formulario -------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario(gafi_ei_formulario $form)
    {
        if (isset($this->s__id_licencia_x_cargo)) {
            $datos = $this->cn()->get_licencia_x_cargo($this->s__id_licencia_x_cargo);
            $form->set_datos($datos);
        }
    }

    public function evt__formulario__alta($datos)
    {
        try {
            $this->cn()->agregar_licencia_x_cargo($datos);
            unset($this->s__id_licencia_x_cargo);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }
    }

    public function evt__formulario__modificacion($datos)
    {
        try {
            $this->cn()->modificar_licencia_x_cargo($datos, $this->s__id_licencia_x_cargo);
            unset($this->s__id_licencia_x_cargo);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }
    }

    public function evt__formulario__cancelar()
    {
        unset($this->s__id_licencia_x_cargo);
        $this->set_pantalla('pant_inicial');
    }

    //-----------------------------------------------------------------------------------
    //---- formulario_anulacion ---------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario_anulacion(gafi_ei_formulario $form)
    {
    }

    public function evt__formulario_anulacion__anulacion($datos)
    {
        try {
            $this->cn()->anular_licencia_x_cargo($datos, $this->s__id_licencia_x_cargo);
            unset($this->s__id_licencia_x_cargo);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }
    }
    public function evt__formulario_anulacion__cancelar()
    {
        unset($this->s__id_licencia_x_cargo);
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
        unset($this->s__id_licencia_x_cargo);

        if (!empty($datos)) {
            $this->s__filtro = $datos;
            //como no quiero que el id vigencia sea parte del where, lo quito de las clausualas
            //eso hace que el filtro contenga ese campo pero que no se genere en el where automaticamente
            $clausulas = $this->dep('filtro')->get_sql_clausulas();
            if (isset($clausulas["vigencia"])) {
                unset($clausulas["vigencia"]);
            }
            $this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND', $clausulas);
        } else {
            toba::notificacion()->info('Seleccione alg&uacuten FILTRO para continuar');
        }
    }

    public function evt__filtro__cancelar()
    {
        unset($this->s__filtro);
        unset($this->s__id_licencia_x_cargo);
    }
}
