<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
$admin = new Admin();
if(  ( IS_AJAX_BACKEND == 1 ) ){
  require_once( __DIR__.'/lib/class.AjaxCatCarusel.php' );
  class BlockClass extends AjaxCatCarusel {} 
}else{
  require_once( __DIR__.'/lib/class.CatCarusel.php' ); 
  class BlockClass extends CatCarusel {}  
}
require_once('lib/class.Image.php');
require_once('../vendors/phpmorphy/phpmorphy_init.php'); // Морфология

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

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

class Goods extends BlockClass{
  
  function getAjaxCompleteScript(){
    $output = '';
    
    $output .= '
    <script>
      $(document).ajaxComplete(function() {
        CKEDITOR.replace( "longtxt1" );
        CKEDITOR.replace( "longtxt2" );
        CKEDITOR.replace( "longtxt3" );
      });
    </script>'; 
    
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
      if($key == 'previous_owner') continue; // Не учитывать разделитель
      if( in_array( $key, array('is_hit', 'is_new', 'is_sale' ) ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
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
      
      if($key == 'previous_owner') continue; // Не учитывать разделитель
      if( in_array( $key, array('is_hit', 'is_new', 'is_sale' ) ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      ($i) ? $prefix = ', ' : $prefix = '';
      if($key == 'size'){
        if($_POST['actual']){
          $_POST[$key] = serialize($_POST['actual']);
        }
      }
      
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    
    return $sql_vals;
  }
  
  function getCreateCatSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    $i=0;
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
      
    foreach($this->date_cat_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
  }
  
  function getUpdateCatSlide_SqlVals(){
    $sql_vals = ''; $i=0;
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    foreach($this->date_cat_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
  }
  
  function show_cat_table_header_rows(){
    $output = '
                <tr class="tth">
            		  <td style="width: 55px;">#</td>
            		  <td style="width: 50px;">Скрыть</td>
                  <td style="width: 50px;">На главной</td>
            		  <td colspan = "2">Название</td>
                  <td >Выгрузка в xls</td>
            		  <td style="width: 80px">Действие</td>
                </tr>';
    
    return $output;
  }
    
  function show_cat_table_rows($item, $i = 0){
    $output = '';
              extract($item);
              $output .= '
                <tr class="r'.($i % 2).'" id="trc_'.$id.'" style="cursor: move;">			 
                  <td style="width: 20px;">'.$id.'<input type="hidden" value="'.$id.'" name="itCatSort[]"></td>
                  
                  <td style="width: 30px;" class="img-act"><div title="Скрыть" onclick="star_cat_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td>
                  
                  <td style="width: 30px;" class="img-act"><div title="На главной" onclick="star_cat_check('.$id.', \'fl_show_mine\')" class="star_check '.$this->getStarValStyle($fl_show_mine).'" id="fl_show_mine_'.$id.'"></div></td>
              	  
                  <td style="width: 50px;">
              ';
              if($img){
                $output .= '
                  <div class="zoomImg"><img style="width:50px" src="../images/'.$this->carusel_name.'/cat/slide/'.$img.'"></div>  
                ';
              }else if($color){
                $output .= '
                  <div class="zoomImg" style = "background-color: '.$color.'">
                ';
              }
              $output .= '
                  </td>
              	   
                  <td style="text-align: left;">
                    <a  href="..'.IA_URL.$this->carusel_name.'.php?editc='.$id.'" 
                        class = "btn btn-info btn-sm"
                        title = "Редактировать"
                        style = "color: #fff;">
                        <i class="fas fa-pencil-alt"></i>
                    </a> &nbsp;
                    <a href="'.IA_URL.$this->carusel_name.'.php?c_id='.$id.'" title="редактировать">'.$title.'</a>
                  </td>
              	  
                  <td style="text-align: left;">
                    <a href="'.IA_URL.$this->carusel_name.'.php?xls_id='.$id.'" title="Выгрузка в xls" class = "btn btn-sm btn-warning text-light">в .xls <i class="fas fa-table"></i></a>
                  </td>
            	';

              $output .= '
                  <td style="" class="action_btn_box">
                    '.$this->show_cat_table_row_action_btn($id).'
                  </td>
        			  </tr>
              ';
    
    return $output;
  }
  
  function show_form($item = null, $output = '', $id = null){
    
    $opt_arr = array('opt1_id', 'opt2_id', 'opt3_id', 'opt4_id', 'opt5_id', 'opt6_id', 'opt7_id', 'opt8_id', 'opt9_id' );
    $opt_item_arr = array('opt1', 'opt2', 'opt3', 'opt4', 'opt5', 'opt6', 'opt7', 'opt8', 'opt9' );
    
    $output .= '
      <style>
        input[type="text"].price_input{ background-color: #88d888; font-weight: bold;  font-size: 20px; }
    </style>';
    
    $output .= '<div class = "c_form_box">';
    
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a href="/'.$url.'" class="btn btn-info pull-right" style="color:#fff"><i class="icon-eye-open icon-white"></i> Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }
      
      $output .= $this->show_form_row( 'ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']) );
    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; 
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt3"))) $class_input = ' class="ckeditor" '; 
      
      $type = '';
      $create_val = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date"))) $type = 'date';
      if( in_array($key, array("article", "article_provider", "old_price", "amount"))){ $type = 'text';}
      if( in_array($key, array("price"))){ $type = 'text'; $class_input = ' class="form-control price_input" ';  $create_val = 0;}
      
      if($key == 'cat_id'){
        $tmp  = '<select name="cat_id" class="form-control">';
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
        if($item) {
          $_SESSION[$this->carusel_name]['c_id'] = htmlspecialchars($item[$key]);
          $this->bread = array();
          $this->show_bread_crumbs($item_cat_id);
          #$this->admin->setForName('bread', $this->getForName('bread'));
          if($_SESSION[$this->carusel_name]['c_id']){
            $c_title = db::value('title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
            $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
          }
          $title .=' редактирование записи';
          
          $this->title = $title;
        }
        if(!$item_cat_id) $item_cat_id = 0;
        $tmp .= $this->get_category_option($item_cat_id);
        $tmp .= '</select>';
        $this->item_cat_id = $item_cat_id;
        
        $output .= $this->show_form_row( $val, $tmp);
        continue;  
      }
      
      // Вспомогательные поля для храниения поискового индекса
      if(($key == 'orm_search_name') || ($key == 'orm_search')) continue;
      
      // Выбор Варианты наличия
      if($key == 'availability_id'){
        $tmp = $this->show_select(DB_PFX.'availability', 'title', $item, $key, false );
        $tmp .= ' <a href="'.IA_URL.'availability.php" target="_blank"> Вариант наличия </a>';
        $output .= $this->show_form_row( $val, $tmp);
        continue;  
      } 
           
      // Выбор Страна
      if($key == 'country_id'){
        $tmp = $this->show_select(DB_PFX.'country', 'title', $item, $key );
        $tmp .= ' <a href="'.IA_URL.'country.php" target="_blank"> Добавить Страну </a>';
        $output .= $this->show_form_row( $val, $tmp);
        continue;  
      }      
      
      // Выбор Бренд
      if($key == 'brand_id'){
        $tmp = $this->show_select(DB_PFX.'brand', 'title', $item, $key );
        $tmp .= ' <a href="'.IA_URL.'brand.php" target="_blank"> Добавить Бренд </a>';
        $output .= $this->show_form_row( $val, $tmp);
        continue;  
      }
      
      // Выбор Едениц измерения
      if($key == 'units_id'){
        $tmp = $this->show_select(DB_PFX.'units', 'title', $item, $key, false );
        $tmp .= ' <a href="'.IA_URL.'units.php" target="_blank"> Добавить Еденицы </a>';
        $output .= $this->show_form_row( $val, $tmp);
        continue;  
      }
      
      if($key == 'is_hit'){
        $output .= $this-> show_iCheck('col_checkbox1', $item, $key, $val);
        continue;  
      }
      if($key == 'is_new'){
        $output .= $this-> show_iCheck('col_checkbox2', $item, $key, $val);
        continue;  
      }
      if($key == 'is_sale'){
        $output .= $this-> show_iCheck('col_checkbox3', $item, $key, $val);
        continue;  
      }     
      
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
      
    $output .= ' </div> ';
    
    return $output;
    
  }
  
  function show_cat_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    
    /*$output .= '
      <div class="panel panel-default"> 
        <div class="panel-heading"> 
          <h3 class="panel-title">Основное</h3>
        </div> 
        <div class="panel-body"> 
    ';*/
    
    $opt_arr = array('opt1_id', 'opt2_id', 'opt3_id', 'opt4_id', 'opt5_id', 'opt6_id', 'opt7_id', 'opt8_id', 'opt9_id' );
    
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name."_cat", $id); 
      
      if($url){
        $tmp = '<a class="btn btn-info pull-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      } 
        
       $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name."_cat", $id, $item['title']));  
      
    } 
    
    if($item) {
      $_SESSION[$this->carusel_name]['c_id'] = htmlspecialchars($item['id']);
      $this->bread = array();
      
      $this->show_bread_crumbs($_SESSION[$this->carusel_name]['c_id']);
      #$this->admin->setForName('bread', $this->getForName('bread'));
      if($_SESSION[$this->carusel_name]['c_id']){
        $c_title = db::value('title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
        $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
      }
      $title .=' редактирование каталога';
    
      $this->title  = $title;
    }
    foreach($this->date_cat_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt3"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date"))) $type = 'date';
      
