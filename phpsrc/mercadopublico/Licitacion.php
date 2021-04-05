<?php

//Clase padre para heredar cone

class Licitacion extends \corsica\regulb\DbMySql
{
	//tichet de prueba
	//private $ticket = 'F8537A18-6766-4DEF-9E59-426B4FEE2844';
	//ticket dante santoni
		private $ticket = '615B0FA7-75F9-467F-896C-87D672C34D26';

	private $url = 'http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json';

	//	private $logger = null;
	//	public function __construct() {
	//		$this->logger = new Logger("C:\\AppServ\\www\\mto\\FILES\\LOG\\", "licitacion.txt", true);
	//}

	public function filtrar($filtro)
	{
		$where =  " where CodigoEstado = 5 ";
		$where .= "   and Nombre like ".$this->texto($filtro->keywords,true);

		$sql = "select * from t_lic ";
		$sql .= $where;
		$sql .= " order by nombre desc";
		$sql .= "   limit " . self::PAGINACION * ($filtro->pagina - 1) . ", " . self::PAGINACION;
		$registros = $this->selectObj($sql);   
		$q = $this->select1("select count(*) from t_lic ".$where);  
		$pgs = ceil($q / self::PAGINACION);


		$out = array("registros" => $registros, "totalRegistros" => $q, "totalPaginas" => $pgs);
		return $out;
	}
	public function diaActual()
	{
		$params = array("estado" => "activas");
	
		$jsonresult = $this->call($params);
		$listado = $jsonresult->Listado;
		foreach ($listado as $item) {
			$this->save($item);
		}
	}
	public function cargaLicitacion($CodigoExterno)
	{
		$params = array("codigo" => $CodigoExterno);
		$jsonresult = $this->call($params);
		$listado = $jsonresult->Listado;
		foreach ($listado as $item) {
			$this->save($item);
		}
	}
	public function diaAnteriorPublicada()
	{
		$fecha = date('dmY',strtotime("-1 days"));
		$estado = "publicada";

		$params = array("fecha" => $fecha, "estado" => $estado);

		$jsonresult = $this->call($params);
		$listado = $jsonresult->Listado;


		if ($listado != null) {
			foreach ($listado as $item) {
				$this->save($item);
			}
			$params['registros'] = count($listado);
		} else {
			$params['error'] = json_encode($listado);
		}
		return $params;
	}
	private function save($item)
	{
		$id = $this->itemId($item);
		if ($id == 0) {
			$this->insertItem($item);
			return array("code" => 200, "message" => "ok");
		} else {
		//	$this->updateItem($id, $item);
		}
	}
	private function itemId($item)
	{
		$id =  $this->select1("select id from t_lic where CodigoExterno = " . $this->texto($item->CodigoExterno));
		return ($id == null) ? 0 : $id * 1;
	}
	private function insertItem($item)
	{
		$sql = "INSERT INTO t_lic( CodigoExterno, Nombre, CodigoEstado, FechaCierre) VALUES (";
		$sql .= $this->texto($item->CodigoExterno) . ",";
		$sql .= $this->texto($item->Nombre) . ",";
		$sql .= $this->numero($item->CodigoEstado) . ",";
		$sql .= $this->fechaLic($item->FechaCierre) . ")";
		$this->insert($sql);
	}
	private function fechaLic($str)
	{
		$str = str_replace('T', ' ', $str);
		$str = "'" . $str . "'";
		return $str;
	}


	private function updateItem($id, $item)
	{
		$sql = "UPDATE t_lic ";
		$sql .= " set      Nombre = " . $this->texto($item->Nombre) . ",";
		$sql .= "    CodigoEstado = " . $this->numero($item->CodigoEstado) . ",";
		$sql .= "     FechaCierre = " . $this->fechaLic($item->FechaCierre) . ",";
		$sql .= "        where id = " . $this->numero($id);
		$this->update($sql);
	}


	private function baseUrl()
	{
		return $this->url . "?ticket=" . $this->ticket;
	}
	{"Codigo":"4950-16-AG21","Nombre":"Orden de Compra generada por invitaci\u00f3n a compra \u00e1gil: 4950-10-COT21","CodigoEstado":4}

