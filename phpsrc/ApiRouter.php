<?php

namespace corsica\regulb;

use Exception;

/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class ApiRouter
{
    /** para cargar el archivo json */
    private $params = null;

    public function __construct()
    {

        //cargo la configuración
        $path = realpath(dirname(__FILE__) . "/apirouter.json");
        $configarray = json_decode(file_get_contents($path), true);
        $this->params = $configarray;
    }


    public function ejecutar($data)
    {
        $entidades = $this->params;
        //Verifica la entidad
        $entidad = $data->entidad;
        if (!array_key_exists($entidad, $entidades)) {
            throw new Exception("El nombre de entidad no se encuentra: [" . $entidad . "]");
        }
        //Verifica el método
        $metodo = $data->metodo;
        if (!array_key_exists($metodo, $entidades[$entidad]['metodos'])) {
            throw new Exception("El nombre de metodo no se encuentra: [" . $metodo . "] para la entidad [" . $entidad . "]");
        }

        //cargo el método y la clase

        $clase = $entidades[$entidad]['clase'];
        $clase = str_replace("/", "\\", $clase);

        $argnames = $entidades[$entidad]['metodos'][$metodo];

        $argumentos = null;
        $qargs = count($argnames);
        for ($i = 0; $i < $qargs; $i++) {
            if ($argumentos == null) {
                $argumentos = array();
            }
            $argumento = $argnames[$i];
            // echo "xxxxxx";
            // var_dump( $data->$argumento );

            array_push($argumentos, $data->$argumento);
        }

        $object = new $clase();

        //return array("clase"=>$clase,"metodo"=>$metodo,"argumentos"=>json_encode($argumentos));

        switch ($qargs) {
            case 0:
                $out = $object->$metodo();
                break;
            case 1:
                $out = $object->$metodo($argumentos[0]);
                break;
            case 2:
                $out = $object->$metodo($argumentos[0], $argumentos[1]);
                break;
            case 3:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2]);
                break;
            case 4:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3]);
                break;
            case 5:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4]);
                break;
            case 6:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4], $argumentos[5]);
                break;
            case 7:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4], $argumentos[5], $argumentos[6]);
                break;
            case 8:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4], $argumentos[5], $argumentos[6], $argumentos[7]);
                break;
            default:
                throw new Exception("El numero de argumentos es mayor a 8");
                break;
        }

        return $out;
    }
}
