<?php

class ApiManage extends Api {

  private static function password() {
    return DbProvider::getDb()->getReserved(DbBase::$KEY_MANAGE_PASSWORD);
  }
  private static function initialized() {
    return self::password() !== false;
  }
  private static function passwordCorrect($pwd) {
    return $pwd === self::password();
  }
  /**
   * Would refresh $_SESSION['last_timestamp']
   */
  private static function loginValid() {
    $TIMEOUT = defined('MANAGE_LOGIN_TIMEOUT') ? MANAGE_LOGIN_TIMEOUT : 600;
    $now = time();
    $valid = ($_SESSION['pwd'] === self::password()) && ($now - ((int) $_SESSION['last_timestamp']) < $TIMEOUT);
    if ($valid) {
      // refresh time
      $_SESSION['last_timestamp'] = $now;
    }
    return $valid;
  }
  private static function saltPassword($pwd) {
    return md5('tpv-salt' . $pwd);
  }

  private static function settings() {
    $settings = json_decode(DbProvider::getDb()->getReserved(DbBase::$KEY_MANAGE_SETTINGS), true);
    return $settings === '' ? new stdClass : $settings;
  }
  private static function updateSetting($settingId, $value) {
    $settings = self::settings();
    $settings[$settingId] = $value;
    DbProvider::getDb()->setReserved(DbBase::$KEY_MANAGE_SETTINGS, json_encode($settings));
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
        $_SESSION['last_timestamp'] = time();
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
          return [ 'status' => STATUS_UNAUTHORIZED, 'result' => 'System had been initialized' ];
        } else {
          $pwd = $_POST['pwd'];
          if (strlen($pwd) < 8) {
            return [ 'status' => STATUS_PASSWORD_TOO_SHORT, 'result' => 'Length of password should equal or greater than 8' ];
          }
          if (preg_match('/^\d+$/', $pwd) == 1 ||
              preg_match('/^[a-z]+$/i', $pwd) == 1 ||
              preg_match('/^[0-9a-z!@#$%^&*]+$/i', $pwd) == 0) {
            return [ 'status' => STATUS_PASSWORD_INVALID, 'result' => 'Password should contains at least two of [0-9] [a-z] [!@#$%^&*]' ];
          }
          $pwd = self::saltPassword($pwd);
          DbProvider::getDb()->setReserved(DbBase::$KEY_MANAGE_PASSWORD, $pwd);
          return [ 'result' => $generateSession($pwd) ];
        }
      }
      case 'export': {
        $_SERVER['HTTP_X_TPV_MANAGE_TOKEN'] = $_REQUEST['token'];
        break;
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
        return [ 'result' => [ 'login' => self::loginValid(), 'initialized' => self::initialized() ] ];
      }
    }

    if (!self::loginValid()) {
      return [
        'status' => STATUS_UNAUTHORIZED,
        'result' => "Cannot login with this token: token is empty, unaccepted, outdated or password had been changed since login"
      ];
    }
    switch ($action) {
      case 'add': {
        if (DbBase::keyReserved($key)) {
          return [ 'status' => STATUS_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->add($key, $value))
            ? "Value of key ($key) added"
            : [ 'status' => STATUS_KEY_ALREADY_EXIST, 'result' => "Key already exist: $key" ];
      }
      case 'count': {
        $ret = DbProvider::getDb()->count((string) $_REQUEST['prefix']);
        return $ret !== false ? $ret : [ 'status' => STATUS_API_FAILED, 'result' => 'Cannot count keys' ];
      }
      case 'delete': {
        if (DbBase::keyReserved($key)) {
          return [ 'status' => STATUS_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->delete($key))
            ? "Key ($key) deleted"
            : [ 'status' => STATUS_API_FAILED, 'result' => "Failed deleting key: $key" ];
      }
      case 'erase_all': {
        foreach (DbProvider::getDb()->getAll() as $index => $key_value_pair) {
          DbProvider::getDb()->delete($key_value_pair['key']);
        }
        return [ 'result' => 'All data erased' ];
      }
      case 'erase_data': {
        foreach (DbProvider::getDb()->getAll() as $index => $key_value_pair) {
          if (!DbBase::keyReserved($key_value_pair['key'])) {
            DbProvider::getDb()->delete($key_value_pair['key']);
          }
        }
        return [ 'result' => 'All data erased (except for reserved keys)' ];
      }
      case 'erase_pwd': {
        DbProvider::getDb()->deleteReserved(DbBase::$KEY_MANAGE_PASSWORD);
        return [ 'result' => 'Password deleted, please set a new one ASAP' ];
      }
      case 'export': {
        $type = (string) $_REQUEST['type'];
        $prefix = (string) $_REQUEST['prefix'];
        $includeReserved = ((string) $_REQUEST['include_reserved']) === 'true';
        switch (strtolower($type)) {
          case 'json': {
            ob_end_clean();
            header('Content-disposition: attachment; filename="export-' . time() . '.json"');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            echo '[';
            $first = true;
            foreach (DbProvider::getDb()->getAll($prefix) as $index => $key_value_pair) {
              if (!$includeReserved && DbBase::keyReserved($key_value_pair['key'])) {
                continue;
              }
              if ($first) { $first = false; } else { echo ','; }
              echo json_encode($key_value_pair);
            }
            echo ']';
            die();
          }
          case 'xml': {
            ob_end_clean();
            header('Content-disposition: attachment; filename="export-' . time() . '.xml"');
            header('Content-Type: application/xml');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            echo '<?xml version="1.0"?>' . "\n";
            echo '<tinywebdb>' . "\n";
            foreach (DbProvider::getDb()->getAll($prefix) as $index => $key_value_pair) {
              if (!$includeReserved && DbBase::keyReserved($key_value_pair['key'])) {
                continue;
              }
              $xmlEscaper = function($str = '') {
                $str = str_replace('&', '&amp;', $str);
                $str = str_replace('<', '&lt;', $str);
                $str = str_replace('>', '&gt;', $str);
                $str = str_replace('\'', '&apos;', $str);
                $str = str_replace('"', '&quot;', $str);
                $str = str_replace("\n", '&#10;', $str);
                return $str;
              };
              echo '<pair><key>';
              echo $xmlEscaper($key_value_pair['key']);
              echo '</key><value>';
              echo $xmlEscaper($key_value_pair['value']);
              echo '</value></pair>' . "\n";
            }
            echo '</tinywebdb>';
            die();
          }
          case 'csv': {
            ob_end_clean();
            header('Content-disposition: attachment; filename="export-' . time() . '.csv"');
            header('Content-Type: text/csv');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            foreach (DbProvider::getDb()->getAll($prefix) as $index => $key_value_pair) {
              if (!$includeReserved && DbBase::keyReserved($key_value_pair['key'])) {
                continue;
              }
              $csvEscaper = function($str = '') {
                $str = str_replace('"', '""', $str);
                return '"' . $str . '"';
              };
              echo $csvEscaper($key_value_pair['key']);
              echo ',';
              echo $csvEscaper($key_value_pair['value']);
              echo "\n";
            }
            die();
          }
          case 'xlsx': {
            if (!file_exists(__DIR__ . '/../../lib/xlsxwriter/xlsxwriter.class.php')) {
              return [ 'status' => STATUS_EXPORT_XLSX_UNSUPPORTED, 'result' => 'Please ensure submodule `xlsxwriter` is inited and updated' ];
            }
            ob_end_clean();
            header('Content-disposition: attachment; filename="export-' . time() . '.xlsx"');
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            require __DIR__ . '/../../lib/xlsxwriter/xlsxwriter.class.php';
            $writer = new XLSXWriter();
            $writer->writeSheetRow('Sheet1', [ 'key', 'value' ], [ 'font-style' => 'bold' ]);
            foreach (DbProvider::getDb()->getAll($prefix) as $index => $key_value_pair) {
              if (!$includeReserved && DbBase::keyReserved($key_value_pair['key'])) {
                continue;
              }
              $writer->writeSheetRow('Sheet1', [ $key_value_pair['key'], str_replace("\n", '\n', $key_value_pair['value']) ]);
            }
            $writer->writeToStdOut();
            die();
          }
          default: {
            return [ 'status' => STATUS_EXPORT_UNACCEPTED_TYPE, ];
          }
        }
      }
      case 'get': {
        $ret = DbProvider::getDb()->get($key);
        return $ret !== false ? $ret : [ 'status' => STATUS_KEY_NOT_FOUNT, 'result' => "No record for key: $key" ];
      }
      case 'has': {
        return DbProvider::getDb()->has($key);
      }
      case 'import_json': {
        if (!isset($_FILES["file"])) {
          return [ 'status' => STATUS_API_FAILED, 'result' => 'Where the f is the file?' ];
        }
        $importJson = json_decode(file_get_contents($_FILES["file"]["tmp_name"]), true);
        $failed = [];
        foreach ($importJson as $item) {
          if (DbBase::keyReserved($item['key'])) {
            $failed[] = $item['key'];
          } else {
            if (DbProvider::getDb()->set($item['key'], $item['value']) === false) {
              $failed[] = $item['key'];
            }
          }
        }
        return [ 'result' => [ 'failed' => $failed ] ];
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
          return [ 'status' => STATUS_UNACCEPTED_LIMIT, 'result' => [] ];
        }
        $returnResult = [];
        $returnResult['count'] = DbProvider::getDb()->count((string) $_REQUEST['prefix']);
        if (($page - 1) * $perPage >= $returnResult['count']) {
          $page = max(1, ceil($returnResult['count'] / $perPage));
        }
        $returnResult['actualPage'] = $page;
        $returnResult['content'] = DbProvider::getDb()->getPage($page, $perPage, $prefix);
        if ($valueLengthLimit > 0) {
          foreach ($returnResult['content'] as &$item) {
            $item['value'] = substr($item['value'], 0, $valueLengthLimit);
          }
        }
        return [ 'result' => $returnResult ];
      }
      case 'search': {
        $text = (string) $_REQUEST['text'];
        $ignoreCase = ((string) $_REQUEST['ignore_case'] === 'true');
        $range = (string) $_REQUEST['range'];
        if ($range === 'both') {
          $range = [ 'key', 'value' ];
        } else {
          $range = [ $range ];
        }
        $page = (int) $_REQUEST['page'];

        $result = DbProvider::getDb()->search($text, $page, $ignoreCase, $range);

        return [
          'result' => [
            'count' => $result['count'],
            'content' => $result['result']
          ]
        ];
      }
      case 'set': {
        if (DbBase::keyReserved($key)) {
          return [ 'status' => STATUS_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->set($key, $value))
            ? "Value of key ($key) set succeed"
            : [ 'status' => STATUS_API_FAILED, 'result' => "Failed setting key: $key" ];
      }
      case 'setting_update': {
        $settingId = (string) $_REQUEST['settingId'];
        switch ($settingId) {
          case 'all_category':
          case 'allow_browser':
          case 'special_tags': {
            self::updateSetting($settingId, $value);
            return [ 'result' => 'Succeed' ];
          }
          default: {
            return [ 'status' => STATUS_SETTING_NOT_RECOGNISED, 'result' => 'The settingId can not be recognised.' ];
          }
        }
      }
      case 'settings': {
        return [ 'result' => self::settings() ];
      }
      case 'update': {
        if (DbBase::keyReserved($key)) {
          return [ 'status' => STATUS_KEY_RESERVED, 'result' => "Key reserved: $key" ];
        }
        return (DbProvider::getDb()->update($key, $value))
            ? "Value of key ($key) updated"
            : [ 'status' => STATUS_KEY_NOT_FOUNT, 'result' => "Key not found: $key" ];
      }
    }
    return [ 'status' => STATUS_API_NOT_FOUND, 'result' => "Unimplemented managing api: $action" ];
  }

}