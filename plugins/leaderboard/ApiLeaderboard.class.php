<?

class ApiLeaderboard extends Api {

  public static $LEADERBOARD_PREFIX = 'LEADERBOARD_';
  public static $LEADERBOARD_MAX_LENGTH = 10;

  static function getBoard($boardName) {
    $leaderboard = DbProvider::getDb()->get(self::$LEADERBOARD_PREFIX . $boardName);
    if (empty($leaderboard)) {
      return array();
    }
    return json_decode($leaderboard, true);
  }

  static function setBoard($boardName, $leaderboard) {
    $boardName = self::$LEADERBOARD_PREFIX . $boardName;
    if (DbProvider::getDb()->has($boardName) || DbBase::getSetting('leaderboard_allow_create') === 'true') {
      DbProvider::getDb()->set($boardName, json_encode($leaderboard));
      return true;
    } else {
      return false;
    }
  }

  function handle() {
    $action = strtolower(explode('/', $GLOBALS['args'], 2)[0]);

    $name = (string) $_REQUEST['name'];
    $score = (int) $_REQUEST['score'];
    $boardName = (string) $_REQUEST['board'];

    switch ($action) {
      case 'add_score': {
        $leaderboard = self::getBoard($boardName);
        $pos = 0;
        while ($pos < count($leaderboard) && $leaderboard[$pos]['score'] >= $score) {
          $pos ++;
        }
        array_splice($leaderboard, $pos, 0, [[ 'name' => $name, 'score' => $score ]]);
        if (count($leaderboard) > self::$LEADERBOARD_MAX_LENGTH) {
          array_pop($leaderboard);
        }
        return self::setBoard($boardName, $leaderboard)
            ? [ 'result' => $leaderboard ]
            : [ 'status' => STATUS_PLUGIN_API_FAILED, 'result' => 'Creating new board is not allowed' ];
      }
      case 'get_board': {
        return [ 'result' => self::getBoard($boardName) ];
      }
    }
    return [ 'status' => STATUS_API_NOT_FOUND, 'result' => "Can not find `$action` in Leaderboard api" ];
  }

}
