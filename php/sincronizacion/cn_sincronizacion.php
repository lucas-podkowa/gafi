<?php
include_once 'direccion/dao_direccion.php';
include_once 'empleados/dao_empleado.php';
include_once 'persona/dao_persona.php';
class cn_sincronizacion extends gafi_cn
{

    // ###################################################################################
    //----  X M L  -----------------------------------------------------------------------
    // ###################################################################################

    public function copiar_archivo_al_servidor($datos = array())
    {
        if (isset($datos['xml_personas'])) {
            if (is_array($datos['xml_personas'])) {
                setlocale(LC_TIME, 'es_AR.UTF-8');
                $temp_nombre = "personas_" . date("dmY_Hms") . ".xml";
                $temp_archivo = toba::proyecto()->get_www_temp();
                $ruta_temp = $temp_archivo['path']; //ruta del archivo que estoy creando
                $ruta_absoluta = str_replace("www/temp", "www/" . strftime('archivos_mapuche/%Y/%B'), $ruta_temp);

                if (!file_exists($ruta_absoluta)) {
                    mkdir($ruta_absoluta, 0766, true);
                }
                move_uploaded_file($datos['xml_personas']['tmp_name'], $ruta_absoluta . '/' . $temp_nombre);
                return $ruta_absoluta . '/' . $temp_nombre;
            }
        } else if (isset($datos['xml_cargos'])) {

            if (is_array($datos['xml_cargos'])) {
                setlocale(LC_TIME, 'es_AR.UTF-8');
                $temp_nombre = "cargos_" . date("dmY_Hms") . ".xml";
                $temp_archivo = toba::proyecto()->get_www_temp();
                $ruta_temp = $temp_archivo['path']; //ruta del archivo que estoy creando
                $ruta_absoluta = str_replace("www/temp", "www/" . strftime('archivos_mapuche/%Y/%B'), $ruta_temp);
                if (!file_exists($ruta_absoluta)) {
                    mkdir($ruta_absoluta, 0766, true);
                }
                move_uploaded_file($datos['xml_cargos']['tmp_name'], $ruta_absoluta . '/' . $temp_nombre);
                return $ruta_absoluta . '/' . $temp_nombre;
            }
        } else if (isset($datos['xml'])) {

            //ese se utiliza exclusivamente para analizar algo respecto a los porcentajes de didicacion docente
            if (is_array($datos['xml'])) {
                setlocale(LC_TIME, 'es_AR.UTF-8');
                $temp_nombre = "cargos_docentes" . date("dmY_Hms") . ".xml";
                $temp_archivo = toba::proyecto()->get_www_temp();
                $ruta_temp = $temp_archivo['path']; //ruta del archivo que estoy creando
                $ruta_absoluta = str_replace("www/temp", "www/" . strftime('archivos_mapuche/%Y/%B'), $ruta_temp);
                if (!file_exists($ruta_absoluta)) {
                    mkdir($ruta_absoluta, 0766, true);
                }
                move_uploaded_file($datos['xml']['tmp_name'], $ruta_absoluta . '/' . $temp_nombre);
                return $ruta_absoluta . '/' . $temp_nombre;
            }
        }
    }

    // ###################################################################################
    //----  P E R S O N A S   -   D I R E C C I O N E S   Y   L E G A J O S -------------
    // ###################################################################################