	protected function call($urlparams)
	{
		$url = $this->baseUrl();
		$urlparams = (array) $urlparams;
		foreach ($urlparams as $key => $value) {
			$url .= "&" . $key . "=" . $value;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_ENCODING, "ISO-8859-1");
		curl_setopt($ch, CURLOPT_URL,  $url);
		//set the content type to application/json
		$headers = array();

		array_push($headers, 'Content-Type:application/json');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		//return response instead of outputting
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute the POST request
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//LLAMADA
		$this->traza("URL:" . $url);

		$json_result = curl_exec($ch);
		//si falla devuelve false
		if (!$json_result) {
			//ERROR DE LLAMADO
			$this->traza("Errores CURL=" . curl_error($ch));
			return curl_error($ch);
		} else {
			//TRANSFORMO EL JSON A DATOS
			$this->traza("Respuesta OK CURL=" . $json_result);
			$result = json_decode($json_result);
			//se procesa el resultado
			return $result;
		}
	}
}
/*
Publicada = "5"
Cerrada = "6"
Desierta = "7"
Adjudicada = "8"
Revocada = "18"
Suspendida = "19"

,"Comprador":
	{
	 "CodigoOrganismo":"7248"
	,"NombreOrganismo":"MOP - Dirección de Vialidad"
	
	,"RutUnidad":"61.202.000-0"
	,"CodigoUnidad":"1995"
	,"NombreUnidad":"Dirección de Vialidad - VIII Región - Provincia Concepción"
	,"DireccionUnidad":"RUTA 150 CAMINO PENCO KM. 6,5"
	,"ComunaUnidad":"Penco"
	,"RegionUnidad":"Región del Biobío "
	
	,"RutUsuario":"17.617.694-6"
	,"CodigoUsuario":"1525866"
	,"NombreUsuario":"Pamela Francisca Caamaño Cuadros"
	,"CargoUsuario":"Jefe Unidad de Abastecimiento Provincial"
	}

		{"Correlativo":1
		,"CodigoProducto":30131502
		,"CodigoCategoria":"30131500"
		,"Categoria":"Artículos para estructuras, obras y construcciones / Productos de construcción estructurales / Bloques"
		,"NombreProducto":"Bloques de hormigón"
		,"Descripcion":"Blego Estándar – ancho 1,0 m;  alto, 1,0 m; largo 2,0 m."
		,"UnidadMedida":"Unidad","Cantidad":90.0
		,"Adjudicacion":{
			"RutProveedor":"96.936.910-9"
			,"NombreProveedor":"INDUSTRIA DE TUBOS Y PREFABRICADOS DE HORMIGON BUDNIK S A"
			,"Cantidad":90.0,"MontoUnitario":188526.0
			}
		}
	
Publicada
Cerrada
Desierta
Adjudicada
Revocada
Suspendida
Todos (muestra todos los estados posibles antes señalados)


http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json?fecha=02022014&estado={estado}&ticket=F8537A18-6766-4DEF-9E59-426B4FEE2844

http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json?fecha=02022014&estado=adjudicada&ticket=F8537A18-6766-4DEF-9E59-426B4FEE2844

Por {código} de Organismo Público:

Ejemplo de {CódigoOrganismo} = 694

http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json?fecha={fecha}&CodigoOrganismo={CódigoOrganismo}&ticket=F8537A18-6766-4DEF-9E59-426B4FEE2844

http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json?fecha=02022014&CodigoOrganismo=6945&ticket=F8537A18-6766-4DEF-9E59-426B4FEE2844

Por {código} de Proveedor:

Ejemplo de {CódigoProveedor} = 17793

http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json?fecha=02022014&CodigoProveedor={CódigoProveedor}&ticket=F8537A18-6766-4DEF-9E59-426B4FEE2844

http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json?fecha=02022014&CodigoProveedor=17793&ticket=F8537A18-6766-4DEF-9E59-426B4FEE2844




*/