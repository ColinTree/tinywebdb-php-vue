<?php

class ApiGetvalue extends Api {

  static function checkAllowBrowser() {
    if (json_decode(DbProvider::getDb()->get(DbBase::$KEY_MANAGE_SETTINGS), true)['allow_browser'] === 'false'
        && (isset($_SERVER['HTTP_ORIGIN'])
         || isset($_SERVER['HTTP_REFERER'])
         || isset($_SERVER['HTTP_USER_AGENT'])
         || isset($_SERVER['HTTP_COOKIE'])
         || $_SERVER['HTTP_ACCEPT'] != 'application/json')) {
      http_response_code(403);
      die(json_encode([ 'RESUFED VIA SETTING (allow_browser = false)', '', '' ]));
    }
  }

  private function handleSpecialTags($key) {
    $settings = json_decode(DbProvider::getDb()->get(DbBase::$KEY_MANAGE_SETTINGS), true);
    $special_tags = isset($settings['special_tags']) ? json_decode($settings['special_tags'], true) : [];
    $tag_count   = isset($special_tags['count'])   && !empty($special_tags['count'])   ? $special_tags['count']   : 'special_count';
    $tag_getall  = isset($special_tags['getall'])  && !empty($special_tags['getall'])  ? $special_tags['getall']  : 'special_getall';
    $tag_listget = isset($special_tags['listget']) && !empty($special_tags['listget']) ? $special_tags['listget'] : 'special_listget';
    $tag_search  = isset($special_tags['search'])  && !empty($special_tags['search'])  ? $special_tags['search']  : 'special_search';
    $tag = explode('#', $key, 2);
    while (count($tag) < 2) {
      $tag[] = '';
    }
    $params = explode('#', $tag[1]);
    $tag = $tag[0];
    switch ($tag) {
      case $tag_count: {
        $count = DbProvider::getDb()->count($params[0]);
        die(json_encode([ 'VALUE', $key, (string) $count ]));
      }
      case $tag_getall: {
        $offset = count($params) >= 2 ? (int) $params[1] : 0;
        $limit = count($params) >= 3 ? (int) $params[2] : 0;
        $result = [];
        foreach (DbProvider::getDb()->getAll($params[0]) as $index => $key_value_pair) {
          if (DbBase::keyReserved($key_value_pair['key'])) {
            continue;
          }
          if ($offset > 0) {
            $offset--;
            continue;
          }
          if ($limit > 0 && count($result) >= $limit) {
            break;
          }
          $result[] = $key_value_pair;
        }
        // Use getPage to implement this may have higher proformance with mysql?
        die(json_encode([ 'VALUE', $key, json_encode($result) ]));
      }
      case $tag_listget: {
        $result = [];
        foreach ($params as $index => $key) {
          $result[] = [ 'key' => $key, 'value' => DbProvider::getDb()->get($key) ];
        }
        die(json_encode([ 'VALUE', $key, json_encode($result) ]));
      }
      case $tag_search: {
        $result = DbProvider::getDb()->search($params[0], 1, false, ['key']);
        die(json_encode([ 'VALUE', $key, json_encode($result) ]));
      }
    }
  }

  function handle() {
    self::checkAllowBrowser();
    $key = (string) $_REQUEST['tag'];
    if (DbBase::keyReserved($key)) {
      http_response_code(403);
      die(json_encode([ 'KEY RESERVED', $key, '' ]));
    }
    $this->handleSpecialTags($key);
    die(json_encode([ 'VALUE', $key, (string) DbProvider::getDb()->get($key) ]));
  }

}