<?php

namespace corsica\regulb\mercadopublico;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use DateTime;
use DateInterval;
//Clase padre para heredar cone

class MercadoPublicoManager extends \corsica\regulb\DbMySql
{
  protected $logger = null;
  public function __construct($conn = null)
	{
			$this->conn = $conn;

			$this->logger  = new Logger('mp');
			$path = dirname(dirname(dirname(__FILE__))) . '/log/mp.log';
			$this->logger->pushHandler(new StreamHandler($path, Logger::DEBUG));
	}
  public function cargarCompradores()
  {
    $api = new Api();
    $lista = $api->compradores();
    $comprador = new Comprador();
    $comprador->cargarCompradores($lista);
  }
  public function cargarOCs($fecha)
  {
    $api = new Api();
    $lista = $api->ocompras(null, $fecha);
    $model = new OCompra();
    $model->cargarOCompras($fecha, $lista);
  }
  public function actualizarOC($codigo)
  {
    $api = new Api();
    $lista = $api->ocompras($codigo);
    $model = new OCompra();
    $model->actualizarOC($lista[0]);
  }
  public function actualizarIncompletas()
  {
    $model = new OCompra();
    $rows = $model->listarIncompletas();
    $seconds = 2;
    foreach ($rows as $row) {
      echo "\n".$row->codigo;
      $this->actualizarOC($row->codigo);
      sleep($seconds);
    }
  }
  public function cargarOCsComprador($idcomprador,$fechaini,$fechafin)
  {
    $fechas = $this->getDates($fechaini,$fechafin);
    $api = new Api();
    $model = new OCompra();
    $seconds = 2;
    foreach($fechas as $fecha) {
      echo "\n".$fecha;
      $lista = $api->ocompras(null, $fecha,$idcomprador);
      $model->cargarOCompras($fecha, $lista);
      sleep($seconds);
    }

  }
  public function getDates($fechaini,$fechafin) {
    $out = array();
    $diff24Hours = new DateInterval('PT24H');
  //echo $fechaini;
    $mydate = new DateTime($fechaini);
    $end = new DateTime($fechafin);

    $diff = date_diff($mydate,$end);
    $run = $diff->invert;
    $i=0;
    while($run==0) {
      //echo date_format($mydate,"Y-m-d");
    //  echo "\n";
      array_push($out,date_format($mydate,"Y-m-d"));
      $mydate = date_add($mydate,$diff24Hours);
      $diff = date_diff($mydate,$end);
      $run = $diff->invert; 
      $i++;
      if ($i>1000) break; 
    }
    return $out;
 
  }
}
