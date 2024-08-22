<?php

include_once 'empleados/dao_empleado.php';
include_once 'licencias/dao_licencia.php';
include_once 'configuracion/dao_configuracion.php';
class dao_reportes
{

	static function get_fichadas_persona($filtro = array())
	{
		$where = '(1=1)';
		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		$sql = "SELECT 	
				p.nombre,
				p.apellido,
				p.numero_documento,
				a.persona_id,
				a.fecha_hora,
				a.evento,
				a.terminal_nombre,
				a.en_reloj,
				td.siglas AS tipo_documento
			FROM 
				asistencia a 
				INNER JOIN persona p ON a.persona_id = p.persona_id
				INNER JOIN tipo_documento td ON p.tipo_documento_id = td.tipo_documento_id
			WHERE 									
				$where
			ORDER BY fecha_hora
		";
		return consultar_fuente($sql);
	}


	static function get_fichadas_manuales($filtro = array())
	{
		$where = '(1=1)';
		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		$sql = "SELECT
				concat(p.apellido || ' ' || p.nombre || ' (' || p.numero_documento || ')' ) AS persona_desc,
				a.persona_id,
				a.fecha_hora,
				a.evento,
				a.terminal_nombre
			FROM 
				asistencia a 
				INNER JOIN persona p ON a.persona_id = p.persona_id
			WHERE 									
				$where
				AND a.en_reloj is false
			ORDER BY fecha_hora
		";
		return consultar_fuente($sql);
	}

	static function get_posicion_declaracion($datos, $fecha_hora_entrada, $fecha_hora_salida)
	{
		$i = 0; // con este indice se va a selecionar la posición de la declaración
		$cdatos = count($datos);
		if ($cdatos > 0) {
			for ($j = 0; $j < $cdatos; $j++) {
				$hora_entrada = date('H:i:s', strtotime($fecha_hora_entrada));
				$hora_salida = date('H:i:s', strtotime($fecha_hora_salida));

				if ($hora_entrada <= $datos[$j]['desde'] && $hora_entrada < $datos[$j]['hasta'] && $hora_salida > $datos[$j]['desde'] && $hora_salida <= $datos[$j]['hasta']) {
					$i = $j;
					break; // ya encontré el horario.
				} elseif ($hora_entrada >= $datos[$j]['desde'] && $hora_entrada < $datos[$j]['hasta'] && $hora_salida > $datos[$j]['desde'] && $hora_salida <= $datos[$j]['hasta']) {
					$i = $j;
					break; // ya encontré el horario.
				} elseif ($hora_entrada <= $datos[$j]['desde'] && $hora_entrada < $datos[$j]['hasta'] && $hora_salida > $datos[$j]['desde'] && $hora_salida >= $datos[$j]['hasta']) {
					$i = $j;
					break; // ya encontré el horario.
				} elseif ($hora_entrada >= $datos[$j]['desde'] && $hora_entrada < $datos[$j]['hasta'] && $hora_salida > $datos[$j]['desde'] && $hora_salida >= $datos[$j]['hasta']) {
					$i = $j;
					break; // ya encontré el horario.
				}

				// entro y salio antes de su declaración entrada y salida < a desde y hasta
				elseif ($hora_entrada < $datos[$j]['desde'] && $hora_entrada < $datos[$j]['hasta'] && $hora_salida < $datos[$j]['desde'] && $hora_entrada < $hora_salida) {
					$i = -1;
				}

				// entro y salio después que su declaración.
				elseif ($hora_entrada > $datos[$j]['desde'] && $hora_entrada > $datos[$j]['hasta'] && $hora_salida > $datos[$j]['desde'] && $hora_salida > $datos[$j]['hasta'] && $hora_entrada < $hora_salida && $i < 0) {
					$i = -2;
				}
			}
		}
		return $i;
	}

