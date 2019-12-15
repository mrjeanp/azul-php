<?php
require_once __DIR__."/../vendor/autoload.php";

use Dotenv\Dotenv;
use Azul\Azul;

$denv = Dotenv::createImmutable(__DIR__.'/../', 'test.env');
$denv->load();

try {
  $azul = new Azul;
  $azul->Store = getenv('MID');
  $hold = $azul->sale([
    'CardNumber' => getenv('VALID_CARD'),
    'Expiration' => getenv('VALID_EXPIRATION'),
    'CVC' => getenv('VALID_CVC'),
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderNumber' => 'HOLD-1',
  ]);

  var_dump($hold);
}
catch (\Exception $e) {
  var_dump($e);
}