    public function analizar_xml_direcciones_y_personas($archivo = null)
    {
        $exito = false;
        //$archivo = "/home/lucas/toba3/proyectos/gafi/www/archivos_mapuche/2023/febrero/personas_08022023_190255.xml";
        //$archivo = "/home/lucas/Documentos/aaa.xml";
        $xml = simplexml_load_file($archivo);
        $nuevas_personas = $nuevas_personas_sin_localidad = $sin_localidad = $actualizar = $filas = array();

        foreach ($xml->children() as $child) {

            foreach ($child->datos as $f) {
                if ($f->etiqueta == 'Dependencia' && $f->valor == '06 - FACULTAD DE INGENIERIA') {
                    $exito = true;
                }
            }

            if ($exito) {
                if (isset($child->agente)) {
                    foreach ($child->agente as $agente) {
                        foreach ($agente->domicilios as $d) {
                            if ($d->principal == 'Si') {
                                $objJsonDocument = json_encode($d);
                                $a = json_decode($objJsonDocument, true);
                                $localidad_id = self::optener_localidad($a);
                                $persona_id = dao_empleado::get_persona_legajo($a['nro_legaj'], $a['nro_docum']);
                                $fila = compact('localidad_id', 'persona_id');
                                $fila['nombre_calle'] = mb_convert_encoding($a['calle'], 'iso-8859-1', 'utf-8');
                                $fila['nombre'] = mb_convert_encoding(substr($a['desc_nombr'], 0, 99), 'iso-8859-1', 'utf-8');
                                $fila['apellido'] = mb_convert_encoding($a['desc_appat'], 'iso-8859-1', 'utf-8');
                                $fila['numero_documento'] = $a['nro_docum'];
                                $fila['fecha_nacimiento'] = str_ireplace('-', '/', $a['fec_nacim']);
                                $fila['numero_calle'] = (!is_array($a['numero'])) ? str_ireplace('/', '', $a['numero']) : null;
                                $fila['detalle'] = (!is_array($a['zona_paraje_barrio'])) ? mb_convert_encoding($a['zona_paraje_barrio'], 'iso-8859-1', 'utf-8') : null;
                                $fila['email'] = (!is_array($a['correo_electronico'])) ? substr($a['correo_electronico'], 0, 49) : null;
                                $fila['tipo_documento_id'] = ($a['tipo_docum'] == 'DNI') ? 1 : 2;
                                $fila['nacionalidad'] = 1;
                                $fila['dependencia_id'] = 1;
                                $fila['legajo'] = $a['nro_legaj'];

                                if (is_null($fila['localidad_id'])) {
                                    if (is_null($fila['persona_id'])) {
                                        $fila['mensaje'] = mb_convert_encoding('Personas que aún no eisten en la base de datos GAFI pero no pueden ser agregadas por no poder identificar la Localidad', 'iso-8859-1', 'utf-8');
                                        $fila['accion'] = 3;
                                        array_push($filas, $fila);
                                    } else {
                                        $fila['mensaje'] = mb_convert_encoding('Personas cuya direccion no podrá ser actualizada por no poder identificar su Localidad', 'iso-8859-1', 'utf-8');
                                        $fila['accion'] = 2;
                                        array_push($filas, $fila);
                                    }
                                } else {
                                    if (is_null($fila['persona_id'])) {
                                        $fila['mensaje'] = mb_convert_encoding('Personas que aún no existen en la base de datos GAFI y serán dadas de alta', 'iso-8859-1', 'utf-8');
                                        $fila['estado_civil_id'] = 1; //se da de alta como soltero por defecto y luego se acualiza con legajos
                                        $fila['accion'] = 1;
                                        array_push($filas, $fila);
                                    } else {
                                        $fila['direccion_id'] = dao_direccion::get_direccion_id($fila['persona_id']);
                                        $fila['mensaje'] = mb_convert_encoding('Revisar si existen modificaciones', 'iso-8859-1', 'utf-8');
                                        $fila['accion'] = 0;
                                        array_push($filas, $fila);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $filas;
    }

    public function optener_localidad($registro = array())
    {
        $localidad_id = dao_direccion::get_id_localidad($registro['localidad'], $registro['codigo_postal']);
        if (is_null($localidad_id)) {
            $provincia_id = dao_direccion::get_codigo_provincia($registro['provincia']);
            if (!is_null($provincia_id)) {
                $nueva_localidad = array('nombre' => $registro['localidad'], 'codigo_postal' => $registro['codigo_postal'], 'provincia_id' => $provincia_id);
                $id = $this->dep('localidad')->nueva_fila($nueva_localidad);
                $this->dep('localidad')->set_cursor($id);
                $this->dep('localidad')->sincronizar();
                $localidad_id = $this->dep('localidad')->get()['localidad_id'];
            }
        }
        return $localidad_id;
    }

    public function procesar_direcciones_y_personas($filas = array())
    {
        foreach ($filas as $f) {
            switch ($f['accion']) {
                case 0: //actualizar direccion persona y luego datos persona

                    $actualizar = false;
                    $dir = dao_direccion::get_info_direccion($f['direccion_id']);
                    if ($dir['localidad_id'] != $f['localidad_id']) {
                        $dir['localidad_id'] = $f['localidad_id'];
                        $actualizar = true;
                    }

                    if ($dir['nombre_calle'] != $f['nombre_calle']) {
                        $dir['nombre_calle'] = $f['nombre_calle'];
                        $actualizar = true;
                    }
                    if ($dir['numero_calle'] != $f['numero_calle']) {
                        $dir['numero_calle'] = $f['numero_calle'];
                        $actualizar = true;
                    }
                    if ($dir['detalle'] != $f['detalle']) {
                        $dir['detalle'] = $f['detalle'];
                        $actualizar = true;
                    }

                    if ($actualizar) {
                        $this->dep('direccion')->set($dir);
                        $this->dep('direccion')->sincronizar();
                        $this->dep('direccion')->resetear();
                    }

                    break;
                case 1: //'Personas que aún no existen en la base de datos GAFI y serán dadas de alta'
                    $persona = self::agregar_nueva_persona($f);

                    if (isset($persona['persona_id'])) {
                        $f['persona_id'] = $persona['persona_id'];
                        self::agregar_legajo_de_nueva_persona($f);
                    } else {
                        throw new Exception('No se pudo dar de alta a la persona ' . $f['apellido'] . ', ' . $f['nombre']);
                    }
                    break;
                default:
                    break;
            }
        }
    }

    // ###################################################################################
    //----  C A R G O S  -----------------------------------------------------------------
    // ###################################################################################

    public function analizar_xml_cargos($archivo = null)
    {
        //$archivo = "/home/lucas/toba3/proyectos/gafi/www/archivos_mapuche/2022/noviembre/siu_cargos_x_legajo.xml";
        $xml = simplexml_load_file($archivo);
        $fila = $filas = array();

        foreach ($xml->children() as $child) {
            if (isset($child->agente)) {
                foreach ($child->agente as $agente) { //recorre cada etiqueta <agente> del XML
                    $objJsonDocument = json_encode($agente);
                    $a = json_decode($objJsonDocument, true); //convierte un obketo JSON a una matriz php

                    if (isset($a['cargos_agente'][0])) {
                        //revisa si existe la corresponeiente <cargos_agente>, si tiene es xq posee varios cargos
                        //entonces pasa a recorrer cada uno de los cargos almacenandolo en la variable $c
                        foreach ($a['cargos_agente'] as $c) {
                            $fila['desc_cargo'] = $c['desc_categoria'] . ' ' . $c['desc_dedic'];
                            $fila['agente'] = $c['agente_corte'];
                            $fila['legajo'] = $c['nro_legaj'];
                            $fila['numero_cargo'] = $c['nro_cargo'];
                            $fila['claustro_id'] = dao_empleado::get_valor_preferencia_tipo_escalafon($c['tipo_escalafon']);
                            $fila['legajo_id'] = dao_empleado::get_legajo_id($c['nro_legaj']);

                            $alta = $c['fecha_alta'];
                            $myDateTime = DateTime::createFromFormat('d/m/Y', $alta);
                            $fila['fecha_alta'] = $myDateTime->format('Y-m-d');

                            if (!is_array($c['fecha_baja'])) {
                                $baja = $c['fecha_baja'];
                                $myDateTime = DateTime::createFromFormat('d/m/Y', $baja);
                                $fila['fecha_baja'] = $myDateTime->format('Y-m-d');
                            } else {
                                $fila['fecha_baja'] = null;
                            }

                            if ((intval($c['porc_aplic']) < 100)) {
                                $fila['reduccion_horaria'] = intval($c['hs_dedic']) - intval(round(floatval($c['hs_dedic']) * (floatval($c['porc_aplic']) / 100)));
                            } else {
                                $fila['reduccion_horaria'] = 0;
                            }
                            $fila['estado'] = true;
                            $fila['categoria_id'] = dao_empleado::get_categoria_id($c['categoria']);
                            $fila['dedicacion_id'] = dao_empleado::get_dedicacion_id($c['porc_aplic'], $c['categoria']);

                            $fila['legajo_id'] = dao_empleado::get_legajo_id($c['nro_legaj']);
                            $cargo_id = null;

                            if (!is_null($fila['legajo_id'])) {
                                $cargo_id = dao_empleado::get_cargo_id($fila['legajo_id'], $fila['numero_cargo']);
                                $fila['cargo_id'] = $cargo_id;
                                if ((is_null($cargo_id)) && (!is_null($fila['legajo_id']))) {
                                    if (is_null($fila['categoria_id']) or is_null($fila['dedicacion_id'])) {
                                        $fila['accion'] = 2;
                                        $fila['mensaje'] = mb_convert_encoding('Cargos con Categoría o Dedicación Horaria incompatibles con la base de datos', 'iso-8859-1', 'utf-8');
                                    } else {
                                        $fila['accion'] = 1;
                                        $fila['mensaje'] = mb_convert_encoding('Cargos nuevos que serán dados de alta en la base de datos GAFI', 'iso-8859-1', 'utf-8');
                                    }
                                } else {
                                    $fila['accion'] = 0;
                                    $fila['mensaje'] = mb_convert_encoding('Cargos coincidentes que serán revisados', 'iso-8859-1', 'utf-8');
                                }
                            } else {
                                $fila['cargo_id'] = null;
                                $fila['accion'] = 3;
                                $fila['mensaje'] = mb_convert_encoding('Cargos cuyo Legajo no se halla en la Basde de Datos', 'iso-8859-1', 'utf-8');
                            }
                            array_push($filas, $fila);
                        }
                    } else { //significa que posee un solo cargo por lo que el recorrido ya no es una matriz sino un array normal
                        $c = $a['cargos_agente'];
                        $fila['desc_cargo'] = $c['desc_categoria'] . ' ' . $c['desc_dedic'];
                        $fila['agente'] = $c['agente_corte'];
                        $fila['legajo'] = $c['nro_legaj'];
                        $fila['numero_cargo'] = $c['nro_cargo'];
                        $fila['claustro_id'] = dao_empleado::get_valor_preferencia_tipo_escalafon($c['tipo_escalafon']);

                        $alta = $c['fecha_alta'];
                        $myDateTime = DateTime::createFromFormat('d/m/Y', $alta);
                        $fila['fecha_alta'] = $myDateTime->format('Y-m-d');

                        if (!is_array($c['fecha_baja'])) {
                            $baja = $c['fecha_baja'];
                            $myDateTime = DateTime::createFromFormat('d/m/Y', $baja);
                            $fila['fecha_baja'] = $myDateTime->format('Y-m-d');
                        } else {
                            $fila['fecha_baja'] = null;
                        }

                        if ((intval($c['porc_aplic']) < 100)) {
                            $fila['reduccion_horaria'] = intval($c['hs_dedic']) - intval(round(floatval($c['hs_dedic']) * (floatval($c['porc_aplic']) / 100)));
                        } else {
                            $fila['reduccion_horaria'] = 0;
                        }
                        $fila['estado'] = true;
                        $fila['categoria_id'] = dao_empleado::get_categoria_id($c['categoria']);
                        $fila['dedicacion_id'] = dao_empleado::get_dedicacion_id($c['porc_aplic'], $c['categoria']);

                        $fila['legajo_id'] = dao_empleado::get_legajo_id($c['nro_legaj']);
                        $cargo_id = null;

                        if (!is_null($fila['legajo_id'])) {

                            $cargo_id = dao_empleado::get_cargo_id($fila['legajo_id'], $fila['numero_cargo']);
                            $fila['cargo_id'] = $cargo_id;
                            if ((is_null($cargo_id)) && (!is_null($fila['legajo_id']))) {
                                if (is_null($fila['categoria_id']) or is_null($fila['dedicacion_id'])) {
                                    $fila['accion'] = 2;
                                    $fila['mensaje'] = mb_convert_encoding('Cargos con Categoría o Dedicación Horaria incompatibles con la base de datos', 'iso-8859-1', 'utf-8');
                                } else {
                                    $fila['accion'] = 1;
                                    $fila['mensaje'] = mb_convert_encoding('Cargos nuevos que serán dados de alta en la base de datos GAFI', 'iso-8859-1', 'utf-8');
                                }
                            } else {
                                $fila['accion'] = 0;
                                $fila['mensaje'] = mb_convert_encoding('Cargos coincidentes que serán revisados', 'iso-8859-1', 'utf-8');
                            }
                        } else {
                            $fila['cargo_id'] = null;
                            $fila['accion'] = 3;
                            $fila['mensaje'] = mb_convert_encoding('Cargos cuyo Legajo no se halla en la Basde de Datos', 'iso-8859-1', 'utf-8');
                        }
                        array_push($filas, $fila);
                    }
                }
            }
        }
        return $filas;
    }

    public function procesar_cargos($filas = array())
    {
        $cargos_mapuche = array();
        $cargos_bd = dao_empleado::get_numeros_de_cargos();

        foreach ($filas as $f) {
            array_push($cargos_mapuche, $f['numero_cargo']);
            switch ($f['accion']) {
                case 0: //Cargos coincidentes que serán revisados. Actualizar direccion persona y luego datos persona
                    $cargo = dao_empleado::get_cargo($f['legajo_id'], $f['numero_cargo']);
                    $actualizar = false;
                    if ($cargo['dedicacion_id'] != $f['dedicacion_id']) {
                        $cargo['dedicacion_id'] = $f['dedicacion_id'];
                    }

                    if ($cargo['categoria_id'] != $f['categoria_id']) {
                        $cargo['categoria_id'] = $f['categoria_id'];
                        $actualizar = true;
                    }

                    if ($cargo['reduccion_horaria'] != $f['reduccion_horaria']) {
                        $cargo['reduccion_horaria'] = $f['reduccion_horaria'];
                        $actualizar = true;
                    }

                    if ($cargo['fecha_alta'] != $f['fecha_alta']) {
                        $cargo['fecha_alta'] = $f['fecha_alta'];
                        $actualizar = true;
                    }

                    if ($actualizar) {

                        $filtro = array('cargo_id' => $cargo['cargo_id']);
                        $this->dep('cargo')->cargar($filtro);
                        $this->dep('cargo')->set($cargo);
                        $this->dep('cargo')->sincronizar();
                        $this->dep('cargo')->resetear();
                    }
                    break;
                case 1: //'Personas que aún no existen en la base de datos GAFI y serán dadas de alta'
                    $this->dep('cargo')->nueva_fila($f);
                    $this->dep('cargo')->sincronizar();
                    $this->dep('cargo')->resetear();
                    break;
                default:
                    break;
            }
        }
        $inactivos = array_diff($cargos_bd, $cargos_mapuche);
        // inactivos contiene los elementos que estan en nuestra DB pero no estan en mapuche
        dao_empleado::desactivar_cargos_actualizados($inactivos);
    }

    // ###################################################################################
    //----  E X T R A S  -----------------------------------------------------------------
    // ###################################################################################

    public function agregar_nueva_persona($registro)
    {
        $permitido = dao_persona::verificar_dni_duplicado($registro);
        if ($permitido) {
            $direccion = self::agregar_direccion_para_nueva_persona($registro);
            $registro['direccion_id'] = $direccion['direccion_id'];
            $id = $this->dep('persona')->nueva_fila($registro);
            $this->dep('persona')->set_cursor($id);
            $this->dep('persona')->sincronizar();
            $persona = $this->dep('persona')->get();
            $this->dep('persona')->resetear();
            return $persona;
        } else {
            throw new Exception('El DNI ' . $registro['numero_documento'] . ' ya existe en la base de datos');
        }
    }

    public function agregar_direccion_para_nueva_persona($registro)
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

    public function agregar_legajo_de_nueva_persona($registro)
    {
        $existe = dao_empleado::existe_legajo($registro['legajo'], null);
        if (!$existe) {
            $leg['legajo'] = $registro['legajo'];
            $leg['persona_id'] = $registro['persona_id'];
            $leg['estado'] = true;
            $leg['dependencia_id'] = $registro['dependencia_id'];

            $this->dep('legajo')->nueva_fila($leg);
            $this->dep('legajo')->sincronizar();
            $this->dep('legajo')->resetear();
        } else {
            throw new Exception('El legajo ' . $registro['legajo'] . ' ya existe en la base de datos');
        }
    }


    // ###################################################################################
    //----  ANALISIS DEDICACION  ---------------------------------------------------------
    // ###################################################################################

    public function analizar_porcentaje_dedicacion($archivo = null)
    {
        $archivo = "/home/lucas/Descargas/cargos_docentes_fio_21-05-2024.xml";

        $xmlContent = file_get_contents($archivo);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

        // $xml = simplexml_load_file($archivo);
        // //$xmlContent = file_get_contents($archivo);
        // //$xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        // $objJsonDocument = json_encode($xml, JSON_UNESCAPED_UNICODE);
        // ei_arbol(json_decode($objJsonDocument, true));


        $fila = $filas = array();

        foreach ($xml->children() as $child) {

            if (isset($child->agente)) {
                foreach ($child->agente as $agente) { //recorre cada etiqueta <agente> del XML

                    $objJsonDocument = json_encode($agente, JSON_UNESCAPED_UNICODE);
                    // $objJsonDocument = json_encode($agente);
                    $a = json_decode($objJsonDocument, true); //convierte un obketo JSON a una matriz php

                    if (isset($a['cargos_agente'][0])) {
                        //revisa si existe la corresponeiente <cargos_agente>, si tiene es xq posee varios cargos
                        //entonces pasa a recorrer cada uno de los cargos almacenandolo en la variable $c
                        foreach ($a['cargos_agente'] as $c) {
                            $fila['agente'] = $c['agente_corte'];
                            $fila['legajo'] = $c['nro_legaj'];
                            $fila['categoria'] = $c['desc_categoria'];
                            $fila['dedicacion'] = $c['desc_dedic'];
                            $fila['numero_cargo'] = $c['nro_cargo'];


                            if (isset($c['nro_exped']) && !is_array($c['nro_exped']) && !empty($c['nro_exped'])) {
                                $fila['nro_exped'] = $c['nro_exped'];
                            } else {
                                $fila['nro_exped'] = "Sin numero";
                            }

                            $fila['porcdedicdocente'] = $c['porcdedicdocente'];
                            $fila['porcdedicinvestig'] = $c['porcdedicinvestig'];
                            $fila['porcdedicaextens'] = $c['porcdedicaextens'];
                            $fila['porcdedicagestion'] = $c['porcdedicagestion'];

                            $carreras_cargo = array();
                            if (isset($c['carreras_cargo']['carreras'][0])) {
                                foreach ($c['carreras_cargo']['carreras'] as $carrera) {
                                    array_push($carreras_cargo, $carrera['desc_carrera']);
                                    if (empty($fila['carreras_cargo'])) {
                                        $fila['carreras_cargo'] = $carrera['desc_carrera'];
                                    } else {
                                        $fila['carreras_cargo'] .= ', ' . $carrera['desc_carrera'];
                                    }
                                }
                            } else {
                                $fila['carreras_cargo'] = $c['carreras_cargo']['carreras']['desc_carrera'];
                            }


                            array_push($filas, $fila);
                        }
                    } else { //significa que posee un solo cargo por lo que el recorrido ya no es una matriz sino un array normal
                        $c = $a['cargos_agente'];

                        $fila['agente'] = $c['agente_corte'];
                        $fila['legajo'] = $c['nro_legaj'];
                        $fila['categoria'] = $c['desc_categoria'];
                        $fila['dedicacion'] = $c['desc_dedic'];
                        $fila['numero_cargo'] = $c['nro_cargo'];


                        if (isset($c['nro_exped']) && !is_array($c['nro_exped']) && !empty($c['nro_exped'])) {
                            $fila['nro_exped'] = $c['nro_exped'];
                        } else {
                            $fila['nro_exped'] = "Sin numero";
                        }
                        $fila['porcdedicdocente'] = $c['porcdedicdocente'];
                        $fila['porcdedicinvestig'] = $c['porcdedicinvestig'];
                        $fila['porcdedicaextens'] = $c['porcdedicaextens'];
                        $fila['porcdedicagestion'] = $c['porcdedicagestion'];

                        $carreras_cargo = array();
                        if (isset($c['carreras_cargo']['carreras'][0])) {
                            foreach ($c['carreras_cargo']['carreras'] as $carrera) {
                                array_push($carreras_cargo, $carrera['desc_carrera']);
                                if (empty($fila['carreras_cargo'])) {
                                    $fila['carreras_cargo'] = $carrera['desc_carrera'];
                                } else {
                                    $fila['carreras_cargo'] .= ', ' . $carrera['desc_carrera'];
                                }
                            }
                        } else {
                            $fila['carreras_cargo'] = $c['carreras_cargo']['carreras']['desc_carrera'];
                        }

                        array_push($filas, $fila);
                    }
                }
            }
        }
        return $filas;
    }

    public function analizar_porcentaje_dedicacion_con_repeticion($archivo = null)
    {
        $archivo = "/home/lucas/Descargas/cargos_docentes_fio_21-05-2024.xml";

        $xmlContent = file_get_contents($archivo);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

        // $xml = simplexml_load_file($archivo);
        // //$xmlContent = file_get_contents($archivo);
        // //$xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        // $objJsonDocument = json_encode($xml, JSON_UNESCAPED_UNICODE);
        // ei_arbol(json_decode($objJsonDocument, true));


        $fila = $filas = array();

        foreach ($xml->children() as $child) {

            if (isset($child->agente)) {
                foreach ($child->agente as $agente) { //recorre cada etiqueta <agente> del XML

                    $objJsonDocument = json_encode($agente, JSON_UNESCAPED_UNICODE);
                    // $objJsonDocument = json_encode($agente);
                    $a = json_decode($objJsonDocument, true); //convierte un obketo JSON a una matriz php

                    if (isset($a['cargos_agente'][0])) {
                        //revisa si existe la corresponeiente <cargos_agente>, si tiene es xq posee varios cargos
                        //entonces pasa a recorrer cada uno de los cargos almacenandolo en la variable $c
                        foreach ($a['cargos_agente'] as $c) {

                            $fila['agente'] = $c['agente_corte'];
                            $fila['legajo'] = $c['nro_legaj'];
                            $fila['categoria'] = $c['desc_categoria'];
                            $fila['dedicacion'] = $c['desc_dedic'];
                            $fila['numero_cargo'] = $c['nro_cargo'];


                            if (isset($c['nro_exped']) && !is_array($c['nro_exped']) && !empty($c['nro_exped'])) {
                                $fila['nro_exped'] = $c['nro_exped'];
                            } else {
                                $fila['nro_exped'] = "Sin numero";
                            }

                            $fila['porcdedicdocente'] = $c['porcdedicdocente'];
                            $fila['porcdedicinvestig'] = $c['porcdedicinvestig'];
                            $fila['porcdedicaextens'] = $c['porcdedicaextens'];
                            $fila['porcdedicagestion'] = $c['porcdedicagestion'];

                            if (isset($c['carreras_cargo']['carreras'][0])) {
                                foreach ($c['carreras_cargo']['carreras'] as $carrera) {
                                    if (isset($carrera['desc_carrera']) && !is_array($carrera['desc_carrera']) && !empty($carrera['desc_carrera'])) {
                                        $fila['carreras_cargo'] = $carrera['desc_carrera'];
                                    } else {
                                        $fila['carreras_cargo'] = "Sin Asignacion";
                                    }
                                    array_push($filas, $fila);
                                }
                            } else {
                                $fila['carreras_cargo'] = $c['carreras_cargo']['carreras']['desc_carrera'];
                                array_push($filas, $fila);
                            }
                        }
                    } else { //significa que posee un solo cargo por lo que el recorrido ya no es una matriz sino un array normal
                        $c = $a['cargos_agente'];

                        $fila['agente'] = $c['agente_corte'];
                        $fila['legajo'] = $c['nro_legaj'];
                        $fila['categoria'] = $c['desc_categoria'];
                        $fila['dedicacion'] = $c['desc_dedic'];
                        $fila['numero_cargo'] = $c['nro_cargo'];


                        if (isset($c['nro_exped']) && !is_array($c['nro_exped']) && !empty($c['nro_exped'])) {
                            $fila['nro_exped'] = $c['nro_exped'];
                        } else {
                            $fila['nro_exped'] = "Sin numero";
                        }
                        $fila['porcdedicdocente'] = $c['porcdedicdocente'];
                        $fila['porcdedicinvestig'] = $c['porcdedicinvestig'];
                        $fila['porcdedicaextens'] = $c['porcdedicaextens'];
                        $fila['porcdedicagestion'] = $c['porcdedicagestion'];

                        if (isset($c['carreras_cargo']['carreras'][0])) {
                            foreach ($c['carreras_cargo']['carreras'] as $carrera) {
                                if (isset($carrera['desc_carrera']) && !is_array($carrera['desc_carrera']) && !empty($carrera['desc_carrera'])) {
                                    $fila['carreras_cargo'] = $carrera['desc_carrera'];
                                } else {
                                    $fila['carreras_cargo'] = "Sin Asignacion";
                                }

                                array_push($filas, $fila);
                            }
                        } else {
                            $fila['carreras_cargo'] = $c['carreras_cargo']['carreras']['desc_carrera'];
                            array_push($filas, $fila);
                        }
                    }
                }
            }
        }
        return $filas;
    }
}
//categoria = (100M, 110M, 90S)

/*
Código    Descripción                Descripción                Horas
31E     Secretario Facultad     Exclusiva               40,00
41E     Vice Decano Facultad    Exclusiva               40,00
40E     Decano de Facultad      Exclusiva               40,00
70E     Profesor Titular        Exclusiva               40,00
80E     Profesor Asociado       Exclusiva               40,00
90E     Profesor Adjunto        Exclusiva               40,00
100E    Jefe Trab. Prácticos    Exclusiva               40,00
70S     Profesor Titular        Semi-Exclusiva          20,00
80S     Profesor Asociado       Semi-Exclusiva          20,00
90S     Profesor Adjunto        Semi-Exclusiva          20,00
100S    Jefe Trab. Prácticos    Semi-Exclusiva          20,00
110S    Ayudante de 1ra.        Semi-Exclusiva          20,00
70M     Profesor Titular        Simple                  10,00
80M     Profesor Asociado       Simple                  10,00
90M     Profesor Adjunto        Simple                  10,00
100M    Jefe Trab. Prácticos    Simple                  10,00
110M    Ayudante de 1ra.        Simple                  10,00
120M    Ayudante de 2da.        10 Horas Semanales      10,00
 */