	// Evaldo. Valida una marcación (entrada o salida) comparandola con el horario jurado para devolver "c" correcto. "t" tarde. "a" salió antes. "f" sin declarar
	static function validar_marcacion($persona_id, $fecha_hora, $evento, $asume_evento = '')
	{
		$dia_id = date('w', strtotime($fecha_hora)) + 1; // devuelve de 0 a 6. nosotros usamos de 1 a 7. 
		$fecha = date("Y-m-d", strtotime($fecha_hora));

		$sql = "SELECT 	desde, hasta 
			FROM declaracion_jurada dj
			INNER JOIN horario h ON dj.declaracion_jurada_id = h.declaracion_jurada_id
			AND dia_id = $dia_id
			AND dj.persona_id = $persona_id
			AND dj.estado;";
		$datos = consultar_fuente($sql);

		$i = 0;
		$mas_cerca = '24:00:00';
		$cdatos = count($datos);
		if ($cdatos > 1) { // hay más de una fraja horaria para el mismo día. ej. de 7 a 1 y de 14 a 18
			for ($j = 0; $j < $cdatos; $j++) {
				if ($asume_evento == 1) {
					$fecha_hora_declarada = $fecha . ' ' . $datos[$j]['desde'];
				} elseif ($asume_evento == 0) {
					$fecha_hora_declarada = $fecha . ' ' . $datos[$j]['hasta'];
				}
				//$hora = date('H:i:s', strtotime($fecha_hora));
				$date1 = new DateTime($fecha_hora);
				$date2 = new DateTime($fecha_hora_declarada);

				$dif = $date2->diff($date1);
				if ($dif->format('%H:%I:%S') > '00:00:00') {
					$mas_cerca = $dif->format('%H:%I:%S');
				}
				$j++;
			}
			$i = $j - 1;
		}

		// prioridad tiene la marcación erronea o repetida
		if ($evento >= 3) { // entrada o salida repetida con pocos segundos de diferencia/02/2023 
			return 'R';
		}

		if (!$datos) { // no tiene declarado para ese día/persona.
			return 'N';
		}


		if ($asume_evento == 1) { // entrada
			if (strtotime($fecha . ' ' . $datos[$i]['desde']) >= strtotime($fecha_hora)) {
				return 'C'; // correcto
			} else {
				return 'T'; // tarde
			}
		}

		if ($asume_evento == 0) { // salida
			if (strtotime($fecha . ' ' . $datos[$i]['hasta']) <= strtotime($fecha_hora)) {
				return 'C'; //correcto
			} else {
				return 'A'; // salió antes
			}
		}
	}

	static function cuantas_horas_extra_y_normales($persona_id, $fecha_hora_entrada, $fecha_hora_salida)
	{
		// fecha_hora_entrada y fecha_hora_salida siempre son la misma fecha.
		$dia_id = date('w', strtotime($fecha_hora_entrada)) + 1; // devuelve de 0 a 6. nosotros usamos de 1 a 7. 
		$fecha = date('Y-m-d', strtotime($fecha_hora_entrada));

		$sql = "SELECT 	desde, hasta 
			FROM declaracion_jurada dj
			INNER JOIN horario h ON dj.declaracion_jurada_id = h.declaracion_jurada_id
			AND dia_id = $dia_id
			AND dj.persona_id = $persona_id
			AND dj.estado;";
		$datos = consultar_fuente($sql);

		if (count($datos) > 0) {

			$extras	 = 0;
			$normales = 0;

			$i = dao_reportes::get_posicion_declaracion($datos, $fecha_hora_entrada, $fecha_hora_salida);

			if ($i > -1) { // la entra y salida caen fuera de los horarios declarados.
				// claculo las horas extras antes de su horario declarado.
				$fecha_hora_entrada_declarada = $fecha . ' ' . $datos[$i]['desde'];
				$date1 = new DateTime($fecha_hora_entrada_declarada);
				$date2 = new DateTime($fecha_hora_entrada);

				$dif = $date2->diff($date1);
				if ($dif->format('%H:%I:%S') > '00:00:00' && $fecha_hora_entrada_declarada > $fecha_hora_entrada) { // hay horas extras antes de su horario declarado.
					$partes2 = explode(':', $dif->format('%H:%I:%S'));
					$extras = $partes2[2] + $partes2[1] * 60 + $partes2[0] * 3600; // horas convertidas a segundos
				}


				// calculo las horas extras después de su horario declarado
				$fecha_hora_salida_declarada = $fecha . ' ' . $datos[$i]['hasta'];
				$date1 = new DateTime($fecha_hora_salida);
				$date2 = new DateTime($fecha_hora_salida_declarada);

				$dif = $date2->diff($date1);
				if ($dif->format('%H:%I:%S') > '00:00:00'  && $fecha_hora_salida_declarada < $fecha_hora_salida) { // hay horas extras antes de su horario declarado.
					$partes2 = explode(':', $dif->format('%H:%I:%S'));
					$extras += ($partes2[2] + $partes2[1] * 60 + $partes2[0] * 3600); // horas convertidas a segundos
				}
			}


			// el total de las horas de ese tramo menos las horas extras me da las normales.
			$date1 = new DateTime($fecha_hora_salida);
			$date2 = new DateTime($fecha_hora_entrada);
			$dif = $date1->diff($date2);
			if ($dif->format('%H:%I:%S') > '00:00:00') { // hay horas extras antes de su horario declarado.
				$partes2 = explode(':', $dif->format('%H:%I:%S'));
				$totales = $partes2[2] + $partes2[1] * 60 + $partes2[0] * 3600; // horas convertidas a segundos
				if ($extras > 0) {
					$normales = abs($totales - $extras);
				} elseif ($i == -1) { // si $i == -1 la entrada y salida estan fuera del horario declarado, por lo tanto, es todo horas extras
					$extras = $totales;
				} else {
					$normales = $totales;
				}
			}
			return array('normales' => $normales, 'extras' => $extras); // retorno en segundos para poder sumar facilmente.

		} else {
			// verifico que tenga horario declarado. sinó no se puede calcular las horas extras.
			if (count($datos) == 0) {
				// si no encontro un horario para ese dia verifico si tiene algun horario.. puede ser que todo el día sea hora extra.
				$sql = "SELECT 	desde, hasta 
					FROM declaracion_jurada dj
					INNER JOIN horario h ON dj.declaracion_jurada_id = h.declaracion_jurada_id
					--AND dia_id = $dia_id
					AND dj.persona_id = $persona_id
					AND dj.estado;";
				$datos = consultar_fuente($sql);

				if (count($datos) > 0) { // si tienen un horario declarado. pero no para ese día.
					$date1 = new DateTime($fecha_hora_salida);
					$date2 = new DateTime($fecha_hora_entrada);
					$dif = $date1->diff($date2);
					if ($dif->format('%H:%I:%S') > '00:00:00') {
						$partes2 = explode(':', $dif->format('%H:%I:%S'));
						$extras = $partes2[2] + $partes2[1] * 60 + $partes2[0] * 3600; // horas convertidas a segundos
					}
					return array('normales' => '00:00:00', 'extras' => $extras);
				} else {
					return array('normales' => '-1');
				}
			}
		}
	}

