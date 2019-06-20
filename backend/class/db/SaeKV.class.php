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

  function search(string $text, int $page, bool $ignoreCase, array $range) {
    if ($ignoreCase) {
      $text = strtolower($text);
    }
    $countStartIndex = self::$SEARCH_RESULT_PER_PAGE * ($page - 1);
    $result = [];
    $count = 0;
    foreach ($this->getAll() as $index => $key_value_pair) {
      $match = false;
      foreach ($range as $field) {
        if (isset($key_value_pair[$field]) && $key_value_pair[$field]) {
          $field = $ignoreCase ? strtolower($key_value_pair[$field]) : $key_value_pair[$field];
          if (count(explode($text, $field, 2)) > 1) {
            $match = true;
            break;
          }
        }
      }
      if ($match) {
        $count++;
        if ($count > $countStartIndex && count($result) < self::$SEARCH_RESULT_PER_PAGE) {
          $result[] = $key_value_pair;
        }
      }
    }
    return [ 'count' => $count, 'result' => $result ];
  }

  function eraseData() {
    foreach ($this->getAll() as $index => $key_value_pair) {
      if (!DbBase::keyReserved($key_value_pair['key'])) {
        $this->delete($key_value_pair['key']);
      }
    }
  }

  function eraseAll() {
    foreach ($this->getAll() as $index => $key_value_pair) {
      $this->delete($key_value_pair['key']);
    }
  }

  static function obj2arr($obj) {
    $ret = [];
    foreach ($obj as $key => $value) {
      $ret[] = [ 'key' => $key, 'value' => $value ];
    }
    return $ret;
  }

}

class DbSaeKVIterator extends DbBaseIterator {

  private $kv;
  private $startKey = '';

  function __construct(string $prefix, SaeKV $kv) {
    $this->kv = $kv;
    parent::__construct($prefix);
  }

  public function getNextPage() {
    if ($this->page === 1 && $this->position === 0) {
      $this->startKey = '';
    }
    $this->currentValue = $this->kv->pkrget($this->prefix, DbBaseIterator::$PERPAGE, $this->startKey);
    end($this->currentValue);
    $this->startKey = key($this->currentValue);
    $this->currentValue = DbSaeKV::obj2arr($this->currentValue);
  }

}