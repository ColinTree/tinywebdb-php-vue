<?php

class ApiManage extends Api {

  function handle() {
    $key = (string) $_REQUEST['key'];
    $value = (string) $_REQUEST['value'];

    $args = explode('/', $GLOBALS['args'], 2);
    $action = strtolower($args[0]);
    unset($args);

    switch ($action) {
      case 'has': {
        return DbProvider::getDb()->has($key);
      }
      case 'count': {
        $ret = DbProvider::getDb()->count((string) $_REQUEST['prefix']);
        return $ret !== false ? $ret : [ 'state' => STATE_API_FAILED, 'result' => 'Cannot count keys' ];
      }
      case 'get': {
        $ret = DbProvider::getDb()->get($key);
        return $ret !== false ? $ret : [ 'state' => STATE_KEY_NOT_FOUNT, 'result' => "No record for key: $key" ];
      }
      case 'set': {
        return (DbProvider::getDb()->set($key, $value))
            ? "Value of key ($key) set succeed"
            : [ 'state' => STATE_API_FAILED, 'result' => "Failed setting key: $key" ];
      }
      case 'add': {
        return (DbProvider::getDb()->add($key, $value))
            ? "Value of key ($key) added"
            : [ 'state' => STATE_KEY_ALREADY_EXIST, 'result' => "Key already exist: $key" ];
      }
      case 'update': {
        return (DbProvider::getDb()->update($key, $value))
            ? "Value of key ($key) updated"
            : [ 'state' => STATE_KEY_NOT_FOUNT, 'result' => "Key not found: $key" ];
      }
      case 'delete': {
        return (DbProvider::getDb()->delete($key))
            ? "Key ($key) deleted"
            : [ 'state' => STATE_API_FAILED, 'result' => "Failed deleting key: $key" ];
      }
      case 'mdelete': {
        return [ 'result' => DbProvider::getDb()->mDelete(json_decode((string) $_REQUEST['keys'])) ];
      }
      case 'page': {
        $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
        $perPage = isset($_REQUEST['perPage']) ? (int) $_REQUEST['perPage'] : 100;
        $prefix = isset($_REQUEST['prefix']) ? (string) $_REQUEST['prefix'] : '';
        if ($perPage < 1 || $perPage > 100) {
          return [ 'state' => STATE_UNACCEPTED_LIMIT, 'result' => [] ];
        }
        return [ 'result' => DbProvider::getDb()->getPage($page, $perPage, $prefix) ];
      }
    }
    return [ 'state' => STATE_API_NOT_FOUND, 'result' => "Unimplemented managing api: $action" ];
  }

}