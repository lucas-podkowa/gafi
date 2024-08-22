<?php
class dao_direccion
{

	static function get_paises($filtro = array())
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		$sql = "SELECT 	p.pais_id, p.nombre
			FROM pais p
			WHERE $where
			ORDER BY p.nombre
		";
		return consultar_fuente($sql);
	}

	static function get_provincias($filtro = array())
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		if (isset($filtro['provincia_id'])) {
			$where .= " AND p.provincia_id = '{$filtro['provincia_id']}' ";
		}

		// cascada pk dos id
		if (isset($filtro['pais_id'])) {
			$where .= " AND p.pais_id = '{$filtro['pais_id']}'";
		}

		$sql = "SELECT 	
					p.provincia_id,
					p.nombre,
					p.pais_id
			FROM 
					provincia p
		
			WHERE 									
					$where
			ORDER BY p.nombre
		";
		return consultar_fuente($sql);
	}

	static function get_codigo_provincia($nombre_provincia = null)
	{
		$sql = "SELECT p.provincia_id
			FROM provincia p
			WHERE UPPER (p.nombre) ILIKE UPPER('%{$nombre_provincia}%')
		";
		
		$result = consultar_fuente($sql);
		if (!empty($result)) {
			return $result[0]['provincia_id'];
		}
	}
	
	
	static function get_provincias_con_paises($filtro = array())
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		if (isset($filtro['provincia_id'])) {
			$where .= " AND p.provincia_id = '{$filtro['provincia_id']}' ";
		}

		// cascada pk dos id
		if (isset($filtro['pais_id'])) {
			$where .= " AND p.pais_id = '{$filtro['pais_id']}'";
		}

		$sql = "SELECT 	
					p.provincia_id,
					p.nombre,
					p.pais_id,
					pa.nombre as nombre_pais,
					concat(p.nombre || ' ('  || pa.nombre ||')') AS provincia_desc
			FROM 
					provincia p INNER JOIN pais pa ON p.pais_id = pa.pais_id 		
		
			WHERE 									
					$where
			ORDER BY p.nombre
		";
		return consultar_fuente($sql);
	}

	//metodo llamado para mostrar los datos en el combo_editable
	static function get_localidad_desc($id = null)
	{
		$sql = "SELECT 	
					concat(l.nombre, ' / ', pro.nombre,' / ' || pa.nombre) AS localidad_desc
			FROM 
					localidad l
					INNER JOIN provincia pro ON l.provincia_id = pro.provincia_id
					INNER JOIN pais pa ON pro.pais_id = pa.pais_id
			WHERE 									
					l.localidad_id = $id 
			ORDER BY localidad_desc
			
		";

		$result = consultar_fuente($sql);
		if (!empty($result)) {
			return $result[0]['localidad_desc'];
		}
	}

	static function get_localidades($filtro = array())
	{
		$where = '(1=1)';

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		} elseif (!is_array($filtro) and $filtro != '') {
			$where .= "AND (translate(l.nombre, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU') ILIKE '%{$filtro}%')";
		}


		if (isset($filtro['localidad_id'])) {
			$where .= " AND l.localidad_id = '{$filtro['localidad_id']}' ";
		}

		$sql = "SELECT 	
					l.localidad_id,
					l.nombre,
					l.codigo_postal,
					pro.nombre as provincia,
					concat(l.nombre, ' / ', pro.nombre,' / ' || pa.nombre) AS localidad_desc
			FROM 
			localidad l
					INNER JOIN provincia pro ON l.provincia_id = pro.provincia_id
					INNER JOIN pais pa ON pro.pais_id = pa.pais_id
			WHERE 									
					$where
			ORDER BY l.nombre
		";
		return consultar_fuente($sql);
	}

	static function get_id_localidad($localidad = null, $cp = null)
	{
		$result = array();
		$loc = str_ireplace('-MNES.', '', $localidad);
		//translate(l.nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU');
		$sql = "SELECT 	l.localidad_id, l.nombre
				FROM	localidad l
				WHERE  l.nombre ILIKE '%{$loc}%'
					--AND l.codigo_postal = '{$cp}'
		";
		$datos = consultar_fuente($sql);

		if (count($datos) > 1) {
			$sql = "SELECT 	l.localidad_id
					FROM	localidad l
					WHERE  l.nombre ILIKE '%{$loc}%'
						AND l.codigo_postal = '{$cp}' 
					";
			$result = consultar_fuente($sql);
		} else if (count($datos) == 1){
			$result = $datos;
		}

		if (isset($result[0]['localidad_id'])) {
			return $result[0]['localidad_id'];
		} else {
			return null;
		}
	}

	static function get_direccion_id($persona_id = null)
	{
		$sql = "SELECT p.direccion_id
				FROM  persona p
				WHERE p.persona_id = '{$persona_id}'
		";
		$datos = consultar_fuente($sql);
		if (isset($datos[0]['direccion_id'])) {
			return $datos[0]['direccion_id'];
		} else {
			return null;
		}
	}

	static function get_info_direccion($direccion_id = null)
	{
		$sql = "SELECT 	* 
				FROM	direccion d
				WHERE  d.direccion_id = {$direccion_id}
		";
		$datos = consultar_fuente($sql);

		if (isset($datos[0])) {
			return $datos[0];
		} else {
			return null;
		}
	}

	/*
	
	
	
	static function get_departamentos($filtro=array())
	{
		$where = '(1=1)';
		
		
		// cascada con 2 pk
		if(isset($filtro['id_provincia']) && isset($filtro['nodo_provincia'])){
			$where .= " AND d.id_provincia = '{$filtro['id_provincia']}'  AND d.nodo_provincia = '{$filtro['nodo_provincia']}' "; 	
		}
		
		// para la operacion de localizaciones.
		if(isset($filtro['id_provincia_a']) && isset($filtro['nodo_provincia'])){
			$where .= " AND d.id_provincia = '{$filtro['id_provincia_a']}'  AND d.nodo_provincia = '{$filtro['nodo_provincia']}' ";
		}
		
		if(isset($filtro['id_departamento'])){
			$where .= " AND d.id_departamento = '{$filtro['id_departamento']}' "; 	
		}
		
		// para cargar en los combos en cascada
		if (isset($filtro) && !is_array($filtro) ){
			$where .= " AND d.id_provincia = '{$filtro}' "; 
		}
				
		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if(isset($filtro['where'])){
			$where .= ' AND ' . $filtro['where']; 	
		}
	
		$sql = "SELECT 	
					d.id_departamento,
					d.nodo,
					d.nombre
			FROM 
					lc_departamento d
		
			WHERE 									
					$where
			ORDER BY d.nombre
		";		
		return consultar_fuente($sql);
	}
	
	
	
	static function localidad_esta_siendo_usada($id_localidad, $nodo_localidad)
	{
		$existe = false;
		$sql = "SELECT 	
					p.id_localidad,
					p.nodo_localidad
			FROM 
					pna_persona p
					
			WHERE 		p.id_localidad = {$id_localidad} and p.nodo_localidad = {$nodo_localidad};";
		$result = consultar_fuente($sql);
		if (! empty($result)) {
			$existe = 1;
		}

		$sql = "
			SELECT 	
					t.id_localidad,
					t.nodo_localidad
			FROM 
					tm_taller t
					
			WHERE 		t.id_localidad = {$id_localidad} and t.nodo_localidad = {$nodo_localidad};";
		$result = consultar_fuente($sql);
		if (! empty($result)) {
			$existe = 1;
		}
		
		return $existe;
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	
	static function get_departamento_localidad($id_localidad, $nodo_localidad)
	{
		if(isset($id_localidad)){
			$sql = "SELECT 	
					l.id_departamento, 
					l.nodo_departamento
			FROM 
					lc_localidad l		
			WHERE 									
					l.id_localidad = '{$id_localidad}' AND l.nodo = '{$nodo_localidad}'
			";
		}
		$datos = consultar_fuente($sql);
		return $datos[0];
	}
	
	static function get_provincia_departamento($id_departamento, $nodo_departamento)
	{
		
		if(isset($id_departamento)){
			$sql = "SELECT 	
					d.id_provincia,
					d.nodo_provincia
			FROM 
					lc_departamento d
			WHERE 									
					d.id_departamento = '{$id_departamento}' AND d.nodo = '{$nodo_departamento}'
			";
		}
		$datos = consultar_fuente($sql);
		return $datos[0];
	}
	
	static function get_pais_provincia($id_provincia, $nodo_provincia)
	{
		
		if(isset($id_provincia)){
			$sql = "SELECT 	
					p.id_pais,
					nodo_pais
				FROM 
					lc_provincia p
				WHERE 									
					p.id_provincia = '{$id_provincia}' AND p.nodo = '{$nodo_provincia}'
			";
		}
		$datos = consultar_fuente($sql);
		return $datos[0];
	}


	*/
}
