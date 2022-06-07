<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once(NX_PATH.'/lib/class.Url.php');

$pager = array(
  'perPage' => 100,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'url', 'module', 'module_id');

$url = new Url('url', false, $pager);

$url->setHeader('ЧПУ');
$url->setIsPager(true);
$url->setIsFilter(true);
$url->setFilterField($arrfilterfield);



if($output = $url->getContent($admin)){
  $admin->setContent($output);
  
  echo $admin->showAdmin('content');
}