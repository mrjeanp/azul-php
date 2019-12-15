azul-php
--------------------

Es una librería en PHP para facilitar el uso de *AZUL Web Services*. AZUL es una compañia dominicana 
que procesa de pagos en línea, Web Services es un servicio que provee esta empresa para procesar pagos
mediante el consumo de su interfaz HTTP.


## Instalación
1. Instalar [composer](https://getcomposer.org).
2. En tu proyecto, ejecutar: `$ composer install`

## Como Usarla

```php
<?php
require_once __DIR__."/vendor/autoload.php";

use Azul\Azul;
use Azul\AzulException;

try {
  $azul = new Azul;
  $azul->set('auth1', 'username'); // Auth1 otorgado por AZUL.
  $azul->set('auth2', 'password'); // Auth2 otorgado por AZUL.
  $azul->set('domain', 'pruebas'); // Entorno (pruebas|pagos)
  $azul->set('certificate_path', /* Ruta absoluta del certificado otorgado por AZUL */);
  $azul->set('key_path', /* Ruta absoluta de la llave del certificado */);
  
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
    'CardNumber' => ...,
    'Expiration' => ...,
    'CVC' => ...,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderId' => 'SALE-1',
  ]);
```

### Hold
```php
 $hold = $azul->hold([
    'CardNumber' => ...,
    'Expiration' => ...,
    'CVC' => ...,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
    'CustomOrderId' => 'HOLD-1',
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
    'AzulOrderId' => ...,
    'OriginalDate' => ...,
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
    'AzulOrderId' => ...,
    'OriginalDate' => ...,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
  ]);
```



### Create Token
```php
 $created = $azul->createToken([
    'CardNumber' => ...,
    'Expiration' => ...,
    'CVC' => ...,
  ]);
```

### Delete Token
```php
 $hold = $azul->deleteToken([
    'DataVaultToken' => '...'
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
