<?php
namespace corsica\regulb\mercadopublico;
//Clase padre para heredar cone

class Comprador extends \corsica\regulb\DbMySql
{

public function	cargarCompradores($lista) {
	$sql = "insert ignore into t_comprador (id,nombre) values ";
	$s = " ";
	foreach($lista as $row) {
		$sql.= $s."(".$this->numero($row->CodigoEmpresa).",".$this->texto($row->NombreEmpresa).")";
		$s=",";
	}
	return $this->insert($sql);
}

}