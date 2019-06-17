<?php

class ApiGetvalue extends Api {

  static function checkAllowBrowser() {
    if (json_decode(DbProvider::getDb()->get(DbBase::$KEY_MANAGE_SETTINGS), true)['allow_browser'] === 'false'
        && (isset($_SERVER['HTTP_ORIGIN'])
         || isset($_SERVER['HTTP_REFERER'])
         || isset($_SERVER['HTTP_USER_AGENT'])
         || isset($_SERVER['HTTP_COOKIE'])
         || $_SERVER['HTTP_ACCEPT'] != 'application/json')) {
      http_response_code(403);
      echo json_encode([ 'RESUFED VIA SETTING (allow_browser = false)', '', '' ]);
      die();
    }
  }

  function handle() {
    self::checkAllowBrowser();
    $key = (string) $_REQUEST['tag'];
    if (DbBase::keyReserved($key)) {
      http_response_code(403);
      return [ 'noProcess' => true, 'result' => json_encode([ 'KEY RESERVED', $key, '' ]) ];
    }
    return [ 'noProcess' => true, 'result' => json_encode([ 'VALUE', $key, (string) DbProvider::getDb()->get($key) ]) ];
  }

}