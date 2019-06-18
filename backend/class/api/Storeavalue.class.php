<?php

class ApiStoreavalue extends Api {

  function handle() {
    ApiGetvalue::checkAllowBrowser();
    $key = (string) $_REQUEST['tag'];
    $value = (string) $_REQUEST['value'];
    if (DbBase::keyReserved($key)) {
      http_response_code(403);
      die(json_encode([ 'STORED', $key, 'Key reserved, refuse to store' ]));
    }
    DbProvider::getDb()->set($key, $value);
    die(json_encode([ 'STORED', $key, $value ]));
  }

}