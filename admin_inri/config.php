<?
require_once('lib/class.Admin.php');
$admin = new Admin();

$output = AllFunction::OneFormAdmin( 'Блок контакты', DB_PFX.'config', $admin);

$admin->setContent($output);
echo $admin->showAdmin('content');