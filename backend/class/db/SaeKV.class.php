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

  function delete(String $key) {
    return $this->kv->delete($key);
  }

  function set(String $key, String $value) {
    return $this->kv->set($key, $value);
  }

  function get(String $key) {
    return $this->kv->get($key);
  }

  function getPage(int $page = 1, int $perPage = 100, String $prefix = '') {
    $start_key = '';
    $currPage = 0;
    while ($currPage < $page) {
      $currPage++;
      $ret = $this->kv->pkrget($prefix, $perPage, $start_key);
      if (count($ret) < $perPage) {
        return $currPage < $page ? [] : $ret;
      }
      end($ret);
      $start_key = key($ret);
    }
    return $ret;
  }

}