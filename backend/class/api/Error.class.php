<?php

class ApiError extends Api {

  private $message;

  function __construct(string $message = 'Internal Error') {
    $this->message = $message;
    parent::__construct();
  }

  function handle() {
    return [ 'state' => STATE_INTERNAL_ERROR, 'result' => $this->message ];
  }

}