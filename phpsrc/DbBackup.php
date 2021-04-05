<?php


namespace corsica\regulb;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\regulb\Config;

class DbBackup
{

  public function getCommand()
  {
    $config = new Config();
    $database = $config->getParam("database", "database");
    $username = $config->getParam("database", "username");
    $password = $config->getParam("database", "password");
    $servername = $config->getParam("database", "servername");
    $backuppath = $config->getParam("database", "backup-path");
    $today = $this->today();


    $command = 'mysqldump ' ;
    //$command .=  ' -h ' . $servername;
    $command .=  ' -u' . $username;
    $command .=  ' -p' . $password;
    $command .=  ' ' . $database;
    $command .=  ' > ' . $backuppath . $database . $today . ".sql";
    return $command;
  }
  private function today()
  {
    return date("Y-m-d");
  }
  public function backup()
  {
    $command = $this->getCommand();
    exec($command, $output = array(), $worked);
    switch ($worked) {
      case 0:
        echo 'Funcionó';
        break;
      case 1:
        echo 'Error al exportar ' . getcwd();
        break;
      case 2:
        echo 'Hubo un error en la sentencia ' . $command;
        break;
    }
  }
}
