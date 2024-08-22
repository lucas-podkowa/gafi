<?php
class dao_persona
{
	static function get_personas($filtro = array())
	{
		$where = '(1=1)';
		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		if (!is_array($filtro) and $filtro != '') {
			$where .= "AND ((p.nombre ILIKE '%{$filtro}%') OR (p.apellido ILIKE '%{$filtro}%') OR p.apellido || ' ' || p.nombre ILIKE '%{$filtro}%' 
			OR (p.numero_documento ILIKE '%{$filtro}%')) AND length('{$filtro}') > 2 ";

			//  AND length('{$filtro}') > 1 es para que filtre solo con cadenas de largo mayores a 2
		}

		$sql = "SELECT 	
				p.persona_id, 
				p.nombre, 
				p.apellido, 
				p.fecha_nacimiento,
				p.nacionalidad as nacionalidad_id, 
				p.direccion_id, 
       			p.tipo_documento_id, 
       			p.numero_documento, 
       			p.estado_civil_id,
                pa.nombre as nacionalidad,
                d.nombre_calle,
                d.numero_calle,
                l.nombre as localidad, 
				concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')' ) AS ape_nom_dni,
       			concat(p.apellido || ' ' || p.nombre ) AS ape_nom,
				concat(d.nombre_calle || ' ' || d.numero_calle || ' (' || l.nombre || ')') AS direccion
			FROM 
				persona p 
				INNER JOIN pais pa ON p.nacionalidad = pa.pais_id
				INNER JOIN direccion d ON p.direccion_id = d.direccion_id
                INNER JOIN localidad l ON l.localidad_id = d.localidad_id
			WHERE 									
				$where
			ORDER BY ape_nom
		";
		return consultar_fuente($sql);
	}

	static function filtrar_personas($filtro)
	{

		// como $filtro viene con un $where pre-armado no es util, dado que tiene una fecha_hora y 
		// esa condicion genera un error al no haber campos que lo utilicen en esta consulta

		// ---------------------------    generar nuevo where ------------------------------
		$where = '(1=1)';

		if (isset($filtro['fecha_hora'])) {
			unset($filtro['fecha_hora']);
		}

		if (isset($filtro['persona_id'])) {
			if ($filtro['persona_id']['condicion'] == 'es_igual_a') {
				$where .= ' AND ' . 'p.persona_id = ' . $filtro['persona_id']['valor'];
			} else {
				$where .= ' AND ' . '(p.persona_id != ' . $filtro['persona_id']['valor'] . ' OR p.persona_id IS NULL)';
			}
		}

		if (isset($filtro['claustro_id'])) {
			if ($filtro['claustro_id']['condicion'] == 'es_igual_a') {
				$where .= ' AND ' . 'c.claustro_id = ' . $filtro['claustro_id']['valor'];
			} else {
				$where .= ' AND ' . '(c.claustro_id != ' . $filtro['claustro_id']['valor'] . ' OR c.claustro_id IS NULL)';
			}
		}

		// ---------------------------    consultar con where sin fecha_hora ------------------------------

		$sql = "SELECT 	
				p.persona_id,
				l.legajo_id, 
				l.legajo,
                c.cargo_id,
				c.claustro_id,
				c.numero_cargo,
				concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')' ) AS persona_desc,
				concat(cat.nombre || ' (' || d.nombre || ')' ) AS cargo_desc
			FROM 
				persona p
				INNER JOIN legajo l on p.persona_id = l.persona_id
				INNER JOIN cargo c ON l.legajo_id = c.legajo_id
				INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
				INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
			WHERE 									
				$where
				AND c.estado is TRUE
			ORDER BY persona_desc
		";
		return consultar_fuente($sql);



		// $sql = "SELECT
		//         concat(cat.nombre || ' ' || d.nombre || ' (' || cl.nombre ||')') AS cargo_desc
		// 	FROM
		// 		cargo c
		// 		INNER JOIN legajo l ON l.legajo_id = c.legajo_id
		// 		INNER JOIN claustro cl ON cl.claustro_id = c.claustro_id
		// 		INNER JOIN categoria cat ON cat.categoria_id = c.categoria_id
		// 		INNER JOIN dedicacion d ON d.dedicacion_id = c.dedicacion_id
		// 	WHERE
		// 		c.cargo_id= {$cargo_id['cargo_id']}
		// 	ORDER BY c.fecha_alta
		// ";

		// $result = consultar_fuente($sql);
		// if (!empty($result)) {
		// 	return $result[0];
		// }
	}

	static function get_persona_desc($id = null)
	{
		$sql = "SELECT 	
					concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')') AS ape_nom_dni
				FROM 
					persona p 
				WHERE 									
					p.persona_id = $id 
			ORDER BY ape_nom_dni
		
		";

		$result = consultar_fuente($sql);
		if (!empty($result)) {
			return $result[0]['ape_nom_dni'];
		}
	}


	static function get_legajos_persona($filtro = array())
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}
		if (isset($filtro['persona_id'])) {
			$where .= " AND l.persona_id = '{$filtro['persona_id']}' ";
		}

		$sql = "SELECT 	
				l.legajo_id, 
				l.legajo,
                l.persona_id,
                l.estado,
                l.dependencia_id,
				concat(d.nombre || ' (' || d.siglas || ')' ) AS dependencia_desc,
				concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')' ) AS persona_desc
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

	static function get_legajo_persona($persona_id = null)
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}
		if (isset($filtro['persona_id'])) {
			$where .= " AND l.persona_id = '{$filtro['persona_id']}' ";
		}

		$sql = "SELECT l.legajo	FROM legajo l 
				INNER JOIN persona p on l.persona_id = p.persona_id
				AND p.persona_id = '{$persona_id}'
			";
		return consultar_fuente($sql);
	}


	static function get_tipos_de_documento($filtro = array())
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		$sql = "SELECT 	
					tipo_documento_id,
					nombre,
					siglas
			FROM 	tipo_documento
			WHERE 	$where
		";
		return consultar_fuente($sql);
	}

	static function get_estados_civiles($filtro = array())
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		$sql = "SELECT 	
						estado_civil_id,
						nombre
				FROM 	estado_civil
				WHERE 	$where
		";
		return consultar_fuente($sql);
	}

	static function verificar_dni_duplicado($registro)
	{
		$sql = "SELECT 	p.numero_documento
				FROM 	persona p
				WHERE 	p.numero_documento = '{$registro['numero_documento']}'
				AND 	p.nacionalidad  = '{$registro['nacionalidad']}'	";

		$datos = consultar_fuente($sql);
		if (isset($datos[0]['numero_documento'])) {
			return false;
		} else {
			return true;
		}
	}

	static function actualizar_cantidad_huellas_persona($registro)
	{
		$sql = "UPDATE 	persona set cantidad_huellas = '{$registro['cantidad_huellas']}';
				WHERE 	persona.persona_id = '{$registro['persona_id']}'";

		return consultar_fuente($sql);
	}


	static function get_claustros_persona($persona_id = null)
	{
		$sql = "SELECT 	
				c.claustro_id
			FROM 
				cargo c
				INNER JOIN legajo l ON l.legajo_id = c.legajo_id
				AND l.persona_id = '{$persona_id}'
		";
		$result = consultar_fuente($sql);
		$datos = array();
		foreach ($result as $r) {
			array_push($datos, $r['claustro_id']);
		}
		return $datos;
	}

	static function get_sectores($sector_id = null)
	{
		$sql = 'SELECT 	
				s.sector_id,
				s.nombre,
				s.claustro_id
			FROM 
				sector s;';
		return consultar_fuente($sql);
	}
}
