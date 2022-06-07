<?php #echo "Я Рек)";

header('Content-Type: text/html; charset=utf-8');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$session_id = session_id();
// Сжате выходного скрипта на лету
ob_start('ob_gzhandler'); 
session_set_cookie_params(604800); 
session_start();
date_default_timezone_set("Asia/Yekaterinburg"); 

require_once('define.php');
require_once('lib/class.Site.php');
require_once('lib/class.EMail.php');
require_once('lib/class.Article.php');
require_once('lib/class.Goods.php');
require_once('lib/class.basket.php');
require_once('lib/class.Search.php');
require_once('lib/class.HavingPoorVision.php');
require_once('lib/class.Reviews.php');
require_once(NX_PATH.ADM_DIR.'/lib/global.lib.php'); 
require_once(NX_PATH.ADM_DIR.'/config.inc.php');
require_once(NX_PATH.ADM_DIR.'/lib/mysql.lib.php');
require_once(NX_PATH.ADM_DIR.'/lib/class.db.php');
require_once(NX_PATH.ADM_DIR.'/lib/class.Image.php');
require_once(NX_PATH.ADM_DIR.'/lib/class.Images.php');
require_once(NX_PATH.ADM_DIR.'/lib/class.Url.php'); 

db_open();
