<?php
class dao_configuracion
{
    //$variable = null;
    public static function get_ciclos_lectivos($filtro = array())
    {
        $where = '(1=1)';
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
                    cl.ciclo_lectivo_id,
                    cl.ciclo,
                    cl.inicio,
                    cl.fin
                FROM ciclo_lectivo cl
			    WHERE $where
			    ORDER BY cl.ciclo DESC
		";

        return consultar_fuente($sql);
    }

    public static function get_ciclos_lectivos_ce($filtro = null)
    {
        $where = '(1=1)';
        if (!is_array($filtro) and $filtro != '') {
            if (is_int($filtro)) {
                $where .= "AND (cl.ciclo = '{$filtro}')  AND length('{$filtro}') > 1 ";
            }
        }

        $sql = "SELECT
       				cl.ciclo_lectivo_id,
                    concat(cl.ciclo || ' (' || cl.inicio || ' al ' || cl.fin || ')') AS ciclo_desc
       			FROM
				ciclo_lectivo cl
			WHERE
				$where
		";
        $datos = consultar_fuente($sql);
        return $datos;
    }

    public static function get_ciclo_lectivo($id = null)
    {
        $sql = "SELECT
                    cl.ciclo_lectivo_id,
                    cl.ciclo,
                    cl.inicio,
                    cl.fin
                FROM    ciclo_lectivo cl
			    WHERE   cl.ciclo_lectivo_id = {$id}
		";
        return consultar_fuente($sql);
    }

    public static function get_ciclo_lectivo_desc($id = null)
    {
        $sql = "SELECT 	cl.ciclo
                FROM ciclo_lectivo cl
			    WHERE cl.ciclo_lectivo_id = {$id}
		";

        $result = consultar_fuente($sql);

        if (!empty($result)) {
            return $result[0]['ciclo'];
        }
    }

    public static function get_tipos_receso()
    {
        $sql = "SELECT
                    tr.tipo_receso_id,
                    tr.nombre
                FROM tipo_receso tr
		";
        return consultar_fuente($sql);
    }

    public static function get_recesos($filtro = array())
    {
        $where = '(1=1)';

        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
                    r.receso_id,
                    r.numero_resolucion,
                    r.tipo_receso_id,
                    r.desde,
                    r.hasta,
                    tr.nombre as tipo_receso
                FROM receso r
                INNER JOIN tipo_receso tr on r.tipo_receso_id = tr.tipo_receso_id
			    WHERE $where
			    ORDER BY r.receso_id ASC
		";
        return consultar_fuente($sql);
    }

    public static function get_dias_no_laborables($filtro = array())
    {
        $where = '(1=1)';

        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
                    d.dia_no_laborable_id,
                    d.fecha,
                    d.hora_inicio,
                    d.motivo
                FROM dia_no_laborable d
                WHERE $where
			    ORDER BY fecha ASC
		";
        return consultar_fuente($sql);
    }

    public static function es_dia_no_laborable($fecha)
    {
        if (isset($fecha)) {
            $sql = "SELECT
		            d.dia_no_laborable_id,
		            d.hora_inicio,
		            d.motivo
		        FROM dia_no_laborable d
		        WHERE 
				d.fecha = TO_DATE('{$fecha}','YYYY-MM-DD')
			";
            $result = consultar_fuente($sql);
            if (!empty($result)) {
                return $result[0];
            }
        }
        return false;
    }

    public static function existe_dia_no_laborable($filtro = array(), $id = array())
    {
        $where = '(1=1)';
        if (is_array($filtro) and isset($filtro['motivo']) and isset($filtro['fecha'])) {
            if (isset($id['dia_no_laborable_id'])) {
                $where .= " AND d.dia_no_laborable_id != '{$id['dia_no_laborable_id']}'
                AND (d.fecha = '{$filtro['fecha']}' AND (d.motivo  ILIKE '%{$filtro['motivo']}%'))";
            } else {
                $where .= "AND (d.fecha = '{$filtro['fecha']}' AND (d.motivo  ILIKE '%{$filtro['motivo']}%'))";
            }
        }

        $sql = "SELECT
                    d.dia_no_laborable_id
                FROM dia_no_laborable d
			    WHERE $where
		";
        $result = consultar_fuente($sql);
        return !empty($result);
    }

    ///-- Preferencias. Tabla con valores por defecto. Métodos para gestionalrla --///

