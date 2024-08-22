<?php
include "licencias/dao_licencia.php";
class ci_licencias_vigentes_x_periodo extends gafi_ci
{
    protected $s__filtro;

    //-----------------------------------------------------------------------------------
    //---- Configuraciones --------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf()
    {
        if (!isset($this->s__filtro)) {
            $this->s__filtro = array('vigencia' => ['condicion' => 'es_igual_a', 'valor' => $ahora = date('Y-m-d')]);
        }
    }

    //-----------------------------------------------------------------------------------
    //---- cuadro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__filtro)) {
            $cuadro->set_datos(dao_licencia::get_licencias_x_cargo(isset($this->s__filtro) ? $this->s__filtro : null));

            $cadena = "Licencias";

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
                        break;
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
    }
}
