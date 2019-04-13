<?php

class DbSaeKV extends DbBase {

  private $kv;

  function __construct($accessKey = null, $timeout = 3000) {
    $this->kv = new SaeKV($timeout);
    if (is_null($accessKey)) {
      $ret = $this->kv->init();
    } else {
      $ret = $this->kv->init($accessKey);
    }
    if (!$ret) {
      throw new Exception('SaeKV init failed: ' . $this->kv->errno());
    }
  }

  function has(string $key) {
    return $this->get($key) !== false;
  }

  function count(string $prefix = '') {
    $count = 0;
    $start_key = '';
    while (true) {
      $ret = $this->kv->pkrget($prefix, 100, $start_key);
      if ($ret === false) {
        return false;
      }
      $count += ($c = count($ret));
      if ($c < 100) break;
      end($ret);
      $start_key = key($ret);
    }
    return $count;
  }

  function delete(string $key) {
    return $this->kv->delete($key);
  }

  function set(string $key, string $value) {
    return $this->kv->set($key, $value);
  }

  function add(string $key, string $value) {
    return $this->kv->add($key, $value);
  }

  function update(string $key, string $value) {
    return $this->kv->replace($key, $value);
  }

  function get(string $key) {
    return $this->kv->get($key);
  }

  function getPage(int $page = 1, int $perPage = 100, string $prefix = '') {
    $start_key = '';
    $currPage = 0;
    while ($currPage < $page) {
      $currPage++;
      $ret = $this->kv->pkrget($prefix, $perPage, $start_key);
      if (count($ret) < $perPage) {
        return $currPage < $page ? [] : self::obj2arr($ret);
      }
      end($ret);
      $start_key = key($ret);
    }
    return self::obj2arr($ret);
  }

  private static function obj2arr($obj) {
    $ret = [];
    foreach ($obj as $key => $value) {
      $ret[] = [ 'key' => $key, 'value' => $value ];
    }
    return $ret;
  }

}