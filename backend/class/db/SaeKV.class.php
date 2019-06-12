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
      throw new Exception("SaeKV init failed: {$this->kv->errno()}");
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

  function mDelete(array $keys) {
    $retarr = [];
    foreach ($keys as $index => $key) {
      if (!is_string($key)) {
        $retarr[$key] = false;
        continue;
      }
      $retarr[$key] = $this->delete($key);
    }
    return $retarr;
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

  function getAll(string $prefix = '') {
    return new DbSaeKVIterator($prefix, $this->kv);
  }

  static function obj2arr($obj) {
    $ret = [];
    foreach ($obj as $key => $value) {
      $ret[] = [ 'key' => $key, 'value' => $value ];
    }
    return $ret;
  }

}

class DbSaeKVIterator implements Iterator {

  private $prefix = '';
  private $kv;

  private $startKey = '';
  // one-based
  private $page = 0;
  private $currentValue = [];
  // zero-based
  private $position = -1;

  function __construct(string $prefix, SaeKV $kv) {
    $this->prefix = $prefix;
    $this->kv = $kv;
    $this->rewind();
  }

  public function current() {
    return $this->currentValue[$this->position];
  }
  public function key() {
    return ($this->page - 1) * 100 + $this->position;
  }
  public function next() {
    if ($this->position >= 0 && $this->position < count($this->currentValue)) {
      $this->position ++;
    } else {
      $this->page ++;
      $this->position = 0;
      $this->currentValue = $this->kv->pkrget($this->prefix, 100, $this->startKey);
      end($this->currentValue);
      $this->startKey = key($this->currentValue);
      $this->currentValue = DbSaeKV::obj2arr($this->currentValue);
    }
  }
  public function rewind() {
    $this->startKey = '';
    $this->page = 0;
    $this->position = -1;
    $this->next();
  }
  public function valid() {
    return count($this->currentValue) > 0
        && $this->position < count($this->currentValue);
  }

}