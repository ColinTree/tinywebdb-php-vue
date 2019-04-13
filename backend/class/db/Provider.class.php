<?php

final class DbProvider {

  private static $db;

  static function setDb(DbBase $db) {
    self::$db = $db;
  }

  static function getDb() {
    if (self::$db instanceof DbBase) {
      return self::$db;
    }
    throw new Error('No database can be provided');
  }

}