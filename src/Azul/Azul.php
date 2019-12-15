<?php namespace Azul;

use stdClass;

class Azul {

  protected $values = [];

  protected $settings = [
    'domain' => 'pruebas',
    'auth1' => 'test',
    'auth2' => 'test',
    'certificate_path'=> null,
    'key_path' => null,
  ];

  protected $defaults = [
    "Channel" => "EC",
    "Store" => "",
    "PosInputMode" => "E-Commerce",
    "CurrencyPosCode" => "$",
    "Payments" => "1",
    "Plan" => "0",
    "OriginalTrxTicketNr" => "",
    "RRN" => null,
    "AcquirerRefData" => "1",
    "CustomerServicePhone" => "",
    "ECommerceUrl" => "",
    "OrderNumber" => ""
  ];

  public function __construct(array $config = [], array $defaults = []) 
  {
    $this->defaultValues();
    $this->add( $defaults );
    $this->config( $config );
  }

  public function __get($name) {
    return $this->values[$name] ?: null;
  }

  public function __set($name, $value) : void {
    $this->values[$name] = $this->format($name, $value);
  }

  public function defaultValues() {
    $this->values = $this->defaults;
  }

  public function sale(array $data = []) : stdClass {
    self::requires($data, ["Amount", "Itbis"]);
    self::requiresEither($data, 
      ['DataVaultToken'], 
      ['CardNumber', 'Expiration', 'CVC']
    );

    $this->TrxType = "Sale";
    $this->CardNumber = '';
    $this->Expiration = ''; 
    $this->CVC = ''; 

    $this->add($data);

    return $this->send($this->values());
  }

  public function refund (array $data = []) : stdClass {
    self::requires($data, 
      ["AzulOrderId", "OriginalDate", "Amount", "Itbis"]);

    $this->TrxType = "Refund";
    $this->AcquirerRefData = ''; // avoids a runtime error
    $this->CardNumber = '';
    $this->Expiration = '';
    $this->CVC = '';

    $this->add($data);
    return $this->send($this->values());
  }

  public function hold (array $data = []) : stdClass {
    self::requires($data, ["Amount", "Itbis"]);
    self::requiresEither($data, 
      ['DataVaultToken'], 
      ['CardNumber', 'Expiration', 'CVC']
    );

    $this->TrxType = "Hold";
    $this->CardNumber = '';
    $this->Expiration = ''; 
    $this->CVC = ''; 

    $this->add($data);
    return $this->send( $this->values() );
  }

  public function post (array $data = []) : stdClass {
    self::requires($data, ['AzulOrderId', 'Amount', 'Itbis']);

    $this->add($data);
    return $this->send(
      $this->values([ 
        'Channel', 'Store', 'AzulOrderId', 'Amount', 'Itbis' 
      ]), "ProcessPost"
    );
  }

  public function cancel (array $data = []) : stdClass {
    self::requires($data, ["AzulOrderId"]);
    $this->add($data);
    return $this->send(
      $this->values([ 'Channel', 'Store', 'AzulOrderId' ])
      , "ProcessVoid"
    );
  }

  public function verify(array $data = []) : stdClass { 
    self::requires($data, ['CustomOrderId']);
    $this->CVC = '';
    $this->add($data);
    return $this->send(
      $this->values([ 'Channel', 'Store', 'CustomOrderId' ])
      ,"VerifyPayment"
    );
  }

  public function createToken(array $data = []) : stdClass {
    self::requires($data, ['CardNumber', 'Expiration', 'CVC']);
    $this->TrxType= "CREATE";
    $this->add($data);
    return $this->send(
      $this->values([
        'Channel', 'Store', 'TrxType', 'CardNumber', 'Expiration', 'CVC'
      ]),
      "ProcessDataVault"
    );
  }

  public function deleteToken(array $data = []) : stdClass {
    self::requires($data, ['DataVaultToken']);
    $this->TrxType = 'DELETE';
    $this->CardNumber = '';
    $this->Expiration = '';
    $this->CVC = '';

    $this->add($data);

    return $this->send(
      $this->values([
        'Channel', 'Store', 'TrxType', 'CardNumber', 
        'Expiration', 'CVC', 'DataVaultToken',
      ]),
      'ProcessDataVault'
    );
  }

