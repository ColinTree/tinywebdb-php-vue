<?php

abstract class DbBase {

  /**
   * delete key-value
   *
   * @param string $key
   * @return bool succeed or not
   */
  abstract function delete(String $key);

  /**
   * add / update value of the key
   *
   * @param string $key
   * @param string $value
   * @return bool succeed or not
   */
  abstract function set(String $key, String $value);

  /**
   * get value of the key
   *
   * @param string $key
   * @return string|bool value when succeed OR false when there is no such key
   */
  abstract function get(String $key);

  /**
   * get list of values
   * @param int $page default to 1 (first page)
   * @param int $perPage default to 100, should between 1-100
   * @param string $prefix default to empty (get all keys)
   */
  abstract function getPage(int $page = 1, int $perPage = 100, String $prefix = '');

}