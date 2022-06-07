<?php #echo "Я Индекс админ)";

header('Content-Type: text/html; charset=utf-8');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

date_default_timezone_set("Asia/Yekaterinburg"); 

require_once('lib/class.Admin.php');

// Сжате выходного скрипта на лету
ob_start('ob_gzhandler'); 
//session_set_cookie_params(TIME_LIFE_COOKIE);

$admin = new Admin();

echo $admin->showAdmin();
