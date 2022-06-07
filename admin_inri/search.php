<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
$output = '';

#pri($admin);

$output .= $admin->showSearch();

$admin->setContent($output);
echo $admin->showAdmin('content');