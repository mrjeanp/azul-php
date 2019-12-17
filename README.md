azul-php
--------------------

Cualquiera que haya trabajado con la API de AZUL conoce bien las inconsistencias que existen en este servicio, las cuales generan grandes frustraciones al momento de desarrollar. Esta librería te puede ayudar a evitar la mayoría de frustraciones.

La documentación también ha sido mejorada con respecto al PDF original de AZUL, con una mejor organización del texto y una referencia interactiva de la API.

Consulta esta [wiki](https://github.com/mrjeanp/azul-php/wiki) para saber más sobre AZUL Webservices.

## Instalación
1. Instala [composer](https://getcomposer.org).
2. `$ composer require mrjeanp/azul-php`
2. `$ composer install`

## Cómo usarla

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
    'CardNumber' => ...,
    'Expiration' => ...,
    'CVC' => ...,
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
Crea una transacción de venta usando la información de una tarjeta o un token del DataVault.

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
Crea una transacción en HOLD (en espera) usando la info. de una tarjeta o token del DataVault.

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
Cancela una transacción en HOLD.

```php
 $cancel = $azul->cancel([
   'CustomOrderId' => $hold->CustomOrderId
 ]);
```

### Post
Confirma una transacción en HOLD.

```php
 $post = $azul->post([
    'AzulOrderId' => $hold->AzulOrderId,
    'OriginalDate' => $hold->DateTime,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
  ]);
```

### Verify
Verifica una transacción previa (HOLD o SALE)

```php
 $verify = $azul->verify([
    'CustomOrderId' => $sale->CustomOrderId
  ]);
```

### Refund
Crea una transacción de reembolso.

```php
 $refund = $azul->refund([
    'AzulOrderId' => $sale->AzulOrderId
    'OriginalDate' => $sale->DateTime,
    'Amount' => 2000.00,
    'Itbis' => 1000.00,
  ]);
```

### Create Token
Crea un token del DataVault.

```php
 $created = $azul->createToken([
    'CardNumber' => $cc_number,
    'Expiration' => $cc_exp,
    'CVC' => $cc_cvc,
  ]);
```

### Delete Token
Elimina un token del DataVault.

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
