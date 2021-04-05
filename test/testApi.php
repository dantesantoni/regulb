<?php

require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
echo "\ncomeinza\n";
use corsica\regulb\mercadopublico\Api;
use corsica\regulb\mercadopublico\MercadoPublicoManager;

//TEST DE API
$x = new Api();


$codigo = null;
$fecha = "03042021";
//$response = $x->licitaciones($codigo,$fecha);

$fecha = "2021-04-03";
//$response = $x->ocompras(null,$fecha);


//$response = $x->compradores();
//echo json_encode($response);

/********************************************* */
//TES MANAGER
$mp = new MercadoPublicoManager();
//$mp->cargarCompradores();
//$mp->cargarOCs($fecha);
$codigo ="2373-4-SE21";
//$mp->actualizarOC($codigo);

//$mp->getDates("2020-01-01","2020-01-05");
$idcomprador = "7325";
//$mp->cargarOCsComprador($idcomprador,"2020-01-01","2020-12-31");
$mp->actualizarIncompletas();
echo "\n fin\n";
