<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Log.php');

class AllLog extends Log{
  
}

$pager = array(
  'perPage' => 100,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'module', 'date', 'dump_data');

$carisel = new AllLog('all_log', false, $pager);

$carisel->setHeader('Лог');
$carisel->setIsFilter(true);
$carisel->setFilterField($arrfilterfield); 

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}