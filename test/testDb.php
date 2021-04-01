<?php

require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
echo "\ncomeinza\n";
use corsica\regulb\salarios\Salario;
$x = new Salario();
$response = $x->getFechaDb();

echo json_encode($response);
echo "\n fin\n";
