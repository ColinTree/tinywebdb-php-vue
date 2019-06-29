<?php

class ApiTinywebdb extends Api {

  static function checkAllowBrowser() {
    if (DbBase::getSetting('tinywebdb_allow_browser') === 'false'
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
    $special_tags = json_decode(DbBase::getSetting('tinywebdb_special_tags', '{}'), true);
    $PLUGIN_SETTINGS = PLUGINS['tinywebdb']['settings']['special_tags']['children'];
    $tag_count = !isset($special_tags['count'])
        ? $PLUGIN_SETTINGS['count']['default']
        : (empty($special_tags['count']) ? $PLUGIN_SETTINGS['count']['placeholder'] : $special_tags['count']);
    $tag_getall = !isset($special_tags['getall'])
        ? $PLUGIN_SETTINGS['getall']['default']
        : (empty($special_tags['getall']) ? $PLUGIN_SETTINGS['getall']['placeholder'] : $special_tags['getall']);
    $tag_listget = !isset($special_tags['listget'])
        ? $PLUGIN_SETTINGS['listget']['default']
        : (empty($special_tags['listget']) ? $PLUGIN_SETTINGS['listget']['placeholder'] : $special_tags['listget']);
    $tag_search = !isset($special_tags['search'])
        ? $PLUGIN_SETTINGS['search']['default']
        : (empty($special_tags['search']) ? $PLUGIN_SETTINGS['search']['placeholder'] : $special_tags['search']);
    $tag = explode('#', $key, 2);
    while (count($tag) < 2) {
      $tag[] = '';
    }
    $params = explode('#', $tag[1]);
    $tag = $tag[0];
    switch ($tag) {
      case $tag_count: {
        if ($tag_count === 'disabled') {
          break;
        }
        $count = DbProvider::getDb()->count($params[0]);
        die(json_encode([ 'VALUE', $key, (string) $count ]));
      }
      case $tag_getall: {
        if ($tag_getall === 'disabled') {
          break;
        }
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
        if ($tag_listget === 'disabled') {
          break;
        }
        $result = [];
        foreach ($params as $index => $key) {
          $result[] = [ 'key' => $key, 'value' => DbProvider::getDb()->get($key) ];
        }
        die(json_encode([ 'VALUE', $key, json_encode($result) ]));
      }
      case $tag_search: {
        if ($tag_search === 'disabled') {
          break;
        }
        $result = DbProvider::getDb()->search($params[0], 1, false, ['key']);
        die(json_encode([ 'VALUE', $key, json_encode($result) ]));
      }
    }
  }

  function handle() {
    self::checkAllowBrowser();
    $key = (string) $_REQUEST['tag'];
    $value = (string) $_REQUEST['value'];
    if (DbBase::keyReserved($key)) {
      http_response_code(403);
      die(json_encode([ 'KEY RESERVED', $key, '' ]));
    }

    $action = strtolower(explode('/', $GLOBALS['args'], 2)[0]);

    switch ($action) {
      case 'getvalue': {
        $this->handleSpecialTags($key);
        die(json_encode([ 'VALUE', $key, (string) DbProvider::getDb()->get($key) ]));
      }
      case 'storeavalue': {
        DbProvider::getDb()->set($key, $value);
        die(json_encode([ 'STORED', $key, $value ]));
      }
    }
    return [ 'status' => STATUS_API_NOT_FOUND, 'result' => "Can not find `$action` in Tinywebdb api" ];
  }

}