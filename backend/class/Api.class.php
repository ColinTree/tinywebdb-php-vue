<?php

const STATUS_SUCCEED = 0;
const STATUS_API_NOT_FOUND = 1;  // with http code 404
const STATUS_API_FAILED = 2;     // with http code 503
const STATUS_INTERNAL_ERROR = 3; // with http code 500
const STATUS_UNAUTHORIZED = 4;   // with http code 401
const STATUS_KEY_NOT_FOUNT = 10;
const STATUS_KEY_RESERVED = 11;
const STATUS_UNACCEPTED_LIMIT = 20;
const STATUS_KEY_ALREADY_EXIST = 30;
const STATUS_PASSWORD_TOO_SHORT = 40;
const STATUS_PASSWORD_INVALID = 41;
const STATUS_SETTING_NOT_RECOGNISED = 50;
const STATUS_EXPORT_UNACCEPTED_TYPE = 60;

abstract class Api {

  /**
   * This would return string result depend on config `DEBUG_MODE`
   */
  static function throwable2string(throwable $t) {
    return defined('DEBUG_MODE') && DEBUG_MODE === true ? $t->__toString() : $t->getMessage();
  }

  function __construct() {
    header('Content-Type: application/json');
    if (defined('ACCESS_CONTROL_ALLOW_ORIGIN')) {
      header('Access-Control-Allow-Origin: ' . ACCESS_CONTROL_ALLOW_ORIGIN);
    }
    header('Access-Control-Allow-Headers: ' . implode(', ', [ 'Content-Type', 'X-TPV-Manage-Token' ]));

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      exit;
    }

    ob_start();
    try {
      $handleResult = $this->handle();
    } catch (Throwable $t) {
      $handleResult = [ 'status' => STATUS_INTERNAL_ERROR, 'result' => self::throwable2string($t) ];
    } finally {
      $result = ob_get_clean();
    }

    if (isset($handleResult['noProcess']) && $handleResult['noProcess'] === true) {
      echo $handleResult['result'];
      return;
    }

    $status = STATUS_SUCCEED;
    if (is_null($handleResult)) {
      // ignore
    } else if (is_array($handleResult)) {
      if (isset($handleResult['status'])) {
        $status = $handleResult['status'];
      }
      if (isset($handleResult['result'])) {
        $result = $handleResult['result'];
      }
      if (isset($handleResult['extra'])) {
        $extraResult = $handleResult['extra'];
      }
    } else {
      $result = $handleResult;
    }

    $http_code = 200;
    switch ($status) {
      case STATUS_API_NOT_FOUND:
        $http_code = 404; break;
      case STATUS_API_FAILED:
        $http_code = 503; break;
      case STATUS_INTERNAL_ERROR:
        $http_code = 500; break;
      case STATUS_UNAUTHORIZED:
        $http_code = 401; break;
    }
    http_response_code($http_code);
    $response = isset($extraResult) ? $extraResult : [];
    $response['status'] = $status;
    $response['result'] = $result;
    exit(json_encode($response));
  }

  /**
   * All echo would be treated as default output result.
   * Args from the get request can be found in $GLOBALS['args]
   * @return mixed null, array for [ 'status': status, 'result': result ] or any other for result message
   */
  abstract function handle();
}