<?php

const DEBUG_MODE = false;
const ACCESS_CONTROL_ALLOW_ORIGIN = '*';

// Sae KVDB
// DbProvider::setDb(new DbSaeKV());

// Sae MySQL
// DbProvider::setDb(new DbMySQL(SAE_MYSQL_HOST_M, SAE_MYSQL_USER, SAE_MYSQL_PASS, SAE_MYSQL_DB, SAE_MYSQL_PORT));

// Common MySQL
// DbProvider::setDb(new DbMySQL('host', 'user', 'password', 'database'));

// MySQL with port specified
// DbProvider::setDb(new DbMySQL('host', 'user', 'password', 'database', 3306));