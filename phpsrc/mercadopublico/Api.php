<?php
namespace corsica\regulb\mercadopublico;
//Clase padre para heredar cone
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Api
{
	//tichet de prueba
	//private $ticket = 'F8537A18-6766-4DEF-9E59-426B4FEE2844';
	//ticket dante santoni
	public const URL_LICITACION ='http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json';
	public const URL_OCOMPRA ='http://api.mercadopublico.cl/servicios/v1/publico/ordenesdecompra.json';
	public const URL_COMPRADOR ='http://api.mercadopublico.cl/servicios/v1/Publico/Empresas/BuscarComprador';
	public const URL_VENDEDOR ='http://api.mercadopublico.cl/servicios/v1/publico/licitaciones.json';
	public const TICKET ='615B0FA7-75F9-467F-896C-87D672C34D26';
	//private $ticket = '615B0FA7-75F9-467F-896C-87D672C34D26';
	protected $logger = null;
/**
 * Llama al la url de licitaciones
 * @param codigo - Codigo de la licitación
 * @param fecha - fecha de la licitación
 */
	public function licitaciones($codigo="",$fecha="",$estado="",$idcomprador="",$idproveedor="") {
		$params = array();
		if (strlen($codigo)>0) {
			$params["codigo"]=$codigo;
		}
		if (strlen($fecha)>0) {
			$params["fecha"]=$fecha;
		}
		if (strlen($estado)>0) {
			$params["estado"]=$estado;
		}
		if (strlen($idcomprador)>0) {
			$params["codigoorganismo"]=$idcomprador;
		}
		if (strlen($idproveedor)>0) {
			$params["CodigoProveedor"]=$idproveedor;
		}
		return $this->call(Api::URL_LICITACION,$params);
	}
	/**
	 * 
	 */
	public function compradores() {
		$response = $this->call(Api::URL_COMPRADOR, array());
		return $response->listaEmpresas;
	}
	/**
	 * @param codigo - el código único de la licitación
	 * @param fecha - La fecha en formato yyyy-mm-dd
	 */
	public function ocompras($codigo="",$fecha="",$idcomprador="") {

		$params = array();
		if (strlen($codigo)>0) {
			$params["codigo"]=$codigo;
		}
		if (strlen($fecha)==10) {
			$yyyy=substr($fecha,0,4);
			$mm=substr($fecha,5,2);
			$dd=substr($fecha,8,2);
			$params["fecha"]=$dd.$mm.$yyyy;
		}
		if (strlen($idcomprador)>0) {
			$params["CodigoOrganismo"]=$idcomprador;
		}

		$response = $this->call(Api::URL_OCOMPRA, $params);
		if ($response->Codigo) {
			exit("ERROR ".$response->Codigo.", ".$response->Mensaje);
		}
		return $response->Listado;
	}
	public function __construct($conn = null)
	{
			$this->conn = $conn;

			$this->logger  = new Logger('curl');
			$path = dirname(dirname(dirname(__FILE__))) . '/log/curl.log';
			$this->logger->pushHandler(new StreamHandler($path, Logger::DEBUG));
	}


	private function fechaLic($str)
	{
		$str = str_replace('T', ' ', $str);
		$str = "'" . $str . "'";
		return $str;
	}



/**
 * ESta call es siempre GET
 */
	protected function call($url,$params)
	{
		$url = $url."?ticket=".Api::TICKET;
		if (count($params)>0)
			$url .= "&".http_build_query($params);

		$this->logger->debug("llamando url",array("url"=>$url));

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
		$this->logger->debug("URL:" . $url);

		$json_result = curl_exec($ch);
		//si falla devuelve false
		if (!$json_result) {
			//ERROR DE LLAMADO
			$this->logger->debug("Errores CURL=" . curl_error($ch));
			return curl_error($ch);
		} else {
			//TRANSFORMO EL JSON A DATOS
			$this->logger->debug("Respuesta OK CURL=" . $json_result);
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