      if($key == 'parent_id'){
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];

        if(!$item_cat_id) $item_cat_id = 0;
        $output .= '<input type = "hidden" name = "parent_id" value = "'.$item_cat_id.'">';
        continue;
      }
      
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
            $val.$this->getCatErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'">'
          );
          
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea>'
          );
         
        }
        
      }else{
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="#FFFFFF">'
          );
          
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50></textarea>'
          );
          
        }
      }
      
    }
    
    if($is_open_panel_div){
      $is_open_panel_div = false; 
      $output .= $this->getCardPanelFooter();
    }
    
    $output .= ' </div> ';
    
    $output .= ' Изображение  (Иделальный размер '.$this->img_cat_ideal_width.' x '.$this->img_cat_ideal_height.'):';
    $output .= '<BR/><INPUT type="file" name="picture" id = "fr_picture" value="" class="w100"><BR/>';
    
    return $output;
    
  }
   
  function show_select($table, $name = "title", &$item, &$key, $is_not_val = true ){
    $output = '';
    
    $output .= '<select name="'.$key.'" class="form-control">';
    
    if($is_not_val){
      $output .= '<option value = "0"  ';
      if(!$item[$key]) { $output .= 'selected';  }  $output .= '>Нет</option>'; 
    }
    
    $s = "SELECT * FROM `$table` WHERE `hide` = 0 ORDER BY `ord`";
    $q = $this->pdo->query($s);
    #$q->rowCount();
    
    while ($row = $q->fetch()){
    
      $output .= '<option value = "'.$row['id'].'"  ';
      if($row['id'] == $item[$key]){
        $output .= 'selected'; 
      }
      $output .= '>'.$row[$name];
      $output .= '</option>';
    }
    $output .= '</select>';
    
    return $output;
  }
  
  function show_iCheck($check_class, &$item, &$key, &$val){
    $output = '';
    
    ($item && $item[$key]) ? $coldate = 'checked' : $coldate = ''; #pri($coldate);
    
    $output .= $this->show_form_row( 
      $val.$this->getErrorForKey($key), 
        '<input type="checkbox" class="'.$check_class.'" name="'.$key.'" '.$coldate.'>'
      );
      
    $output .= '
        <script type="text/javascript">
          $(document).ready(function(){
            $(".'.$check_class.'").iCheck({
              checkboxClass: "icheckbox_flat-red",
              radioClass: "iradio_flat-red"
            });
          });
        </script>';
    
    
    return $output;
  }
  
  function get_show_table_menu_btn( $c_id = ''){
    $output = '';
    $btn_class = 'btn btn-default btn-sm mb-1';
    
    $output .=  '
    <div class="expansion_table_box py-2">
      <a href="/'.ADM_DIR.'/'.$this->carusel_name.'.php" class="'.$btn_class.'" title = "Каталог"><i class="fas fa-home"></i></a>
      <a href="?view_tree"          class = "'.$btn_class.'" ><i class="fas fa-tree"></i> Дерево всех категорий</a>
      <a href="?full_tree"          class = "'.$btn_class.'" ><i class="fas fa-sitemap"></i> Полный каталог</a>
      <a href="?upload_good_csv"    class = "'.$btn_class.'" ><i class="fas fa-sync"></i> Обновить каталог</a>
      <a href="?update_information" class = "'.$btn_class.'" ><i class="fas fa-exchange-alt"></i> Обновление цен</a>';
      #<a href="?upload_img"        class = "'.$btn_class.'" ><i class="fas fa-upload"></i> Загрузить изображения</a>
    $output .=  '
      <a href="?xls_id='.$c_id.'"   class = "'.$btn_class.'" ><i class="fas fa-download"></i> Скачть раздел в xls</a>';
    $output .=  '
    </div>';
    
    return $output;
  }
  
  function show_table_header_rows(){
    $output = '
          <tr class="tth nodrop nodrag">
          	<td style="width: 55px;">#</td>
      		  <td style="width: 50px;">Скрыть</td>
            <td style="width: 50px;">На главной</td>
            <td style="width: 60px;">Картинка</td>
      		  <td>Название</td>';
      		  #<td>Ед. изм.</td>
    $output .= '
            <td style="width: 140px">Наличие</td>
            <td style="width: 140px">Цена</td>
            <td style="width: 120px">Действие</td>
          </tr>
    <style>
      input[type="text"].price_input{ background-color: #88d888; font-weight: bold; font-size: 15px; }
      tr.r1>td>input[type="text"].price_input{padding: 0px 2px;text-align: right;}
    </style>
    ';
    
    return $output;
  }
  
  function show_table_rows($item){
    $output = '';
    extract($item);
    
    if(!isset($this->units) || !$this->units){
      $unit_items =  db::select("*", DB_PFX."units" );
      
      foreach($unit_items as $unit_item){
        $this->units[ $unit_item['id'] ] = $unit_item['reduction'];
      }
    }
    
    if(!isset($this->availability) || !$this->availability){
      $sitems =  db::select("*", DB_PFX."availability", null, "ord" );
      
      foreach($sitems as $sitem){
        $this->availability[ $sitem['id'] ] = $sitem['title'];
      }
    }
    
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              <input type="checkbox" class="group_checkbox" name="group_item[]" value="'.$id.'"> '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
          </td>
            
            <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td> 
             
            <td class="img-act" style="text-align: center;"><div title="На главной" onclick="star_check('.$id.', \'fl_show_mine\')" class="star_check '.$this->getStarValStyle($fl_show_mine).'" id="fl_show_mine_'.$id.'"></div></td>
            
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
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" 
                  class = "btn btn-info btn-sm"
                  title = "Редактировать"
                  style = "color: #fff;">
                  <i class="fas fa-pencil-alt"></i>
              </a> &nbsp;
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>';
            
    #$output .= '
    #        <td>
    #          <select class="form-control" 
    #                  name="form_tbl_dt['.$id.'][units_id]"
    #                  style = "width: 80px; padding-left: 2px; padding-right: 2px;"> ';
    #foreach($this->units as $k => $v){
    #  $selected = '';
    #  if( $k == $units_id) $selected = ' selected ';
    #  $output .= '
    #          <option value="'.$k.'" '.$selected.' >'.$v.'</option>';
    #}
    #$output .= '
    #          </select>
    #        </td>';
    
    #        <td><input class="form-control " type="text" name="form_tbl_dt['.$id.'][old_price]" value="'.$old_price.'"></td>          
    $output .= '  
            <td>
              <select class="form-control" 
                      name="form_tbl_dt['.$id.'][availability_id]"
                      style = "width: 180px; padding-left: 2px; padding-right: 2px;"> ';
    foreach($this->availability as $k => $v){
      $selected = '';
      if( $k == $availability_id) $selected = ' selected ';
      $output .= '
              <option value="'.$k.'" '.$selected.' >'.$v.'</option>';
    }
    $output .= '
              </select>
            </td>';
    
    $output .= '
            <td>
              <input class="form-control price_input" type="text" name="form_tbl_dt['.$id.'][price]" value="'.$price.'">
            </td>
            
            <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).' 
            </td>
  			  </tr>';
    
    return $output;
  }
  
  function show_table_row_action_btn($id){
    $output = '';
    
    $output .= '
              <a  class = "btn btn-warning btn-sm my-1 ajax_edit_item"
                  title = "Копировать"
                  href  = "?adds&copyid='.$id.'">
                <i class="far fa-copy"></i>
              </a>';
              
    $output .= parent::show_table_row_action_btn($id);
    
    return $output;
  }  
  
  function show_entrie_catalog($parent = 0) {
    
		#$list = db::select('id, title, `hide`, `img`', '`'.$this->prefix."cat_".$this->carusel_name.'`', "parent_id = $parent", "`ord`");
    
    $s = "
      SELECT `id`, `title`, `hide`, `img`
      FROM `".$this->cat_carusel_name."`
      WHERE `parent_id` = $parent
      ORDER BY `ord`
    ";
	  $q = $this->pdo->query($s);
    $list = $q->fetchAll();
    
    if (!$list) return; #  mysql_error();
			
		$output .= '
      <ul class="listingb">
    ';
		
		foreach ($list as $item) {
			extract($item);
			if (!$title) continue;
			if ($hide) $liclass="class='subhide'";
			else  $liclass="";
			$output .= '<li '.$liclass.'>';
			
			$c = $this->count_goods($id);
			$lnk = "?c_id=$id";
      
      $output .= ' <span class="label">'.$id.'</span> ';
      if($img){
        $output .= '
          <span class = "posit label" data-content=\'<img style="max-height:150px; max-width:150px;" src = "/images/'.$this->carusel_name.'/cat/slide/'.$img.'">\'>
            image
          </span> &nbsp;
        ';
      }
      $output .= ' <a href="'.$lnk.'"><strong>'.$title.'</strong> ('.$c.')</a> ';
			#$listing = db::select('*', '`'.$this->prefix.$this->carusel_name.'`', "cat_id = $id", '`ord`');
      $s = "
        SELECT *
        FROM `".$this->prefix.$this->carusel_name."`
        WHERE `cat_id` = $id
        ORDER BY `ord`
      ";
  	  $q = $this->pdo->query($s);
      $listing = $q->fetchAll();
      
			$output .= '<ul style = "list-style:none; padding-left: 15px;">';
			foreach ($listing as $posi){ 
				$output .= '
          <li style = "display:block">
            <span class = "label" >
              '.$posi['id'].'
            </span> 
        ';
        if($posi['img']){
          $output .= '
          <span class = "posit label" data-content=\'<img style="max-height:150px; max-width:150px;" src = "/images/'.$this->carusel_name.'/slide/'.$posi['img'].'">\'>
            image
          </span> 
          ';
        }
        $output .= '
            <span title="Скрыть" onclick="star_check('.$posi['id'].', \'hide\')" class="star_check '.$this->getStarValStyle($posi['hide']).'" id="hide_'.$posi['id'].'"></span>
            <span title="На главной" onclick="star_check('.$posi['id'].', \'fl_show_mine\')" class="star_check '.$this->getStarValStyle($posi['fl_show_mine']).'" id="fl_show_mine_'.$posi['id'].'"></span>
            <a href="?edits='.$posi['id'].'">'.$posi['title'].'</a> 
            <span class="badge badge-success">'.$posi['price'].'</span>
            <span class="badge badge-success">'.$posi['price_ye'].'</span>
          </li>
        ';
			}
			$output .= '</ul>';
			$output .= $this->show_entrie_catalog($id);
			$output .= '</li>';
		}
		$output .= '</ul>'; 
    
    return $output;
	}
  
  function makeGroupOperations(){
    
    $output = "";
    $group_action = '';
    $group_items = '';
    
    if(isset($_POST['group_action']) && $_POST['group_action']){
      $group_action = $_POST['group_action'];
    }
    
    if(isset($_POST['group_item']) && $_POST['group_item']){
      $group_items = $_POST['group_item'];
    }
    
    $output .= parent::makeGroupOperations();
    
    switch($group_action){
        
      case 'set_new_tbl_date':
        
          if(isset($_POST['form_tbl_dt']) && $_POST['form_tbl_dt']){
            $form_tbl_dt = $_POST['form_tbl_dt'];
            
            foreach ($form_tbl_dt as $k => $v){
              $updt_arr = array();
              foreach($v as $field_name => $field_val){
                $updt_arr[$field_name] = $field_val;
              } #pri($updt_arr);
              $res = db::update( $this->prefix.$this->carusel_name, $updt_arr, 'id = '.$k, null, 0); #pri($res);
               
            }
          }
        
        break;
        
    }
    
  }
  
  function getGroupOperations(){
    $output = '';
    #pri($_POST);
    
    $output .= $this->makeGroupOperations();
    
    $output .= parent::getGroupOperations();
    
    $output .= '
    <div class = "group_operation_box">
      <button type    = "submit" 
              class   = "btn btn-warning" 
              name    = "group_action"  
              value   = "set_new_tbl_date"  
              style   = "position: fixed; right: 107px; bottom: 10px; z-index: 100;"
              onclick = "javascript: if (confirm(\'Сохранить новые цены и статусы?\')) { return true;} else { return false;}">
        <i class="fas fa-coins"></i> </span> Сохранить новые цены
      </button>
    </div>';
    
    $this->admin->adminFooterScripts .= '';
    
    return $output;
  }
  
  function full_tree(){
    $output = '';
    
    if( isset($_SESSION[$this->carusel_name]['c_id']) && $_SESSION[$this->carusel_name]['c_id'] ){
      $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
      $this->bread = array();
      $this->show_bread_crumbs($item_cat_id);
      $this->admin->setForName('bread', $this->getForName('bread')); 
    }
    $this->title = 'Полный каталог'; 
    $this->header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    
    $output .= $this->get_show_table_menu_btn();
    
    if( isset($_POST['goods_price']) && $_POST['goods_price'] ){
      $carusel_error = '';
      $curent_prices = $cprace_row = array();
      
      $arr_curent_price = db::select("`id`, `price`, `title`", $this->prefix.$this->carusel_name );
      foreach($arr_curent_price as $kc => $vc ){
        $curent_prices[$vc['id']] = $vc['price'];
        $cprace_row[$vc['id']]    = $vc;
      }
      $res_table = '';
      $res_table .= '
        <table class = "table table-sm" style = "width:inherit;">
          <thead>
            <tr>
              <td>id</td>
              <td>Наименование</td>
              <td style = "color: orangered;">Старая цена</td>
              <td style = "color: green;">Новая цена</td>     
            </tr>
          </thead>
          <tbody>';
            
      foreach($_POST['goods_price'] as $k => $v){
        
        #$curent_price = db::value("price", $this->prefix.$this->carusel_name, "id = $k", 0 );
        $curent_price = '';
        if( $v <> $curent_prices[$k] ){
          #pri($curent_prices[$k]);
          
          $res_table .= '
            <tr>
              <td>'.$k.'</td>
              <td>'.$cprace_row[$k]['title'].'</td> 
              <td style = "color: orangered;">'.number_format($curent_prices[$k], 0, ',', ' ').'</td>
              <td style = "color: green;">'.number_format($v, 0, ',', ' ').'</td>
            </tr>';
          
          if($res = db::update( $this->prefix.$this->carusel_name, array ("price" => $v), "id = $k" )){
            if($this->log){ // Ведение лога
              $res_log = $this->log->addLogRecord("Обновление цены", "update_price", $this->prefix.$this->carusel_name, $k, $curent_price ); 
            } 
          }else{
            $carusel_error = "Произошла ошибка при ОБНОВЛЕНИИ цены в бд функсия full_tree()";
            if($this->log){ // Ведение лога
                $res_log = $this->log->addLogRecord($carusel_error, "update_price", $this->prefix.$this->carusel_name, $k, $curent_price ); 
            }
          }
        
        }
      }
      
      $res_table .= '
          </tbody>
        </table>';
      
      if($carusel_error){
        $output .= '
          <div class="alert alert-danger" role="alert">
            '.$carusel_error.'<br />
            <p>
              <a href="/'.$this->admin_dir.'/all_log.php">Перейти к логам</a>
            </p>
          </div>';
      }else{
        $output .= '
          <div class="alert alert-success" role="alert">
            Цены успешно обновлены
          </div>';
        $output .= $res_table; 
      }
      
    }
    
    $output .= $this->full_tree_content();
    
    return $output;
  }
  
  function show_entrie_catalog_content(){
    $output = '';
    $output .= '
      <form action="" method = "post">';
    $output .= parent::show_entrie_catalog_content();            
        $output .= '
        <input type="submit" value="сохранить" class="btn btn-success btn-large" id="submit">
      </form>';
      
    return $output;
  }
  
  function get_full_tree_line( $item ){
    $output = '';
    
    $output .= '
      <div style = "listitem_item">
        <span class = "label" style = "color: #888;" >
          '.$item['id'].'
        </span> 
    ';
    if($item['img']){
      $output .= '
      <span class = "posit label" data-content=\'<img style="max-height:150px; max-width:150px;" src = "/images/'.$this->carusel_name.'/slide/'.$item['img'].'">\'>
        image
      </span> 
      ';
    }
    $output .= '
        <span title="Скрыть" onclick="star_check('.$item['id'].', \'hide\')" class="star_check '.$this->getStarValStyle($item['hide']).'" id="hide_'.$item['id'].'"></span>';
    if(isset($item['fl_show_mine'])){
      $output .= '
        <span title="На главной" onclick="star_check('.$item['id'].', \'fl_show_mine\')" class="star_check '.$this->getStarValStyle($item['fl_show_mine']).'" id="fl_show_mine_'.$item['id'].'"></span>';
    }
    $output .= '
        <a href="?edits='.$item['id'].'">'.$item['title'].'</a>';
    if(isset($item['is_yandex_market_xml'])){
      $output .= '
        <span title="Показывать в яндекс Маркет" onclick="star_check('.$item['id'].', \'is_yandex_market_xml\')" class="star_check '.$this->getStarValStyle($item['is_yandex_market_xml']).'" id="is_yandex_market_xml_'.$item['id'].'"></span>';
    } 
    if(isset($item['price'])){
      #$output .= '
      #  <span class="badge badge-success">'.$item['price'].'</span>';
      $output .= '
            <input class = "form-control full_tree_price" type = "text" name = "goods_price['.$item['id'].']" value = "'.$item['price'].'">';
    }
    if(isset($item['price_ye'])){
      $output .= '
        <span class="badge badge-success">'.$item['price_ye'].'</span>';
    }
    $output .= '
      </div>';
    
    return $output;
  }
  
  private function cleanValueCh($value) {
		$result = $value;
		$v = explode(' ', $result);
		if ( !empty($v[0]) and '"' != mb_substr(trim($v[0]), -1) ) {
			$result = ltrim($result, '"');
		}
		$result = preg_replace('~(?=("))\1{2,}~', '"' , $result);
		$count_ch = substr_count($result, '"');
		if ( $count_ch and $count_ch%2 ) {
			$result = rtrim($result, '"');
		}
		return $result;
	}
  
  function updateInformation(){
    $output = '';
    
   	try {
        $c_id = $_SESSION[$this->carusel_name]['c_id']; 
       
        if( isset($_SESSION[$this->carusel_name]['c_id']) && $_SESSION[$this->carusel_name]['c_id'] ){
          $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
          $this->bread = array();
          $this->show_bread_crumbs($item_cat_id); 
          $this->admin->setForName('bread', $this->getForName('bread')); 
        }
        
        $this->title = 'Обновление цен'; 
        $this->header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
        
        $output .= $this->get_show_table_menu_btn( $c_id );
          
        
        $information_text ='';
        
        if( isset( $_POST['information_text'])  ){
        
          $update_info      = '';
          $information_text = $_POST['information_text'];
          $data_in          = explode("\n", $information_text);
          
      		for( $i = 0; $i < count($data_in); $i++ ) {
      			$row = trim($data_in[$i]);
      			if ( !empty($row) ) {
      				$data_in[$i] = explode("\t", $data_in[$i]);
      				for($j = 0; $j < count($data_in[$i]); $j++) {
      					$data[$i][$j] = trim(str_replace("…", '-', $data_in[$i][$j]));
      				}
      			}
      		} #pri($data); 
          
          #$all_brand_arr    = array();
          #$all_brand_id_arr = array();
          #$all_brand = db::select('id, title', DB_PFX.'brand');
          #foreach($all_brand as $k => $v){
          #  $all_brand_arr[$v['title']] = $v['id'];
          #  $all_brand_id_arr[$v['id']] = $v['title'];
          #} #pri( $all_brand_arr ); die();
          
        	$order = 0;
        	$i     = 0;
          $td_style = 'padding: 2px 5px; border: 1px solid #ddd;';
          foreach ( $data as $row ) {    # pri($row);                                     
      			$i++;
            if ( !empty($row[0])  ) { 
              # $brand_name = $this->cleanValueCh($row[0]);
              
      				$article      = $this->cleanValueCh($row[0]);
              $price        = str_replace( ' ', '', $this->cleanValueCh($row[1]));
              $old_price    = str_replace( ' ', '', $this->cleanValueCh($row[2]));
              
              #$update_date  = $this->cleanValueCh($row[3]);
              
              
              #$brand_id = '';
              #if(isset($all_brand_arr[$brand_name]) && $all_brand_arr[$brand_name]){
              #  $brand_id = intval($all_brand_arr[$brand_name]);
              #}
              
              $s = 'article = "'.$article.'"';
              #if($brand_id){
              #  $s = 'article = "'.$article.'" AND brand_id = '.$brand_id;
              #} #pri( $s );
              
              
              if( $good_select = db::select ( "*", DB_PFX.'goods', $s, null, null, null, 0  ) ){ 
                
                
                foreach($good_select as $good_row){ #pri($good_row);
                  $arr_upd = array(
                    'price'       =>  floatval(str_replace(',', '.', $price)),
                    'old_price'   =>  floatval(str_replace(',', '.', $old_price)),
                  #  'update_date' =>  addslashes($update_date),
                  ); # pri($arr_upd); 
                  
                  $res_upd = db::update( DB_PFX.'goods', $arr_upd, 'id = '.$good_row['id'], null, 0 ); #pri($res_upd);
                  $brand_name = '';
                  if($res_upd){
                    $new_row = db::row( '*', DB_PFX.'goods', 'id = '.$good_row['id'], null, 0  );
                    
                    #if(isset($new_row['brand_id']) && $new_row['brand_id']){
                    #  $brand_name = $all_brand_id_arr[$new_row['brand_id']];
                    #}
                    
                    $update_info .= '
                      <tr>
                        <td style="'.$td_style.'">'.$i.'</td>';
                        #<td style="'.$td_style.'">'.$brand_name.'</td>
                    $update_info .= '
                        <td style="'.$td_style.'">'.$new_row['title'].'</td> 
                        <td style="'.$td_style.' text-align:right;">'.$new_row['article'].'</td> 
                        <td style="'.$td_style.' text-align:right;">'.$new_row['price'].'</td>
                        <td style="'.$td_style.' text-align:right;">'.$new_row['old_price'].'</td>';
                        #<td style="'.$td_style.'">'.$new_row['update_date'].'</td>
                    $update_info .= '
                        <td style="'.$td_style.'">обновлен</td>
                        <td style="'.$td_style.'"><a href = "//'.$_SERVER['HTTP_HOST'].'/'.ADM_DIR.'/goods.php?edits='.$good_row['id'].'" target = "_blank">Админка</a></td> 
                      </tr>
                    ';
                  }else{
                    
                    #if(isset($good_row['brand_id']) && $good_row['brand_id']){
                    #  $brand_name = $all_brand_id_arr[$good_row['brand_id']];
                    #}
                    $update_info .= '
                      <tr>
                        <td style="'.$td_style.'">'.$i.'</td>';
                        #<td style="'.$td_style.'">'.$brand_name.'</td>
                    $update_info .= '
                        <td style="'.$td_style.'">'.$good_row['title'].'</td> 
                        <td style="'.$td_style.' text-align:right;">'.$good_row['article'].'</td> 
                        <td style="'.$td_style.' text-align:right;">'.$good_row['price'].'</td>
                        <td style="'.$td_style.' text-align:right;">'.$good_row['old_price'].'</td>';
                        #<td style="'.$td_style.'">'.$good_row['update_date'].'</td>
                    $update_info .= '
                        <td style="'.$td_style.'"><span style = "color: red;">Ошибка при обновлении</span></td>
                        <td style="'.$td_style.'"><a href = "//'.$_SERVER['HTTP_HOST'].'/'.ADM_DIR.'/goods.php?edits='.$good_row['id'].'" target = "_blank">Админка</a></td> 
                      </tr>
                    ';
                     
                  }
                
                }
                
                
                
              }else{
                $update_info .= '
                      <tr>
                        <td style="'.$td_style.'">'.$i.'</td>';
                        #<td style="'.$td_style.'">'.$brand_name.'</td>
                $update_info .= '        
                        <td style="'.$td_style.'"></td> 
                        <td style="'.$td_style.' text-align:right;">'.$article.'</td> 
                        <td style="'.$td_style.' text-align:right;">'.$price.'</td>
                        <td style="'.$td_style.' text-align:right;">'.$old_price.'</td>';
                        #<td style="'.$td_style.'"></td>
                $update_info .= '
                        <td style="'.$td_style.'"><span style = "color: red;">Товар не найден</span></td> 
                        <td style="'.$td_style.'"></td> 
                      </tr>
                    ';
              } 
             
            } # pri($row);
          }
            
          if($update_info){
            
            $update_info = '
              <p><b>Результаты обновления цен</b></p>
              
              <table cellpadding="4" cellspacing="0">
                <tbody>
                  <tr>
                    <td style="'.$td_style.'">№</td>
                    <td style="'.$td_style.'">Наименование</td> 
                    <td style="'.$td_style.'">Артикул</td> 
                    <td style="'.$td_style.'">Цена, руб.</td>
                    <td style="'.$td_style.'">Старая цена, руб.</td>
                    <td style="'.$td_style.'">Результат</td>
                    <td style="'.$td_style.'">Админка</td>
                  </tr>
                <tr>
                '.$update_info.'
                
              </table>';
              #<td style="'.$td_style.'">Бренд</td>
              #<td style="'.$td_style.'">Дата обновления</td>
            $output .= $update_info; 
            
            $email_order = db::value('val', DB_PFX.'config', "name = 'email_order'");
            #$email_order = '1@in-ri.ru';
            #pri($email_order);
            // Если несколько адресов перечисленно через запятую
            
            #$exp_email_order = explode(',', $email_order);
            #if(is_array($exp_email_order)){
            #  $email_order = array();
            #  foreach($exp_email_order as $ee_mail){
            #    $email_order[] = $ee_mail;
            #  }
            #}
            
            $date = date("d.m.y H:i:s");
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= "Content-type: text/html; charset=utf-8 \r\n";
            $headers .= "From: ".$_SERVER['HTTP_HOST']." <info@".$_SERVER['HTTP_HOST'].">";
            #$is_sending = mail($email_order.', 1@in-ri.ru', "Обновление цен ".$_SERVER['HTTP_HOST']."  $date", $update_info, $headers);
            
            $is_sending = mail($email_order, "Обновление цен ".$_SERVER['HTTP_HOST']." $date", $update_info, $headers);
          } 
            
    			
    		}
        
        $output .= '
            <div class="container">
<p>Пример файла в Excel</p>
<pre style = "font-style: mono;" >
| Артикул   | Цена      | Старая цена |
---------------------------------------
| 25310     | 465,6     | 507,5       |
| EZ9S16240 | 749,77    | 817         |
| EZ9S16140 | 482,09    | 525         |
| EZ9S16340 | 1142,22   | 1245        |
</pre>
            </div>
            <div class="container">
              <form method="post" enctype="multipart/form-data" action="/'.ADM_DIR.'/goods.php?update_information" class="noprint mt20 form-horizontal">
                <div class="form-group c_row">
    							<div class=" col-xs-12">Текст из файла xls</div>
    						</div>
                <div class="form-group c_row">
                  <div class="col-12 col-sm-12 col-md-12 c_title control-label">
                    <textarea class = "form-control" name = "information_text" style = "min-height: 250px;">'.$information_text.'</textarea>
                  
                  </div>
                </div>
                <div class="form-group c_row">
                  <div class="col-12 col-sm-12 col-md-12 c_cont">
                    <input type="submit" value="Загрузить" class="btn btn-primary btn-sm float-l " />
                  </div>
                </div>
              
    					</form>
    					<div class="clear"></div>
    					<br/>
    		    </div>
    					<br/>';
        
        return $output;
    
		}
		catch (Exception $e) {
			echo $e->getMessage();
		} 
    
    return $output;
  }
  
  function get_xls_table_header(){ # Заголовок таблицы
    $output = '';
    
    foreach($this->date_arr as $k => $v ){      
      if( !isset($this->date_arr_not_xls_export[$k]) ){ # Не входит в исключенные поля
        $output .= '
          <th>'.$v.'</th> ';
      }
    }
    
    return $output;
  }
  
  function get_xls_table_cat_row( $cat_item, $style_td ){ # Строка категории
    $output = '';
    $output .= '
        <tr>
          <td style = "'.$style_td.'"><b>'.$this->cat_numbers[$cat_item['id']].'</b></td>
          <td style = "'.$style_td.'"><b>'.$cat_item['title'].'</b></td>
          <td style = "'.$style_td.'"></td>'; # '.$cat_item['id'].'
          
    foreach($this->date_arr as $k => $v ){ 
      if( !isset($this->date_arr_not_xls_export[$k]) ){ # Не входит в исключенные поля
        $output .= '
            <td style = "'.$style_td.'"></td> ';
      }
    }
    $output .= '
        </tr>';
    
    return $output;
  }
  
  function get_cat_numbers( $cid, $prefix = '' ){
    $cat_items = db::select(  '*', DB_PFX.'goods_cat', "parent_id = ".$cid, 'ord', null, null, 0 );
    #pri($cat_items);
    
    $i = 1;
    foreach($cat_items as $cat_item){
      $number = $i++.'.';
      $this->cat_numbers[$cat_item['id']] = $prefix.$number;
      $this->get_cat_numbers( $cat_item['id'], $prefix.$number );
    }
  }
  
  function set_handbook_linked(){     # Справочник связанных значений
  
    $this->date_arr_not_xls_export = array( # Не экспортировать поля
      'title'             => 'Название',    # расчитывается отдельно
      'cat_id'            => 'Категория',   # расчитывается отдельно
      'article'           => 'Артикул',     # расчитывается отдельно
      /*'article_provider'  => 'Артикул поставщика',
      'old_price'         => 'Старая цена',
      'price'             => 'Цена',
      'amount'            => 'Количество',
      
      'availability_id'   => 'Варианты наличия',
      'country_id'        => 'Страна',
      'brand_id'          => 'Бренд',
      'units_id'          => 'Еденицы измерения',
      
      'longtxt1'          => 'Краткое описание',
      'longtxt2'          => 'Полное описание',
      'longtxt3'          => 'Технические характеристики',
      
      'is_hit'            => 'Хит продаж', 
      'is_new'            => 'Новинка',
      'is_sale'           => 'Скидка',
      
      'seo_h1'            => 'SEO h1',
      'seo_title'         => 'SEO Title',
      'seo_description'   => 'SEO Description',
      'seo_keywords'      => 'SEO Keywords',
      'img_alt'           => 'Alt изображение',
      'img_title'         => 'Title изображение',*/
      
      'orm_search_name'   => 'поле для поискового индекса orm_search_name', # расчитывается отдельно
      'orm_search'        => 'поле для поискового индекса orm_search',      # расчитывается отдельно
    );
    $this->float_field    = array('old_price', 'price');
    $this->handbook_field = array('brand', 'availability', 'units');
    
    if(!isset($this->brand)){
      $brands = db::select('*', DB_PFX.'brand' );
      
      foreach( $brands as $brand){
        $this->brand[$brand['id']]         = $brand;
        $this->brand_name[$brand['title']] = $brand;
      } #pri($this->brand);
    }
    
    if(!isset($this->availability)){
      $availabilitys = db::select('*', DB_PFX.'availability' );
      foreach( $availabilitys as $availability){
        $this->availability[$availability['id']]         = $availability;
        $this->availability_name[$availability['title']] = $availability;
      } #pri($this->availability);
    }
    
    if(!isset($this->units)){
      $units = db::select('*', DB_PFX.'units' );
      foreach( $units as $unit){
        $this->units[$unit['id']]         = $unit;
        $this->units_name[$unit['title']] = $unit;
      } #pri($this->units);
    }
    if(!isset($this->cat_numbers)){
      $this->get_cat_numbers(0);
    } #pri($this->cat_numbers);
  }
  
  function xls_cat_slide( $id ){ #pri('xls_cat_slide');
    $output = '';
    
    $this->set_handbook_linked(); 
    
    
    $header .='<h1><a href="'.IA_URL.$this->carusel_name.'.php">'.$this->header.'</a></h1>';
    $this->header = $header;
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    if($id){
      $c_title = db::value('title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "id = ".$id );
      $title .='<a href="?c_id='.$id.'"> '.$c_title.' </a> → '; 
    }
    
    $title .=' выгрузка в xls каталога';
    
    $this->title  = $title;
    (!is_null($this->admin)) ?  : $output .=  '<h3>'.$title.'</h3><br><br>';
    
    
    
    if($xls_cat_rows = $this->get_xls_cat_rows( $id )){
      $output .= '
      <div> <a href="/'.ADM_DIR.'/goods.php?xls_id='.$id.'&get_xls=1" class = "btn btn-success" target = "_blank">Скачать xls</a> </div>';
      $output_table = '
      <div class="table-responsive">
        <table class = "table table-sm table-bordered" cellspacing="0 cellpadding="0 >
          <thead>
            <tr>
              <th>№ раздела</th>
              <th>Наименование</th>
              <th>Артикул</th>';
      $output_table .= $this->get_xls_table_header();
      $output_table .= '
            </tr>
          </thead>
          <tbody>
            '.$xls_cat_rows.'
          </tbody>
        </table>
      </div>';
      
    }
    if(isset($_GET['get_xls'])){
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream");
      header("Content-Type: application/download");
      header("Content-Disposition: attachment;filename=".$_SERVER['HTTP_HOST']."_".date('d.m.Y H:i:s').".xls"); 
      header("Content-Transfer-Encoding: binary");
      
      echo $output_table;
      die();
    }else{
      $output .= '
      <div class="table-responsive">';
      $output .= $output_table;
      $output .= '
      </div>';
    }
    
    #pri($this);
    
    return $output;
  }
  
  function get_xls_cat_rows($cid){ 
    $output = '';
    
    $cat_item = db::row( '*', DB_PFX.'goods_cat', "id = ".$cid );
    $style_td = 'border: 1px solid #777;';
    $output  .= $this->get_xls_table_cat_row($cat_item, $style_td);
    
    
    $items = db::select(  '*', DB_PFX.'goods', "cat_id = ".$cid, 'ord' );
    
    # Узнаем последнее время обновления в разделе
    #$last_update_time = '';
    #if($last_update_time_row = db::row( '*', $this->prefix.$this->carusel_name, "cat_id = ".$cid, "update_date DESC " )){
    #  $last_update_time = $last_update_time_row['update_date'];  
    #} pri($last_update_time); 
    
    foreach($items as $item){
      $brand = $availability = '';
     
      if( isset($item['brand_id']) )        $brand_id        = $this->brand[$item['brand_id']]['title'];
      if( isset($item['availability_id']) ) $availability_id = $this->availability[$item['availability_id']]['title'];
      if( isset($item['units_id']) )        $units_id        = $this->units[$item['units_id']]['title']; 
      $article = $item['article']; # if( !$article ) $article = $item['id'];
      $output .= '
      <tr style = "vertical-align: top;">
        <td style = "'.$style_td.'"></td>
        <td style = "'.$style_td.'">'.$item['title'].'</td>
        <td style = "'.$style_td.' text-align: right;">'.$article.'</td>'; 
        
      foreach($this->date_arr as $k => $v ){      
        if( !isset($this->date_arr_not_xls_export[$k]) ){ # Не входит в исключенные поля
          $item_val = $item[$k];
          
          
          if( isset($$k) && $$k) $item_val = $$k;
          #$item_val = str_replace("\r\n","", $item_val);
          if(isset($_GET['get_xls'])){
            $item_val = htmlspecialchars($item_val);
          }
          $output .= '
            <td style = "'.$style_td.'">'.$item_val.'</td> '; 
        }
      }    
      #if( $last_update_time && ( $last_update_time == $item['update_date'] ) ){
      #  $output .= '<td style = "'.$style_td.' background: yellow;">'.$item['update_date'].'</td>';
      #}else{
      #  $output .= '<td style = "'.$style_td.'">'.$item['update_date'].'</td>';
      #}
      
      $output .= '
      </tr>';
      
    }
    
    $cat_items = db::select(  '*', DB_PFX.'goods_cat', "parent_id = ".$cid, 'ord' );
    
    foreach($cat_items as $cat_item){
      $output .= $this->get_xls_cat_rows( $cat_item['id'] );
    }
    
    return $output;
  }
  
  function viewFormUploadCSV(){
    $output = '';
    
    $c_id = $_SESSION[$this->carusel_name]['c_id']; 
    
    if( isset($_SESSION[$this->carusel_name]['c_id']) && $_SESSION[$this->carusel_name]['c_id'] ){
      $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
      $this->bread = array();
      $this->show_bread_crumbs($item_cat_id);
      $this->admin->setForName('bread', $this->getForName('bread')); 
    } 
    $this->title = 'Обновление каталога'; 
    $this->header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    $output .= $this->get_show_table_menu_btn( $c_id );
    $output .= '
        <div class="container">
          <form method="post" enctype="multipart/form-data" action="" class="">
            <div class="my-3">
              <label for="price" class="form-label">
                Прайс-лист в формате CSV (разделители ";")<br>
                <small>Образец файла можно получить путем экспорта товаров в xls,<br> потом сохранить его как .csv</small>
              </label>
              
              <input class="form-control" type="file" id="price" name="price"  />
              
            </div>
            <div class="mb-3">   
              <input type="submit" value="Загрузить файл" class="btn btn-primary " />
            </div>  
					</form>
		    </div>
				<br/>';
    
    return $output;
  }
  
  private function uploadCSV() {
		try {
			if ( isset($_FILES['price']['tmp_name']) and file_exists($_FILES['price']['tmp_name']) ) {
				if ( move_uploaded_file($_FILES['price']['tmp_name'], $this->source_file . 'price.csv') ) {
					chmod($this->source_file . 'price.csv', 0777);
				}
			}
		}
		catch (Exception $e) {
			echo $e->getMessage();
		}
	}
  
  function conv($str){
    return mb_convert_encoding( $str, "utf-8", "windows-1251" );
  }
  
  function add_inri_csv_goods( $data_xls_row ){ 
    $output = '';
    
    if( $data_xls_row ){
      $new_goods_cat_arr = array(
        'id'                => $this->conv( $data_xls_row[0] )
      );
      $new_goods_arr = array(
        'cat_id'            => $this->update_cat_id,             # Предпологается то первой строкой будет категория
        'title'             => $this->conv( $data_xls_row[1] ),
        'article'           => $this->conv( $data_xls_row[2] )
      );
      
      $i = 3;  
       
      foreach($this->date_arr as $k => $v ){ 
      
        if( !isset($this->date_arr_not_xls_export[$k]) ){ # Не входит в исключенные поля
          $item_val = $this->conv( $data_xls_row[$i] );
          
          if( in_array($k, $this->float_field ) ){
            $item_val = str_replace( ',', '.', $data_xls_row[$i]);
          }
          
          # $this->handbook_field = array('brand', 'availability', 'units'); 
          # Обратный поиск id по значению для brand_id, availability_id, units_id и т.д.
          foreach( $this->handbook_field as $valtit ){
            
            if( $k == $valtit.'_id' ){
              $class_nm = $valtit.'_name'; 
              if( isset($this->$class_nm[$item_val]['id']) ){ 
                $item_val = $this->$class_nm[$item_val]['id'];  
              }
            }
            
            $new_goods_arr[$k]  = $item_val;
          }
          $i++;
        }
      }
    
    }
    
    
    if( $new_goods_cat_arr['id'] ){ # Является разделом
      
      $is_goods_cat =  db::row( '*', DB_PFX.'goods_cat', "title = '".$new_goods_arr['title']."'", null, 0 );
      if($is_goods_cat){
        $this->update_cat_id = $is_goods_cat['id'];
        $this->update_info['goods_cat_update'] += 1;
      }else{
        $insert_arr = array( 'title' => $new_goods_arr['title'] );
        $this->update_cat_id = db::insert( DB_PFX.'goods_cat', $insert_arr, 0 );
        $this->update_info['goods_cat_insert'] += 1;
      }
      $output .= '<p>Категория: '.$new_goods_arr['title'].' <a href="/'.ADM_DIR.'/goods.php?editc='.$this->update_cat_id.'" target = "_blank" >Админка</a> </p>';
      
    }else{                           # Является товаром
      $fl_is_action = false;
      
      # Правка 1
      # Если артикул и наименование совпадают,
      # Но есть такой товар где то в другом разделе
      # Не нужно добавлять позицую
      # Вывести уведомление
      
      $is_double_goods = db::select(  
        "*",
        DB_PFX.'goods', 
        "     `title`    = '".$new_goods_arr['title']."' 
          AND `article`  = '".$new_goods_arr['article']."'
          AND `cat_id`  != '".$this->update_cat_id."'
        ",
        null,
        null,
        null, 
        0                          ); #pri($is_double_goods);  
        
      if( $is_double_goods ){
        foreach( $is_double_goods as $kg => $vg ){
          $this->update_info['double_goods'] += 1;
          $vg_cat_title = db::value('title', DB_PFX.'goods_cat', 'id = '.$vg['cat_id'] );
          $this->double_goods[] = 'Товар: '.$vg['title'].' <a href="/'.ADM_DIR.'/goods.php?edits='.$vg['id'].'" target = "_blank" >Админка</a> [Категория: <a href="/'.ADM_DIR.'/goods.php?c_id='.$vg['cat_id'].'" target = "_blank">'.$vg_cat_title.'</a> ]';  
        }
        $fl_is_action = true;
      }
      # Енд Правка 1
      
      
      # Правка 2
      # Если товар существцет 
      # артикул совподает, а имена разные, то : 
      # 1. создать новый товарони попадают в отдельный каталог админки «Неразобранные» 
      # 2. переместить его в раздел Неразобранные /'.ADM_DIR.'/goods.php?c_id=594 
      # 3. Вывести уведобление о позиции. 
      # 4. ЧПУ при этом не формируеть
      
      #$is_unsorted_goods = db::row(  
      #  "*", 
      #  DB_PFX.'goods', 
      #  "title != '".$new_goods_arr['title']."' AND label_id = '".$new_goods_arr['label_id']."'",
      #  null, 
      #  0                         ); 
      #
      #if( $is_unsorted_goods && !$fl_is_action){
      #  $new_goods_arr['cat_id'] = 594; #pri('Товар: '.$new_goods_arr['title']);
      #  $goods_id = $res_goods   = db::insert( DB_PFX.'goods', $new_goods_arr, 0 );
      #  # $url = null;
      #  # $this->url->set_url( $url, DB_PFX.'goods', $res_goods, $new_goods_arr['title'] );
      #  $this->update_info['remuve_goods_unsorted'] += 1;
      #  $this->goods_unsorted[] = 'Товар: '.$new_goods_arr['title'].' <a href="/'.ADM_DIR.'/goods.php?edits='.$goods_id.'" target = "_blank" >Админка</a>';
      #  $fl_is_action = true;
      #}
      
      # Енд Правка 2
      
      
      
      if(!$fl_is_action){                         # Является товаром
        
          $is_goods = db::row(
            "*", 
            DB_PFX.'goods', 
            "     `title`    = '".$new_goods_arr['title']."' 
              AND `article`  = '".$new_goods_arr['article']."'
              AND `cat_id`   = '".$this->update_cat_id."'      ",
            null,  
            0); 
            
          if($is_goods){
            $res_goods = db::update( DB_PFX.'goods', $new_goods_arr, "id = ".$is_goods['id'] );
            $goods_id  = $is_goods['id'];
            $this->update_info['goods_update'] += 1;
          }else{
            $goods_id = $res_goods = db::insert( DB_PFX.'goods', $new_goods_arr, 0 );
            $url      = null;
            $this->url->set_url( $url, DB_PFX.'goods', $res_goods, $new_goods_arr['title'] );
            $this->update_info['goods_insert'] += 1;
          } 
          $output .= '<p>Товар: '.$new_goods_arr['title'].' <a href="/'.ADM_DIR.'/goods.php?edits='.$goods_id.'" target = "_blank" >Админка</a> </p>';
      
      }
    }
    
    #pri($data_arr);
    #pri($new_goods_arr);
    $output .= pri( $new_goods_arr, 1);
    
    return $output;
  }
  
  function parseCSVData( ) { 
    $output = '<h2>Загрузка товаров</h2>';
    
    $this->update_info    = array(
                              'goods_cat_insert'       => 0, # Кол-во добавленных Категорий
                              'goods_cat_update'       => 0, # Кол-во обновленных Категорий
                              'goods_insert'           => 0, # Кол-во добавленных Товаров
                              'goods_update'           => 0, # Кол-во обновленных Товаров
                              'remuve_goods_unsorted'  => 0, # Кол-во нераспознаных Товаров
                              'double_goods'           => 0, # Кол-во дублей Товаров
                            );
    $this->goods_unsorted = array();
    $this->double_goods   = array();
    $this->url            = new Url('url');
    $csv_file             = $this->source_file . 'price.csv';
    
    $this->set_handbook_linked();
    
    $row = 0;
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
      
      while (($data_xls_row = fgetcsv($handle, 100000, ";")) !== FALSE) {
        $row++;
        $num = count($data_xls_row);
        
        $output .= "<br /><p><b> $num полей в строке $row: </b></p>\n"; 
        
        if($row == 1){
          # Пропускаем заголовки таблицы
        }else{
          $output .= $this->add_inri_csv_goods($data_xls_row);
        }
      }
      
      fclose($handle);
    }
    
		$res =  '
			<p>Добавлено категорий:        <strong>' . $this->update_info['goods_cat_insert']     . '</strong></p>
			<p>Обновлено категорий:        <strong>' . $this->update_info['goods_cat_update']     . '</strong></p>
			<p>Добавлено товаров:          <strong>' . $this->update_info['goods_insert']         . '</strong></p>
			<p>Обновлено товаров:          <strong>' . $this->update_info['goods_update']         . '</strong></p>
      <p>Дублей товаров:             <strong>' . $this->update_info['double_goods']         . '</strong></p>';
      
    #$res .=  '  
    #  <p>Размещено в <a href="/'.ADM_DIR.'/goods.php?c_id=594" target = "_blank">нераспознаные</a>:
    #                                 <strong>' . $this->update_info['remuve_goods_unsorted']. '</strong></p>';
                                     
    if($this->update_info['remuve_goods_unsorted']){
      $res .= '
        <p><b>Список нераспознаных товаров:</b></p>';
      foreach($this->goods_unsorted as $k => $v ){
        $res .= $v.'</br>';
      }
      $res .= '</br>';
    }
    if($this->update_info['double_goods']){
      $res .= '
        <p><b>Список Дулированых товаров:</b></p>';
      foreach($this->double_goods as $k => $v ){
        $res .= $v.'</br>';
      }
    }
    
    return $res.$output;
    
	}
  
  function upload_good_from_csv_file(){
    $output = '';
    
    $output = $this->viewFormUploadCSV();
    
		if ( $_FILES ) {
			$this->uploadCSV(); # die('ok');
      if(file_exists ( $this->source_file . 'price.csv' ) ){
			#$f = file_get_contents($this->source_file . 'price.csv');
			$output .= $this->parseCSVData();
			unlink($this->source_file . 'price.csv');
      }else{
        $output .= '<p><span style = "color: red;">Файл не выбран</span></p>';
      }
		}
    
    return $output;
  }
  
  function getContent(&$admin = null){
    $carisel = $this;
    
    if(!is_null($admin)){
      if(isset($admin->is_admin_navigation) && ($admin->is_admin_navigation) ){
        $this->admin = &$admin;
      }
    }
    
    $output = '';
        
    if (isset($_SESSION["WA_USER"])){
      
      if(isset($_GET['view_tree'])){
        $output .= $carisel->view_tree();
      }elseif(isset($_GET['full_tree'])){
        $output .= $carisel->full_tree();
      }
      
      
      elseif(isset($_GET['upload_good_csv'])){           # Обновить каталог .csv
        $output .= $carisel->upload_good_from_csv_file();
      }
      #elseif(isset($_GET['upload_img'])){               # Загрузить кртинки
      #  $output .= $this->viewFormUploadZip(); 
  	  #	if ( $_FILES ) {
  		#		$output .= $this->uploadZipImg();
  		#	}
      #}
      elseif(isset($_GET['update_information'])){        # Обновить цены
        $output .= $this->updateInformation(); 
        
      }elseif(isset($_GET["xls_id"])){                   # Сохранить в .xls
        $output .= $carisel->xls_cat_slide(intval($_GET["xls_id"]));  
      }
    }
      
    if(!$output){
      $output .= parent::getContent( $admin);
    }else{
       if(!is_null($admin)){
        $admin->setForName('header', $this->getForName('header'));
        $admin->setForName('bread', $this->getForName('bread'));
        $admin->setForName('title', $this->getForName('title'));
        $admin->setForName('cont_footer', $this->getForName('cont_footer'));
      }
    }
    
    return $output;
  }
  
}

