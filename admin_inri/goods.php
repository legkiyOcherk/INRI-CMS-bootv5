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
                    <a href="'.IA_URL.$this->carusel_name.'.php?c_id='.$id.'" title="редактировать">'.$title.'</a>
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
        input[type="text"].price_input{
        background-color: #88d888; font-weight: bold;  font-size: 20px; text-align: right;
      }
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
  
    function get_show_table_menu_btn( $c_id ){
    $output = "";
    
    $output .=  '
      <table class="table table-sm">
        <tr class="r0">
          <td>
            <a href="?view_tree"          class = "btn btn-default" ><i class="fas fa-tree"></i>  Дерево всех категорий</a>
            <a href="?full_tree"          class = "btn btn-default" ><i class="fas fa-server"></i> Полный каталог</a>';
            #<a href="?uploadPrice"        class = "btn btn-default" ><i class="fas fa-sync"></i> Обновить каталог</a>
            #<a href="?upload_img"         class = "btn btn-default" ><i class="fas fa-upload"></i> Загрузить изображения</a>
            #<a href="?update_information" class = "btn btn-default" ><i class="fas fa-exchange-alt"></i> Обновление цен</a>
    $output .=  '
            <a href="?xls_id='.$c_id.'"   class = "btn btn-default" ><i class="fas fa-download"></i> Скачть ВЕСЬ катлог в xls</a>
          </td>
        </tr>
      </table>';
    
    return $output;
  }
  
  
  function show_table_header_rows(){
    $output = '
          <tr class="tth nodrop nodrag">
          	<td style="width: 55px;">#</td>
      		  <td style="width: 50px;">Скрыть</td>
            <td style="width: 50px;">На главной</td>
            <td style="width: 60px;">Картинка</td>
      		  <td>Название</td>
      		  <td>Ед. изм.</td>
            <td style="width: 140px">Цена</td>
            <td style="width: 80px">Действие</td>
          </tr>
    <style>
      input[type="text"].price_input{
      background-color: #88d888; font-weight: bold;  font-size: 20px;   text-align: right;
    }
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
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>
            
            <td>'.$portion.' '.$this->units[$units_id].'</td>
            <td><input class="form-control price_input" type="text" name="price" value="'.$price.'"></td>
            
            <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).' 
            </td>
  			  </tr>';
    
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
  
  function full_tree(){
    
    if( isset($_SESSION[$this->carusel_name]['c_id']) && $_SESSION[$this->carusel_name]['c_id'] ){
      $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
      $this->bread = array();
      $this->show_bread_crumbs($item_cat_id);
      $this->admin->setForName('bread', $this->getForName('bread')); 
    }
    $this->title = 'Полный каталог'; 
    $this->header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    $output .=  '
    <table class="table table-condensed">
      <tr class="r0">
        <td>
          <a href="?view_tree"          class = "btn btn-default" ><i class="fas fa-tree"></i> Дерево всех категорий</a>
          <a href="?full_tree"          class = "btn btn-default" ><i class="fas fa-server"></i> Полный каталог</a>
        </td>
      </tr>
    </table>';
    $output .= '
		  <script>
		  $(function(){
			  $("#article").keyup(function(){
			    var q=$(this).val();
			    $.post("'.$this->carusel_name.'.php?ajx&act=search", {que:q}).done(function( data ) 
				  {
				  	$("#exists").html(data);
				  });
		    });
		  });
		  </script>

      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-7 col-lg-8">
            <div class="row">
              <div class="col-xs-12">';
	  $output .= $this->show_entrie_catalog();
    $output .= '
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-5 col-lg-4">
            <div class="row">
              
                <div class="box box-primary box-solid">
                  <div class="box-header with-border">
                    <h3 class="box-title">Поиск</h3>

                    <div class="box-tools pull-right">
                      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>                    <!-- /.box-tools -->
                  </div>                      <!-- /.box-header -->
                  <div class="box-body">
                    <div class="form-group">
                      <input type="text" class="text form-control" name="article" id="article" placeholder="Запрос..."> 
                    </div>
                  </div>                      <!-- /.box-body -->
                </div>
              
                
            </div>
            
            <div class="row">
              <div class="" id="exists"></div>
            </div>
          </div>
        </div>
      </div>
      
      <script>
      $(function(){
        $(".posit").popover({
	        html:true,
	        trigger:"hover",
	        placement:"top"
        })
      });
      </script>
      
      <style>
      .listingb li
      {
	      padding: 5px 0 0 0;
      }
      .listingb{
        padding:5px 0;
        margin: 0 0 0 15px;
      }
      .listingb a{
        color: #265c88;
      }
      .container{
        width: 100%;
      }
      .listingb .label{
        color: #000000;
      }
      
      </style>
    ';
    
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
  
  function xls_cat_slide( $id ){ #pri('xls_cat_slide');
    $output = '';
    
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
    
    if(!isset($this->brand)){
      $brands = db::select('*', DB_PFX.'brand' );
      foreach( $brands as $brand){
        $this->brand[$brand['id']] = $brand;
      } #pri($this->brand);
    }
    
    if(!isset($this->availability)){
      $availabilitys = db::select('*', DB_PFX.'availability' );
      foreach( $availabilitys as $availability){
        $this->availability[$availability['id']] = $availability;
      } #pri($this->availability);
    }
    
    if(!isset($this->units)){
      $units = db::select('*', DB_PFX.'units' );
      foreach( $units as $unit){
        $this->units[$unit['id']] = $unit;
      } #pri($this->units);
    }
    if(!isset($this->cat_numbers)){
      $this->get_cat_numbers(0);
    } #pri($this->cat_numbers);
    
    
    
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
      header("Content-Disposition: attachment;filename=lampa66_".date('d.m.Y H:i:s').".xls"); 
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
      $article = $item['article'];
      if( !$article ) $article = $item['id'];
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
      
      // Обновить каталог
      elseif(isset($_GET['uploadPrice'])){
        $output .= $carisel->upload_price();
      }
      elseif(isset($_GET['upload_img'])){ // Загрузить кртинки
        $output .= $this->viewFormUploadZip(); 
  			if ( $_FILES ) {
  				$output .= $this->uploadZipImg();
  			}
      }elseif(isset($_GET['update_information'])){ // Обновить цены
        $output .= $this->updateInformation(); 
        
      }elseif(isset($_GET["xls_id"])){
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