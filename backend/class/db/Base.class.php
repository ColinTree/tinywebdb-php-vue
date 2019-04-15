<?php

abstract class DbBase {

  public static $KEY_RESERVED_PREFIX = '___RESERVED_';
  public static $KEY_MANAGE_SETTINGS = '___RESERVED_MANAGE_SETTINGS';

  public static function keyReserved ($key) {
    $len = strlen(self::$KEY_RESERVED_PREFIX);
    return strlen($key) >= $len && substr($key, 0, $len) == self::$KEY_RESERVED_PREFIX;
  }

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
  abstract function mDelete(array $keys);

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
   * @return list list of [ key, value ] pairs
   */
  abstract function getPage(int $page = 1, int $perPage = 100, string $prefix = '');

}