<?php

require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
echo "\ncomienza\n";
use corsica\regulb\DbBackup;
$x = new DbBackup();
$command = $x->getCommand();
echo json_encode($command);
$x->backup();

//echo json_encode($response);
echo "\n fin\n";
