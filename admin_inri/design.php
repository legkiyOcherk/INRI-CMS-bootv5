<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
$output = AllFunction::OneSeoFormAdmin( 'Оформление сайта', DB_PFX.'design', $admin);  

$admin->setContent($output);
echo $admin->showAdmin('content');