	static function get_horas_trabajadas($filtro = array())
	{
		$where = '(1=1)';
		$sector = '';
		// generado con el componente filtro. dep('filtro')->get_sql_where();
		if (isset($filtro['where'])) {
			$where .= ' AND ' . $filtro['where'];
		}

		// para el WHERE. AGREGO la tabla sector_x_persona
		if (isset($filtro['sector_id'])) {
			$sector = ' INNER JOIN persona_x_sector ps ON ps.persona_id = p.persona_id';
		}

		// TODO: consulta en base a las fichadas.. si no tiene fichda al inicio del periodo no muestra los feriados en esa fecha
		$sql = "SELECT 	
				 DISTINCT p.nombre,
				p.apellido,
				l.legajo,
				concat(p.apellido || ' ' || p.nombre ) AS ape_nom,
				p.numero_documento,
				a.persona_id,
				a.fecha_hora,
				a.en_reloj,
				SUBSTRING(a.fecha_hora::text, 12, 5) AS hora,
				SUBSTRING(a.fecha_hora::text, 1, 11) AS fecha,
				CASE 
            				WHEN date_part('dow', fecha_hora)=1 THEN 'Lunes'
            				WHEN date_part('dow', fecha_hora)=2 THEN 'Martes'
            				WHEN date_part('dow', fecha_hora)=3 THEN 'Mi&eacute;rcoles'
            				WHEN date_part('dow', fecha_hora)=4 THEN 'Jueves'
            				WHEN date_part('dow', fecha_hora)=5 THEN 'Viernes'
            				WHEN date_part('dow', fecha_hora)=6 THEN 'S&aacute;bado'
            				WHEN date_part('dow', fecha_hora)=7 THEN 'Domingo'
            				ELSE '--'
				END AS dia_semana,
				date_part('dow', fecha_hora) AS dow,
				a.evento,
				a.terminal_nombre,
				td.siglas AS tipo_documento
			FROM 
				asistencia a 
				INNER JOIN persona p ON a.persona_id = p.persona_id
				INNER JOIN tipo_documento td ON p.tipo_documento_id = td.tipo_documento_id
				INNER JOIN legajo l on l.persona_id = p.persona_id
				INNER JOIN cargo c on l.legajo_id = c.legajo_id
				{$sector}
			WHERE 									
				$where
			ORDER BY a.persona_id, a.fecha_hora
		";
		$datos = consultar_fuente($sql);
		$fecha = '';
		$persona_id = $datos[0]['persona_id'];
		$fecha_hora = '';
		$contador_igual_fecha = 0;
		$contador_horas_dia = 0; //en segundos
		$contador_horas_dia_normales = 0; //en segundos
		$contador_horas_dia_extra = 0; //en segundos
		$contador_horas_totales_normales = 0; //en segundos
		$contador_horas_totales_extra = 0; //en segundos
		$contador_horas_totales = 0; //en segundos
		$tiene_horario_declarado = true;
		$j = 1;

		for ($i = 0; $i < count($datos); $i++) {

			//agrego una leyenda en la ultima columna para identificar y fue una asistencia manual o registrada desde un reloj
			if ($datos[$i]['en_reloj']) {
				$datos[$i]['fuente'] = '<font color = green>Reloj</font>';
			} else {
				$datos[$i]['fuente'] = '<font color = red><b>Manual</b></font>';
			}


			// ENTRADA. verifico si por error la persona marco entrada dos veces o más 
			if (($datos[$i]['evento'] == 1 or $datos[$i]['evento'] == 3) and (isset($datos[$i + 1]) && ($datos[$i + 1]['evento'] == 1 and $datos[$i]['fecha'] == $datos[$i + 1]['fecha']))) {
				$date1 = new DateTime($datos[$i + 1]['fecha_hora']);
				$date2 = new DateTime($datos[$i]['fecha_hora']);
				$dif = $date2->diff($date1);
				if ($dif->format("%H:%I:%S") < '00:05:00') { // que ademas la diferencia sea menor a 5 minutos
					$datos[$i + 1]['evento'] = 3; // sin encuentro dos entradas consecutivas para el mismo día cambio el evento a 3.
					$datos[$i + 1]['asume_evento'] = '';
				}
			} else {
			}

			// SALIDA. verifico si por error la persona marco salida dos veces o más. 
			if (($datos[$i]['evento'] == 0 or $datos[$i]['evento'] == 4) and (isset($datos[$i + 1]) && ($datos[$i + 1]['evento'] == 0 and $datos[$i]['fecha'] == $datos[$i + 1]['fecha']))) {
				$date1 = new DateTime($datos[$i + 1]['fecha_hora']);
				$date2 = new DateTime($datos[$i]['fecha_hora']);
				$dif = $date2->diff($date1);
				if ($dif->format("%H:%I:%S") < '00:05:00') { // que ademas la diferencia sea menor a 05 minutos
					$datos[$i + 1]['evento'] = 4; // si encuentro dos salidas consecutivas para el mismo día cambio el evento a 4.
					$datos[$i + 1]['asume_evento'] = '';
				}
			}

			// se asume que las fechas vienen ordenadas. y la entrada (1) viene primero que la salida (0)
			if ((($fecha != $datos[$i]['fecha'] or ($contador_igual_fecha % 2) == 0) and ($datos[$i]['evento'] < 3)) and $persona_id == $datos[$i]['persona_id']) { // primer hora de la misma fecha
				if ($i > 0 and $fecha != $datos[$i]['fecha']) {
					$datos[($i - ($j - 1)) - 1]['horas_dia'] = gmdate("H:i:s", (int) $contador_horas_dia); // esta en segundos, convierto al formato hora:minuto:segundo

					// horas totales. convierto a segundos y sumo.
					$contador_horas_totales += $contador_horas_dia;

					// horas normales. convierto a segundos y sumo.
					$contador_horas_totales_normales += $contador_horas_dia_normales;

					// horas extra. convierto a segundos y sumo.
					$contador_horas_totales_extra += $contador_horas_dia_extra;

					// inicializo para el dia de la posición acutal
					$contador_horas_dia = 0; //segundos
					$contador_horas_dia_normales = 0;
					$contador_horas_dia_extra = 0;
				}
				/*	elseif($i > 0 and $datos[$i]['evento'] < 3){
					// convierto a segundos y sumo.
					$partes = explode(":", $datos[$i]['horas']);
	       				$contador_horas_dia += $partes[2] + $partes[1]*60 + $partes[0]*3600;  
				}*/

				// inicializo un nuevo día
				$fecha = $datos[$i]['fecha'];
				$fecha_hora = $datos[$i]['fecha_hora'];
				$contador_igual_fecha = 1;
				$datos[$i]['asume_evento'] = 1; // sin importar que marcó (entrada o salida) se asume que quiso marcar una entrada

				// si es el primer fichaje del día y no es 1. marco como posible error.
				if ($datos[$i]['evento'] == 0) { // es salida y debería ser entrada.
					$datos[$i]['evento'] = 5;
					//$datos[$i]['asume_evento'] = 1;
				}
			} elseif (((($contador_igual_fecha % 2) <> 0) and ($datos[$i]['evento'] < 3)) and ($persona_id == $datos[$i]['persona_id'])) {
				// el evento 3 se le asigna al principio de esta funcion. indica que se leyo por error mas de una vez,,
				// si es la misma fecha quiere decir que estoy ante la primer salida
				$date1 = new DateTime($fecha_hora); // $i-1
				$date2 = new DateTime($datos[$i]['fecha_hora']);
				$dif = $date2->diff($date1);
				$datos[$i]['horas'] = $dif->format('%H:%I:%S');
				$contador_igual_fecha++;

				$datos[$i]['asume_evento'] = 0; // sin importar que marcó (entrada o salida) se asume que quiso marcar una salida

				// convierto a segundos y sumo.
				$partes = explode(":", $datos[$i]['horas']);
				$contador_horas_dia += $partes[2] + $partes[1] * 60 + $partes[0] * 3600;

				$horasEyN = dao_reportes::cuantas_horas_extra_y_normales($persona_id, $fecha_hora, $datos[$i]['fecha_hora']);
				if ($horasEyN['normales'] <> -1) {
					$contador_horas_dia_normales += $horasEyN['normales']; //en segundos
					$contador_horas_dia_extra += $horasEyN['extras']; //en segundos
					$datos[$i]['horas_normales'] = gmdate("H:i:s", (int) $horasEyN['normales']);
					$datos[$i]['horas_extra'] = gmdate("H:i:s", (int) $horasEyN['extras']);
					$tiene_horario_declarado = true;
				} else {
					$tiene_horario_declarado = false;
					$datos[$i]['horas_normales'] = 'Debe declarar un horario.';
					$datos[$i]['horas_extra'] = 'Debe declarar un horario.';
				}
			} elseif ($persona_id != $datos[$i]['persona_id']) { // cambia de persona, ya estoy en el primer registro de la siugiente personas por eso voy a usar $i-1
				$datos[($i - 1) - $j]['horas_dia'] = gmdate("H:i:s", (int) $contador_horas_dia); // esta en segundos, convierto al formato hora:minuto:segundo


				// horas totales. convierto a segundos y sumo.
				$contador_horas_totales += $contador_horas_dia; //horas totales esta en segundos. convierto las horas y mninutos a segundos y sumo.
				$contador_horas_totales_normales += $contador_horas_dia_normales;
				$contador_horas_totales_extra += $contador_horas_dia_extra;



				// horas totales de la persona sin discriminar normales y extra
				$horas = floor($contador_horas_totales / 3600); // floor redondea hacia abajo si tiene decimales.
				$minutos = floor(($contador_horas_totales - ($horas * 3600)) / 60);
				$segundos = $contador_horas_totales - ($horas * 3600) - ($minutos * 60);

				// para que tenga la forma 00:00:00 y no 00:0:00 (dos digitos)
				if ($horas < 10)
					$horas = "0$horas";
				if ($minutos < 10)
					$minutos = "0$minutos";
				if ($segundos < 10)
					$segundos = "0$segundos";

				$datos[$i - 1]['horas_totales'] = "$horas:$minutos:$segundos"; // esta en segundos, convierto al formato hora:minuto:segundo

				// horas normales de la persona
				$horas = floor($contador_horas_totales_normales / 3600); // floor redondea hacia abajo si tiene decimales.
				$minutos = floor(($contador_horas_totales_normales - ($horas * 3600)) / 60);
				$segundos = $contador_horas_totales_normales - ($horas * 3600) - ($minutos * 60);

				if ($horas < 10)
					$horas = "0$horas";
				if ($minutos < 10)
					$minutos = "0$minutos";
				if ($segundos < 10)
					$segundos = "0$segundos";

				$datos[$i - 1]['horas_totales_normales'] = "$horas:$minutos:$segundos"; // esta en segundos, convierto al formato hora:minuto:segundo

				// horas extra de la persona.
				$horas = floor($contador_horas_totales_extra / 3600); // floor redondea hacia abajo si tiene decimales.
				$minutos = floor(($contador_horas_totales_extra - ($horas * 3600)) / 60);
				$segundos = $contador_horas_totales_extra - ($horas * 3600) - ($minutos * 60);

				if ($horas < 10)
					$horas = "0$horas";
				if ($minutos < 10)
					$minutos = "0$minutos";
				if ($segundos < 10)
					$segundos = "0$segundos";

				$datos[$i - 1]['horas_totales_extra'] = "$horas:$minutos:$segundos"; // esta en segundos, convierto al formato hora:minuto:segundo

				//----- se inicializa las variables para la nueva pesona.
				$persona_id = $datos[$i]['persona_id']; // la nueva persona
				$fecha = $datos[$i]['fecha'];
				$fecha_hora = $datos[$i]['fecha_hora'];
				$contador_igual_fecha = 1;
				$datos[$i]['asume_evento'] = 1; // sin importar que marcó (entrada o salida) se asume que quiso marcar una entrada

				// si es el primer fichaje del día y no es 1. marco como posible error.
				if ($datos[$i]['evento'] == 0) { // es salida y debería ser entrada.
					$datos[$i]['evento'] = 5;
					//$datos[$i]['asume_evento'] = 1;
				}
				//----- fin inicializacion

				$contador_horas_totales = 0;
				$contador_horas_totales_normales = 0;
				$contador_horas_totales_extra = 0;
				$contador_horas_dia = 0;
				$contador_horas_dia_normales = 0;
				$contador_horas_dia_extra = 0;
			}


			// para la última posición.
			if ($i == count($datos) - 1) {
				$datos[$i]['horas_dia'] = gmdate('H:i:s', (int) $contador_horas_dia); // esta en segundos, convierto al formato hora:minuto:segundo
				// horas totales. convierto a segundos y sumo.
				//$partes2 = explode(":", $datos[$i]['horas_dia']);
				$contador_horas_totales += $contador_horas_dia; //horas totales esta en segundos. convierto las horas y mninutos a segundos y sumo.
				$contador_horas_totales_normales += $contador_horas_dia_normales;
				$contador_horas_totales_extra += $contador_horas_dia_extra;


				// horas totales de la persona sin discriminar normales y extra
				$horas = floor($contador_horas_totales / 3600); // floor redondea hacia abajo si tiene decimales.
				$minutos = floor(($contador_horas_totales - ($horas * 3600)) / 60);
				$segundos = $contador_horas_totales - ($horas * 3600) - ($minutos * 60);

				// para que tenga la forma 00:00:00 y no 00:0:00 (dos digitos)
				if ($horas < 10)
					$horas = "0$horas";
				if ($minutos < 10)
					$minutos = "0$minutos";
				if ($segundos < 10)
					$segundos = "0$segundos";

				$datos[$i]['horas_totales'] = "$horas:$minutos:$segundos"; // esta en segundos, convierto al formato hora:minuto:segundo

				// horas normales de la persona
				$horas = floor($contador_horas_totales_normales / 3600); // floor redondea hacia abajo si tiene decimales.
				$minutos = floor(($contador_horas_totales_normales - ($horas * 3600)) / 60);
				$segundos = $contador_horas_totales_normales - ($horas * 3600) - ($minutos * 60);

				if ($horas < 10)
					$horas = "0$horas";
				if ($minutos < 10)
					$minutos = "0$minutos";
				if ($segundos < 10)
					$segundos = "0$segundos";

				$datos[$i]['horas_totales_normales'] = "$horas:$minutos:$segundos"; // esta en segundos, convierto al formato hora:minuto:segundo

				// horas extra de la persona.
				$horas = floor($contador_horas_totales_extra / 3600); // floor redondea hacia abajo si tiene decimales.
				$minutos = floor(($contador_horas_totales_extra - ($horas * 3600)) / 60);
				$segundos = $contador_horas_totales_extra - ($horas * 3600) - ($minutos * 60);

				if ($horas < 10)
					$horas = "0$horas";
				if ($minutos < 10)
					$minutos = "0$minutos";
				if ($segundos < 10)
					$segundos = "0$segundos";

				$datos[$i]['horas_totales_extra'] = "$horas:$minutos:$segundos"; // esta en segundos, convierto al formato hora:minuto:segundo
			}

			// vierifico la marcación y depende del resultado (entro tarde, salió antes, vino en un horario que no esta declarado) muestro en colores.
			// $fecha_hora se la tomo como la hora de entrada. si estoy ante una etrada es la misma que el siguiente parámetro

			switch (self::validar_marcacion($datos[$i]['persona_id'], $datos[$i]['fecha_hora'], $datos[$i]['evento'], $datos[$i]['asume_evento'])) {
				case 'C':
					$datos[$i]['evento'] = '<font color=green>' . (($datos[$i]['evento'] == 1) ? 'Entrada' : 'Salida') . '</font>';
					$datos[$i]['asume_evento'] = (($datos[$i]['asume_evento'] == 1) ? 'Entrada' : 'Salida');
					break;
				case 'T':
					$datos[$i]['evento'] = '<font color=red>Entrada</font>';
					$datos[$i]['asume_evento'] = 'Entrada';
					break;
				case 'A':
					$datos[$i]['evento'] = '<font color=red>Salida</font>';
					$datos[$i]['asume_evento'] = 'Salida';
					break;
				case 'R':
					$datos[$i]['evento'] = '<font color=orange>' . (($datos[$i]['evento'] == 3) ? 'Entrada' : 'Salida') . '</font>';
					$datos[$i]['asume_evento'] = (($datos[$i]['asume_evento'] == 1) ? 'Entrada' : (($datos[$i]['asume_evento'] == '') ? 'Ingnorado' : ''));
					break;
				case 'N':
					$datos[$i]['evento'] = '<font color=blue>' . (($datos[$i]['evento'] == 1) ? 'Entrada' : 'Salida') . '</font>';
					$datos[$i]['asume_evento'] = (($datos[$i]['asume_evento'] == 1) ? 'Entrada' : 'Salida');
					break;
			}

			// agrego los días que faltan.. faltas y sus justificaciones si existen (licencia, feriados, etc)
			if ($tiene_horario_declarado) {
				$proxima_fecha = ((count($datos) - 1) > $i) ? $datos[$i + 1]['fecha'] : '';
				$aux_fecha = date('Y-m-d', strtotime($datos[$i]['fecha'] . '+ 1 days'));

				$nuevos = array();
				$j = 1;
				while (strtotime(trim($aux_fecha)) < strtotime(trim($proxima_fecha))) { // recorro todas las fechas que faltan en sus fichadas
					if (dao_empleado::es_dia_de_trabajo($datos[$i]['persona_id'], $aux_fecha)) { // no vino y es un día declarado por el trabajador, dow es el numero de dia de la semana
						$nuevos = $datos[$i];
						$nuevos['fecha'] = $aux_fecha;
						$nuevos['fecha_hora'] = $aux_fecha;
						$nuevos['hora'] = '-';
						$nuevos['horas'] = '-';
						$nuevos['horas_dia'] = '-';
						$nuevos['horas_normales'] = '-';
						$nuevos['horas_extra'] = '-';
						$nuevos['horas_totales'] = '-';
						$nuevos['horas_totales_normales'] = '-';
						$nuevos['horas_totales_extra'] = '-';

						$dias = array('Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado');
						$nuevos['dia_semana'] = $dias[date('w', strtotime($aux_fecha))];

						$nuevos['evento'] = '<font color=red> <b>Inasistencia</b> </font>';
						$nuevos['asume_evento'] = 'Inasistencia';
						$nuevos['terminal_nombre'] = '-';

						// es una licencia?
						if ($licencia = dao_licencia::get_licencia_x_fecha_persona($datos[$i]['persona_id'], $aux_fecha)) {
							$nuevos['asume_evento'] = '<font color=green><b>' . $licencia['nombre_tipo_lic'] . '</b></font>';
						}

						// es un dia no laborable
						if ($no_laboral = dao_configuracion::es_dia_no_laborable($aux_fecha)) {
							$nuevos['asume_evento'] = '<font color=green><b>' . $no_laboral['motivo'] . '</b></font>';
						}

						// se agrega el dia en vacío para que lo puedan visualizar en el reporte
						$datos = self::insertToArray($nuevos, $i + $j, $datos);

						$j++;
						$nuevos = array();
					}
					$aux_fecha = date('Y-m-d', strtotime($aux_fecha . '+ 1 days'));
				}
			}

			// si agregó dias de inasistencia corrijo la posición de $i
			$i = $i + ($j - 1); // salteo las pociciones que agreugue	
		}
		return $datos;
	}

