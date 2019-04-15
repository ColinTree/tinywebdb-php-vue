<?php

class ApiGetvalue extends Api {

  function handle() {
    $key = (string) $_REQUEST['key'];
    if (DbBase::keyReserved($key)) {
      http_response_code(403);
      return [ 'noProcess' => true, 'result' => json_encode([ 'KEY RESERVED', $key, '' ]) ];
    }
    return [ 'noProcess' => true, 'result' => json_encode([ 'VALUE', $key, (string) DbProvider::getDb()->get($key) ]) ];
  }

}