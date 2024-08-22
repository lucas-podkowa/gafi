<?php
include_once 'direccion/dao_direccion.php';
include_once 'persona/dao_persona.php';


class cn_persona extends gafi_cn
{
    protected $s__id_persona;

    function cargar_persona($id)
    {
        $this->dep('persona')->cargar($id);
        //$persona = $this->dep('persona')->get();
    }

    function resetear_persona()
    {
        $this->dep('persona')->resetear();
    }


    /*	inserta una nueva persona en el datos_tabla persona	*/
    function agregar_persona($registro)
    {
        $permitido = dao_persona::verificar_dni_duplicado($registro);
        if ($permitido) {
            $direccion = $this->agregar_direccion_persona($registro);
            $registro['direccion_id'] = $direccion['direccion_id'];

            $id = $this->dep('persona')->nueva_fila($registro);
            $this->dep('persona')->set_cursor($id);

            $this->dep('persona')->sincronizar();


            $id_persona = $this->dep('persona')->get();
            $this->dep('persona')->resetear();
            return $id_persona;
        } else {
            return false;
        }
    }
    function agregar_direccion_persona($registro)
    {
        $dir['nombre_calle'] = $registro['nombre_calle'];
        $dir['numero_calle'] = $registro['numero_calle'];
        $dir['detalle'] = $registro['detalle'];
        $dir['localidad_id'] = $registro['localidad_id'];

        $id = $this->dep('direccion')->nueva_fila($dir);
        $this->dep('direccion')->set_cursor($id);
        $this->dep('direccion')->sincronizar();
        $id_direccion = $this->dep('direccion')->get();
        $this->dep('direccion')->resetear();
        return $id_direccion;
    }

    function modificar_direccion_persona($registro)
    {
        $this->dep('direccion')->set($registro);
        $this->dep('direccion')->sincronizar();
        $this->dep('direccion')->resetear();
    }


    /*
		modifica una persona del datos_tabla persona
	*/
    function modificar_persona($registro)
    {
        $valido = true;

        if ($this->dep('persona')->esta_cargada()) {
            $old = $this->dep('persona')->get();
            if ($old['numero_documento'] != $registro['numero_documento']) {
                $permitido = dao_persona::verificar_dni_duplicado($registro);
                if (!$permitido) {
                    $valido = false;
                }
            }
            if ($valido) {
                self::modificar_direccion_persona($registro);
                $this->dep('persona')->set($registro);
                $this->dep('persona')->sincronizar();
                $this->dep('persona')->resetear();
            } else {
                toba::notificacion()->warning('El número de documento ' . $registro['numero_documento'] . ' ya se encuentra registrado');
            }
        }
    }

    function actualizar_cantidad_huellas_persona($persona)
    {
        dao_persona::actualizar_cantidad_huellas_persona($persona);
    }


    /*
		elimina una persona del datos_tabla persona
	*/
    function eliminar_persona()
    {
        if ($this->dep('persona')->esta_cargada() && $this->dep('persona')->dep('persona')->hay_cursor()) {
            // eliminar datos de tablas hija. en alguna de las dos tiene que haber hijos.
            $this->dep('persona')->eliminar();
            // sincronizamos la relacion.
            $this->dep('persona')->sincronizar();
            $this->dep('persona')->resetear();
        }
    }


    /*
		Obtiene el listado de personas
	*/
    function get_personas($filtro = null)
    {
        if ($this->dep('persona')->esta_cargada()) {
            return $this->dep('persona')->get_filas($filtro, false);
        }
    }
    /*
		setea el cursor a una fila del datos_tabla persona
		Obtiene la persona seteada
	*/
    function get_persona($id)
    {
        $this->dep('persona')->cargar($id);
        $datos = $this->dep('persona')->get();

        $this->dep('direccion')->cargar(array('direccion_id' => $datos['direccion_id']));
        $dir = $this->dep('direccion')->get();


        $datos['nombre_calle'] = $dir['nombre_calle'];
        $datos['numero_calle'] = $dir['numero_calle'];
        $datos['detalle'] = $dir['detalle'];
        $datos['localidad_id'] = $dir['localidad_id'];
        return $datos;
    }

    // ************* Tipo de Documento ************* //


    function cargar_tipo_de_documento($id)
    {
        $this->dep('tipo_de_documento')->cargar($id);
        $tipo_de_documento = $this->dep('tipo_de_documento')->get();
        $this->s__id_documento_tipo = $tipo_de_documento['id_tipo_documento'];
    }

    function resetear_tipo_de_documento()
    {
        $this->dep('tipo_de_documento')->resetear();
    }

    /*
		inserta una nueva tipo_de_documento en el datos_tabla tipo_de_documento
	*/
    function agregar_tipo_de_documento($registro)
    {
        $id = $this->dep('tipo_de_documento')->nueva_fila($registro);
        $this->dep('tipo_de_documento')->set_cursor($id);
        $this->dep('tipo_de_documento')->sincronizar();
        $this->dep('tipo_de_documento')->resetear();
    }


    /*
		modifica una tipo_de_documento del datos_tabla tipo_de_documento
	*/
    function modificar_tipo_de_documento($registro)
    {
        $this->dep('tipo_de_documento')->set($registro);
        $this->dep('tipo_de_documento')->sincronizar();
        $this->dep('tipo_de_documento')->resetear();
    }

    /*
		elimina una tipo_de_documento del datos_tabla tipo_de_documento
	*/
    function eliminar_tipo_de_documento()
    {
        $this->dep('tipo_de_documento')->eliminar();
        $this->dep('tipo_de_documento')->sincronizar();
        $this->dep('tipo_de_documento')->resetear();
    }


    /*
		Obtiene el listado de tipos_de_documento
	*/
    function get_tipos_de_documento($filtro = null)
    {
        if ($this->dep('tipo_de_documento')->esta_cargada()) {
            return $this->dep('tipo_de_documento')->get_filas($filtro, false);
        }
    }
    /*
		setea el cursor a una fila del datos_tabla tipo_de_documento
		Obtiene la tipo_de_documento seteada
	*/
    function get_tipo_de_documento($id_registro)
    {
        if ($this->dep('tipo_de_documento')->esta_cargada()) {
            return $this->dep('tipo_de_documento')->get();
        }
    }

    function get_id_tipo_de_documento()
    {
        return $this->s__id_tipo_de_documento;
    }

    function agregar_asistencia($registro)
    {
        $asistencia = array();
        $d = $registro['fecha_hora'][0] . " " . $registro['fecha_hora'][1] . ":00";
        $formato = 'Y-m-d H:i:s';
        $datetime = DateTime::createFromFormat($formato, $d);

        $asistencia['persona_id'] = $registro['persona_id'];
        $asistencia['fecha_hora'] = $datetime->format('Y-m-d H:i:s');
        $asistencia['evento'] = $registro['evento'];
        $asistencia['terminal_nombre'] = $registro['terminal_nombre'];
        $asistencia['en_reloj'] = false;

        $id = $this->dep('asistencia')->nueva_fila($asistencia);
        $this->dep('asistencia')->set_cursor($id);
        $this->dep('asistencia')->sincronizar();
        $id_asistencia = $this->dep('asistencia')->get();
        $this->dep('asistencia')->resetear();
        return $id_asistencia;
    }
}
