<?php

class DbMySQL extends DbBase {

  private $host;
  private $user;
  private $password;
  private $database;
  private $port;
  private $socket;

  private $table;
  private $timeout;

  function __construct(string $host = '', string $user = '', string $password = '', string $database = '',
        int $port = 3306, string $socket = '') {
    $this->host = $host;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;
    $this->port = $port;
    $this->socket = $socket;
    
    $this->table = 'tpv';
    $this->timeout = 3;
  }
  function setTable(string $table) {
    $this->table = $table;
  }
  /**
   * notice that linker may retry connect for serial times, and this timeout is just for each time
   */
  function setTimeout(int $timeout) {
    $this->timeout = $timeout;
  }

  private function runMysql(string $statment, $paramTypes = null, &...$params) {
    $mysqli = mysqli_init();
    $mysqli->options(11 /*MYSQL_OPT_READ_TIMEOUT*/, $this->timeout);
    $ret = $mysqli->real_connect($this->host, $this->user, $this->password, $this->database, $this->port, $this->socket);
    if ($ret === false) {
      throw new Exception('Cannot connect to mysql server: ' . mysqli_error($mysqli));
    }
    $stmt = $mysqli->prepare($statment);
    if ($stmt === false) {
      throw new Exception('Cannot prepare stmt: ' . mysqli_error($mysqli));
    }
    if ($paramTypes !== null && !$stmt->bind_param($paramTypes, ...$params)) {
      throw new Exception("Cannot bind param($stmt->errno): $stmt->error");
    }
    if (!$stmt->execute()) {
      throw new Exception("Cannot execute query: $stmt->error");
    }
    $result = $stmt->get_result();
    $stmt->close();
    $mysqli->close();
    return $result;
  }

  function has(string $key) {
    $result = $this->runMysql("SELECT EXISTS(SELECT `value` FROM `$this->table` WHERE `key` = ? LIMIT 1)", 's', $key);
    return $result->fetch_array(MYSQLI_NUM)[0] > 0;
  }

  function count(string $prefix = '') {
    $prefix .= '%';
    $result = $this->runMysql("SELECT COUNT(`value`) FROM `$this->table` WHERE `key` LIKE ?", 's', $prefix);
    return $result->fetch_array(MYSQLI_NUM)[0];
  }

  function delete(string $key) {
    if (!$this->has($key)) {
      return true;
    }
    try {
      $this->runMysql("DELETE FROM `$this->table` WHERE `key` = ?", 's', $key);
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  function set(string $key, string $value) {
    return $this->has($key) ? $this->update($key, $value) : $this->add($key, $value);
  }

  function add(string $key, string $value) {
    if ($this->has($key)) {
      return false;
    }
    try {
      $this->runMysql("INSERT INTO `$this->table` (`key`,`value`) VALUES (?, ?)", 'ss', $key, $value);
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  function update(string $key, string $value) {
    try {
      $this->runMysql("UPDATE `$this->table` SET `value` = ? WHERE `key` = ?", 'ss', $value, $key);
    } catch (Exception $e) {
      return false;
    }
    return true;
  }

  function get(string $key) {
    $result = $this->runMysql("SELECT `value` FROM `$this->table` WHERE `key` = ? LIMIT 1", 's', $key);
    $row = $result->fetch_array(MYSQLI_ASSOC);
    return isset($row['value']) ? $row['value'] : false;
  }

  function getPage(int $page = 1, int $perPage = 100, string $prefix = '') {
    $prefix .= '%';
    $start = ($page - 1) * $perPage;
    $result = $this->runMysql("SELECT * FROM `$this->table` WHERE `key` LIKE ? LIMIT ?, ?", 'sii', $prefix, $start, $perPage);
    $rtn = [];
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $rtn[] = $row;
    }
    return $rtn;
  }

  function getAll(string $prefix = '') {
    return new DbMySQLIterator($prefix, $this);
  }

  function search(string $text, int $page, bool $ignoreCase, array $range) {
    $text = '%' . $text . '%';
    if ($text === '%%') {
      $text = '%';
    }
    $caseProcessing = $ignoreCase ? 'UPPER' : '';
    $start = ($page - 1) * DbBase::$SEARCH_RESULT_PER_PAGE;
    $sql = "SELECT * FROM `$this->table` WHERE ";
    $sqlParamTypes = '';
    $sqlParams = [];
    foreach ($range as $index => $field) {
      $range[$index] = "$caseProcessing(`$field`) LIKE $caseProcessing(?)";
      $sqlParamTypes .= 's';
      $sqlParams[] = $text;
    }
    $sql .= implode(' OR ', $range);

    $countResult = $this->runMysql(str_replace('SELECT *', 'SELECT COUNT(`value`)', $sql), $sqlParamTypes, ...$sqlParams);
    $count = $countResult->fetch_array(MYSQLI_NUM)[0];

    $sql .= " LIMIT ?, ?";
    $sqlParamTypes .= 'ii';
    $sqlParams[] = $start;
    $sqlParams[] = DbBase::$SEARCH_RESULT_PER_PAGE;
    $result = $this->runMysql($sql, $sqlParamTypes, ...$sqlParams);
    $rtn = [];
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $rtn[] = $row;
    }
    return [ 'count' => $count, 'result' => $rtn ];
  }

  function eraseData() {
    $reservedPrefix = DbBase::$KEY_RESERVED_PREFIX . '%';
    $this->runMysql("DELETE FROM `$this->table` WHERE `key` NOT LIKE ?", 's', $reservedPrefix);
  }

  function eraseAll() {
    $this->runMysql("DELETE FROM `$this->table`");
  }

}

class DbMySQLIterator extends DbBaseIterator {

  private $dbMySQL;

  function __construct(string $prefix, DbMySQL $dbMySQL) {
    $this->dbMySQL = $dbMySQL;
    parent::__construct($prefix);
  }

  public function getNextPage() {
    $this->currentValue = $this->dbMySQL->getPage($this->page, DbBaseIterator::$PERPAGE, $this->prefix);
  }

}