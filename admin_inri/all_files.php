<?php
require_once('lib/class.Admin.php');
$admin = new Admin();

require_once('lib/class.Files.php');
require_once('lib/class.Image.php'); 

class AllFiles extends Files{
  
}

$date_arr = array(
    'title'     => 'Название',
    'longtxt1'  => 'Описание',
    'hide'      => 'Скрыть',
    'module'    => 'Название модуля (таблицы бд к которой привязан URL)',
    'module_id' => 'Название модуля id модуля',
    'img_alt'   => 'Alt изображение',
    'img_title' => 'Title изображение'
  );
$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 2, 5, 50, 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'file', 'module', 'module_id' );
  
$all_files = new AllFiles('all_files', $date_arr, true, true, $pager);

$all_files->setHeader('Файлы');
//ЧПУ
#$all_files->setIsUrl(true);
$all_files->setIsPager(true);
$all_files->setIsFilter(true);
$all_files->setFilterField($arrfilterfield);

$all_files->setImg_ideal_width(450);  
$all_files->setImg_ideal_height(450); 
  
#$all_files->setDate_arr($date_arr);
$all_files->checkbox_array = array('hide');                # Галочка в форме

if($output = $all_files->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}

