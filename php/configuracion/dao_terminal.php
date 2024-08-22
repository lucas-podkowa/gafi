<?php
class dao_terminal
{
    
    ///-- Terminal. Los reloj. --///
    
    static function get_terminales($filtro=array())
	{
		$where = '1=1';
		
		//x nombre
		if(isset($filtro['nombre']) && !is_array($filtro['nombre'])) {
			$where .= ' AND nombre = ' . $filtro['nombre'];
		}
		
		//x descripcion
		if(isset($filtro['ubicacion']) && !is_array($filtro['ubicacion'])) {
			$where .= ' AND ubicacion = ' . $filtro['ubicacion'];
		}
		

		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if(isset($filtro['where'])){
			$where .= ' AND ' . $filtro['where']; 	
		}
		
		$sql = 'SELECT 
				terminal_id,
				nombre,
				ubicacion,
				ip,
				password,
				puerto,
				activo
				
			FROM 
				terminal
			WHERE ';
				
			$sql .=  $where . ' ORDER BY nombre';
			
		return consultar_fuente($sql);
	}
	
	/*
	static function get_valor_preferencia($nombre_pref)
	{
		
		$sql = "SELECT 
				valor
			FROM 
				preferencias
			WHERE nombre = '{$nombre_pref}'";
		$dato = consultar_fuente($sql);
		return $dato[0]['valor'];
	}
	*/

}