	static function horas_extra_de_un_agente_en_un_periodo($filtro = array())
	{
		$datos = self::get_horas_trabajadas($filtro);
		$horas = 0;
		$horas_normales = 0;
		$horas_extras = 0;
		$dat = array();
		foreach ($datos as $fichada) {
			if ($fichada['asume_evento'] == 'Salida' and $fichada['horas'] > '00:00:00') {
				// convierto a segundos y sumo.
				$partes = explode(':', $fichada['horas']);
				$horas += $partes[2] + $partes[1] * 60 + $partes[0] * 3600;
			}
			if ($fichada['asume_evento'] == 'Salida' and $fichada['horas_normales'] > '00:00:00') {
				$partes = explode(':', $fichada['horas_normales']);
				$horas_normales += $partes[2] + $partes[1] * 60 + $partes[0] * 3600;
			}
			if ($fichada['asume_evento'] == 'Salida' and $fichada['horas_extra'] > '00:00:00') {
				$partes = explode(':', $fichada['horas_extra']);
				$horas_extras += $partes[2] + $partes[1] * 60 + $partes[0] * 3600;
			}
		}

		if ($horas > 0) {
			// horas de la persona.
			$choras = floor($horas / 3600); // floor redondea hacia abajo si tiene decimales.
			$cminutos = floor(($horas - ($choras * 3600)) / 60);
			$csegundos = $horas - ($choras * 3600) - ($cminutos * 60);

			if ($choras < 10)
				$choras = "0$choras";
			if ($cminutos < 10)
				$cminutos = "0$cminutos";
			if ($csegundos < 10)
				$csegundos = "0$csegundos";
			$dat['horas_totales'] = "$choras:$cminutos:$csegundos"; // esta en segundos, convierto al formato hora:minuto:segundo

			// horas normales de la persona.
			$choras = floor($horas_normales / 3600);
			$cminutos = floor(($horas_normales - ($choras * 3600)) / 60);
			$csegundos = $horas_normales - ($choras * 3600) - ($cminutos * 60);

			if ($choras < 10)
				$choras = "0$choras";
			if ($cminutos < 10)
				$cminutos = "0$cminutos";
			if ($csegundos < 10)
				$csegundos = "0$csegundos";
			$dat['horas_normales'] = "$choras:$cminutos:$csegundos";


			// horas extras de la persona.
			$choras = floor($horas_extras / 3600);
			$cminutos = floor(($horas_extras - ($choras * 3600)) / 60);
			$csegundos = $horas_extras - ($choras * 3600) - ($cminutos * 60);

			if ($choras < 10)
				$choras = "0$choras";
			if ($cminutos < 10)
				$cminutos = "0$cminutos";
			if ($csegundos < 10)
				$csegundos = "0$csegundos";
			$dat['horas_extras'] = "$choras:$cminutos:$csegundos";

			if (isset($datos) && is_array($datos) && count($datos) > 0) {
				$dat['agente'] = $datos[0]['ape_nom'];
				$dat['numero_documento'] = $datos[0]['numero_documento'];
				$dat['legajo'] = $datos[0]['legajo'];
			}
		}
		return $dat;
	}

