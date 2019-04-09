<?php

abstract class DbBase {

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
   */
  abstract function getPage(int $page = 1, int $perPage = 100, string $prefix = '');

}