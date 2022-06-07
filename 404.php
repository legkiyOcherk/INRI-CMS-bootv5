<? #echo "Я 404)";
require_once('require.php');

@header('HTTP/1.0 404 Not Found');

$site = new Site;

echo $site->showSite('404');

