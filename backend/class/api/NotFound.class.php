<?php

class ApiNotFound extends Api {

  function handle() {
    return [ 'status' => STATUS_API_NOT_FOUND, 'result' => "Api ({$GLOBALS['api']}) not found" ];
  }

}