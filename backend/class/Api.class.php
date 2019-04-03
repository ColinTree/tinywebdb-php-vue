<?php

const STATE_SUCCEED = 0;
const STATE_API_NOT_FOUND = 1;  // with http code 404
const STATE_API_FAILED = 2;     // with http code 503
const STATE_INTERNAL_ERROR = 3; // with http code 500
const STATE_KEY_NOT_FOUNT = 10;
const STATE_UNACCEPTED_LIMIT = 20;

const ARG_SEPERATOR = ';;';

abstract class Api {

  function __construct() {
    ob_start();
    try {
      $result = $this->handle();
    } catch (Throwable $t) {
      $result = [ 'code' => STATE_INTERNAL_ERROR, 'message' => DEBUG_MODE === true ? $t->__toString() : $t->getMessage() ];
    } finally {
      $message = ob_get_clean();
    }

    $state = STATE_SUCCEED;
    if (is_null($result)) {
      // ignore
    } else if (is_int($result)) {
      $state = $result;
    } else if (is_array($result)) {
      $state = $result['code'];
      $message = $result['message'];
    } else {
      $message = (string) $result;
    }

    $http_code = 200;
    switch ($state) {
      case STATE_API_NOT_FOUND:
        $http_code = 404; break;
      case STATE_API_FAILED:
        $http_code = 503; break;
      case STATE_INTERNAL_ERROR:
        $http_code = 500; break;
    }
    http_response_code($http_code);
    header('Content-Type: application/json');
    if (defined('ACCESS_CONTROL_ALLOW_ORIGIN')) {
      header('Access-Control-Allow-Origin: ' . ACCESS_CONTROL_ALLOW_ORIGIN);
    }
    echo json_encode([
      'state' => $state,
      'result' => $message
    ]);
  }

  /**
   * All echo would be treated as default output message.
   * Args from the get request can be found in $GLOBALS['args]
   * @return mixed null, int for code, array for [ 'code': code, 'message': message ] or any other for message replacement
   */
  abstract function handle();
}