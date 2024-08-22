<?php

// se debe agragar la ejecución de este bach en cron cada cieto tiempo (ej cada 5 min)
// # toba item ejecutar -p comedorfi -t 103000008 -u toba

require 'zklibrary/zklibrary.php';


// obtengo los terminales activos
$sentencia = "SELECT nombre, ip, puerto FROM terminal WHERE activo = true;";
$terminales = toba::db()->consultar($sentencia);

foreach ($terminales as $t) {
	$zk = new ZKLibrary($t['ip'], $t['puerto']);
	$zk->connect();
	$zk->disableDevice();

	try {
		$fichadas = $zk->getAttendance();
		$terminal_nombre = $t['nombre'];
		foreach ($fichadas as $registro) {
			$sentencia = "INSERT INTO asistencia (persona_id, evento, fecha_hora, terminal_nombre, en_reloj) VALUES ($registro[1],$registro[2], " . "'$registro[3]', '$terminal_nombre', true);";
			//echo $sentencia . "\n";
			toba::db()->ejecutar($sentencia);
		}

		// borro las fichadas del reloj. ya esta guardado en la bd
		$zk->clearAttendance();
	} catch (exception $e) {
		echo 'no se pudo bajar el fichaje';
	}


	// aprovecho para sincroniza con la hora del servidor.
	//$zk->setTime(date('Y-m-d H:i:s'));

	$zk->enableDevice();
	$zk->disconnect();
}
