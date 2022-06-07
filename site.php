<?php #echo "Я Site.php)";

require_once('require.php');

$site = new Site;

echo $site->showSite();
