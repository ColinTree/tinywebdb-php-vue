<?php

function loadClass($className) {
  $possible = [];
  if (substr($className, 0, 3) == 'Api') {
    $apiName = substr($className, 3);
    $possible[] = "class/api/$apiName.class.php";
  } else if (substr($className, 0, 2) == 'Db') {
    $dbName = substr($className, 2);
    $possible[] = "class/db/$dbName.class.php";
  }
  $possible[] = "class/mock/$className.class.php";
  foreach ($possible as &$path) {
    if (file_exists($path)) {
      require_once $path;
    }
  }
  if (!class_exists($className)) {
    throw new Exception("Class not found: $className");
  }
}
spl_autoload_register('loadClass');

require_once 'class/Api.class.php';

if (!file_exists('config.php')) {
  new ApiError('Server is not configured yet.');
  exit;
}

try {
  require_once 'config.php';

  $a = isset($_GET['a']) && $_GET['a'] != '' ? $_GET['a'] : 'Index';
  if (substr($a, 0, 1) == '/') {
    $a = substr($a, 1);
  }
  $a = explode('/', $a, 2);
  $a[0] = ucfirst(strtolower($a[0]));
  $api = "Api$a[0]";
  if ($api == 'Api') {
    $api .= 'Index';
  }
  $args = isset($a[1]) ? (string) $a[1] : '';
  unset($a);
} catch (Throwable $t) {
  new ApiError(Api::throwable2string($t));
  exit;
}

try {
  loadClass($api);
} catch (Exception $e) {
  new ApiNotFound();
  exit;
}

try {
  new $api;
} catch (Throwable $t) {
  new ApiError(Api::throwable2string($t));
}