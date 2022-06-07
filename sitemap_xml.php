<?php
header("Content-Type: text/xml");
header("Expires: Thu, 19 Feb 1998 13:24:18 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0");
header("Cache-Control: max-age=0");
header("Pragma: no-cache");
      
$prefix="<url><loc>http://".$_SERVER["SERVER_NAME"]."/";
$suffix="</loc></url>"."\r\n";

require_once('define.php');
require_once(NX_PATH.ADM_DIR.'/lib/global.lib.php');
require_once(NX_PATH.ADM_DIR.'/config.inc.php');
require_once(NX_PATH.ADM_DIR.'/lib/mysql.lib.php');
require_once(NX_PATH.ADM_DIR.'/lib/class.db.php');

function get_table_link( $table,  $prefix, $suffix ){
  $output = '';
  global $PDO;
  $s = "
    SELECT `$table`.*, `".DB_PFX."url`.`url` 
    FROM `$table`
    LEFT JOIN `".DB_PFX."url`
    ON (`".DB_PFX."url`.`module` = '$table') AND (`".DB_PFX."url`.`module_id` = `$table`.`id`)
    AND `$table`.`hide`  = 0 
  ";

  if($q = $PDO->query($s)){
    if($count = $q->rowCount()){
      while($r = $q->fetch()){
        if(isset($r['url']) && $r['url'])
          if( !isset($r['link']) || !$r['link'] )
      	    $output .= $prefix.$r['url']."/".$suffix;
      }
    }
  }
  
  return $output;
}

db_open();
global $PDO;



print '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
print '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\r\n";
echo $prefix.$suffix;


switch(SITE_TYPE){
  
  case 'CUTAWAY':
    print get_table_link( DB_PFX.'smpl_article',  $prefix, $suffix);
    break;
  
  case 'CORPORATE':
    print get_table_link( DB_PFX.'articles_cat',  $prefix, $suffix);
    print get_table_link( DB_PFX.'articles',  $prefix, $suffix);
    print get_table_link( DB_PFX.'news',  $prefix, $suffix);
    break;
    
  case 'ONLINESHOP':
    
    break;
  
  default:
    die('Не задан тип сайта');
    break;
}


print '</urlset>';
