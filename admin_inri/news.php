<?php
require_once(__DIR__.'/lib/class.Admin.php');
$admin = new Admin();
if(  ( IS_AJAX_BACKEND == 1 ) ){
  require_once( __DIR__.'/lib/class.AjaxCarusel.php');
  class BlockClass extends AjaxCarusel {}
}else{
  require_once( __DIR__.'/lib/class.Carusel.php');  
  class BlockClass extends Carusel {}
} 
require_once( __DIR__.'/lib/class.Image.php' );
require_once( __DIR__.'/../vendors/phpmorphy/phpmorphy_init.php' ); // Морфология

function get_phpmorphy($descr_str) {
    global $morphy;
    
    $descr_str = strip_tags($descr_str);
    
    $descrs = str_word_count($descr_str, 1, "АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя0123456789");
    $orm_search = '';
    foreach($descrs as $descr){
      
      $des = mb_strtoupper($descr, 'UTF-8');
      //echo "des = $des<br>";
      $collection = $morphy->findWord($des);
      if(false === $collection) { 
        #echo $des, " NOT FOUND\n<br>";
        $orm_search .= $des." ";
        continue;
      } else {
        
      }
    
      foreach($collection as $paradigm) {
        #echo "lemma: ", $paradigm[0]->getWord(), "\n<br>";
        $orm_search .= $paradigm[0]->getWord()." ";
        break;
      }
    }
    
    $orm_search = trim($orm_search);
    
    return $orm_search;
}

class News extends BlockClass{ 
  
  function star_check(){
    
    if (!isset($_POST['id']) or !intval($_POST['id']) or !$_POST['field']) return;
		
    $fields = array('hide', 'flShowMine', 'fl_show_mine', 'fl_mine_slider');
		$id = intval($_POST['id']);
		$field = str_replace(' ', '', $_POST['field']);
		if (array_search($field, $fields) === false) return;
     
		$q = $this->pdo->query("SELECT `$field` FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = $id");
    $r = $q->fetch();
    $state = $r[$field];
    
    $new_state = ($state == 1) ? 0 : 1;

    $sql = "
      UPDATE `".$this->prefix.$this->carusel_name."` 
      SET `$field`=:$field
      WHERE `".$this->prefix.$this->carusel_name."`.`id` = $id
    ";
    $values = array($field=>$new_state);
    
    $stm = $this->pdo->prepare($sql);
    $res = $stm->execute($values);
		
    if (!$res) return;
		
    echo $new_state;
    
  }
  
  function show_table_header_rows(){
    $output = '
          <tr class="tth nodrop nodrag">
          	<th style="width: 55px;">#</th>
      		  <th style="width: 50px;">Скрыть</th>
            <th style="width: 115px;">Дата</th>
            <th style="width: 60px;">Картинка</th>
      		  <th>Название</th>
      		  <th style="width: 80px">Действие</th>
          </tr>';
    
    return $output;
  }
  
  function show_table_rows($item){
    $output = '';
    extract($item);
    
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              <input type="checkbox" class="group_checkbox" name="group_item[]" value="'.$id.'"> '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
          </td>
            
          <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td>';
            
          #<td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'fl_show_mine\')" class="star_check '.$this->getStarValStyle($fl_show_mine).'" id="fl_show_mine_'.$id.'"></div></td>';
            
    $date_str = '';
    if($date){
      $dArr = explode("-", $date);
      $year = $dArr[0];
      $day = $dArr[2];
      $month = $dArr[1];
      switch($dArr[1]){      
        case "01": $month = ' января ';  break;       
        case "02": $month = ' февраля '; break;       
        case "03": $month = ' марта ';   break;       
        case "04": $month = ' апреля ';  break;       
        case "05": $month = ' мая ';     break;       
        case "06": $month = ' июня ';    break;       
        case "07": $month = ' июля ';    break;       
        case "08": $month = ' августа '; break;       
        case "09": $month = ' сентября ';break;       
        case "10": $month = ' октября '; break;       
        case "11": $month = ' ноября ';  break;       
        case "12": $month = ' декабря '; break; 
      }
      
      $date_str = $day.' '.$month.' '.$year;
    }
          
    $output .= '
            <td>'.$date_str.'</td>';
    $output .= '
            <td style="max-width: 60px;">';
            
    if($img){
      $output .= '
            <div class="zoomImg" ><img style="width:50px;" src="../images/'.$this->carusel_name.'/slide/'.$img.'"></div>        ';
    }elseif($color){
      $output .= '
            <div class="zoomImg" style = "background-color: '.$color.'">';
    }
    $output .= '
            </td>
        	  
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать"><b>'.$title.'</b> '.$longtxt1.'</a>
            </td>
            
        	  <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).'
            </td>
  			  </tr>';
    
    return $output;
  }
  
  function show_form($item = null, $output = '', $id = null){ 
    
    $output .= '<div class = "c_form_box">';
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a class="btn btn-info pull-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }  
      
      $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']));
    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt3", "longtxt4"))) $class_input = ' class="ckeditor" '; 
      
      $type = '';
      $create_val = '';
      if( in_array($key, array("color"))) { $type = 'color'; $create_val = '#FFFFFF'; } 
      if( in_array($key, array("date"))) { $type = 'date'; $class_input = ' class="form-control" style = "max-width: 180px;" '; }
      if( in_array($key, array("datetime"))) $type = 'datetime';
      if( in_array($key, array("title", "link", "seo_h1", "seo_title", "seo_keywords", "img_alt", "img_title" ))) $type = 'text';
      
      // Вспомогательные поля для храниения поискового индекса
      if(($key == 'orm_search_name') || ($key == 'orm_search')) continue;
      
      // Отступы SEO
      if($key == 'seo_h1'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('SEO');
        $is_open_panel_div = true;   
      }
      
      if($key == 'img_alt'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('Атрибуты основого изображения');
        $is_open_panel_div = true;   
      }
      
      if($item){
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'" >'
          );
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea>'
          );
        }
        
      }else{
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="'.$create_val.'">'
          );
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.$create_val.'</textarea>'
          );
        }
      }
      
    }
    
    if($is_open_panel_div){
      $is_open_panel_div = false; 
      $output .= $this->getCardPanelFooter();
    }
    
    $output .= $this->getFormPicture($id, $item); 
    
    return $output;
    
  }
  
  function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    $i=0;
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
  }
  
  function getUpdateSlide_SqlVals(){
    $sql_vals = ''; $i=0;
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
  }
}

$date_arr = array(
    'title'            => 'Название',
    'date'             => 'Дата',
    'longtxt1'         => 'Краткий текст',
    'longtxt2'         => 'Полный текст (для отдельной страницы)',
   #'fl_show_mine'     => 'На главной',

    'orm_search_name'  => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'       => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса    
    'seo_h1'           => 'SEO H1',
    'seo_title'        => 'SEO Title',
    'seo_description'  => 'SEO Description',
    'seo_keywords'     => 'SEO Keywords',
    'img_alt'          => 'Alt изображение',
    'img_title'        => 'Title изображение', 
  );
$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title', 'date', 'longtxt1', 'longtxt2');

$carisel = new News('news', $date_arr, false, false, $pager);

$carisel->setHeader('Новости');
$carisel->setIsUrl(true);
$carisel->setIsImages(true);
$carisel->setIsFiles(true);
$carisel->setIsPager(true);
$carisel->setIsFilter(true);
$carisel->setIsLog(true);
$carisel->setFilterField($arrfilterfield); 

$carisel->setImg_ideal_width(750);  
$carisel->setImg_ideal_height(750); 
#$carisel->setDate_arr($date_arr);

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}