<?php
include "licencias/dao_licencia.php";
class ci_licencias_busqueda_personalizada extends gafi_ci
{
    protected $s__filtro;

    //-----------------------------------------------------------------------------------
    //---- Configuraciones --------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf()
    {
    }

    //-----------------------------------------------------------------------------------
    //---- cuadro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__filtro)) {
            $cuadro->set_datos(dao_licencia::get_licencias_x_cargo(isset($this->s__filtro) ? $this->s__filtro : null));

            $cadena = "Licencias asignadas";

            if (isset($this->s__filtro['anulado'])) {
                if ($this->s__filtro['anulado']['valor'] == 1) {
                    $cadena .= " Anuladas";
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
                        $cadena .= " con fecha de incicio " . $this->s__filtro['fecha_alta']['condicion'] . " " . date("d/m/Y", strtotime($this->s__filtro['fecha_alta']['valor']));
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
            $this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND', $clausulas);
        } else {
            toba::notificacion()->info('Seleccione alg�n FILTRO para continuar');
        }
    }

    public function evt__filtro__cancelar()
    {
        unset($this->s__filtro);
    }

}
