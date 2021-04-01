<?php

namespace corsica\regulb;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\regulb\Config;

/**
 * Para administrar la base de datos
 */
abstract class DbMySql
{
    const PAGINACION = 30;
    /**
     * La conexión a BD, útil para manejar transacciones
     */
    private $conn = null; // para mantener viva la conexión
    /**
     * Logger
     */
    protected $logger = null;
    /**
     * para cuando se abran transacciones almacenará las sentencias y entregará la pila al momento de ROLLBACK
     */
    private $stack = null;

    /**
     * Construye la clase de acceso a un BD MySql junto con un logger
     * @param $conn es la conexión externa, aveces necesario apra transacciones
     */
    // Esta es la forma de pasarle la conexión en caso de que no la tenga creada
    // y poder hacer transacciones
    public function __construct($conn = null)
    {
        $this->conn = $conn;

        $this->logger  = new Logger('sql');
        $path = dirname(dirname(__FILE__)) . '/log/sql.log';
        $this->logger->pushHandler(new StreamHandler($path, $this->logLevel()));
    }
    /**
     * ENTREGA EL NIVEL DE LOG DESDE EL ARCHIVO DE CONFIGURACIÓN
     */
    private function logLevel()
    {
        $config = new Config();
        $loglevel = $config->getParam("database", "loglevel");
        $loglevel = strtoupper($loglevel);
        $levels = array(
            "DEBUG" => Logger::DEBUG, "INFO" => Logger::INFO, "NOTICE" => Logger::NOTICE, "WARNING" => Logger::WARNING, "ERROR" => Logger::ERROR, "CRITICAL" => Logger::CRITICAL
        );
        $out = $levels[$loglevel];
        $out = ($out == null) ? Logger::CRITICAL : $out;
        return $out;
    }




    //?
    private $cantidadDeRegistros = 0;
    //?
    protected function getCantRegistros()
    {
        return $this->cantidadDeRegistros;
    }

    //recorta un string al máximo indicado para que no se caiga el insert/update
    protected function fixmax($str, $max)
    {
        $str = trim($str);
        $l = strlen($str);
        if ($l > $max) {
            $str = substr($str, 0, $max);
        }
        return $str;
    }
    /***********************************************************************
     * evalua si un dato viene nulo para poner un cero
     */
    protected function numero($value)
    {
        $out = (is_numeric($value)) ? $value : 0;
        return $out;
    }
    protected function texto($valor, $like = false)
    {
        $x = ($like) ? '%' : '';
        return "'" . $x . mysqli_real_escape_string($this->getConexion(), $valor) . $x . "'";
    }
    protected function like($valor)
    {
        return "'%" .  mysqli_real_escape_string($this->getConexion(), $valor) . "%'";
    }
    protected function fecha($fecha)
    {
        $out = $fecha;
        if (strpos($fecha, '/')) {
            $d = strtok($fecha, "/"); //busca el dd/MM/yy
            $m = strtok("/");
            $y = strtok("/");
            if (strlen($y) == 2)
                $y = '20' . $y;
            $out =  $y . '-' . $m . '-' . $d;
        }
        $out = "'" . $out . "'";
        return $out;
    }
    protected function fechahora($fecha, $hora = '00:00')
    {
        //yyyy5mm8dd
        $fecha = substr($fecha, 0, 10);
        $out = $this->fecha($fecha);
        $out = substr($out, 0, strlen($out) - 1);
        $out .=  ' ' . $hora . "'";
        return $out;
    }
    /**
     * Evalua si la conexión es nula y la crea
     */
    public function getConexion()
    {

        if ($this->conn == null) {

            // Create connection
            $config = new Config();
            $conn = mysqli_connect(
                $config->getParam("database", "servername"),
                $config->getParam("database", "username"),
                $config->getParam("database", "password"),
                $config->getParam("database", "database")
            );
            if (!$conn) {
                $mensajeError = "ERROR DE CONEXION EN MyDbSql - HOLA!! ";
                $mensajeError .= "[servername : " .  $config->getParam("database", "servername") . "]";
                $mensajeError .= "[username : " .  $config->getParam("database", "username") . "]";
                //$mensajeError .= "[password : " .  $config->getParam("database", "password") . "]";
                $mensajeError .= "[database : " .  $config->getParam("database", "database") . "]";
                $mensajeError .= "[error no : " . mysqli_connect_errno() . "]";
                $mensajeError .= "[error : " . mysqli_connect_error() . "]";
                $this->logger->error($mensajeError);


                exit($mensajeError);
            }
            mysqli_set_charset($conn, "utf8");
            $this->conn = $conn;
        }
        return $this->conn;
    }
    /**
     * Para recibir la conexión desde otra clase cuando se realizan transacciones
     */
    public function setConexion($conn)
    {
        $this->conn = $conn;
    }
    /**
     * obtiene el id de un insert
     */
    protected function lastId()
    {
        return mysqli_insert_id($this->conn);
    }

