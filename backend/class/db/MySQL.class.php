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

  private function mysqli() {
    $link = mysqli_init();
    $link->options(11 /*MYSQL_OPT_READ_TIMEOUT*/, $this->timeout);
    $ret = $link->real_connect($this->host, $this->user, $this->password, $this->database, $this->port, $this->socket);
    if ($ret === false) {
      throw new Exception('Cannot connect to mysql server: ' . mysqli_error($link));
    }
    return $link;
  }

  function delete(string $key) {
    $mysqli = $this->mysqli();
    $stmt = $mysqli->prepare("DELETE FROM `$this->table` WHERE `key` = ?");
    if ($stmt === false) {
      throw new Exception('Cannot prepare stmt: ' . mysqli_error($mysqli));
    }
    if (!$stmt->bind_param('s', $key)) {
      throw new Exception("Cannot bind param: $stmt->error");
    }
    if (!($result = $stmt->execute())) {
      throw new Exception("Cannot execute query: $stmt->error");
    }
    $stmt->close();
    $mysqli->close();
    return $result;
  }

  function set(string $key, string $value) {
    $mysqli = $this->mysqli();
    $stmt = $mysqli->prepare("INSERT INTO `$this->table` (`key`,`value`) VALUES (?, ?)");
    if ($stmt === false) {
      throw new Exception('Cannot prepare stmt: ' . mysqli_error($mysqli));
    }
    if (!$stmt->bind_param('ss', $key, $value)) {
      throw new Exception("Cannot bind param: $stmt->error");
    }
    if (!($result = $stmt->execute())) {
      throw new Exception("Cannot execute query: $stmt->error");
    }
    $stmt->close();
    $mysqli->close();
    return $result;
  }

  function get(string $key) {
    $mysqli = $this->mysqli();
    $stmt = $mysqli->prepare("SELECT `value` FROM `$this->table` WHERE `key` = ? LIMIT 1");
    if ($stmt === false) {
      throw new Exception('Cannot prepare stmt: ' . mysqli_error($mysqli));
    }
    if (!$stmt->bind_param('s', $key)) {
      throw new Exception("Cannot bind param: $stmt->error");
    }
    if (!$stmt->execute()) {
      throw new Exception("Cannot execute query: $stmt->error");
    }
    $result = $stmt->get_result();
    $rtn = false;
    $row = $result->fetch_array(MYSQLI_ASSOC);
    if (isset($row['value'])) {
      $rtn = $row['value'];
    }
    $stmt->close();
    $mysqli->close();
    return $rtn;
  }

  function getPage(int $page = 1, int $perPage = 100, string $prefix = '') {
    $mysqli = $this->mysqli();
    $stmt = $mysqli->prepare("SELECT * FROM `$this->table` WHERE `key` LIKE ? LIMIT ?, ?");
    if ($stmt === false) {
      throw new Exception('Cannot prepare stmt: ' . mysqli_error($mysqli));
    }
    $prefix .= '%';
    $start = ($page - 1) * $perPage;
    if (!$stmt->bind_param('sii', $prefix, $start, $perPage)) {
      throw new Exception("Cannot bind param: $stmt->error");
    }
    if (!$stmt->execute()) {
      throw new Exception("Cannot execute query: $stmt->error");
    }
    $result = $stmt->get_result();
    $rtn = [];
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $rtn[] = $row;
    }
    $stmt->close();
    $mysqli->close();
    return $rtn;
  }

}