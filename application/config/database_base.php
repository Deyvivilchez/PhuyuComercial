<?php
defined('BASEPATH') OR exit('No direct script access allowed');

session_start(); date_default_timezone_set("America/Lima");

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => getenv('DB_HOST') ?: '127.0.0.1',
	'port' => getenv('DB_PORT') ?: '5432',
	'username' => getenv('DB_USER') ?: 'postgres',
	'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '2204',
	'database' => getenv('DB_NAME') ?: 'motorepuestos',
	'dbdriver' => 'postgre',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => (ENVIRONMENT !== 'production')
);
