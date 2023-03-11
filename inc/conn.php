<?php

session_start();

if( !function_exists('mysqli_connect') )
    die ("<html><head></head><body><h1 style=\"color:red\">Не подключена библиотека MySQLi</h1></b></body></html>");

require_once __DIR__.'/constants.php';

/*===================================
Параметры подключения к MySQL
=====================================*/

$host=$_SERVER["HTTP_HOST"];
$mysql_host	= MYSQL_HOST;
$database	= MYSQL_DB;
$login		= MYSQL_LOGIN;
$password	= MYSQL_PASS;
$port		= MYSQL_PORT;

if($_SERVER['REMOTE_ADDR'] == '127.0.0.1') /* это для тестирования на локальном компе */
	{
	$mysql_host	= MYSQL_HOST_LOCAL;
	$database	= MYSQL_DB_LOCAL;
	$login		= MYSQL_LOGIN_LOCAL;
	$password	= MYSQL_PASS_LOCAL;
	$port		= MYSQL_PORT_LOCAL;
	}

$conn=mysqli_connect($mysql_host, $login, $password,$database, $port)
    or die ("<html><head></head><body><h1 style=\"color:red\">Не подключается MySQL на $host!</h1>Говорит: <b>".mysqli_connect_error()."</b></body></html>");
mysqli_query($conn,"SET NAMES utf8");

//Немного приберемся
unset($mysql_host); 
unset($database);
unset($login);  
unset($host);
unset($port);


if(function_exists("date_default_timezone_set"))
	{
	date_default_timezone_set('Europe/Kiev');
	/*---
	Еще варианты:
	Europe/Moscow
	Europe/Paris
	Europe/Minsk

	Полный список: http://ua2.php.net/manual/ru/timezones.php
	----*/
	}

/* Увімкнути автоматичне підключення файлу класу на ім'я */
require_once dirname(__FILE__).'/autoload.php';

//Подключимся к базе пользователя
define('USER_ROLE_ID', 9);

common::mobile();

?>