$date_arr = array(
    'title'             => 'Название',
    'cat_id'            => 'Категория',
    'article'           => 'Артикул',
    'article_provider'  => 'Артикул поставщика',
    'price'             => 'Цена',
    
    'old_price'         => 'Старая цена', 
    'amount'            => 'Количество',
    
    'availability_id'   => 'Варианты наличия',
    'country_id'        => 'Страна',
    'brand_id'          => 'Бренд',
    'units_id'          => 'Еденицы измерения',
    
    'longtxt1'          => 'Краткое описание',
    'longtxt2'          => 'Полное описание',
    'longtxt3'          => 'Технические характеристики',
    
    'is_hit'            => 'Хит продаж', 
    'is_new'            => 'Новинка',
    'is_sale'           => 'Скидка',
    
    'seo_h1'            => 'SEO h1',
    'seo_title'         => 'SEO Title',
    'seo_description'   => 'SEO Description',
    'seo_keywords'      => 'SEO Keywords',
    'img_alt'           => 'Alt изображение',
    'img_title'         => 'Title изображение',
    
    'orm_search_name'   => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'        => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса
  );

$date_cat_arr = array(
    'title'             => 'Название',
    'parent_id'         => 'Категория',
    #'country_id'        => 'Страна',
    'longtxt1'          => 'Краткий текст',
    'longtxt2'          => 'Полный текст (для отдельной страницы)', 
    'longtxt3'          => 'Текст в<br/>подвале раздела',   
    'seo_h1'            => 'SEO h1',
    'seo_title'         => 'SEO Title',
    'seo_description'   => 'SEO Description',
    'seo_keywords'      => 'SEO Keywords',
    'img_alt'           => 'Alt изображение',
    'img_title'         => 'Title изображение',
    
    'orm_search_name'   => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'        => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса
    

  );
     
$pager = array(
  'perPage' => 10,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'longtxt1', 'longtxt2');

$carisel = new Goods('goods', $date_arr, $date_cat_arr, false, false, $pager);

$carisel->setHeader('КАТАЛОГ ТОВАРОВ');
$carisel->setIsUrl(true);
$carisel->setIsImages(true);
$carisel->setIsFiles(true);
$carisel->setIsLog(true);
$carisel->setIsPager(false);
$carisel->setIsFilter(true);
$carisel->setFilterField($arrfilterfield); 
$carisel->setImg_ideal_width(960);  
$carisel->setImg_ideal_height(960);
$carisel->getCatImg_ideal_width(960);  
$carisel->getCatImg_ideal_height(960); 
  
#$carisel->setDate_arr($date_arr);

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}