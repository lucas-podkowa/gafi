<?php
class dao_licencia
{
    public static function get_licencias($filtro = array())
    {
        $where = '(1=1)';

        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
            l.licencia_id,
            l.tipo_licencia_id,
            l.tipo_dia_id,
            l.plazo_maximo_anual,
            l.nombre,
            l.observacion,
            l.equivalencia,
            tl.claustro_id,
            tl.nombre as tipo_licencia,
            td.nombre as tipo_dia,
            concat(l.nombre || ' (' || tl.nombre || ')' ) AS licencia_desc,
            concat(l.nombre || ' (' || tl.nombre || ' - ' ||  c.nombre || ')' ) AS nombre_completo
        FROM
            licencia l
            INNER JOIN tipo_licencia tl on  l.tipo_licencia_id = tl.tipo_licencia_id
            INNER JOIN claustro c on tl.claustro_id = c.claustro_id
            INNER JOIN tipo_dia td on l.tipo_dia_id = td.tipo_dia_id
        WHERE
            $where
        ORDER BY l.licencia_id
    ";
        return consultar_fuente($sql);
    }

    public static function get_licencias_ce($filtro = null)
    {
        $where = '(1=1)';
        if (!is_array($filtro) and $filtro != '') {
            $where .= "AND (l.nombre ILIKE '%{$filtro}%')  AND length('{$filtro}') > 1 ";
        }

        $sql = "SELECT
            l.licencia_id,
            concat(l.nombre || ' (' || tl.nombre || ' - ' ||  c.nombre || ')' ) AS nombre_completo
        FROM
            licencia l
            INNER JOIN tipo_licencia tl on  l.tipo_licencia_id = tl.tipo_licencia_id
            INNER JOIN claustro c on  tl.claustro_id = c.claustro_id
        WHERE
            $where";

        $datos = consultar_fuente($sql);
        return $datos;
    }

    public static function get_licencias_cascade($claustro_id = null)
    {
        $sql = "SELECT
            l.licencia_id,
            concat(l.nombre || ' (' || tl.nombre || ' - ' ||  c.nombre || ')' ) AS nombre_completo
        FROM
            licencia l
        INNER JOIN tipo_licencia tl on  l.tipo_licencia_id = tl.tipo_licencia_id
        INNER JOIN claustro c on  tl.claustro_id = c.claustro_id
        WHERE
            tl.claustro_id = '{$claustro_id}'";

        $datos = consultar_fuente($sql);
        return $datos;
    }

    public static function get_licencia_desc($id = null)
    {
        $sql = "SELECT
            concat(l.nombre || ' (' || tl.nombre || ' - ' || c.nombre ||')' ) AS licencia_desc
        FROM
            licencia l
            INNER JOIN tipo_licencia tl on  l.tipo_licencia_id = tl.tipo_licencia_id
            INNER JOIN claustro c on tl.claustro_id = c.claustro_id
        WHERE l.licencia_id = $id
        ";

        $result = consultar_fuente($sql);

        if (!empty($result)) {
            return $result[0]['licencia_desc'];
        }
    }

    public static function get_claustro_licencia($filtro)
    {

        $where = '(1=1)';

        if (is_array($filtro)) {
            if (isset($filtro['tipo_licencia_id'])) {
                $where .= "AND tl.tipo_licencia_id = '{$filtro['tipo_licencia_id']}'";
            } else if (isset($filtro['licencia_id'])) {
                $where .= "AND l.licencia_id = '{$filtro['licencia_id']}'";
            }
        } else if ($filtro != '') {
            $where .= "AND tl.tipo_licencia_id = '{$filtro}'";
        }

        $sql = "SELECT tl.claustro_id
        FROM licencia l INNER JOIN tipo_licencia tl on l.tipo_licencia_id = tl.tipo_licencia_id
        WHERE $where";

        $result = consultar_fuente($sql);

        if (!empty($result)) {
            return $result[0]['claustro_id'];
        }
    }

