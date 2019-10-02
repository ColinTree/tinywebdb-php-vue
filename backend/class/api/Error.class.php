<?php

class ApiError extends Api {

  private $status;
  private $message;

  function __construct(string $message = 'Internal Error', int $status = STATUS_INTERNAL_ERROR) {
    $this->status = $status;
    $this->message = $message;
    parent::__construct();
  }

  function handle() {
    return [ 'status' => $this->status, 'result' => $this->message ];
  }

}