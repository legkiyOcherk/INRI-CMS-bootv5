<?php #echo "Я Роботс)";

require_once('require.php');

if(!$robots_content = db::value("value", DB_PFX."design", "type = 'user_robots'")){
$robots_content = 'User-agent: * 
Host:
Sitemap: /sitemap.xml'; 
}

echo $robots_content;
/*@header('HTTP/1.0 404 Not Found');
echo "132";
$site = new Site;

echo $site->showSite('404');*/