	static function get_listado_horas_extra($filtro = array())
	{
		$datos = self::get_horas_trabajadas($filtro);
		$horas = 0;
		$horas_normales = 0;
		$horas_extras = 0;
		$listado = array();
		foreach ($datos as $fichada) {

			if (isset($fichada['horas_totales']) && $fichada['horas_totales'] != '-') {
				$dat = array();
				$dat['agente'] = $fichada['ape_nom'];
				$dat['horas_totales'] = $fichada['horas_totales'];
				$dat['horas_totales_extra'] = $fichada['horas_totales_extra'];
				$dat['horas_totales_normales'] = $fichada['horas_totales_normales'];
				$listado[] = $dat;
			}
		}
		return $listado;
	}

	static function insertToArray($value, $pos, $array)
	{
		$carray = count($array);
		if ($pos > $carray || $pos < 0) {
			return false;
		}

		if ($pos == $carray) {
			$array[] = $value;
			return $array;
		}

		$newArray = array();
		for ($i = 0; $i < $carray; $i++) {
			if ($i == $pos) {
				$newArray[] = $value;
				$newArray[] = $array[$i];
			} else {
				$newArray[] = $array[$i];
			}
		}
		return $newArray;
	}

	static function limpiar_tabla_horas_totales($usuario_id)
	{
		$limpieza = "DELETE FROM horas_totales WHERE usuario_id = '{$usuario_id}'";
		return consultar_fuente($limpieza);
	}
}
