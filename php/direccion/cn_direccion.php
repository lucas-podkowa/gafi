<?php
include_once 'direccion/dao_direccion.php';

class cn_direccion extends gafi_cn
{
    protected $s__id_pais;
    protected $s__id_provincia;
    protected $s__id_departamento;

    // ************* Pais ************* //

    public function cargar_pais($id)
    {
        $this->dep('pais')->cargar($id);
        //$pais = $this->dep('pais')->get();
    }

    public function get_paises($filtro = null)
    {
        return $this->dep('pais')->get_filas($filtro, false);
    }

    public function get_pais($id)
    {
        $id_interno = $this->dep('pais')->get_id_fila_condicion($id);
        $this->dep('pais')->set_cursor($id_interno[0]);
        return $this->dep('pais')->get();
    }

    // ************* Provincia ************* //

    public function get_provincias($filtro = null)
    {
        $datos = $this->dep('provincia')->get_filas($filtro, false);
        return consultas::ordenar_algoritmo_burbuja($datos, 'nombre');
    }

    public function get_provincia($id_registro)
    {
        return $this->dep('provincia')->get($id_registro);
    }

    public function cargar_provincia($id)
    {
        $this->dep('provincia')->cargar($id);
    }

    // ************* Localidad ************* //

    public function agregar_localidad($registro)
    {

        $existe = dao_direccion::get_id_localidad($registro['nombre'], $registro['codigo_postal']);
       
		if (is_null($existe)) {
			
            $registro['nombre'] = strtoupper($registro['nombre']);
            $id = $this->dep('localidad')->nueva_fila($registro);
            $this->dep('localidad')->set_cursor($id);
            $this->dep('localidad')->sincronizar();
            $this->dep('localidad')->resetear();
        } else {
            throw new Exception('La Localidad ' . $registro['nombre'] . ' ya existe en la Base de Datos');
        }

    }

    /*

function agregar_pais($registro)
{
//// ----------------------SYNC---------------------- ////
// el campo "nodo" utilizado para la sicronizacion de las tablas entre talleres.
$registro['nodo'] = dao_taller::get_id_taller();
//// ----------------------SYNC---------------------- ////

$id = $this->dep('dr_direccion')->tabla('pais')->nueva_fila($registro);
$this->dep('dr_direccion')->tabla('pais')->set_cursor($id);
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
}

function modificar_pais($registro)
{
$provincias = $this->dep('dr_direccion')->tabla('provincia')->get_filas();
if (empty($provincias)) {
$this->dep('dr_direccion')->tabla('pais')->set($registro);
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
return true;
}else{
return false;
}
}

function eliminar_pais()
{
$provincias = $this->dep('dr_direccion')->tabla('provincia')->get_filas();
if (empty($provincias)) {
$this->dep('dr_direccion')->tabla('pais')->eliminar();
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
return true;
}else{
return false;
}
}

function get_paises($filtro=null)
{
if ($this->dep('dr_direccion')->esta_cargada()) {
return $this->dep('dr_direccion')->tabla('pais')->get_filas($filtro, false);
}
}

function get_id_pais()
{
return $this->s__id_pais;
}

function agregar_provincia($registro)
{
//// ----------------------SYNC---------------------- ////
// el campo "nodo" utilizado para la sicronizacion de las tablas entre talleres.
$registro['nodo'] = dao_taller::get_id_taller();
//// ----------------------SYNC---------------------- ////
$registro['nombre'] = strtoupper($registro['nombre']);
$id = $this->dep('dr_direccion')->tabla('provincia')->nueva_fila($registro);
$this->dep('dr_direccion')->tabla('provincia')->set_cursor($id);
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
}

function modificar_provincia($registro)
{
$departamentos = $this->dep('dr_direccion')->tabla('departamento')->get_filas();
if (empty($departamentos)) {
$registro['nombre'] = strtoupper($registro['nombre']);
$this->dep('dr_direccion')->tabla('provincia')->set($registro);
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
return true;
}else{
return false;
}
}

function eliminar_provincia($id)
{
$departamentos = $this->dep('dr_direccion')->tabla('departamento')->get_filas();
if (empty($departamentos)) {
$this->dep('dr_direccion')->tabla('provincia')->eliminar_fila($this->dep('dr_direccion')->tabla('provincia')->get_cursor());
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
return true;
}else{
return false;
}
}

function seleccionar_provincia($id_registro)
{
if ($this->dep('dr_direccion')->esta_cargada()) {
$id_interno = $this->dep('dr_direccion')->tabla('provincia')->get_id_fila_condicion($id_registro);
$this->dep('dr_direccion')->tabla('provincia')->set_cursor($id_interno[0]);
$provincia = $this->dep('dr_direccion')->tabla('provincia')->get();
$this->s__id_provincia = "{$provincia['id_provincia']}&{$provincia['nodo']}";
}
}

function get_id_provincia()
{
return $this->s__id_provincia;
}

// ************* Localidad ************* //

function agregar_localidad($registro)
{

//// ----------------------SYNC---------------------- ////
// el campo "nodo" utilizado para la sicronizacion de las tablas entre talleres.
$registro['nodo'] = dao_taller::get_id_taller();
//// ----------------------SYNC---------------------- ////
$registro['nombre'] = strtoupper($registro['nombre']);
$id = $this->dep('dr_direccion')->tabla('localidad')->nueva_fila($registro);
$this->dep('dr_direccion')->tabla('localidad')->set_cursor($id);
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
}

function modificar_localidad($registro)
{
$registro['nombre'] = strtoupper($registro['nombre']);
$this->dep('dr_direccion')->tabla('localidad')->set($registro);
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
}

function eliminar_localidad($id)
{
$this->dep('dr_direccion')->tabla('localidad')->eliminar_fila($this->dep('dr_direccion')->tabla('localidad')->get_cursor());
$this->dep('dr_direccion')->sincronizar();
$this->dep('dr_direccion')->resetear();
}

function get_localidades($filtro=null)
{
if ($this->dep('dr_direccion')->esta_cargada()) {
$datos = $this->dep('dr_direccion')->tabla('localidad')->get_filas($filtro, false);
return consultas::ordenar_algoritmo_burbuja($datos, 'nombre');
}
}

function get_localidad($id_registro)
{
if ($this->dep('dr_direccion')->esta_cargada()) {
return $this->dep('dr_direccion')->tabla('localidad')->get();
}
}

function seleccionar_localidad($id_registro)
{
if ($this->dep('dr_direccion')->esta_cargada()) {
$id_interno = $this->dep('dr_direccion')->tabla('localidad')->get_id_fila_condicion($id_registro);
$this->dep('dr_direccion')->tabla('localidad')->set_cursor($id_interno[0]);
}
}
 */
}
