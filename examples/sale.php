<?php
require_once __DIR__."/../vendor/autoload.php";

use Dotenv\Dotenv;
use Azul\Azul;
use Azul\AzulException;

$denv = Dotenv::createImmutable(__DIR__.'/../', 'test.env');
$denv->load();

try {
  $azul = new Azul;
  $azul->Store = getenv('MID');
  $sale = $azul->sale([
    'CardNumber' => getenv('VALID_CARD'),
    'Expiration' => getenv('VALID_EXPIRATION'),
    'CVC' => getenv('VALID_CVC'),
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderNumber' => 'SALE-1',
  ]);

  var_dump($sale);
}
catch (AzulException $e) {
  var_dump($e->getDetails());
}

