<?php

use Azul\Azul;
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__.'/../', 'test.env');
$dotenv->load();

class AzulTest extends TestCase {

  protected $azul;

  protected function setUp()
  {
    $this->azul = new Azul([
      'testing' => true,
      'certificate_path' => getenv('CRT_PATH'),
      'key_path' => getenv('KEY_PATH'),
    ], [
      'Store' => getenv('MID')
    ]);
  }

  function testFormat() {
    $this->azul->Amount = 500.00;
    $this->assertSame(50000, $this->azul->Amount);

    $this->azul->Amount = 50000;
    $this->assertSame(50000, $this->azul->Amount);

    $this->azul->OriginalDate = 20191213;
    $this->assertSame(20191213, $this->azul->OriginalDate);

    $this->azul->OriginalDate = 20191213025924;
    $this->assertSame(20191213, $this->azul->OriginalDate);
  }
  
  function testOriginalDateFail() {
    $this->expectExceptionMessage(
      'OriginalDate should be 8+ characters long');
    $this->azul->OriginalDate = 123;
  }

  function testAmountFail() {
    $this->expectExceptionMessage('Amount should be int or float');
    $this->azul->Amount = '2000';
  }

  function testItbisFail() {
    $this->expectExceptionMessage('Itbis should be int or float');
    $this->azul->Itbis = '200.00';
  }

  function testRequiresFail() {
    $this->expectExceptionMessage('This method requires: [one,two]');
    Azul::requires([ 'one' => 1, 'three'=> 3 ], ['one', 'two']);
  }

  function testRequiresEitherFail() {
    $this->expectExceptionMessage(
      'This method requires: [one] or [two,three]'
    );
    Azul::requiresEither(
      [ 'two' => 2, 'four' => 4 ], 
      ['one'], ['two', 'three']
    );
  }

  function testRequiresOk() {
    Azul::requires(['one'=>1, 'two'=>2], ['one', 'two']);
    $this->assertTrue(true);
  }

  function testRequiresEitherOk() {
    Azul::requiresEither([
      'two' => 2, 'three' => 3
    ], ['one'], ['two', 'three']);

    $this->assertTrue(true);
  }

  function testEndpoint() {
    $this->assertEquals(
      "https://pruebas.azul.com.do/WebServices/JSON/Default.aspx?Query", 
      $this->azul->endpoint('Query')
    );

    $this->azul->config([ 'domain' => 'pagos' ]);

    $this->assertEquals(
      "https://pagos.azul.com.do/WebServices/JSON/Default.aspx?Hello", 
      $this->azul->endpoint('Hello')
    );
  }


  /**
   */
  function testSale() {
    $sale = $this->azul->sale([
      'Amount' => 100000,
      'Itbis' => 50000,
      'CardNumber' => getenv('VALID_CARD'),
      'Expiration' => getenv('VALID_EXPIRATION'),
      'CVC' => getenv('VALID_CVC'),
      'CustomOrderId' => 'SALE-1',
    ]);

    $this->assertObjectHasAttribute('AzulOrderId', $sale);
    $this->assertObjectHasAttribute('DateTime', $sale);
    $this->assertAttributeSame('00', 'IsoCode', $sale);

    return $sale;
  }

  /**
   * @depends testSale
   */
  function testRefund(  stdClass $sale ) {
    $refund = $this->azul->refund([
      'Amount' => 100000,
      'Itbis' => 50000,
      'AzulOrderId' => $sale->AzulOrderId,
      'OriginalDate' => $sale->DateTime,
    ]);

    $this->assertAttributeSame('00', 'IsoCode', $refund);

    return $refund;
  }

  /**
   */
  function testHold() {
    $response = $this->azul->hold([
      'Amount' => 100000,
      'Itbis' => 50000,
      'CustomOrderId' => 'HOLD-1',
      'CardNumber' => getenv('VALID_CARD'),
      'Expiration' => getenv('VALID_EXPIRATION'),
      'CVC' => getenv('VALID_CVC'),
    ]);

    $this->assertAttributeSame('00', 'IsoCode', $response);
    $this->assertObjectHasAttribute('DateTime', $response);
    $this->assertObjectHasAttribute('AzulOrderId', $response);

    return $response;
  }

  /**
   * @depends testHold
   */
  function testPost($hold) {
    $post = $this->azul->post([
      'Amount' => 100000,
      'Itbis' => 50000,
      'AzulOrderId' => $hold->AzulOrderId,
    ]);

    $this->assertAttributeSame('00', 'IsoCode', $post);
    $this->assertObjectHasAttribute('DateTime', $post);
    $this->assertObjectHasAttribute('AzulOrderId', $post);

    return $post;
  }

  /**
   * @depends testSale
   */
  function testVerify($sale) {
    $verify = $this->azul->verify([
      'CustomOrderId' => $sale->CustomOrderId,
    ]);

    $this->assertAttributeSame('00', 'IsoCode', $verify);
    $this->assertAttributeSame(true, 'Found', $verify);
  }

  /**
   */
  function testCreateToken() {
    $created = $this->azul->createToken([
      'CardNumber' => getenv('VALID_CARD'),
      'Expiration' => getenv('VALID_EXPIRATION'),
      'CVC' => getenv('VALID_CVC')
    ]);

    $this->assertAttributeSame('00', 'IsoCode', $created);
    $this->assertObjectHasAttribute('DataVaultToken', $created);

    return $created;
  }

  /**
   * @depends testCreateToken
   */
  function testCancel($created) {
    $hold = $this->azul->hold([
      'DataVaultToken' => $created->DataVaultToken,
      'Amount' => 1000.00,
      'Itbis' => 500.00,
    ]);
    $this->assertAttributeSame('00', 'IsoCode', $hold);
    $this->assertObjectHasAttribute('AzulOrderId', $hold);

    $cancel = $this->azul->cancel([
      'AzulOrderId' => $hold->AzulOrderId
    ]);

    $this->assertAttributeSame('00', 'IsoCode', $cancel);
  }

  /**
   * @depends testCreateToken
   */
  function testDeleteToken($created) {
    $deleted = $this->azul->deleteToken([
      'DataVaultToken' => $created->DataVaultToken
    ]);

    $this->assertAttributeSame('00', 'IsoCode', $deleted);
  }

}