    public static function equivalencia_no_valida($filtro = array())
    {
        if (is_array($filtro) and isset($filtro['equivalencia'])) {

            $sql = "SELECT claustro_id
                    FROM  tipo_licencia tl
                    WHERE tl.tipo_licencia_id = (SELECT tipo_licencia_id FROM licencia WHERE licencia_id = '{$filtro['equivalencia']}')";

            $result = consultar_fuente($sql);

            if (!empty($result)) {
                if ($result[0]['claustro_id'] == "{$filtro['claustro_id']}") {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public static function get_licencia_x_cargo($filtro = array())
    {
        $where = '(1=1)';
        if (is_array($filtro) and isset($filtro['licencia_x_cargo_id'])) {
            $where .= "AND lxc.licencia_x_cargo_id = '{$filtro['licencia_x_cargo_id']}'";
        }
        $sql = "SELECT
            lxc.cargo_id,
            lxc.persona_id,
            lxc.licencia_id,
            lxc.fecha_alta,
            lxc.fecha_baja,
            lxc.anulado,
            c.claustro_id
        FROM
            licencia_x_cargo lxc
            INNER JOIN cargo c on lxc.cargo_id = c.cargo_id
        WHERE
            $where
    ";
        $result = consultar_fuente($sql);
        if (!empty($result)) {
            return $result[0];
        }
    }

    public static function get_licencias_x_cargo($filtro = array())
    {
        $where = '(1=1)';
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        if (!isset($filtro['anulado'])) {
            $where .= ' AND lxc.anulado = false';
        }

        if (isset($filtro['vigencia'])) {

            //$where .= " AND tl.claustro_id = '{$filtro['claustro_id']}' ";
            switch ($filtro['vigencia']['condicion']) {
                case 'es_igual_a':
                    $where .= " AND lxc.fecha_alta <= '{$filtro['vigencia']['valor']}' AND lxc.fecha_baja >= '{$filtro['vigencia']['valor']}'";
                    break;
                case 'es_distinto_de':
                    $where .= " AND lxc.fecha_baja <= '{$filtro['vigencia']['valor']}' AND lxc.fecha_alta >= '{$filtro['vigencia']['valor']}'";
                    break;
                case 'desde':
                    $ahora = date('Y-m-d');
                    $where .= " AND (lxc.fecha_alta >= '{$filtro['vigencia']['valor']}' AND lxc.fecha_baja >= '{$ahora}')";
                    break;
                case 'hasta':
                    $where .= " AND lxc.fecha_baja <= '{$filtro['vigencia']['valor']}'";
                    break;
                case 'entre':
                    $where .= " AND ((lxc.fecha_alta between '{$filtro['vigencia']['valor']['desde']}' AND '{$filtro['vigencia']['valor']['hasta']}')
                    OR  (lxc.fecha_baja between '{$filtro['vigencia']['valor']['desde']}' AND '{$filtro['vigencia']['valor']['hasta']}')
                    OR  (lxc.fecha_alta <= '{$filtro['vigencia']['valor']['desde']}' AND lxc.fecha_baja >= '{$filtro['vigencia']['valor']['hasta']}'))";
                    break;
            }
        }

        $sql = "SELECT
            lxc.licencia_x_cargo_id,
            lxc.cargo_id,
            lxc.persona_id,
            lxc.licencia_id,
            lxc.fecha_alta,
            lxc.fecha_baja,
            anulado,
            concat(cat.nombre || ' ' || d.nombre) AS cargo_desc,
            concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')' ) AS ape_nom_dni,
            l.nombre as nombre_licencia
        FROM
            licencia_x_cargo lxc
            INNER JOIN persona p on  p.persona_id = lxc.persona_id
            INNER JOIN licencia l on l.licencia_id = lxc.licencia_id
            INNER JOIN cargo c on lxc.cargo_id = c.cargo_id
            INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
			INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
        WHERE
            $where
    ";
        return consultar_fuente($sql);
    }
    
    public static function get_licencia_x_fecha_persona($persona_id, $fecha)
    {
    	if(isset($persona_id)){
		$sql = "SELECT
		    lxc.cargo_id,
		    lxc.persona_id,
		    lxc.licencia_id,
		    lxc.fecha_alta,
		    lxc.fecha_baja,
		    lxc.anulado,
		    c.claustro_id,
		    l.nombre nombre_licencia,
		    tl.nombre nombre_tipo_lic
		FROM
		    licencia l INNER JOIN licencia_x_cargo lxc ON lxc.licencia_id = l.licencia_id
		    INNER JOIN tipo_licencia tl ON tl.tipo_licencia_id = l.tipo_licencia_id
		    INNER JOIN cargo c on lxc.cargo_id = c.cargo_id
		WHERE
		    NOT lxc.anulado
		    AND lxc.persona_id = {$persona_id}
		    AND lxc.fecha_alta <= TO_DATE('{$fecha}','YYYY-MM-DD')
		    AND lxc.fecha_baja >= TO_DATE('{$fecha}','YYYY-MM-DD')
	    ";
		$result = consultar_fuente($sql);
		if (!empty($result)) {
		    return $result[0];
		}
	}
	return false;
    }

    public static function get_tipo_dia()
    {
        $sql = "SELECT
            td.tipo_dia_id,
            td.nombre
        FROM
            tipo_dia td

        ORDER BY
            td.tipo_dia_id asc";

        return consultar_fuente($sql);
    }

    public static function get_tipo_licencia($filtro = array())
    {
        $where = '(1=1)';

        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        if (isset($filtro['claustro_id'])) {
            $where .= " AND tl.claustro_id = '{$filtro['claustro_id']}' ";
            //$where .= " AND tl.claustro_id = '1'";
        }

        //cuando se hace un combo anidado en filtros, el parametro llega como variable y no como array
        if (isset($filtro) && !is_array($filtro)) {
            $where .= "AND tl.claustro_id = '{$filtro}' ";
        }

        $sql = "SELECT
            tl.tipo_licencia_id,
            tl.claustro_id,
            tl.nombre,
            concat(tl.nombre || ' (' || c.nombre || ')' ) AS tipo_licencia_desc
        FROM
            tipo_licencia tl
        INNER JOIN claustro c on tl.claustro_id = c.claustro_id
        WHERE $where
        ORDER BY tl.tipo_licencia_id asc";
        return consultar_fuente($sql);
    }

    public static function get_tipo_licencia_desc()
    {
        $sql = "SELECT
            concat(tl.nombre || ' (' || c.nombre || ')' ) AS tipo_licencia_desc
        FROM tipo_licencia tl
        INNER JOIN claustro c on tl.claustro_id = c.claustro_id
        ";

        return consultar_fuente($sql);
    }

    public static function existe_licencia($registro = array(), $licencia_id = null)
    {
        $where = '(1=1)';

        if (isset($licencia_id) && !is_array($licencia_id)) {
            $where .= " AND l.licencia_id != {$licencia_id} ";
        }

        $sql = "SELECT l.licencia_id
        FROM licencia l
        WHERE   $where
            AND l.tipo_licencia_id = {$registro['tipo_licencia_id']}
            AND l.tipo_dia_id = {$registro['tipo_dia_id']}
            AND l.nombre = '{$registro['nombre']}'
        ";

        $result = consultar_fuente($sql);
        return !empty($result);
    }

    public static function existe_licencia_x_cargo($registro = array(), $lxc_id = null)
    {
        $where = '(1=1)';

        if (isset($lxc_id) && !is_array($lxc_id)) {
            $where .= " AND lxc.licencia_x_cargo_id != {$lxc_id} ";
        }

        $sql = "SELECT lxc.licencia_x_cargo_id
        FROM licencia_x_cargo lxc
        WHERE   $where
            AND lxc.licencia_id = {$registro['licencia_id']}
            AND lxc.persona_id = {$registro['persona_id']}
            AND lxc.cargo_id = {$registro['cargo_id']}
            AND lxc.anulado is false
        ";

        $result = consultar_fuente($sql);
        return !empty($result);
    }
}
