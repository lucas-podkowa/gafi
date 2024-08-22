<?php
include_once 'configuracion/dao_configuracion.php';

require 'zklibrary/zklibrary.php'; // no funciona el enrolamiento (startEnroll)

class cn_terminal extends gafi_cn
{    
	// ###################################################################################
	//----  PREFERENCIA  -----------------------------------------------------------------
	// ###################################################################################

	function cargar_terminal($id)
	{
		$this->dep('terminal')->cargar($id);
	}
	
	function esta_cargada_terminal()
	{
		return $this->dep('terminal')->esta_cargada();
	}

	function resetear_terminal()
	{
		$this->dep('terminal')->resetear();
	}

	function get_terminal($id)
	{	
		if ($this->dep('terminal')->esta_cargada()) {
			$id_interno = $this->dep('terminal')->get_id_fila_condicion($id);
			$this->dep('terminal')->set_cursor($id_interno[0]);
			return $this->dep('terminal')->get();
		}
	}

	function agregar_terminal($registro)
	{
		$id = $this->dep('terminal')->nueva_fila($registro);
		$this->dep('terminal')->set_cursor($id);
		$this->dep('terminal')->sincronizar();
		$this->dep('terminal')->resetear();
	}

	function modificar_terminal($registro)
	{
		if ($this->dep('terminal')->esta_cargada()) {
			$this->dep('terminal')->set($registro);
			$this->dep('terminal')->sincronizar();
			$this->dep('terminal')->resetear();
		}
	}
	
	function eliminar_terminal()
	{
		if ($this->dep('terminal')->esta_cargada()) {
			$this->dep('terminal')->eliminar_fila($this->dep('terminal')->get_cursor());
			$this->dep('terminal')->sincronizar();
			$this->dep('terminal')->resetear();
		}	
	}
	
	
	
	// ###################################################################################
	//----  R E L O J  ---------------------------------------------------------------------
	// ###################################################################################
    
    // Métodos para el acceso a los terminales - API
	function terminal_desconectar()
	{

		 $this->dep('terminal')->cargar();
		$id_interno = $this->dep('terminal')->get_id_fila_condicion(array('maestro' => false), false);
		$this->dep('terminal')->set_cursor($id_interno[0]);
		$datos = $this->dep('terminal')->get();
		
		$zk = new ZKLibrary($datos['ip'], $datos['puerto']);
		$zk->connect();
		$zk->enableDevice();
		$zk->disconnect();
	
		
	}
	
	// set_usuario: alta y modificación de usuarios en el reloj
	function terminal_set_usuario($datos)
	{
		// obtengo el terminal maestro

		$this->dep('terminal')->cargar();
		$id_interno = $this->dep('terminal')->get_id_fila_condicion(array('maestro' => true), false);
		$this->dep('terminal')->set_cursor($id_interno[0]);
		$terminal = $this->dep('terminal')->get();
		
			
		
		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();

		$zk->setUser($datos['persona_id'], $datos['persona_id'], $datos['nombre'] . ' ' . $datos['apellido'], null, 0);
		
		$zk->enableDevice();
		$zk->disconnect();
		
		
	}
	
	// set_usuario: alta y modificación de usuarios en el reloj
	function terminal_eliminar_usuario($persona_id)
	{
		// obtengo el terminal maestro

		$this->dep('terminal')->cargar();
		$id_interno = $this->dep('terminal')->get_id_fila_condicion(array('maestro' => true), false);
		$this->dep('terminal')->set_cursor($id_interno[0]);
		$terminal = $this->dep('terminal')->get();
		
			
		
		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();

		$zk->deleteUser($datos['persona_id']);
		
		$zk->enableDevice();
		$zk->disconnect();
		
		
	}
	
	function terminal_enrolar($persona_id, $finger)
	{
		// obtengo el terminal maestro (enrolador normalmente ubicado en RRHH)
		$this->dep('terminal')->cargar();
		$id_interno = $this->dep('terminal')->get_id_fila_condicion(array('maestro' => true), false);
		$this->dep('terminal')->set_cursor($id_interno[0]);
		$terminal = $this->dep('terminal')->get();
		

		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();
		
		if($zk->getUserTemplate($persona_id, $finger)){
			$zk->deleteUserTemp($persona_id, $finger);
		}
		$r = $zk->startEnroll($persona_id, $finger);
		
		$zk->enableDevice();
		$zk->disconnect();
		
		return $r;
	}
	
