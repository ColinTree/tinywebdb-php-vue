<?php

class ApiIndex extends Api {

  function handle() {
    echo 'hello this is index, arg = ' . $GLOBALS['args'];
  }

}