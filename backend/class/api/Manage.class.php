<?php

class ApiManage extends Api {

  private static function password() {
    return DbProvider::getDb()->get(DbBase::$KEY_MANAGE_PASSWORD);
  }
  private static function initialized() {
    return self::password() !== false;
  }
  private static function passwordCorrect($pwd) {
    return $pwd === self::password();
  }
  private static function saltPassword($pwd) {
    return md5('tpv-salt' . $pwd);
  }

  private static function settings() {
    return json_decode(DbProvider::getDb()->get(DbBase::$KEY_MANAGE_SETTINGS), true);
  }
  private static function updateSetting($settingId, $value) {
    $settings = self::settings();
    $settings[$settingId] = $value;
    DbProvider::getDb()->set(DbBase::$KEY_MANAGE_SETTINGS, json_encode($settings));
  }

  function handle() {
    $key = (string) $_REQUEST['key'];
    $value = (string) $_REQUEST['value'];

    $args = explode('/', $GLOBALS['args'], 2);
    $action = strtolower($args[0]);
    unset($args);

    $generateSession = function($pwd = null) {
      $sessionid = uniqid('manage_', true);
      session_id($sessionid);
      session_start();
      if ($pwd !== null) {
        $_SESSION['pwd'] = $pwd;
      }
      return $sessionid;
    };
    switch ($action) {
      case 'login': {
        $pwd = self::saltPassword($_POST['pwd']);
        if (self::passwordCorrect($pwd)) {
          return [ 'result' => [ 'succeed' => true, 'token' => $generateSession($pwd) ] ];
        } else {
          return [ 'result' => [ 'succeed' => false ] ];
        }
      }
      case 'init': {
        if (self::initialized()) {
          return [ 'state' => STATE_UNAUTHORIZED, 'result' => 'System had been initialized' ];
        } else {
          $pwd = $_POST['pwd'];
          if (strlen($pwd) < 8) {
            return [ 'state' => STATE_PASSWORD_TOO_SHORT, 'result' => 'Length of password should equal or greater than 8' ];
          }
          if (preg_match('/^\d+$/', $pwd) == 1 ||
              preg_match('/^[a-z]+$/i', $pwd) == 1 ||
              preg_match('/^[0-9a-z!@#$%^&*]+$/i', $pwd) == 0) {
            return [ 'state' => STATE_PASSWORD_INVALID, 'result' => 'Password should contains at least two of [0-9] [a-z] [!@#$%^&*]' ];
          }
          $pwd = self::saltPassword($pwd);
          DbProvider::getDb()->set(DbBase::$KEY_MANAGE_PASSWORD, $pwd);
          return [ 'result' => $generateSession($pwd) ];
        }
      }
    }
    unset($generateSession);

    session_id($_SERVER['HTTP_X_TPV_MANAGE_TOKEN']);
    session_start();

    switch ($action) {
      case 'logout': {
        session_destroy();
        return [ 'result' => 'sure' ];
      }
      case 'ping': {
        return [ 'result' => [ 'login' => self::passwordCorrect($_SESSION['pwd']), 'initialized' => self::initialized() ] ];
      }
      case 'deletepwd': {
        $result = DbProvider::getDb()->delete(DbBase::$KEY_MANAGE_PASSWORD);
        return [ 'result' => var_export($result) ];
      }
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
      case 'settings': {
        return [ 'result' => self::settings() ];
      }
      case 'setting_update': {
        $settingId = (string) $_REQUEST['settingId'];
        switch ($settingId) {
          case 'all_category': {
            self::updateSetting($settingId, $value);
            return [ 'result' => 'Succeed' ];
          }
          default: {
            return [ 'state' => STATE_API_FAILED, 'result' => 'The settingId can not be recognised.' ];
          }
        }
      }
    }
    return [ 'state' => STATE_API_NOT_FOUND, 'result' => "Unimplemented managing api: $action" ];
  }

}