<?php

class ApiNotFound extends Api {

  function handle() {
    return [ 'code' => STATE_API_NOT_FOUND, 'message' => 'Api (' . $GLOBALS['api'] . ') not found' ];
  }

}