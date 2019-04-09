<?php

class ApiManage extends Api {

  function handle() {
    $args = explode('/', $GLOBALS['args'], 2);
    while (count($args) < 2) {
      $args[] = '';
    }
    $action = strtolower($args[0]);
    $key = (string) $args[1];
    switch ($action) {
      case 'has': {
        return DbProvider::getDb()->has($key) ? 'true' : 'false';
      }
      case 'count': {
        $ret = DbProvider::getDb()->count(/* as prefix */ $key);
        return $ret !== false
            ? '' . $ret // or it will be consider as returning code
            : [ 'code' => STATE_API_FAILED, 'message' => 'Cannot count keys' ];
      }
      case 'get': {
        $ret = DbProvider::getDb()->get($key);
        return $ret !== false
            ? $ret
            : [ 'code' => STATE_KEY_NOT_FOUNT, 'message' => 'No record for key: ' . $key ];
      }
      case 'set': {
        return (DbProvider::getDb()->set($key, (string) $_POST['value']))
            ? 'Value of key (' . $key . ') set succeed'
            : [ 'code' => STATE_API_FAILED, 'message' => 'Failed setting key: ' . $key ];
      }
      case 'add': {
        return (DbProvider::getDb()->add($key, (string) $_POST['value']))
            ? 'Value of key (' . $key . ') added'
            : [ 'code' => STATE_KEY_ALREADY_EXIST, 'message' => 'Key already exist: ' . $key ];
      }
      case 'update': {
        return (DbProvider::getDb()->update($key, (string) $_POST['value']))
            ? 'Value of key (' . $key . ') updated'
            : [ 'code' => STATE_KEY_NOT_FOUNT, 'message' => 'Key not found: ' . $key ];
      }
      case 'delete': {
        return (DbProvider::getDb()->delete($key))
            ? 'Key (' . $key . ') deleted'
            : [ 'code' => STATE_KEY_NOT_FOUNT, 'message' => 'Key not found: ' . $key ];
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