    public static function get_preferencias($filtro = array())
    {
        $where = '1=1';

        //x nombre
        if (isset($filtro['nombre']) && !is_array($filtro['nombre'])) {
            $where .= ' AND nombre = ' . $filtro['nombre'];
        }

        //x descripcion
        if (isset($filtro['descripcion']) && !is_array($filtro['descripcion'])) {
            $where .= ' AND descripcion = ' . $filtro['descripcion'];
        }

        //x valor
        if (isset($filtro['valor']) && !is_array($filtro['valor'])) {
            $where .= ' AND valor = ' . $filtro['valor'];
        }

        //x id
        if (isset($filtro['preferencia_id']) && !is_array($filtro['preferencia_id'])) {
            $where .= ' AND preferencia_id = ' . $filtro['preferencia_id'];
        }
        // generado con el componente filtro. dep('filtro')->get_sql_where();
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
				preferencia_id,
				nombre,
				valor,
				descripcion,
				CASE
					WHEN tipo_valor = 'i' THEN 'Numérico'
					WHEN tipo_valor = 'b' THEN 'Lógico'
					WHEN tipo_valor = 's' THEN 'Texto'
				END tipo_valor
				FROM
				preferencia
			WHERE ";

        $sql .= $where . ' ORDER BY nombre';

        $datos = consultar_fuente($sql);
        switch ($datos['tipo_valor']) {
            case 'b':
                $datos['tipo_valor'] = 'Lógico';
                break;
            case 'i':
                $datos['tipo_valor'] = 'Entero';
                break;
            case 's':
                $datos['tipo_valor'] = 'Texto';
                break;
        }
        return $datos;
    }

    public static function get_valor_preferencia($nombre_pref)
    {

        $sql = "SELECT
				valor
			FROM
				preferencias
			WHERE nombre = '{$nombre_pref}'";
        $dato = consultar_fuente($sql);
        return $dato[0]['valor'];
    }

    public static function existe_ciclo($ciclo, $actual)
    {
        $sql = "SELECT ciclo FROM ciclo_lectivo WHERE ciclo = {$ciclo} AND ciclo <> {$actual}";
        $result = consultar_fuente($sql);
        return !empty($result);
    }

    public static function get_sectores($filtro = array())
    {
        $where = '(1=1)';
        if (isset($filtro['where'])) {
            $where .= ' AND ' . $filtro['where'];
        }

        $sql = "SELECT
                    s.sector_id,
                    s.claustro_id,
                    s.nombre,
                    cl.nombre as claustro
                FROM sector s
                INNER JOIN claustro cl ON s.claustro_id = cl.claustro_id
			    WHERE $where
			    ORDER BY s.nombre DESC
		";
        $datos = consultar_fuente($sql);
        $respuesta = array();
        foreach ($datos as $sector) {
            $cantidad = consultar_fuente("SELECT count(persona_id) from persona_x_sector where sector_id = {$sector['sector_id']}");
            $sector['cantidad'] = $cantidad[0]['count'];
            array_push($respuesta, $sector);
        }
        return $respuesta;
    }

    public static function get_personas_sector($sector_id)
    {
        $sql = "SELECT  pa.persona_id,
                        pa.sector_id
                FROM    persona_x_sector pa
			    WHERE   pa.sector_id = $sector_id
		";
        return consultar_fuente($sql);
    }
    public static function get_id_personas_sector($sector_id)
    {
        $sql = "SELECT  pa.persona_id
                FROM    persona_x_sector pa
			    WHERE   pa.sector_id = $sector_id
		";
        $datos = consultar_fuente($sql);

        $devolver = array();
        foreach ($datos as $p) {
            array_push($devolver, $p['persona_id']);
        }
        return $devolver;
    }

    //public static function quitar_personas_sector($sector_id)
    //{
    //    $sql = "DELETE
    //            FROM persona_x_sector pa
    //		    WHERE pa.sector_id = $sector_id
    //	";
    //    return consultar_fuente($sql);
    //}

    //public static function quitar_persona_de_sector($registro)
    //{
    //    $sql = "DELETE
    //            FROM persona_x_sector pa
    //		    WHERE   pa.sector_id = '{$registro['sector_id']}' AND pa.persona_id = '{$registro['persona_id']}'
    //	";
    //    return consultar_fuente($sql);
    //}
}
