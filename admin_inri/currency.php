<?
require_once('lib/class.Admin.php');
$admin = new Admin();

$output = AllFunction::OneFormAdmin( 'Курсы валют', 'currency', $admin);

$admin->setContent($output);
echo $admin->showAdmin('content');