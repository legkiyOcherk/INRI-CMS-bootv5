<?php #echo "Я Индекс)";

require_once('require.php');


$site = new Site('index');

echo $site->showSite();
