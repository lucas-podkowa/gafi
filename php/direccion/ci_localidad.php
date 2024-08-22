<?php
class ci_localidad extends gafi_ci
{
    protected $s__id;
    protected $s__filtro;
    //-----------------------------------------------------------------------------------
    //---- Configuraciones --------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf()
    {
        if (isset($this->s__id)) {
            $this->cn()->cargar_localidad($this->s__id);
        }
    }

    //-----------------------------------------------------------------------------------
    //---- cuadro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__cuadro(gafi_ei_cuadro $cuadro)
    {
        if (isset($this->s__filtro)) {
            $cuadro->set_datos(dao_direccion::get_localidades(isset($this->s__filtro) ? $this->s__filtro : null));
        }
    }

    public function evt__cuadro__agregar($datos)
    {
		unset($this->s__id);
        $this->set_pantalla('pant_edicion');
    }

    //-----------------------------------------------------------------------------------
    //---- formulario -------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario(gafi_ei_formulario $form)
    {
        if (isset($this->s__id)) {
            $form->set_datos($this->cn()->get_localidad($this->s__id));
        }
    }

    public function evt__formulario__alta($datos)
    {
        try {
            $this->cn()->agregar_localidad($datos);
            unset($this->s__id);
            unset($this->s__filtro);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }

    }

    public function evt__formulario__modificacion($datos)
    {
    }

    public function evt__formulario__cancelar()
    {
        unset($this->s__id);
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
            unset($this->s__id);
            $this->s__filtro = $datos;
            $this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND');
        } else {
            toba::notificacion()->info('Seleccione alg&uacuten FILTRO para continuar');
        }
    }

    public function evt__filtro__cancelar()
    {
        unset($this->s__id);
        $this->set_pantalla('pant_edicion');
    }
}
