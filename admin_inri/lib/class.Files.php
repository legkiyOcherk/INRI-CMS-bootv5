<?php
require_once __DIR__."/class.BaseCarusel.php"; 
require_once __DIR__."/formvalidator.php";// Валидатор

/**
* Предназначен для ajax подключения возможности добавления файлов к любому Item 
* Ключевые данные `modele` и `module_id`
*/

class Files extends BaseCarusel{
 
  var $img_ideal_width = 960;
  var $img_ideal_height = 500;
  var $header = 'Файлы';
  
  var $date_arr = array(
    'title' => 'Название',
    'longtxt1' => 'Описание',
    'module' => 'Название модуля (таблицы бд к которой привязан URL)',
    'module_id' => 'Название модуля id модуля',
    'img_alt' => 'Alt изображение',
    'img_title' => 'Title изображение',
  );
  var $pdo;
  
  var $url_item = null; # Генерация url
  var $is_pager = true; # Отображать пэйджер
  
  var $carusel_name;
  var $sqlTable;
  var $pager = array(
        'perPage' => 10,
        'page' => 1,
        'url' => '',
        'items_per_page' => array( 10, 50, 100, 500, 1000, 5000)
      );
  var $filter_field = array('title');
  
  
  # конструктор
  function __construct ($carusel_name = null, $date_arr = null, $genSqlTable = false, $genImgDir = false, $pager = null) {
     
    //Для пересоздания раскоментить
    #$_SESSION[$carusel_name]['is_table'] = 0;
    #$_SESSION[$carusel_name]['img_dir'] = 0;
    //END Для пересоздания раскоментить
    
    $this->pdo = db_open();
    $this->carusel_name = "all_files";
    
    if($carusel_name){
      $this->carusel_name = $carusel_name;
    }else{
      $this->carusel_name = "all_files";
    }
    
    $this->sqlTable = $this->prefix.$this->carusel_name;  
    
    if($date_arr) $this->date_arr = $date_arr;
  
        
    //Если нет таблицы в базе данных, то создаем ее
    if($genSqlTable){
      
      $create_table = 0;
      if(!isset($_SESSION[$carusel_name]['is_table'])){
        $create_table = 1;
      }elseif($_SESSION[$carusel_name]['is_table'] != 1){
        $create_table = 1;
      }
      
      if($create_table){
          $s = 'SHOW TABLES LIKE "'.$this->sqlTable.'"';  
          $q = $this->pdo->query($s);
          if(!$q->rowCount()){
            // create_sql_table - Создавать таблицу если ее нет
            if($this->create_sql_table()) $_SESSION[$carusel_name]['is_table'] = 1;
            
          }else{
            $_SESSION[$carusel_name]['is_table'] = 1;
          }
      }
    }
    
    //Если нет папок для картинок создаем их
    if($genImgDir){
      
      $create_img_dir = 0;
      if(!isset($_SESSION[$carusel_name]['img_dir'])){
        $create_img_dir = 1;
      }elseif($_SESSION[$carusel_name]['img_dir'] != 1){
        $create_img_dir = 1;
      }
      
      if($create_img_dir){
        // create_img_dir - Создавать дерево директорий если его нет
        $this->create_img_dir();
        $_SESSION[$carusel_name]['img_dir'] = 1;
      }
    }
    //Если нужен пэйджер
    if($pager){
      if(is_array($pager)) $this->pager = $pager;
      $this->setPagerParamers();
    }
  }
  
  /* Инициалицация */
    
  function create_sql_table(){
    
    $arr_varchar_255_not_null = array(         // varchar(255) NOT NULL
      'article' );
    
    $arr_varchar_10_default_null = array(      // varchar(10) DEFAULT NULL
      'date', 'datetime' );
    
    $arr_text = array(                         // text
      'longtxt1', 'longtxt2', 'longtxt3', 'orm_search' );
    
    $arr_tinyint_1_default_null = array(       // tinyint(1) DEFAULT NULL      
      'fl1', 'fl2', 'fl3'  );
    
    $arr_int_11_default_null = array(          // int(11) DEFAULT NULL
      'price', 'userPhone');
    
    $arr_ignore = array(                       // ignore field
      'title', 'ignore', 'module', 'module_id', 'file');
      
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$this->sqlTable.'` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `module` varchar(255) NOT NULL,
      `module_id` int(11) NOT NULL,
      `img` varchar(255) NOT NULL,
      `file` varchar(255) NOT NULL,
      ';
    
    foreach($this->date_arr as $key=>$val){
      if(in_array($key, $arr_varchar_255_not_null)){
        $sql .= '`'.$key.'` varchar(255) NOT NULL,
      ';  
      }elseif(in_array($key, $arr_varchar_10_default_null)){
        $sql .= '`'.$key.'` varchar(10) DEFAULT NULL,
      ';
      }elseif(in_array($key, $arr_text)){
        $sql .= '`'.$key.'` text,
      ';
      }elseif(in_array($key, $arr_tinyint_1_default_null)){
        $sql .= '`'.$key.'` tinyint(1) DEFAULT NULL,
      ';
      }elseif(in_array($key, $arr_int_11_default_null)){
        $sql .= '`'.$key.'`  int(11) DEFAULT NULL,
      ';
      }elseif(in_array($key, $arr_ignore)){
        continue;
      }else{
        $sql .= '`'.$key.'`  varchar(255) DEFAULT NULL,
      ';
      }
      
    }
      
    $sql .= '
      `hide` tinyint(1) NOT NULL DEFAULT "0",
      `ord` int(11) NOT NULL DEFAULT "0",
      `module_ord` int(11) NOT NULL DEFAULT "0",
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ';
    
    #echo "<pre>sql = $sql<br></pre>";
    $q = $this->pdo->query($sql);
    if($q){
      echo "База данных ".$this->sqlTable." Успешно созданна";
      return true;
    }else{
      echo "Ошибка при создании базы данных ".$this->sqlTable." : ".$q;
      echo "<pre>".$sql."</pre>";
      exit;
      return false;
    }
  }
  
