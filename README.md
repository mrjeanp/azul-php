azul-php
--------------------

Es una librería en PHP para facilitar el desarrollo con *AZUL Web Services*. AZUL es una compañia dominicana 
que procesa de pagos en línea, Web Services es un servicio que provee esta empresa para procesar pagos
mediante el consumo de su interfaz HTTP.


## Instalación
1. Instala [composer](https://getcomposer.org).
2. Ejecuta `$ composer require mrjeanp/azul`
2. Ejecuta: `$ composer install`

## Como usarla

```php
<?php
require_once __DIR__."/vendor/autoload.php";

use Azul\Azul;
use Azul\AzulException;

try {
  $azul = new Azul;
  $azul->set('auth1', 'test'); // Auth1 otorgado por AZUL.
  $azul->set('auth2', 'test'); // Auth2 otorgado por AZUL.
  $azul->set('domain', 'pruebas'); // Entorno (pruebas|pagos)
  $azul->set('certificate_path', '/absolute/path/to/azul.crt');
  $azul->set('key_path', '/absolute/path/to/azul.key');
  
  $azul->Store = /* Merchant ID (MID) otorgado por AZUL */;
  
  // Ejecutar transacción
  $sale = $azul->sale([
    'CardNumber' => /* Numero de tarjeta */,
    'Expiration' => /* Fecha de Expiracion */,
    'CVC' => /* Codigo CVC */,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderId' => 'SALE-1',
  ]);
  
  // Resultado de la transacción
  var_dump($sale);
}
catch (AzulException $e) {
  // Detalles del error
  var_dump($e->getDetails());
}
```

### Sale
```php
 $sale = $azul->sale([
    'CardNumber' => $cc_number,
    'Expiration' => $cc_exp,
    'CVC' => $cc_cvc,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderId' => 'SALE-1',
  ]);
  
  $sale = $azul->sale([
    'DataVaultToken' => $data_vault_token,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderId' => 'SALE-2',
  ]);
```

### Hold
```php
 $hold = $azul->hold([
    'CardNumber' => $cc_number,
    'Expiration' => $cc_exp,
    'CVC' => $cc_cvc,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderId' => 'HOLD-1',
  ]);
  
  $hold = $azul->hold([
    'DataVaultToken' => $data_vault_token,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderId' => 'HOLD-2',
  ]);
```

### Cancel
```php
 $cancel = $azul->cancel([
   'CustomOrderId' => $hold->CustomOrderId
 ]);
```

### Post
```php
 $post = $azul->post([
    'AzulOrderId' => $hold->AzulOrderId,
    'OriginalDate' => $hold->DateTime,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
  ]);
```

### Verify
```php
 $verify = $azul->verify([
    'CustomOrderId' => $sale->CustomOrderId
  ]);
```

### Refund
```php
 $refund = $azul->refund([
    'AzulOrderId' => $sale->AzulOrderId
    'OriginalDate' => $sale->DateTime,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
  ]);
```

### Create Token
```php
 $created = $azul->createToken([
    'CardNumber' => $cc_number,
    'Expiration' => $cc_exp,
    'CVC' => $cc_cvc,
  ]);
```

### Delete Token
```php
 $deleted = $azul->deleteToken([
    'DataVaultToken' => $created->DataVaultToken
  ]);
```


## Pruebas
Para correr pruebas, crea un archivo llamado `test.env` y copia el contenido de
`test.env.example` en ese archivo, luego los valores correspondientes 
a tu entorno. 

Ejectuta:
```
$ ./vendor/bin/phpunit
```

---------------
&copy; MIT License
