<?php

class ApiNotFound extends Api {

  function handle() {
    return [ 'state' => STATE_API_NOT_FOUND, 'result' => 'Api (' . $GLOBALS['api'] . ') not found' ];
  }

}