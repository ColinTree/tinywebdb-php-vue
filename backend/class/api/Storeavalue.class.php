<?php

class ApiStoreavalue extends Api {

  function handle() {
    $key = (string) $_REQUEST['key'];
    $value = (string) $_REQUEST['value'];
    if (DbBase::keyReserved($key)) {
      http_response_code(403);
      return [ 'noProcess' => true, 'result' => json_encode([ 'STORED', $key, 'Key reserved, refuse to store' ]) ];
    }
    DbProvider::getDb()->set($key, $value);
    return [ 'noProcess' => true, 'result' => json_encode([ 'STORED', $key, $value ]) ];
  }

}