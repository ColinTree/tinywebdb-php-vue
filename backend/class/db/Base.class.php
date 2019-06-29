<?php

abstract class DbBase {

  public static $KEY_RESERVED_PREFIX = '___RESERVED_';
  public static $KEY_MANAGE_SETTINGS = 'MANAGE_SETTINGS';
  public static $KEY_MANAGE_PASSWORD = 'MANAGE_PASSWORD';

  public static function keyReserved ($key) {
    $len = strlen(self::$KEY_RESERVED_PREFIX);
    return strlen($key) >= $len && substr($key, 0, $len) == self::$KEY_RESERVED_PREFIX;
  }

  function hasReserved(string $key) {
    return $this->has(self::$KEY_RESERVED_PREFIX . $key);
  }
  function getReserved(string $key) {
    return $this->get(self::$KEY_RESERVED_PREFIX . $key);
  }
  function setReserved(string $key, string $value) {
    return $this->set(self::$KEY_RESERVED_PREFIX . $key, $value);
  }
  function deleteReserved(string $key) {
    return $this->delete(self::$KEY_RESERVED_PREFIX . $key);
  }

  public static function getSettings() {
    $settings = DbProvider::getDb()->getReserved(self::$KEY_MANAGE_SETTINGS);
    return json_decode($settings === false ? '{}' : $settings, true);
  }
  public static function getSetting(string $settingId, $default = NULL) {
    $settingValue = self::getSettings()[$settingId];
    return isset($settingValue) ? $settingValue : $default;
  }

  public static $SEARCH_RESULT_PER_PAGE = 20;

  /**
   * check if key exists
   *
   * @param string $key
   * @return bool key exists or not
   */
  abstract function has(string $key);

  /**
   * get count of keys with specified prefix in database
   *
   * @return int|boolean count of keys in database, or false when failed
   */
  abstract function count(string $prefix = '');

  /**
   * delete key-value
   *
   * @param string $key
   * @return bool succeed or not
   */
  abstract function delete(string $key);

  /**
   * delete pairs of key-value
   *
   * @param array<string> $keys
   * @return array<string=>bool> array of succeed or not, with keys paired
   */
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

  /**
   * add / update value of the key
   *
   * @param string $key
   * @param string $value
   * @return bool succeed or not
   */
  abstract function set(string $key, string $value);

  /**
   * add value to the key
   *
   * @param string $key
   * @param string $value
   * @return bool succeed or not
   */
  abstract function add(string $key, string $value);

  /**
   * update value of the key
   *
   * @param string $key
   * @param string $value
   * @return bool succeed or not
   */
  abstract function update(string $key, string $value);

  /**
   * get value of the key
   *
   * @param string $key
   * @return string|bool value when succeed OR false when there is no such key
   */
  abstract function get(string $key);

  /**
   * get list of values
   * @param int $page default to 1 (first page)
   * @param int $perPage default to 100, should between 1-100
   * @param string $prefix default to empty (get all keys)
   * @return array array of [ key, value ] pairs
   */
  abstract function getPage(int $page = 1, int $perPage = 100, string $prefix = '');

  /**
   * get all of values in db via iterator
   * @param string $prefix default to empty (get all keys)
   * @return Iterator each iterator value should be a key-value object array (e.g. `['key'=>'key1', 'value'=>'val1']`)
   */
  abstract function getAll(string $prefix = '');

  /**
   * get all of values in db via iterator
   * @param string $text text to search
   * @param int $page page number of search result (20 a page)
   * @param bool $ignoreCase whether case matters or not
   * @param array $range search range (fields names)
   * @return Iterator each iterator value should be a key-value object array (e.g. `['key'=>'key1', 'value'=>'val1']`)
   */
  abstract function search(string $text, int $page, bool $ignoreCase, array $range);

  /**
   * erase all keys that is not reserved
   */
  abstract function eraseData();

  /**
   * erase all keys (include reserved keys)
   */
  abstract function eraseAll();

}

abstract class DbBaseIterator implements Iterator {

  public static $PERPAGE = 100;

  protected $prefix = '';

  // one-based
  protected $page = 0;
  protected $currentValue = [];
  // zero-based
  protected $position = -1;

  function __construct(string $prefix) {
    $this->prefix = $prefix;
    $this->rewind();
  }

  /**
   * Called in next(), should load next page into $currentValue
   */
  abstract function getNextPage();

  public function current() {
    return $this->currentValue[$this->position];
  }
  public function key() {
    return ($this->page - 1) * 100 + $this->position;
  }
  public function next() {
    if ($this->position >= 0 && $this->position + 1 < count($this->currentValue)) {
      $this->position ++;
    } else {
      $this->page ++;
      $this->position = 0;
      $this->getNextPage();
    }
  }
  public function rewind() {
    $this->page = 0;
    $this->position = -1;
    $this->next();
  }
  public function valid() {
    return count($this->currentValue) > 0
        && $this->position < count($this->currentValue);
  }

}