  function create_img_dir(){
    
    parent::create_img_dir();
    
    if (!is_dir("../images/".$this->carusel_name."/files")){
      mkdir("../images/".$this->carusel_name."/files");
      chmod ("../images/".$this->carusel_name."/files", 0755);
    }
  }
  
  // End Инициалицация
  
  // Валидация введенных данных

  // End Валидация введенных данных
  
  /* All Method */
  
  function setIsUrl($is_url) {
    require_once(NX_PATH.IA_URL.'/lib/class.Url.php');
    if($is_url) $this->url_item = new Url('url');
  }
  
  // END All Method
  
  static function static_get_img_link ($src_path, $src_name, $new_dir, $width, $height = null, $rgb = 0xFFFFFF, $quality = 100 ){
    #echo $_SERVER['PHP_SELF']."  dir = ".$src_path.'/'.$new_dir;
    if (!file_exists($new_dir)) 
      mkdir($new_dir);
    
    /*$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		
		$last_symbols = substr($src_name, -5);
		if (strpos($last_symbols, $format) === false) {
			echo '';
     
		}*/
    
    if (!file_exists($new_dir.'/'.$src_name)) {
      $Image = Image::Factory();
      $path = $Image->resize($src_path.'/'.$src_name, $new_dir.'/'.$src_name, $width, $height, $rgb, $quality);
    }

    return '/'.$new_dir.'/'.$src_name;            
  }
  
  /* AJAX function */
  
  function sort_files(){
    foreach ($_POST["itSort"] as $key=>$val)
	  {
		  $order=$key*10;
		  $this->pdo->query ("UPDATE `".$this->prefix.$this->carusel_name."` SET `module_ord`=$order WHERE `id`=$val");
       
	  }
  }
  
  function ajax_delete_slide($id){
    $output = "";
   	if ($id){
  		$this->delete_picture($id);
  		if($q = $this->pdo->query("DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'")){
        return 'ok';
      }else{
        return $q;
      }
    }  
        
    return 'Нет id';
  }
  
  function ajax_new_file_name(){
    if(!isset($_POST['id'])) return 'Нет id';
    if(!($_POST['id'])) return 'Нет id';
    if(!isset($_POST['name'])) return 'Нет name';
    
    $id = intval($_POST['id']);
    $name = substr(htmlspecialchars(trim($_POST['name'])), 0, 255);
    $s = "UPDATE  `".$this->prefix.$this->carusel_name."` SET  `title` =  '$name' WHERE  `id` = $id";
    if($q = $this->pdo->query($s)){
      return 'ok';
    }else{
      return $q;
    }
  }
  
  // END AJAX function
  
  /* BACKEND function */
  
  function getFilterTableSelect(&$filter_field_name){
    $output = '';
    $data_filter_arr = $this->filter_field; # array("title", "date", "longtxt1", "longtxt2");  
    $ftr_arr = $this->date_arr;
    $ftr_arr['file'] = 'Файл';
    
    foreach($ftr_arr as $k => $v){
      if(in_array($k, $data_filter_arr)){
        ($k == $filter_field_name) ? $selected = 'selected' : $selected = '';
        $output .= '    
            <option value = "'.$k.'" '.$selected.' >'.$v.'</option>';
      }  
    }
    return $output;
  }

  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          	<th style="width: 40px;">#</th>
      		  <th style="width: 50px;">Скрыть</th>
            <th style="width: 60px;">Картинка</th>
      		  <th>Название</th>
            <th>Скачать файл</th>
            <th>Модуль</th>
            <th>Модуль ID</th>
            <th>Ссылка</th>
      		  <th style="width: 80px">Действие</th>
          </tr>';
    