	function terminal_sincronizar_huellas($persona_id, $datos)
	{
		// obtengo el terminal maestro (enrolador normalmente ubicado en RRHH)
		$this->dep('terminal')->cargar();
		$id_interno = $this->dep('terminal')->get_id_fila_condicion(array('maestro' => true), false);
		$this->dep('terminal')->set_cursor($id_interno[0]);
		$terminal = $this->dep('terminal')->get();
		
		// me conecto y deshabilito para que no se utilice mientras hago la transacción
		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();
		
		// obtengo de la persona seleccionada las dos huellas que vamos a usar ($persona_id)
		$template = $zk->getUserTemplateAll($persona_id);
		
		$zk->enableDevice();
		$zk->disconnect();
		
		
		
		// ahora obtengo todos los terminales activos que no sean el maestro para enviar a todos las nuevas huellas.
		$terminales = $this->dep('terminal')->get_filas(null, false);
		foreach($terminales as $t){
			// verifico que no sea el enrolador (maestro) y este activo
			if((!$t['maestro']) && $t['activo']){ 
				$zk = new ZKLibrary($t['ip'], $t['puerto']);
				$zk->connect();
				$zk->disableDevice();
				
				// si no existe el usuario en el terminal se debe dar de alta (id_interno, id app, nombre, contraseña, rol))
				// si no existe la crea de lo contrario modifica los datos
				$zk->setUser($persona_id, $persona_id, $datos['nombre'] . ' ' . $datos['apellido'], null, 0);
				
				// elimino las huellas que porían existir. Sinó no va a poder subir las nuevas en el mismo lugar si ya existen.
				//$zk->deleteUserTemp($persona_id, null);
				//
				// subo las nuevas huellas que vienen del maestro (enrolador)
				// en el reloj en la parte de sistema->sistema la version de arlgoritomo tiene que estar en "Finger VX10.x"
				//
				for($i = 0; $i <= 9; $i++){
					if($zk->getUserTemplate($persona_id, $i)){
						$zk->deleteUserTemp($persona_id, $i);
					}
				}
				
				
				for($i = 0; $i <= 9; $i++){
					if(count($template[$i]) > 0){ //si vino una huella en esta posición.
						$zk->setUserTemplate($template[$i]); //la envío al otro terminal
					}
				}
							
				$zk->enableDevice();
				$zk->disconnect();

			}
		}
	}
	
	function terminal_cantidad_huellas_usuario($persona_id)
	{
		// obtengo el terminal maestro

		$this->dep('terminal')->cargar();
		// obtengo las huellas de maestro ya que estan sincronizados todos los terminales
		$id_interno = $this->dep('terminal')->get_id_fila_condicion(array('maestro' => true), false);
		$this->dep('terminal')->set_cursor($id_interno[0]);
		$terminal = $this->dep('terminal')->get();
		
			
		
		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();

		$huellas = $zk->getUserTemplateAll($persona_id);
		$count = 0;
		for($i=0; $i < count($huellas); $i++){
			if(count($huellas[$i]) > 0){
				$count++;
			}
		}
		
		$zk->enableDevice();
		$zk->disconnect();
		return $count;
	}
	
	// devuelve en que posiciones esta cada huella del usuario. Si las tiene.
	function terminal_get_ubicacion_huellas_usuario($persona_id)
	{
		// obtengo el terminal maestro

		$this->dep('terminal')->cargar();
		// obtengo las huellas de maestro ya que estan sincronizados todos los terminales
		$id_interno = $this->dep('terminal')->get_id_fila_condicion(array('maestro' => true), false);
		$this->dep('terminal')->set_cursor($id_interno[0]);
		$terminal = $this->dep('terminal')->get();
		
			
		
		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();

		$huellas = $zk->getUserTemplateAll($persona_id);
		$posiciones = array();
		for($i=0; $i < count($huellas); $i++){
			if(count($huellas[$i]) > 0){
				$posiciones[] = $huellas[$i][2]; // en la posición 3 esta el fingerID
			}
		}
		
		$zk->enableDevice();
		$zk->disconnect();
		return $posiciones;
	}
	
	function terminal_sincronizar_hora($terminal_id)
	{
		// obtengo datos del terminal (IP y puerto)
		$this->dep('terminal')->cargar($terminal_id);
		$terminal = $this->dep('terminal')->get();
		
		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();

		$zk->setTime(date('Y-m-d H:i:s'));
				
		$zk->enableDevice();
		$zk->disconnect();
	}
	
	function terminal_set_password($terminal_id, $datos)
	{
		// obtengo datos del terminal (IP y puerto)
		$this->dep('terminal')->cargar($terminal_id);
		$terminal = $this->dep('terminal')->get();
		
		$zk = new ZKLibrary($terminal['ip'], $terminal['puerto']);
		$zk->connect();
		$zk->disableDevice();

		// no existe una función para cambiar el password del terminal
		// $zk->alguna-funcion();		
		$zk->enableDevice();
		$zk->disconnect();
	}
	
	
}
?>
