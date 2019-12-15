<?php namespace Azul;

use Exception;

class AzulException extends Exception {

  protected $details;

  public function __construct($message = "", $details = null) {
    $this->details = $details;

    parent::__construct($message);
  }

  public function getDetails() {
    return $this->details;
  }

}
