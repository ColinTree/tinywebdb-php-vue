<?php

class ApiManage extends Api {

  function handle() {
    $args = explode('/', $GLOBALS['args'], 2);
    while (count($args) < 2) {
      $args[] = '';
    }
    $action = strtolower($args[0]);
    $key = (String) $args[1];
    switch ($action) {
      case 'get': {
        $ret = DbProvider::getDb()->get($key);
        return $ret !== false
            ? $ret
            : [ 'code' => STATE_KEY_NOT_FOUNT, 'message' => 'No record for key: ' . $key ];
      }
      case 'set': {
        return (DbProvider::getDb()->set($key, $_POST['value']))
            ? 'Value of key (' . $key . ') set succeed'
            : [ 'code' => STATE_API_FAILED, 'message' => 'Failed setting key: ' . $key ];
      }
      case 'delete': {
        return (DbProvider::getDb()->delete($key))
            ? 'Key (' . $key . ') deleted'
            : [ 'code' => STATE_API_FAILED, 'message' => 'Failed deleting key: ' . $key ];
      }
      case 'page': {
        $args = explode(ARG_SEPERATOR, $key, 3);
        if (count($args) >= 2 && ($args[1] < 1 || $args[1] > 100)) {
          echo json_encode([]);
          return STATE_UNACCEPTED_LIMIT;
        }
        return json_encode(DbProvider::getDb()->getPage(...$args));
      }
    }
    echo 'Unimplemented managing api: ' . $args[0];
    return STATE_API_NOT_FOUND;
  }

}