  protected function send (array $values, string $query = "") : stdClass
  {
    $url = $this->endpoint($query);
    $auth1= $this->get('auth1');
    $auth2= $this->get('auth2');
    $headers = [
      'Content-Type: application/json',
      "Auth1: {$auth1}",
      "Auth2: {$auth2}",
    ];

    $sslcert = $this->get('certificate_path');
    $sslkey = $this->get('key_path');

    $details = [
      'values' => $values,
      'config' => $this->settings,
      'headers' => $headers,
      'url' => $url,
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_FAILONERROR, true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => false,
      CURLOPT_CONNECTTIMEOUT => false,

      CURLOPT_POSTFIELDS => json_encode($values),
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_URL => $url,
    ]);

    $sslcert && curl_setopt($ch, CURLOPT_SSLCERT, $sslcert);
    $sslkey && curl_setopt($ch, CURLOPT_SSLKEY, $sslkey);

    $result = curl_exec($ch);
    $response = json_decode($result);
    $details['response'] = $response;

    $this->throwIfCurlError($ch, $details);

    curl_close ($ch);

    $this->throwIfAzulError($response, $details);

    return $response;
  }

  public function endpoint(string $query = "") : string {
    $d = $this->get('domain');
    $q = $query ? "?{$query}" : "";

    return "https://{$d}.azul.com.do/WebServices/JSON/Default.aspx{$q}"; 
  }

  public function values(array $allowed = []) : array {
    if ( count($allowed) < 1 ) return $this->values;
    return array_intersect_key(
      $this->values, 
      array_flip($allowed)
    );
  }

  public function add(array $values) : Azul {
    foreach($values as $key => $value) {
      $this->$key = $value;
    }
    return $this;
  }

  public function config(array $settings = null) : array {
    if ($settings) 
      $this->settings = array_merge($this->settings, $settings);

    return (array) $this->settings;
  }


  public function set(string $key, $value) : void {
    $this->settings[$key] = $value;
  }

  public function get(string $key) {
    return $this->settings[$key];
  }


  public static function contains(array $expected, array $target) : bool {
    $expected = array_flip($expected);
    $got = array_intersect_key($target, $expected);
    return count($got) === count($expected);
  }

  public static function requires( array $data, array $expected) : void {

    if ( ! self::contains( $expected, $data ) )
      throw new AzulException(
        "This method requires: [".implode(",", $expected) . "]"
      );

  }

  public static function requiresEither (array $data) : void {
    $args = func_get_args();
    array_shift($args);

    foreach ( $args as $expected ) 
      if ( self::contains( $expected, $data ) ) return;

    $sArgs = implode(" or ",
      array_map(function($a) {
        return '['.implode(",",$a).']';
      }, $args)
    );

    throw new AzulException( "This method requires: {$sArgs}" );
  }

  protected function throwIfCurlError($ch, $details) : void {
    $errno = curl_errno($ch);
    if ($errno) {
      $message = "cURL ({$errno}): " . curl_strerror($errno) . ", " . curl_error($ch);
      $message .= $this->get('testing') ? json_encode($details) : "";
      curl_close($ch);
      throw new AzulException($message, $details);
    }
  }

  protected function throwIfAzulError(stdClass $response, $details) : void {
    if ($response->IsoCode  !== '00') 
      throw new AzulException(
        "Hubo un error procesando su método de pago, intente con otra tarjeta o contacte su banco\n" . ($this->get('testing') ? json_encode($details): ""), 
        $details
      );
  }

  protected function format($name, $value) {

    switch ($name) {

    case 'Amount':
    case 'Itbis':
      if (is_string($value)) 
        throw new AzulException("{$name} should be int or float");

      return is_float($value) ? (int)number_format($value, 2, '', '') 
        : $value;
      break;

    case 'OriginalDate':
      if (strlen(strval($value)) < 8 )
        throw new AzulException("$name should be 8+ characters long");
      return (int)substr(strval($value), 0, 8);
      break;

    default:
      return $value;
      break;
    }
  }
}

