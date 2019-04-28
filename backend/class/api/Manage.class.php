<?php

class ApiManage extends Api {

  private static function passwordCorrect ($pwd) {
    return $pwd == md5('tpv-salt' . 'password');// TODO: implement this
  }
  private static function saltPassword($pwd) {
    return md5('tpv-salt' . $pwd);
  }

  function handle() {
    $key = (string) $_REQUEST['key'];
    $value = (string) $_REQUEST['value'];

    $args = explode('/', $GLOBALS['args'], 2);
    $action = strtolower($args[0]);
    unset($args);

    if ($action == 'login') {
      $pwd = self::saltPassword($_POST['pwd']);
      if (self::passwordCorrect($pwd)) {
        $sessionid = uniqid('manage_', true);
        session_id($sessionid);
        session_start();
        $_SESSION['pwd'] = $pwd;
        return [ 'result' => [ 'succeed' => true, 'token' => $sessionid ] ];
      } else {
        return [ 'result' => [ 'succeed' => false ] ];
      }
    }

    session_id($_SERVER['HTTP_X_TPV_MANAGE_TOKEN']);
    session_start();

    if ($action == 'logout') {
      session_destroy();
      return [ 'result' => 'sure' ];
    }

    if (!self::passwordCorrect($_SESSION['pwd'])) {
      return [
        'state' => STATE_UNAUTHORIZED,
        'result' => "Cannot login with this token: token is empty, unaccepted, outdated or password had been changed since login"
      ];
    }
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
        if (DbBase::keyReserved($key)) {
          return [ 'state' => STATE_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->set($key, $value))
            ? "Value of key ($key) set succeed"
            : [ 'state' => STATE_API_FAILED, 'result' => "Failed setting key: $key" ];
      }
      case 'add': {
        if (DbBase::keyReserved($key)) {
          return [ 'state' => STATE_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->add($key, $value))
            ? "Value of key ($key) added"
            : [ 'state' => STATE_KEY_ALREADY_EXIST, 'result' => "Key already exist: $key" ];
      }
      case 'update': {
        if (DbBase::keyReserved($key)) {
          return [ 'state' => STATE_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->update($key, $value))
            ? "Value of key ($key) updated"
            : [ 'state' => STATE_KEY_NOT_FOUNT, 'result' => "Key not found: $key" ];
      }
      case 'delete': {
        if (DbBase::keyReserved($key)) {
          return [ 'state' => STATE_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->delete($key))
            ? "Key ($key) deleted"
            : [ 'state' => STATE_API_FAILED, 'result' => "Failed deleting key: $key" ];
      }
      case 'mdelete': {
        $keys = json_decode((string) $_REQUEST['keys']);
        if (!is_array($keys)) {
          $keys = [];
        }
        $reserved = [];
        foreach ($keys as $index => $key) {
          if (DbBase::keyReserved($key)) {
            $reserved[] = $key;
            unset($keys[$index]);
          }
        }
        $result = DbProvider::getDb()->mDelete($keys);
        foreach ($reserved as $key) {
          $result[$key] = false;
        }
        return [ 'result' => $result ];
      }
      case 'page': {
        $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
        $perPage = isset($_REQUEST['perPage']) ? (int) $_REQUEST['perPage'] : 100;
        $prefix = isset($_REQUEST['prefix']) ? (string) $_REQUEST['prefix'] : '';
        $valueLengthLimit = isset($_REQUEST['valueLengthLimit']) ? (int) $_REQUEST['valueLengthLimit'] : 0;
        if ($perPage < 1 || $perPage > 100) {
          return [ 'state' => STATE_UNACCEPTED_LIMIT, 'result' => [] ];
        }
        $result = DbProvider::getDb()->getPage($page, $perPage, $prefix);
        if ($valueLengthLimit > 0) {
          foreach ($result as &$item) {
            $item['value'] = substr($item['value'], 0, $valueLengthLimit);
          }
        }
        return [ 'result' => $result ];
      }
    }
    return [ 'state' => STATE_API_NOT_FOUND, 'result' => "Unimplemented managing api: $action" ];
  }

}