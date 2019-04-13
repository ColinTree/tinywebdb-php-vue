<?php

class ApiError extends Api {

  private $message;

  function __construct(string $message = 'Internal Error') {
    $this->message = $message;
    parent::__construct();
  }

  function handle() {
    return [ 'code' => STATE_INTERNAL_ERROR, 'message' => $this->message ];
  }

}