    public function update($sql)
    {
        //$sql = utf8_decode($sql);
        $resource = null;
        //error_reporting(E_ERROR | E_PARSE);
        $out = 0;
        $conn = $this->getConexion();
        $this->logger->info($sql);

        if ($conn->query($sql) === TRUE) {
            $out = $conn->affected_rows;
        } else {
            $this->logger->error($conn->error, array("sql" => $sql));
        }
        return $out;
    }

    public function getInsertedId()
    {
        $conn = $this->getConexion();
        return $conn->insert_id;
    }

    public function insert($sql)
    {
        return $this->update($sql);
    }
    public function delete($sql)
    {
        return $this->update($sql);
    }
    public function autocommit($onoff)
    {
        $conn = $this->getConexion();
        $conn->autocommit($onoff);
    }
    public function commit()
    {
        $conn = $this->getConexion();
        $conn->commit();
    }
    public function rollback($message = "")
    {
        $conn = $this->getConexion();
        $this->logger->error("ROLLBACK:" . $message);
        $conn->rollback();
    }


    /**
     * Retorna un dato escalar o null si no lo encuentra
     */
    public function select1($sql)
    {
        $conn = $this->getConexion();
        $this->logger->debug($sql);
        $result = mysqli_query($conn, $sql);
        if ($result !== false) {
            $this->logger->debug($sql);
            $row = $result->fetch_array(MYSQLI_NUM);
            if (is_array($row) && count($row) == 1) {
                return $row[0];
            } else {
                return null;
            }
        } else {
            $this->logger->error($sql);
            return null;
        }
    }
    //$mysqli->multi_query($sql)
    public function multiQuery($sql)
    {
        $out = array();
        $conn = $this->getConexion();
        $this->logger->debug($sql);
        $result = mysqli_multi_query($conn, $sql);
        return $result;
    }
    public function select($sql)
    {
        //aqui había un problema cuando haca varias queries seguidas
        //error_reporting(E_ERROR | E_PARSE);
        $out = array();
        $conn = $this->getConexion();
        $this->logger->debug($sql);
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $this->cantidadDeRegistros = mysqli_num_rows($result);
            $row = mysqli_fetch_assoc($result);
            while ($row != null) {
                array_push($out, $row);
                $row = mysqli_fetch_assoc($result);
            }
        } else {
            $this->logger->error($conn->error, array("sql" => $sql));
        }
        return $out;
    }
    //lo mismo pero devuelve un objeto en vez de un array
    public function selectObj($sql)
    {
        //aqui había un problema cuando haca varias queries seguidas
        //error_reporting(E_ERROR | E_PARSE);
        $out = array();
        $conn = $this->getConexion();
        $this->logger->debug($sql);
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $this->cantidadDeRegistros = mysqli_num_rows($result);
            $row = mysqli_fetch_object($result);
            while ($row != null) {
                array_push($out, $row);
                $row = mysqli_fetch_object($result);
            }
        } else {
            $this->logger->error($conn->error, array("sql" => $sql));
        }

        return $out;
    }

    /**
     * 
     */
    public function getFechaDb()
    {
        return $this->select1("select current_date as x");
    }
    /**
     * 
     */
    public function getFechaHoraDb()
    {
        return $this->select1("select now as x");
    }
}
