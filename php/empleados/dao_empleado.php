<?php
class dao_empleado
{

    // ###################################################################################
    //----  L E G A J O  -----------------------------------------------------------------
    // ###################################################################################

    public static function get_legajos($filtro = array())
    {
        $where = '(1=1)';

        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
				l.legajo_id,
				l.legajo,
                l.persona_id,
                l.estado,
                l.dependencia_id,
				concat(d.nombre || ' (' || d.siglas || ')' ) AS dependencia_desc,
				concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')' ) AS persona_desc,
				concat(l.legajo || ' - (' || p.apellido || ' ' || p.nombre || ')'   ) AS legajo_desc
			FROM
				legajo l
				INNER JOIN persona p on l.persona_id = p.persona_id
				INNER JOIN dependencia d on l.dependencia_id = d.dependencia_id
			WHERE
				$where
			ORDER BY l.legajo
		";
        return consultar_fuente($sql);
    }

    public static function get_legajos_ce($filtro = null)
    {
        $where = '(1=1)';

        if (!is_array($filtro) and $filtro != '') {
            $where .= "AND (l.legajo ILIKE '%{$filtro}%') AND length('{$filtro}') > 1 ";
        }

        $sql = "SELECT
				l.legajo_id,
				concat(l.legajo || ' - (' || p.apellido || ' ' || p.nombre || ')'   ) AS legajo_desc
			FROM
				legajo l
				INNER JOIN persona p on l.persona_id = p.persona_id
			WHERE
				$where
		";
        return consultar_fuente($sql);
    }

    public static function get_legajo_id($legajo = null)
    {

        $sql = "SELECT 	l.legajo_id
				FROM  legajo l
				WHERE l.legajo ILIKE '{$legajo}'
		";
        $result = consultar_fuente($sql);

        if (isset($result[0]['legajo_id'])) {
            return $result[0]['legajo_id'];
        } else {
            return null;
        }
    }

    public static function get_legajo_desc($id = null)
    {
        $sql = "SELECT
					concat(l.legajo || ' - (' || p.apellido || ' ' || p.nombre || ')'   ) AS legajo_desc
				FROM
					legajo l
					INNER JOIN persona p on l.persona_id = p.persona_id
				WHERE
					l.legajo_id = $id
			ORDER BY legajo_desc
		";

        $result = consultar_fuente($sql);
        if (!empty($result)) {
            return $result[0]['legajo_desc'];
        }
    }

    public static function existe_legajo($nuevo = null, $actual = null)
    {
        $where = '(1=1)';

        if (!is_null($actual)) {
            $where .= "AND l.legajo <> '{$actual}'";
        }

        $sql = "SELECT l.legajo FROM legajo l WHERE l.legajo ='{$nuevo}' AND $where";
        $result = consultar_fuente($sql);
        return !empty($result);
    }

    public static function persona_valida_para_legajo($persona_id = null, $legajo = null)
    {
        $sql = "SELECT l.legajo FROM legajo l WHERE l.persona_id ='{$persona_id}' AND l.legajo <> '{$legajo}'";
        $result = consultar_fuente($sql);
        return !empty($result);
    }

    public static function get_carga_horaria_legajo($filtro = array())
    {
        if (isset($filtro['legajo_id'])) {
            $sql = "SELECT 	sum(d.carga_horaria) as total,
							sum(c.reduccion_horaria) as reduccion
					FROM cargo c
					INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
					WHERE c.legajo_id = '{$filtro['legajo_id']}'
					AND c.fecha_baja is null";
            $datos = consultar_fuente($sql);
        }

        if (isset($datos[0]) && !is_null($datos[0]['total'])) {
            return $datos[0]['total'] - $datos[0]['reduccion'];
        } else {
            return 0;
        }
    }

    public static function get_afectaciones_legajo($filtro = array())
    {
        $where = '(1=1)';

        if (isset($filtro['legajo_id'])) {
            $where .= " AND al.legajo_id = '{$filtro['legajo_id']}' ";
        }

        if (isset($filtro['afectacion_legajo_id'])) {
            $where .= " AND al.afectacion_legajo_id = '{$filtro['afectacion_legajo_id']}' ";
        }

        $sql = "SELECT
				al.afectacion_legajo_id,
				al.legajo_id,
				al.afectacion_horaria_id,
				al.activa,
				al.fecha_inicio,
				a.funcion,
				a.horas_semanales,
                l.legajo
			FROM
				afectacion_legajo al
				INNER JOIN afectacion_horaria a ON al.afectacion_horaria_id = a.afectacion_horaria_id
                INNER JOIN legajo l ON l.legajo_id = al.legajo_id
			WHERE
				$where
		";
        return consultar_fuente($sql);

        // if (isset($datos[0])) {
        //     return $datos[0];
        // } else {
        //     return null;
        // }
    }

    // ###################################################################################
    //----  C A R G O  -------------------------------------------------------------------
    // ###################################################################################

    public static function get_cargos($filtro = array())
    {
        $where = 'c.estado = true';

        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
				c.cargo_id,
				c.dedicacion_id,
				c.categoria_id,
				c.legajo_id,
				c.fecha_alta,
				c.fecha_baja,
				c.claustro_id,
				c.observacion,
				c.reduccion_horaria,
				c.numero_cargo,
				d.nombre as dedicacion,
				cat.nombre as categoria,
				cl.nombre as claustro,
				l.legajo,
                concat(cat.nombre || ' ' || d.nombre || ' (' || cl.nombre ||')') AS cargo_desc
			FROM
				cargo c
				INNER JOIN legajo l ON l.legajo_id = c.legajo_id
				INNER JOIN claustro cl ON cl.claustro_id = c.claustro_id
				INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
				INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
			WHERE
				$where
			ORDER BY c.fecha_alta
		";
        return consultar_fuente($sql);
    }

    public static function verificar_cargo_duplicado($nuevo = array(), $old = array())
    {
        $sql = "SELECT
                c.dedicacion_id,
				c.categoria_id,
				c.legajo_id,
				c.claustro_id,
				c.fecha_alta,
				c.fecha_baja
			FROM
				cargo c
			WHERE 	c.numero_cargo = {$nuevo['numero_cargo']}
				AND	c.dedicacion_id = {$nuevo['dedicacion_id']}
				AND c.categoria_id = {$nuevo['categoria_id']}
				AND c.legajo_id = {$nuevo['legajo_id']}
				AND c.claustro_id = {$nuevo['claustro_id']}
				AND c.cargo_id != {$old['cargo_id']}
		";
        $result = consultar_fuente($sql);
        //si result esta vacio significa que no hubieron coincidencias y empty devuelve TRUE, por lo que negamos empty para que devuelva FALSE cuando esta vacio
        //entonces al preguntar, ¿existe un dubplicado?: devolvera un true ya que result no esa vacío
        return !empty($result);
    }

    public static function get_cargo_desc($cargo_id = array())
    {
        $sql = "SELECT
                concat(cat.nombre || ' ' || d.nombre || ' (' || cl.nombre ||')') AS cargo_desc
			FROM
				cargo c
				INNER JOIN legajo l ON l.legajo_id = c.legajo_id
				INNER JOIN claustro cl ON cl.claustro_id = c.claustro_id
				INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
				INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
			WHERE
				c.cargo_id= {$cargo_id['cargo_id']}
			ORDER BY c.fecha_alta
		";

        $result = consultar_fuente($sql);
        if (!empty($result)) {
            return $result[0];
        }
    }

    public static function get_cargos_legajo($filtro = array())
    {
        $where = 'c.estado = true';
        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        if (isset($filtro['legajo_id'])) {
            $where .= " AND c.legajo_id = '{$filtro['legajo_id']}' ";
        }

        $sql = "SELECT
				c.cargo_id,
				c.dedicacion_id,
				c.categoria_id,
				c.legajo_id,
				c.fecha_alta,
				c.fecha_baja,
				c.claustro_id,
				c.reduccion_horaria,
				c.observacion,
				c.numero_cargo,
				d.nombre as dedicacion,
				cat.nombre as categoria,
				cl.nombre as claustro,
				l.legajo,
                concat(cat.nombre || ' ' || d.nombre || ' (' || cl.nombre ||')') AS cargo_desc
			FROM
				cargo c
				INNER JOIN legajo l ON l.legajo_id = c.legajo_id
				INNER JOIN claustro cl ON cl.claustro_id = c.claustro_id
				INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
				INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
			WHERE
				$where
			ORDER BY c.fecha_alta
		";
        return consultar_fuente($sql);
    }

    public static function get_cargo_id($legajo_id, $numero_cargo)
    {
        $sql = "SELECT 	c.cargo_id
				FROM 	cargo c
				WHERE	c.legajo_id = '{$legajo_id}' AND c.numero_cargo = '{$numero_cargo}'	";

        $datos = consultar_fuente($sql);

        if (isset($datos[0]['cargo_id'])) {
            return $datos[0]['cargo_id'];
        } else {
            return null;
        }
    }

    public static function get_numeros_de_cargos()
    {
        $sql = "SELECT c.numero_cargo FROM cargo c where c.estado = true";
        $datos = consultar_fuente($sql);

        $numeros = array();
        for ($i = 0; $i < sizeof($datos); $i++) {
            array_push($numeros, $datos[$i]['numero_cargo']);
        }
        return $numeros;
    }

    public static function desactivar_cargos_actualizados($inactivos)
    {
        foreach ($inactivos as $nro_cargo) {
            $sql = "UPDATE cargo SET estado = false where numero_cargo = '{$nro_cargo}'";
            consultar_fuente($sql);
        }
    }


    public static function get_cargo($legajo_id, $numero_cargo)
    {
        $sql = "SELECT * FROM cargo c
				WHERE c.legajo_id = '{$legajo_id}'
				AND c.numero_cargo = '{$numero_cargo}'	";

        $datos = consultar_fuente($sql);
        if (isset($datos[0])) {
            return $datos[0];
        } else {
            return null;
        }
    }

    public static function cantidad_cargos_activos($legajo_id)
    {
        $sql = "SELECT 	count(cargo_id) as activos
				FROM 	cargo c
				WHERE	c.legajo_id = '{$legajo_id['legajo_id']}' 
                    AND c.estado = true
                    AND	c.fecha_alta < now() 
                    AND (c.fecha_baja is null OR c.fecha_baja > now())
			";

        $datos = consultar_fuente($sql);

        if (isset($datos[0]['activos'])) {
            return (int) $datos[0]['activos'];
        } else {
            return 0;
        }
    }

    public static function get_cargos_cascade_claustro_persona($persona_id, $claustro_id = null)
    {
        $sql = "SELECT
				c.cargo_id,
				concat(cat.nombre || ' (' || d.nombre ||')') AS cargo_desc
			FROM
				cargo c
				INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
				INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
				INNER JOIN legajo l ON l.legajo_id = c.legajo_id
				AND l.persona_id = '{$persona_id}'
				AND c.claustro_id = '{$claustro_id}'
                AND c.estado = true
		";
        $result = consultar_fuente($sql);
        return $result;
    }

    public static function get_carga_horaria_cargo($filtro = array())
    {
        $sql = null;
        if (isset($filtro['dedicacion_id'])) {
            $sql = "SELECT 	d.carga_horaria
					FROM dedicacion d
					WHERE d.dedicacion_id = '{$filtro['dedicacion_id']}'";
        } else if (isset($filtro['cargo_id'])) {
            $sql = "SELECT 	d.carga_horaria
            FROM dedicacion d
            WHERE d.dedicacion_id = (SELECT dedicacion_id from cargo where cargo_id = '{$filtro['cargo_id']}')";
        }

        $datos = consultar_fuente($sql);

        if (isset($datos[0]) && !is_null($datos[0]['carga_horaria'])) {

            if (isset($filtro['reduccion_horaria'])) {
                $carga = $datos[0]['carga_horaria'] - $filtro['reduccion_horaria'];
            } else {
                $carga = $datos[0]['carga_horaria'];
            }
            return $carga;
        } else {
            return 0;
        }
    }

    public static function get_cargos_sin_declaracion_jurada($filtro = array())
    {
        $where = 'c.estado = true';
        $sector = '';
        // para el WHERE. AGREGO la tabla sector_x_persona
        if (isset($filtro['sector_id'])) {
            $sector = ' INNER JOIN persona_x_sector ps ON ps.persona_id = p.persona_id ';
        }
        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }
        $sql = "SELECT
				concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')' ) AS ape_nom_dni,
                concat(cat.nombre || ' (' || d.nombre ||')') AS cargo_desc,
                c.numero_cargo
			FROM
			persona p
                INNER JOIN legajo l ON p.persona_id = l.persona_id
                INNER JOIN cargo c ON l.legajo_id = c.legajo_id
				INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
				INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
                {$sector}
            WHERE
            $where
            AND c.cargo_id NOT IN (SELECT dj.cargo_id from declaracion_jurada dj)
            ORDER BY ape_nom_dni
            ";
        return consultar_fuente($sql);
    }

    public static function posee_licencias_relacionadas_al_cargo($cargo = array())
    {
        //esta funcion se utiliza en una validacion para eliminar cargo, la idea es que no tenga ninguna licencia activa al momento de su eliminacion

        $sql_persona = "SELECT l.persona_id from legajo l where l.legajo_id = {$cargo['legajo_id']}";
        $id_persona = consultar_fuente($sql_persona);

        //$result = consultar_fuente($sql);
        ////si result esta vacio significa que no hubieron coincidencias y empty devuelve TRUE, por lo que negamos empty para que devuelva FALSE cuando esta vacio
        ////entonces al preguntar, ¿existe un dubplicado?: devolvera un true ya que result no esa vacío
        //return !empty($result);
    }

    public static function get_cargos_y_horarios($filtro = array())
    {
        $where = '(1=1)';
        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        if (isset($filtro['legajo_id'])) {
            $where .= " AND c.legajo_id = '{$filtro['legajo_id']}' ";
        }

        $sql = "SELECT
				c.cargo_id,
				c.dedicacion_id,
				c.categoria_id,
				c.legajo_id,
				c.fecha_alta,
				c.fecha_baja,
				c.claustro_id,
				c.observacion,
				c.numero_cargo,
				d.nombre as dedicacion,
				cat.nombre as categoria,
				cl.nombre as claustro,
				l.legajo,
                concat(cat.nombre || ' ' || d.nombre || ' (' || cl.nombre ||')') AS cargo_desc,
				concat(cat.nombre || ' ' || d.nombre) AS cargo_simple
			FROM
				cargo c
				INNER JOIN legajo l ON l.legajo_id = c.legajo_id
				INNER JOIN claustro cl ON cl.claustro_id = c.claustro_id
				INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
				INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
			WHERE
				$where
				AND c.fecha_baja is null
			ORDER BY c.fecha_alta
		";

        $cargos = consultar_fuente($sql);
        $resp = array();

        foreach ($cargos as $cargo) {
            //$dj = self::get_ddjj_cargo($cargo['cargo_id']);
            $dj = consultar_fuente("SELECT dj.declaracion_jurada_id FROM declaracion_jurada dj WHERE dj.cargo_id = '{$cargo['cargo_id']}' AND dj.estado is true");
            if (!empty($dj)) {
                foreach ($dj as $d) {

                    $horarios = self::get_horarios_ddjj($d);
                    if (!empty($horarios)) {
                        foreach ($horarios as $h) {
                            $m = array_merge($h, $cargo);
                            array_push($resp, $m);
                        }
                    } else {
                        $k = [
                            'declaracion_jurada_id' => null,
                            'persona_id' => null,
                            'fecha' => null,
                            'dia_semana' => null,
                            'dia_id' => null,
                            'desde' => null,
                            'hasta' => null,
                            'turno_id' => null,
                            'turno' => null,
                        ];
                        $m = array_merge($k, $cargo);
                        array_push($resp, $m);
                    }
                }
            } else {
                $d = [
                    'declaracion_jurada_id' => null,
                    'persona_id' => null,
                    'fecha' => 'No Posee declaraciones activas',
                    'dia_semana' => null,
                    'dia_id' => null,
                    'desde' => null,
                    'hasta' => null,
                    'turno_id' => null,
                    'turno' => null,
                ];
                $m = array_merge($d, $cargo);
                array_push($resp, $m);
            }
        }
        return $resp;
    }

    public static function get_ddjj_cargo($filtro = array())
    {
        $where = '(1=1)';

        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }
        if (isset($filtro['cargo_id'])) {
            $where .= " AND dj.cargo_id = '{$filtro['cargo_id']}' ";
        }

        $sql = "SELECT
				dj.declaracion_jurada_id,
				dj.persona_id,
                dj.cargo_id,
                dj.fecha,
				dj.estado
			FROM
				declaracion_jurada dj
			WHERE
				$where
            ORDER BY dj.fecha desc
		";
        return consultar_fuente($sql);
    }

    public static function get_horarios_vigentes_del_cargo($cargo_id)
    {
        $dj = consultar_fuente("SELECT dj.declaracion_jurada_id FROM declaracion_jurada dj WHERE dj.cargo_id = '{$cargo_id['cargo_id']}' AND dj.estado is true");
        $horarios = array();
        if (isset($dj[0])) {
            $horarios = self::get_horarios_ddjj($dj[0]);
        }
        return $horarios;
    }
    public static function desactivar_declaraciones_vigentes($cargo_id)
    {
        consultar_fuente("UPDATE declaracion_jurada SET estado = false WHERE cargo_id = '{$cargo_id['cargo_id']}'");
    }
    public static function get_persona_cargo($cargo_id)
    {
        $sql = "SELECT
				l.persona_id
		FROM 	cargo c
		INNER JOIN legajo l ON l.legajo_id = c.legajo_id
		WHERE	c.cargo_id = {$cargo_id}
		";

        $datos = consultar_fuente($sql);

        if (isset($datos[0]['persona_id'])) {
            return $datos[0]['persona_id'];
        } else {
            return false;
        }
    }

    // en base a una fecha devuleve verdadero si es un dia declarado por el trabajador
    public static function es_dia_de_trabajo($persona_id, $fecha)
    {

        $sql = "SELECT
			dia_id
		FROM 
			horario h INNER JOIN declaracion_jurada d ON d.declaracion_jurada_id = h.declaracion_jurada_id
		WHERE	
			h.dia_id = (date_part('dow', TO_DATE('{$fecha}','YYYY-MM-DD')) +1)
			AND d.persona_id = {$persona_id};
		";

        $datos = consultar_fuente($sql);
        if (count($datos) > 0 && isset($datos[0]['dia_id'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function get_persona_legajo($legajo = null, $dni = null)
    {
        $sql = "SELECT
				l.persona_id
		FROM 	legajo l
		INNER JOIN persona p ON l.persona_id = p.persona_id
		AND	l.legajo = '{$legajo}'
		AND	p.numero_documento = '{$dni}'
		";

        $datos = consultar_fuente($sql);

        if (isset($datos[0]['persona_id'])) {
            return $datos[0]['persona_id'];
        } else {
            return null;
        }
    }

    public static function get_cantidad_asistencias_registradas($legajo)
    {
        $sql = "SELECT
                    count(a.asistencia_id) as asistencias
                FROM asistencia a
				WHERE a.persona_id = (SELECT l.persona_id from legajo l where l.legajo_id = {$legajo})
		";

        $datos = consultar_fuente($sql);

        if (isset($datos[0])) {
            return $datos[0]['asistencias'];
        } else {
            return 0;
        }
    }

    public static function get_horarios_ddjj($ddjj_id = array())
    {
        if (isset($ddjj_id['declaracion_jurada_id'])) {
            $where = " dj.declaracion_jurada_id = '{$ddjj_id['declaracion_jurada_id']}' ";
        }

        $sql = "SELECT
				dj.declaracion_jurada_id,
				dj.persona_id,
                dj.cargo_id,
                dj.fecha,
				ds.nombre as dia_semana,
				ds.dia_id,
				h.desde,
				h.hasta,
				h.horario_id
			FROM
				declaracion_jurada dj
				INNER JOIN horario h on dj.declaracion_jurada_id = h.declaracion_jurada_id
				INNER JOIN dia_semana ds on h.dia_id = ds.dia_id
			WHERE
				$where
			ORDER BY dj.fecha desc, h.dia_id asc
		";
        return consultar_fuente($sql);
    }

    public static function get_cabecera_ddjj($ddjj_id = array())
    {
        $sql = "SELECT
				dj.declaracion_jurada_id,
				dj.persona_id,
                dj.cargo_id,
                dj.fecha,
				dj.estado
			FROM
				declaracion_jurada dj
			WHERE
				dj.declaracion_jurada_id = '{$ddjj_id['declaracion_jurada_id']}'
		";
        $datos = consultar_fuente($sql);

        if (isset($datos[0])) {
            return $datos[0];
        } else {
            return false;
        }
    }

    public static function puede_activar_declaracion_jurada($ddjj_id = null)
    {
        $sql_cargo = "SELECT dj.cargo_id FROM declaracion_jurada dj WHERE dj.declaracion_jurada_id = '{$ddjj_id['declaracion_jurada_id']}' ";
        $datos = consultar_fuente($sql_cargo);

        if (isset($datos[0]['cargo_id'])) {
            $datos = consultar_fuente("SELECT dj.estado FROM declaracion_jurada dj WHERE dj.cargo_id = '{$datos[0]['cargo_id']}' AND dj.estado = true ");
            return !isset($datos[0]);
        }
    }

    public static function get_dependencia($filtro = array())
    {
        $where = '(1=1)';

        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }
        if (isset($filtro['dependencia_id'])) {
            $where .= " AND d.dependencia_id = '{$filtro['dependencia_id']}' ";
        }

        $sql = "SELECT
				d.dependencia_id,
				d.nombre,
                d.siglas
			FROM
				dependencia d
			WHERE
				$where
			ORDER BY d.siglas
		";
        return consultar_fuente($sql);
    }

    public static function get_claustros()
    {
        $where = '(1=1)';

        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }
        $sql = "SELECT
				cl.claustro_id,
				cl.nombre
			FROM
				claustro cl
			WHERE
				$where
			ORDER BY
			cl.nombre asc
		";
        return consultar_fuente($sql);
    }

    public static function get_claustros_cascade($persona_id = null)
    {
        $sql = "SELECT
				cl.claustro_id,
				cl.nombre
			FROM
				claustro cl
			WHERE cl.claustro_id IN
			(SELECT c.claustro_id FROM cargo c	INNER JOIN legajo l ON l.legajo_id = c.legajo_id AND l.persona_id = '{$persona_id}')
		";
        $result = consultar_fuente($sql);
        return $result;
    }

    public static function get_valor_preferencia_tipo_escalafon($escalafon = null)
    {
        $sql = "SELECT 	p.valor
			FROM preferencia p
			WHERE p.descripcion ILIKE '{$escalafon}'
		";

        $result = consultar_fuente($sql);

        if (isset($result[0]['valor'])) {
            return $result[0]['valor'];
        } else {
            return null;
        }
    }

    public static function get_dedicaciones($filtro = array())
    {
        $where = '(1=1)';
        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        if (isset($filtro['claustro_id'])) {
            $where .= " AND d.claustro_id = '{$filtro['claustro_id']}' ";
        }

        $sql = "SELECT
				d.dedicacion_id,
				d.nombre,
				d.carga_horaria,
				d.claustro_id
			FROM
				dedicacion d
			WHERE
				$where
			ORDER BY
				d.nombre asc
		";
        return consultar_fuente($sql);
    }

    public static function get_dedicaciones_cascade($claustro_id = null)
    {
        $sql = "SELECT
				d.dedicacion_id,
				d.nombre,
				d.carga_horaria,
				d.claustro_id
			FROM
				dedicacion d
			WHERE
				d.claustro_id = {$claustro_id}
			ORDER BY
				d.nombre asc
		";
        return consultar_fuente($sql);
    }

    public static function get_categoria_id($categoria = null)
    {

        $cat = (strlen($categoria) < 3) ? $categoria : substr($categoria, 0, -1);
        $sql = "SELECT c.categoria_id
				FROM categoria c
				WHERE c.siglas = '{$cat}'";

        $datos = consultar_fuente($sql);
        if (isset($datos[0]['categoria_id'])) {
            return $datos[0]['categoria_id'];
        } else {
            return null;
        }
    }

    public static function get_dedicacion_id($aplicacion, $categoria = null)
    {
        $sql = null;
        $cat = (strlen($categoria) < 3) ? null : substr($categoria, -1);
        if (is_null($cat)) {
            if (intval($aplicacion) < 100) {
                $sql = "SELECT dedicacion_id FROM dedicacion WHERE siglas = 'P'";
            } else {
                $sql = "SELECT dedicacion_id FROM dedicacion WHERE siglas = '35'";
            }
        } else {
            $sql = " SELECT dedicacion_id FROM dedicacion WHERE siglas = '{$cat}'";
        }

        $datos = consultar_fuente($sql);
        if (isset($datos[0]['dedicacion_id'])) {
            return $datos[0]['dedicacion_id'];
        } else {
            return null;
        }
    }

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

    public static function get_categorias()
    {
        $sql = "SELECT
				cat.categoria_id,
				cat.nombre,
                cat.siglas,
				cat.claustro_id
			FROM
				categoria cat

			ORDER BY
				cat.nombre asc
		";
        return consultar_fuente($sql);
    }

    public static function get_categorias_cascade($claustro_id = null)
    {
        $sql = "SELECT
				cat.categoria_id,
				cat.nombre,
                cat.siglas,
				cat.claustro_id
			FROM
				categoria cat
			WHERE cat.claustro_id = {$claustro_id}

			ORDER BY
				cat.nombre asc
		";
        return consultar_fuente($sql);
    }

    public static function get_dias_semana()
    {
        $sql = "SELECT
				ds.dia_id,
				ds.nombre
			FROM
				dia_semana ds
            WHERE dia_id > 1
			ORDER BY
				ds.dia_id asc
		";
        return consultar_fuente($sql);
    }

    public static function get_turnos()
    {
        $sql = "SELECT
				t.turno_id,
				t.nombre
			FROM
				turno t

			ORDER BY
				t.turno_id asc
		";
        return consultar_fuente($sql);
    }

    public static function get_afectaciones_permitidas($legajo_id = null)
    {
        $claustros = consultar_fuente("SELECT DISTINCT(c.claustro_id) FROM cargo c WHERE c.legajo_id = {$legajo_id}");

        $res = array();
        foreach ($claustros as $claustro) {
            $sql = "SELECT 	a.afectacion_horaria_id, a.funcion FROM afectacion_horaria a
					EXCEPT
					(SELECT a.afectacion_horaria_id, a.funcion FROM afectacion_horaria a
					WHERE a.claustro_id != {$claustro['claustro_id']}
			)";
            $res = array_merge($res, consultar_fuente($sql));
        }
        sort($res);
        return array_unique($res, SORT_REGULAR);
    }

    public function index()
    {
        $url = 'https://bbb.fio.unam.edu.ar/bigbluebutton/api/getMeetings?checksum=7af8345722fb7dcdd1baa3a26342c5092842820f';
        $xml = simplexml_load_file($url);

        $nodes = $xml->children();
        $filas = array();
        $personas = $microfonos = $reuniones = 0;
        //echo $nodes;
        foreach ($nodes->meetings as $meets) {
            foreach ($meets as $m) {;
                $moderadores = array();
                $nombreSesion = $m->meetingName;
                $meetingID = $m->meetingID;

                $context = 'bbb-context';
                $moodle_context = $m->metadata->$context;

                $p = date_parse($m->createDate);
                $fecha = date('Y-m-d H:i:s', mktime($p['hour'], $p['minute'], $p['second'], $p['month'], $p['day'], $p['year']));

                $cantParticipantes = $m->participantCount;
                $microfonosAbiertos = $m->voiceParticipantCount;
                $reuniones++;
                $microfonos += $microfonosAbiertos;
                $personas += $cantParticipantes;

                foreach ($m->attendees->attendee as $p) {
                    if ($p->role == 'MODERATOR') {
                        array_push($moderadores, $p->fullName);
                    }
                }

                $fila = compact('nombreSesion', 'cantParticipantes', 'moderadores', 'moodle_context', 'meetingID');
                array_push($filas, $fila);
            }
        }
        $totales = compact('personas', 'reuniones', 'microfonos');

        $title = (date_format(date_create()->setTimezone(new DateTimeZone("America/Argentina/Buenos_Aires")), 'd/m/Y | H:i'));
        return view('sesiones', compact('filas', 'title', 'totales'));
    }
}