    return $output;
  }
  
  function show_table_rows($item){
    $output = '';
    extract($item);
    
    ($hide) ? $star_val = "glyphicon glyphicon-star" : $star_val = "glyphicon glyphicon-star-empty";
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              <input type="checkbox" class="group_checkbox" name="group_item[]" value="'.$id.'"> '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
          </td>
            
            <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$star_val.'" id="hide_'.$id.'"></div></td>  
            <td class = "zoomImg_box" style="">';
            
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
            <td style="text-align: left;">';
    if($file){
      $output .= '
              <a href="/images/'.$this->carusel_name.'/files/'.$file.'" title="Скачать файл" target = "_blank">'.$file.'</a>';
    }
    $output .= '
            </td>
            <td style="text-align: left;">
              '.$module.'
            </td>
            <td style="text-align: left;">
              '.$module_id.'
            </td>
            <td style="text-align: left;">';
    if( $source_link = $this->getAdminSourceLink($module, $module_id) )
      $output .= '
              <a href="'.$source_link.'" title="Источник">Материал</a>';  
    $output .= '
            </td>
            
        	  <td style="" class="img-act">
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" 
                  class = "btn btn-info btn-sm"
                  title = "Редактировать">
                <i class="fas fa-pencil-alt"></i>
              </a>
              
              <span >
              <span class="btn btn-danger btn-sm" 
                    title="удалить" 
                    onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
                <i class="far fa-trash-alt"></i>
              </span>
            </td>
  			  </tr>
  			  </tr>';
    
    return $output;
  }
    
  function show_table(){
    $output = "";
    
    $output .= $this->getFormStyleAndScript(); 
    
    $header = '<h1>'.$this->header.'</h1>';
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    #$this->bread[ucfirst_utf8($this->header)] = $this->carusel_name.'.php'; 
    $this->title  = ucfirst_utf8($this->header);
    
    $s = "
      SELECT COUNT( * ) AS count
      FROM `".$this->prefix.$this->carusel_name."`
    ";
    
    $q = $this->pdo->query($s);
    $r = $q->fetch();
    $count_items = $r['count'];

    $s_filter = $s_sorting = $s_limit = $strPager = $groupOperationsCont = '';
    $s_order = " ORDER BY `ord` ASC ";
    
    if(!$count_items) $output .= "<p>Раздел пуст</p>";
    if($this->is_filter &&  $count_items) $output .= $this->getFilterTable($s_filter);
    if( $count_items) $groupOperationsCont = $this->getGroupOperations();
    if($this->is_pager && $count_items) $strPager = $this->getPager( $count_items, $s_limit);
   
    $output .= $strPager;
    
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name."`
      $s_filter
      $s_sorting
      $s_order
      $s_limit
    ";
    #echo $s;
    
    $output .= '
      <form 
        method="post" 
        action="'.$this->carusel_name.'.php" 
        id="sortSlide"
        class="table-responsive"
      >
        <input type="hidden" name="slideid" value="1">';
        
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        $output .= '
    	    <table id="sortabler" class="table sortab table-sm table-striped ">
            <thead>'.$this->show_table_header_rows().'</thead>
            <tbody>';
        while($item = $q->fetch()){
          $output .= $this->show_table_rows($item);
        }
        $output .= '
            </tbody>
          </table>';
      }
    }
    
    $output .= $groupOperationsCont;
    $output .= '
    <br>
  	<center><a class="btn btn-success" href="?adds" id="submit">Добавить</a></center>
    </form>';
    
    if($this->is_pager) $output .= $strPager;
    
    return $output;
    
  }
  
  function show_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
        
      if($url){
        $tmp = '<a class="btn btn-info float-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }  
      
      $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']));

    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt2"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date")))  $type = 'date';
      if( in_array($key, array("title", "module", "module_id", "img_alt", "img_title" )))  $type = 'text';
      
      if ($key == 'file') continue;
      
      // Отступы SEO
      if($key == 'seo_h1'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('SEO');
        $is_open_panel_div = true;   
      }
      
      if($key == 'img_alt') {
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('Атрибуты основого изображения');
        $is_open_panel_div = true;         
      }
      
      if($key == 'module') {
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('Принадлежность');
        $is_open_panel_div = true;         
      }
      
      if( in_array( $key, $this->checkbox_array) ){
        $output .= $this->show_iCheck('col_'.$key, $item, $key, $val);
        continue;  
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
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="">'
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
      $output .= '
        </div>
      </div>
      ';
    }
    
    $output .= $this->getFormPicture($id, $item);
    
    $output .= $this->getFormFile($id, $item);
      

    
    return $output;
    
  }
  
  function getFormFile($id, $item){
    $output = '';
    
    $output .='
        <div class="card"> 
          <div class="card-header">Файл</div> 
          <div class="card-body">';
    $is_file = false;
    if(isset($item["file"])){
      if ($item["file"] !== ''){
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $file_path = '/images/'.$this->carusel_name.'/files/'.$item["file"];
        $output .= $this->show_form_row( 
              ' Загружено :', 
              ' '.$item["file"].'<br>
                <a target = "_blank" href="'.$protocol.$_SERVER["HTTP_HOST"].$file_path.'">Ссылка</a>
                <span id = "file_path " >'.$protocol.$_SERVER["HTTP_HOST"].$file_path.'</span> '
        );
        $output .= $this->show_form_row( 
          'Действия', 
          ' <a class = "btn btn-danger" href="'.IA_URL.$this->carusel_name.'.php?delete_file=1&id='.$item["id"].'" 
               onClick="javascript: if (confirm('."'Удалить файл?')) { return true;} else { return false;}\"".'>
               <span class = "glyphicon glyphicon-remove"></span> Удалить</a> '
        );
        $is_file = true;      
      }
    }
    if(!$is_file){
      $output .= $this->show_form_row( 
            ' Загрузить файл :', 
            ' <INPUT type="file" name="file_item" value="" class="form-control"> '
      );
    }

    
    $output .= '
      </div>
    </div>';
    
    return $output;
  }
  
  function create_slide(){
    $output = "";
    
    if (isset($_POST['title'])){
      
      $sql_names = ''; $sql_vals = '';      
      
      $this->getCreateSlide_SqlNames_SqlVals($sql_names, $sql_vals);
      
      if(
        $this->pdo->query(
          $s = "
            INSERT INTO `".$this->prefix.$this->carusel_name."` ($sql_names) 
            VALUES                                              ($sql_vals)
          "
        )
      ){
        #echo "s = $s";
      }else{
        echo "s = $s";
        echo "Произошла ошибка при СОЗДАНИИ записи в бд функсия create_slide()";
        exit;
      }
      
			#$id = mysql_insert_id();
      $id = $this->pdo->lastInsertId();

      if ($name = $this->load_picture($id)){
  		  $this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
  	  }
      
      //Загрузка файла
      if ($file_name = $this->load_file()){
  			$this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `file` = '$file_name' WHERE `id` = '$id'");
  		}
      
      $_GET["edits"] = $id;
      $output .= $this->edit_slide($id);
    }else{
      $output .= $this->add_slide($_POST);
    }
    
    return $output;
  }
  
  function update_slide($id){
    $output = "";
    
		#$id = intval($_GET["updates"]);

    if ($name = $this->load_picture($id)){
			$this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
		}
		
    //Загрузка файла
    if ($file_name = $this->load_file()){
			$this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `file` = '$file_name' WHERE `id` = '$id'");
		}
     
    $sql_vals = $this->getUpdateSlide_SqlVals();
     
    if($this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET $sql_vals WHERE `id` = '$id'")){
      
    }else{
      echo "Произошла ошибка при ОБНОВЛЕНИИ записи в бд функсия update_slide()";
      exit;
    }
		
		$output .= $this->edit_slide($id);
    
    return $output;
  }
  
  function load_file(){
    $file_uploaded = FALSE;
		
    if (isset($_FILES["file_item"])){
		  $filename = $_FILES["file_item"]["name"];
			$tmpname =  $_FILES["file_item"]["tmp_name"];
			$exts = explode('.', $filename);
		
    	if (count($exts)){
				$new_filename = 'file_'.$id.'.'.$exts[count($exts)-1];
			}else{
				$new_filename = 'file_'.$id;
			}
		
    	if (is_uploaded_file($tmpname)){
		
      	if ($_FILES['file_item']['name']) {
  				$time=time();
  				$e=explode(".",$_FILES['file_item']['name']);
  				$type=end($e);
  				$target_filename="../images/".$this->carusel_name."/files/$time.".$type;
  				$new_filename="../images/".$this->carusel_name."/temp/$time.".$type;
          $to="../images/".$this->carusel_name."/slide/$time.".$type;
  				$name=$time.".".$type;
  				move_uploaded_file($_FILES['file_item']['tmp_name'], $new_filename);
  				copy ($new_filename,$target_filename);
  				unlink($new_filename);
  				$file_uploaded = TRUE;
  			}
	  	}
	  }
    if($file_uploaded){
      return $name;
    }else{
      return $file_uploaded;
    }
  }
  
  function delete_file($id){
    $output = "";
    
	  $c_path="../images/".$this->carusel_name."/files/";
		$string="select `file` from `".$this->prefix.$this->carusel_name."` where `id`=$id";	
    $q = $this->pdo->query($string);
    $r = $q->fetch();
    $filename = $r['file'];
		if (is_file($c_path.$filename)){
			unlink($c_path.$filename);
		}
    
    #$this->deleteAllValidation($filename);
		$string="update `".$this->prefix.$this->carusel_name."` set `file`='' where `id`='$id'";
		$this->pdo->query($string);
		
    $output = $this->edit_slide($id);
    
    return $output;
  }
  
  
  
  
  function delete_picture_old($id){
    $output = "";
    
	  # удаляем картинку в категории
	  $c_path="../images/".$this->carusel_name."/orig/";
	  #$id=intval($_GET[id]);
	  $string="select img from `".$this->prefix.$this->carusel_name."` where `id`=$id";	
		$q = $this->pdo->query($string);
    $r = $q->fetch();
    $pic_filename = $r['img'];
		if (is_file($c_path.$pic_filename)){
			unlink($c_path.$pic_filename);
		}
    $c_path="../images/".$this->carusel_name."/slide/";
    if (is_file($c_path.$pic_filename)){
			unlink($c_path.$pic_filename);
		}
    $this->deleteAllValidation($pic_filename);
		$string="update `".$this->prefix.$this->carusel_name."` set img='' where `id`='$id'";
		$this->pdo->query($string);
		#print	"<B>Картинка $pic_filename удалена</B><BR>";
    
    // Удаление фйла
    $string="select file from `".$this->prefix.$this->carusel_name."` where `id`=$id";	
		$q = $this->pdo->query($string);
    $r = $q->fetch();
    $file_filename = $r['file'];
    $c_path="../images/".$this->carusel_name."/files/";
    
    if (is_file($c_path.$file_filename)){
			unlink($c_path.$file_filename);
		}
    $string="update `".$this->prefix.$this->carusel_name."` set file='' where `id`='$id'";
    
		$this->pdo->query($string);
    
    $output = $this->edit_slide($id);
    
    return $output;
  }
  
  function delete_slide($id, $view = 'show_table'){
    $output = "";
    if (isset($id) && $id){
   	  #if (isset($_GET["deletes"])){
  		#$id = intval($_GET["deletes"]);
      $this->delete_picture($id);
      $this->delete_file($id);
  		$this->pdo->query("DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'");
    }  
    switch($view){
      case 'show_table':
        header('Location: '.IA_URL.$this->carusel_name.'.php');
        #$output .= $this->show_table();
        break;
        
      case 'ajax':
        $output .= "ok";
        break;
    }
    
    return $output;
  }
  
  function deleteImageForModuleAndModuleId($module, $module_id){
    $s = "
    SELECT * 
    FROM `".$this->prefix.$this->carusel_name."`
    WHERE `module` = '$module'
    AND `module_id` = '$module_id' 
    ";
    #echo "s = $s";
    #die();
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        while($r = $q->fetch()){
          $id = $r['id'];
          #$this->delete_picture($id);
          $this->delete_picture($id);
  		    $this->pdo->query("DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'");
        }
      }
    }
  }
  
  // END BACKEND function
  
  /* Подключение модуля */
    
  function showFilesForModuleModule_id($module, $module_id){
    $output = '';
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name."`
      WHERE
        `module` = '$module' 
        AND `module_id` = $module_id
      ORDER BY `module_ord`
    ";
    //$output .= "s = $s";
    
    $q = $this->pdo->query($s);
    if($q->rowCount()){
      
    $output .= '

    
      <form method="post" action="'.$this->carusel_name.'.php" id="sortSlide">
        <input type="hidden" name="slideid" value="1">
          <div class = "th">
            <div style="width: 5%;">#</div>
        		<div style="width: 10%;">Скрыть</div>
        		<div style="width: 60%;">Название</div>
        		<div style="width: 20%; text-align: right;">Удалить</div>
          </div>
  	    <ul id="sortTable" class=" sortTab ">
 
      ';
      
      while ($item = $q->fetch()){
        extract($item);
        ($hide) ? $star_val = "glyphicon glyphicon-star" : $star_val = "glyphicon glyphicon-star-empty"; 
        
        $output .= '
          <li class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <div style="width: 5%;">'.$id.'<input type="hidden" value="'.$id.'" name="itSort[]"></div>
            
            <div style="width: 10%;" class="img-act">
              <div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$star_val.'" id="hide_'.$id.'"></div>
            </div>
        	  
            <div style="width: 15%;">
        ';
        if($img){
          $output .= '
            <div class="zoomImg"><img style="width:50px" src="../images/'.$this->carusel_name.'/slide/'.$img.'"></div>  
          ';
        }else if($color){
          $output .= '
            <div class="zoomImg" style = "background-color: '.$color.'">
          ';
        }
        $output .= '
            </div>
        	  
            <div style="text-align: left; width: 35%;">
              <input type = "text" class="file_name" value = "'.$title.'" data-id = "'.$id.'">
            </div>
            
            <div style="text-align: left; width: 10%;">
        ';
        if($file){
          $output .= '<a href="/images/'.$this->carusel_name.'/files/'.$file.'" title="Ссылка на файл" target = "_blank">Ссылка '.$file.'</a>';
        }else{
          $output .= 'Нет';
        }
              
        $output .= '
            </div>
        	  
      	';
        /*$output .= '
            <td style="width: 60px; text-align: left; color:#000;" nowrap="" class="id">
            '.$price.'
              <!--<input type="text" class="span1" name="prices[1]" value="'.$price.'">-->
            </td>
        ';*/
        $output .= '
        	  <div style="width: 20%; text-align: right;" class="img-act">
              <a href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" target = "_blank" title="Редактировать запись">
                <img src="../'.IA_URL.'/images/icons/b_props.png" width="16" height="16" border="0">
              </a>&nbsp;
              
              <div class="delete_file" title="удалить" name = "'.$id.'" ><img src="../'.IA_URL.'/images/icons/b_drop.png" width="16" height="16" border="0"></div>

            </div>
  			  </li>
        ';
        
      }
      
      $output .= '
        </ul>
      </form>
      ';
    

    }


    return $output;
  }
 
  function showFilesForm($module, $module_id){
    if(!$module) return 'class Images function showFilesForm параметр module пустой ';
    if(!$module_id) return 'class Images function showFilesForm параметр module_id пустой ';
   
    $output = "";
    
    /*$s = "
    SELECT *
    FROM ".$this->carusel_name."
    ";*/
    
    #$items = db::select("*", $this->prefix.$this->carusel_name, null, "`ord` ASC ",  null, null, 0);
    #echo "$items";
    
    
    $output .= '
      <style>
        .sortTab{     
          position: reletive;
          margin : 0;
          padding-left: 0;
        }
        .sortTab > li {
          display: block;
          width: 100%;
          border-bottom: 1px solid #dddddd;
          border-top: 1px solid #dddddd;
          margin-top: -1px;
          clear: both;
          padding: 10px 0 10px 0; 
          overflow: hidden;
        }
        .sortTab > li > div{
          display: inline-block;
          height: 50px;
          float: left;
        }
        .th{
          display: block;
          width: 100%;
          position: reletive;
          padding-bottom: 20px;
        }
        .th > div{
          display: inline-block;
        }
        .delete_file{
          font-size: 12px;
          top: 0px;
          right: 0px;
          color: red;
          cursor: pointer;
          display: inline-block;
        }
        input[type="text"].file_name{
           font-size: 12px;
          bottom: -10px;
          left: 0px;
          color: blue;
          width: 90%;
          cursor: text;
          border-radius: 0px;
        }
        
        span.ajx { cursor: pointer; border-style: dashed; border-width: 0 0 1px 0; }
        span.ajx:hover { border-style: solid; }
        img.ajx { cursor: pointer; }
        span.del { color: #f00; border-color: #f00; }
       .star_check { /*width: 25px; height: 25px;*/ font-size: 25px; color: #f0ad4e; cursor: pointer; margin: 0 auto; }
        .for_img { position: relative; }
        div.img_preview { display: none; position: absolute; z-index: 99; border: 1px #ccc dotted; }
        .img-act { text-align: center; }
       
      </style>
    <script type="text/javascript" src="js/tablednd.js"></script>
    <script src="'.WA_PATH.'js/jquery.wallform.js"></script>
   
    <script>
    $(window).load(function() {
        /*$( "#sortTable" ).sortable({
            
          stop: function( event, ui ) {
            $(".sortTab").tableDnD({
            onDrop: function() {
              $.post( "'.$this->carusel_name.'.php?ajx&act=sort_item", $( "#sortSlide" ).serialize());
              }
          });
          }
        }); 
        $( "#sortTable" ).disableSelection();
        */
    });
    
    var in_process = false;
    $(document).ready(function() {
      
      //Сохранить новое название
      $(".file_name", this).keyup(function(e) {
        //if(e.which == 13) {
          var id = $(this).data("id");
          var name = $(this).val();
          //alert("You pressed enter!"+id+" "+name);
          
			    $.post("'.IA_URL.$this->carusel_name.'.php?ajx&act=new_file_name", {id:id, name:name}).done(function( data ) {
            $("#exists").html(data);
				  });
        //}
        
      
      });
      
      $(".file_name", this).click(function(e) {
        $(this).focus();
        //alert("focus");
      });
      // END Сохранить новое название
      
           
      // Initialise the first table (as before)
      $( "#sortTable" ).sortable({
        stop: function( event, ui ) {
          $.post( "'.IA_URL.$this->carusel_name.'.php?ajx&act=sort_files&module='.$module.'&module_id='.$module_id.'", $( "#sortSlide" ).serialize());
        }
      });
 
      
      
      $("#filesAddBtn").die("click").live("change", function()			{ 
      
		    
      
        //$("#preview2").html("");
        var d = new Date();
        var n = d.getTime();
        if(in_process == false){
          
  			  //alert(n+" "+in_process);
          in_process = true;
          $("#preview2").html("");
  				$("#fileForm").ajaxForm({
            target: "#preview2", 
  				  
            beforeSubmit:function(){ 
  						console.log("ttest");
  					  $("#fileloadstatus").show();
  					   $("#fileloadbutton").hide();
  					}, 
  					
            success:function(){ 
  				    console.log("test");
  					  $("#fileloadstatus").hide();
  					  $("#fileloadbutton").show();   
              
              // Дублируем для работы после ajax - загрузки
              $( "#sortTable" ).sortable({
                stop: function( event, ui ) {
                  $.post( "'.IA_URL.$this->carusel_name.'.php?ajx&act=sort_files&module='.$module.'&module_id='.$module_id.'", $( "#sortSlide" ).serialize());
                }
              });
              
              //Сохранить новое название
              $(".file_name", this).keyup(function(e) {
                //if(e.which == 13) {
                  var id = $(this).data("id");
                  var name = $(this).val();
                  //alert("You pressed enter!"+id+" "+name);
                  
        			    $.post("'.IA_URL.$this->carusel_name.'.php?ajx&act=new_file_name", {id:id, name:name}).done(function( data ) {
                    $("#exists").html(data);
        				  });
                //}
                
              
              });
              
              $(".file_name", this).click(function(e) {
                $(this).focus();
                //alert("focus");
              });
              // END Сохранить новое название
              
              in_process = false;
            },
           
           	error:function(){ 
  				   	console.log("xtest");
  					  $("#fileloadstatus").hide();
  					  $("#fileloadbutton").show();
              in_process = false;
  					} 
          }).submit();
				}
		
			});
       
       
    });
    
    
    </script>

    ';
    $output .= '

    ';
    //$output .= self::showFilesForModuleModule_id($module, $module_id);

    
      
  	#<center><a class="btn btn-success" href="?adds">Добавить</a></center>
    $output .= <<<HTML
     
    
    
	<script type="text/javascript">
    //Увеличение картинки zoomImg при наведении
    $(document).on("mouseover", ".zoomImg", function(){
          $(this).children("img").stop().animate({width:"200px"}, 400);
        }
    );
    
    $(document).on("mouseout", ".zoomImg", function(){
          $(this).children("img").stop().animate({width:"50px"}, 400); 
        }
    );
    
    function star_check(id, field) {
HTML;
    $output .= '
		$.post(\''.$this->carusel_name.'.php?ajx&act=star_check\', {id:id, field:field}, function(data) {
    ';
    $output .= '
			if (data == 1) {
				$("#"+field+"_"+id).removeClass("glyphicon glyphicon-star-empty")
				$("#"+field+"_"+id).addClass("glyphicon glyphicon-star")
			} else {
				$("#"+field+"_"+id).removeClass("glyphicon glyphicon-star")
				$("#"+field+"_"+id).addClass("glyphicon glyphicon-star-empty")
			}
		})
	}

  $(".delete_file").live("click", function()			{
    if (1/*confirm("Удалить изображение???")*/) {
    var id = $(this).attr("name");
    $(this).parent().parent().remove();
		$.post("'.IA_URL.$this->carusel_name.'.php?ajx&act=ajaxDeleteFile", {ajax_id: id}, 
      function(data) {
		    if (data == "ok") {
          //alert ("Ура ajax_id ="+id);
        }else{
          alert ("Возникла ошибка при удалении(" + data);
        }
		  })
	  }
  });
  
  //--------------
  /*
	function delete_item(id, title) {
		if (confirm("Удалить элемент "+title+"?")) {
			$.post("news_items.php.php?ajx&act=delete_item", {id: id}, function(data) {
				if (data == "ok") {
					$("#tr_"+id).fadeOut("slow")
				}
			})
		}
	}
	function preview_img(id, url) {
		$("#img_preview_"+id).show()
		$("#img_preview_"+id).append("<img src="+url+" />")
	}
	

	function sort_items(id, side) {
		$.post("catalogue.php?ajx&act=sort_items", {id: id, side: side}, function(data) {
			$("#catalogue_ajx").html(data)
		})
	}*/

	</script>

  ';
  $output .= '
  
  <form id="fileForm" method="post" enctype="multipart/form-data" 
    action="'.IA_URL.$this->carusel_name.'.php?ajx&act=ajaxFilesUpload&module='.$module.'&module_id='.$module_id.'" 
  style="clear:both">
  ';
  $output .= '
  <br>
  <h4>Загрузить дополнительные файлы</h4> 
  <div id="fileloadstatus" style="display:none"><img src= "'.IA_URL.'css/img/loader.gif" alt="Загружается...."/></div>
  <div id="fileloadbutton">
  <input type="file" name="filesArr[]" id="filesAddBtn" multiple="true" />
  </div>
  </form>
  <div id="preview2">
  ';
  $output .= self::showFilesForModuleModule_id($module, $module_id);
  $output .='
  </div>
  <br><br>
  ';
  return $output;
  }
  
  function getExtension($str){
    $i = strrpos($str,".");
    if (!$i) { return ""; }
    $l = strlen($str) - $i;
    $ext = substr($str,$i+1,$l);
    
    return $ext;
  }
  
  function ajaxFilesUpload($module, $module_id){
    define ("MAX_SIZE","9000");
    
    $output = ''; $error = '';
    
    $valid_formats = array("jpg", "png", "gif", "bmp","jpeg");
    if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") 
    {
	    $uploaddir = "../images/".$this->carusel_name."/uploads/"; //a directory inside
      
      $i=0;
      foreach ($_FILES['filesArr']['name'] as $name => $value)
      {
        $filename = stripslashes($_FILES['filesArr']['name'][$name]);
        
        $size=filesize($_FILES['filesArr']['tmp_name'][$name]);
        //get the extension of the file in a lower case format
        $ext = self::getExtension($filename);
        $type = strtolower($ext);
     	
        //if(in_array($type,$valid_formats))
        if(1)
        {
	        if ($size < (MAX_SIZE*1024))
	        {
		        $image_name=time().$filename;
		        #$output .= "<img src='".$uploaddir.$image_name."' class='imgList'>";
		        #$newname=$uploaddir.$image_name;
            $time=time().'_'.$i;
            $fileItem_filename="../images/".$this->carusel_name."/files/$time.".$type;
  				  $new_filename="../images/".$this->carusel_name."/temp/$time.".$type;
            //$to="../images/".$this->carusel_name."/slide/$time.".$type;
            $fileItem=$time.".".$type;
            
            if (move_uploaded_file($_FILES['filesArr']['tmp_name'][$name], $new_filename)) 
            {
	            copy ($new_filename, $fileItem_filename);
              //self::resize($new_filename, $to, $type, $this->img_ideal_width);
              
              $this->pdo->query(
                $s = "
                  INSERT INTO `".$this->prefix.$this->carusel_name."` (`title`,      `file`,      `module`,  `module_id`) 
                  VALUES                                                 ('$filename', '$fileItem', '$module', '$module_id')
                "
              );
              unlink($new_filename);
	            
	          }else{
	            $error .=  '<span class="imgList">Вы превысили ограничение ('.(MAX_SIZE*1024).') размера! так что перемещение неудалось! :(  </span>';
            }
             
            /*if (move_uploaded_file($_FILES['filesArr']['tmp_name'][$name], $newname)) 
            {
	            $time=time();
	            mysql_query("INSERT INTO user_uploads(image_name,user_id_fk,created) VALUES('$image_name','$session_id','$time')");
	          }else{
	            $error .=  '<span class="imgList">You have exceeded the size limit! so moving unsuccessful! </span>';
            }*/
	        }else{
			      $error .=  '<span class="imgList">Вы превысили ограничение ('.(MAX_SIZE*1024).') размера!</span>';
          }
        }else{ 
	     	  $error .=  '<span class="imgList">Неизвестный расширение!</span>';
        }  
        $i++;         
      }
    }
    
    if($error){
      $output = $error;
    }else{
      $output .= self::showFilesForModuleModule_id($module, $module_id);
    }
    
    return $output;
  
  }

  // END Подключение модуля 
  
   function getContent(&$admin = null){
    $carisel = $this;
    if(!is_null($admin)){
      if(isset($admin->is_admin_navigation) && ($admin->is_admin_navigation) ){
        $this->admin = &$admin;
      }
    }
    $output = '';
    
    if(isset($_GET['ajx'])){
      if (isset($_SESSION["WA_USER"])){
        
        
        
        if(isset($_GET['act'])){
          if($_GET['act'] == 'star_check'){
            $carisel->star_check();
          }elseif($_GET['act'] == 'sort_item'){
            $carisel->sort_item();
          }elseif($_GET['act'] == 'ajx_pager'){
            echo $carisel->ajx_pager();
          }elseif($_GET['act'] == 'sort_files'){
            if(isset($_GET['module']) && isset($_GET['module_id']))
            $carisel->sort_files();
          }elseif($_GET['act'] == 'delete_item'){
            echo $carisel->delete_slide(intval($_POST['del_id']), 'ajax');
          }elseif($_GET['act'] == 'croppImg'){
            echo $carisel->croppImg();
          }elseif($_GET['act'] == 'ajaxFilesUpload'){
            if(isset($_GET['module']) && isset($_GET['module_id']))
            echo $carisel->ajaxFilesUpload($_GET['module'], $_GET['module_id']);
          }elseif($_GET['act'] == 'ajaxDeleteFile'){
            echo $carisel->ajax_delete_slide($_POST['ajax_id']);
          }elseif($_GET['act'] == 'new_file_name'){
            echo $carisel->ajax_new_file_name();
          }
          
        }

      }
      
    }else{
      #$output='<div style="padding:20px 0;">';
        
      if (isset($_SESSION["WA_USER"])){
        
        
        if(isset($_GET['adds'])){
          $output .= $carisel->add_slide();
        }elseif(isset($_GET['creates'])){
          $output .= $carisel->create_slide();  
        }elseif(isset($_GET["edits"])){
          $output .= $carisel->edit_slide(intval($_GET["edits"]));  
        }elseif(isset($_GET["updates"])){
          if( isset($_POST['title'] )){
            $output .= $carisel->update_slide(intval($_GET["updates"]));    
          }else{
            $output .= $carisel->edit_slide(intval($_GET["updates"]));  
          }
        }elseif(isset($_GET["delete_picture"])&&isset($_GET['id'])){
          $output .= $carisel->delete_picture(intval($_GET['id']));  
        }elseif(isset($_GET["delete_file"])&&isset($_GET['id'])){
          $output .= $carisel->delete_file(intval($_GET['id']));  
        }elseif(isset($_GET["deletes"])){
          $output .= $carisel->delete_slide(intval($_GET['deletes']));  
        }else{
          $output .= $carisel->show_table();
        }
        
      }else{
        $output .=  '<div style ="text-align: center;"><h3> Кончилость время ссесии <a href = "'.IA_URL.'">Повторите авторизацию</a></h3>';
        
      }
      #$output .= "</div>";  
      
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