<?php

class ApiNotFound extends Api {

  function handle() {
    echo 'Api (' . $GLOBALS['api'] . ') not found';
    return STATE_API_NOT_FOUND;
  }

}