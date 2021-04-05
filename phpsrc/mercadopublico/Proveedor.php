<?php
namespace corsica\regulb\mercadopublico;
//Clase padre para heredar cone

class Proveedor extends \corsica\regulb\DbMySql
{

public function	cargarProveedor($proveedor) {
	$sql = "insert ignore into t_proveedor (id,nombre,rut,comuna,region) values ";

	$sql.= "(".$this->numero($proveedor->Codigo).",".$this->texto($proveedor->Nombre);
	$sql.= ",".$this->texto($proveedor->RutSucursal).",".$this->texto($proveedor->Comuna).",".$this->texto($proveedor->Region).")";
	return $this->insert($sql);
}

}