<?php

namespace corsica\regulb;

/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class Config
{

    private $params = null;

    public function __construct()
    {
        $path = realpath(dirname(__FILE__) . "/config.json");
        $configarray = json_decode(file_get_contents($path), true);
        $this->params = $configarray[$configarray['env']];
    }

    public function getParam($groupname, $paramname)
    {
        $group = $this->params[$groupname];
        $param = null;
        if ($group != null)
            $param = $group[$paramname];
        return $param;
    }
    public function show()
    {
        return json_encode($this->params);
    }
}
