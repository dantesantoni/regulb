<?php

namespace corsica\regulb\mercadopublico;
//Clase padre para heredar cone

class OCompra extends \corsica\regulb\DbMySql
{
	/**
	 * Esta se realiza para cargar el listado general
	 */
	//INSERT INTO `t_ocompra`(`id`, `codigo`, `nombre`, `idestado`, `idcomprador`, `idvendedor`) 
	public function	cargarOCompras($fecha, $lista)
	{
		$sql = "insert ignore into t_ocompra (fecha,codigo,nombre,idestado) values ";
		$s = " ";
		foreach ($lista as $row) {
			$sql .= $s . "(" . $this->fecha($fecha) . "," . $this->texto($row->Codigo);
			$sql .=  "," . $this->texto($row->Nombre) . "," . $this->numero($row->CodigoEstado) . ")";
			$s = ",";
		}
		return $this->insert($sql);
	}

	public function	actualizarOC($ocompra)
	{
		$id = $this->select1("select id from t_ocompra where codigo = ".$this->texto($ocompra->Codigo));

		$p = new Proveedor($this->getConexion());
		$p->cargarProveedor($ocompra->Proveedor);

		$sql = "update t_ocompra set ";
		$sql .= "  idcomprador =" . $this->numero($ocompra->Comprador->CodigoOrganismo);
		$sql .= " ,idproveedor =" . $this->numero($ocompra->Proveedor->Codigo);
		$sql .= " ,tipo =" . $this->texto($ocompra->Tipo);
		$sql .= " ,moneda =" . $this->texto($ocompra->TipoMoneda);
		$sql .= " ,neto =" . $this->numero($ocompra->TotalNeto);
		$sql .= " ,iva =" . $this->numero($ocompra->Impuestos);
		$sql .= " ,total =" . $this->numero($ocompra->Total);
		$sql .= " where id =" . $id;
		$this->update($sql);
		$this->cargarDetalles($id,$ocompra->Items->Listado);

	}
	public function	cargarDetalles($idocompra,$detalles) {
		$this->delete("delete from t_ocompra_detalle where idocompra = ".$idocompra);
		foreach($detalles as $d) {
			$this->insertDetalle($idocompra,$d);
		}
	}
	private function	insertDetalle($idocompra,$detalle) {
		$this->insertCategoria($detalle->CodigoCategoria,$detalle->Categoria);
		$this->insertProducto($detalle->CodigoCategoria,$detalle->Producto);
		$sql = "insert into t_ocompra_detalle( idocompra, idcategoria, idproducto, descripcion, cantidad, neto, total) values ";
		$sql .= "(".$this->numero($idocompra);
		$sql .= ",".$this->numero($detalle->CodigoCategoria);
		$sql .= ",".$this->numero($detalle->CodigoProducto);
		$sql .= ",".$this->texto($detalle->EspecificacionComprador);
		$sql .= ",".$this->numero($detalle->Cantidad);
		$sql .= ",".$this->numero($detalle->PrecioNeto);
		$sql .= ",".$this->numero($detalle->Total).")";
		return $this->insert($sql);
	}
	private function	insertCategoria($id,$nombre) {
		$sql = "insert ignore into t_categoria( id, nombre) values ";
		$sql .= "(".$this->numero($id);
		$sql .= ",".$this->texto($nombre).")";
		return $this->insert($sql);
	}
	private function	insertProducto($id,$nombre) {
		$sql = "insert ignore into t_producto( id, nombre) values ";
		$sql .= "(".$this->numero($id);
		$sql .= ",".$this->texto($nombre).")";
		return $this->insert($sql);
	}

/**
 * 
 */
	public function	listarIncompletas() {
		return $this->selectObj("select codigo from t_ocompra where idcomprador = 0 order by id");
	}

}