<?php
class ci_sector extends gafi_ci
{
    protected $s__filtro;
    protected $s__id_sector;
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
        //if (isset($this->s__filtro)) {
        $cuadro->set_datos(dao_configuracion::get_sectores(isset($this->s__filtro) ? $this->s__filtro : null));
        //}
    }
    public function evt__cuadro__agregar($datos)
    {
        $this->set_pantalla('pant_edicion');
    }

    public function evt__cuadro__edicion($seleccion)
    {
        $this->s__id_sector = $seleccion;
        $this->set_pantalla('pant_edicion');
    }

    //-----------------------------------------------------------------------------------
    //---- formulario -------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    public function conf__formulario(gafi_ei_formulario $form)
    {
        if (isset($this->s__id_sector)) {
            $datos = $this->cn()->get_sector($this->s__id_sector);
            $datos['personas'] = dao_configuracion::get_id_personas_sector($this->s__id_sector['sector_id']);
            $form->set_datos($datos);

        }
    }

    public function evt__formulario__alta($datos)
    {
        try {
            $this->cn()->agregar_sector($datos);
            unset($this->s__id_sector);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }
    }

    public function evt__formulario__baja()
    {
    }

    public function evt__formulario__modificacion($datos)
    {

        try {
            $this->cn()->modificar_sector($datos);
            unset($this->s__id_sector);
            $this->set_pantalla('pant_inicial');
        } catch (Throwable $t) {
            toba::notificacion()->warning($t->getMessage());
        }
    }

    public function evt__formulario__cancelar()
    {
        unset($this->s__id_sector);
        $this->set_pantalla('pant_inicial');
    }

    //-----------------------------------------------------------------------------------
    //---- filtro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

	function conf__filtro(gafi_ei_filtro $filtro)
	{
		if (isset($this->s__filtro)) {
			$filtro->set_datos($this->s__filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		unset($this->s__filtro);
		unset($this->s__id_receso);

		if (!empty($datos)) {
			$this->s__filtro = $datos;
			$this->s__filtro['where'] = $this->dep('filtro')->get_sql_where('AND');
		} else {
			toba::notificacion()->info('Seleccione alg&uacuten FILTRO para continuar');
		}
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
		unset($this->s__id_receso);
	}

}
?>