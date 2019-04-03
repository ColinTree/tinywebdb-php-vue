<?php

final class DbProvider {

  private static $db;

  static function setDb(DbBase $db) {
    self::$db = $db;
  }

  static function getDb() {
    return self::$db;
  }

}