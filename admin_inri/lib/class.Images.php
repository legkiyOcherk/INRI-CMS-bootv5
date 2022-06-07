<?php
require_once __DIR__."/class.BaseCarusel.php"; 
require_once __DIR__."/formvalidator.php";// Валидатор

class Images extends BaseCarusel{
  var $img_ideal_width = 960;
  var $img_ideal_height = 500;
  var $header = 'Изображения';
  
  var $date_arr = array(
    'title' => 'Название',
    'module' => 'Название модуля (таблицы бд к которой привязан URL)',
    'module_id' => 'Название модуля id модуля',
    'img_alt' => 'Alt изображение',
    'img_title' => 'Title изображение', 
  );
  var $pdo;
  
  var $url_item = null; // Генерация url
  var $is_pager = true; // Отображать пэйджер
  
  var $carusel_name;
  var $sqlTable;
  var $pager = array(
        'perPage' => 10,
        'page' => 1,
        'url' => '',
        'items_per_page' => array( 10, 50, 100, 500, 1000, 5000)
      );
  var $filter_field = array('title');
  
  // конструктор
  function __construct($carusel_name = null, $date_arr = null, $genSqlTable = false, $genImgDir = false, $pager = null) {
    
    $this->pdo = db_open();
    $this->carusel_name = "all_images";
    
    if($carusel_name){
      $this->carusel_name = $carusel_name;
    }else{
      $this->carusel_name = "all_images";
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
  
  // Инициалицация
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
      'title', 'ignore', 'module', 'module_id');
      
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$this->sqlTable.'` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `module` varchar(255) NOT NULL,
      `module_id` int(11) NOT NULL,
      ';
    $sql .= '`img` varchar(255) NOT NULL,
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
      echo $q->errorInfo();
      exit;
      return false;
    }
  }
  
  // End Инициалицация

  // Валидация введенных данных
    
  // End Валидация введенных данных

  // All Method
  
  function getImg_ideal_width() {
    return $this->img_ideal_width;
  }
  
  function setImg_ideal_width($width) {
    $this->img_ideal_width = $width;
  }

  function getImg_ideal_height() {
    return $this->img_ideal_height;
  }
  
  function setImg_ideal_height($height) {
    $this->img_ideal_height = $height;
  }
  
  function getHeader() {
    return $this->header;
  }
  
  function setHeader($text) {
    $this->header = $text;
  }
  
  function setDate_arr($array) {
    $this->date_arr = $array;
  }
  
  function setIsUrl($is_url) {
    require_once(NX_PATH.IA_URL.'/lib/class.Url.php');
    if($is_url) $this->url_item = new Url('url');
  }
  
  function setIsPager($is_pager){
    $this->is_pager = $is_pager;
  }
  
  function setIsFilter($is_filter){
    $this->is_filter = $is_filter;
  }
  
  function setFilterField($arrFilterField = array('title')){
    $this->filter_field = $arrFilterField;
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
  /* Не допилено
  function get_img_link ($src_path, $src_name, $new_dir, $width, $height = null, $rgb = 0xFFFFFF, $quality = 100 ){
    if (!file_exists($src_path.'/'.$new_dir)) 
			mkdir($src_path.'/'.$new_dir);
    
    if (!file_exists($src_path.'/'.$new_dir.'/'.$src_name)) {
      $Image = Image::Factory();
      $path = $Image->resize($src_path.'/'.$src_name, $src_path.'/'.$new_dir.'/'.$src_name, $width, $height, $rgb, $quality);
    }

    return '/'.$src_path.'/'.$new_dir.'/'.$src_name;            
  }
  */
  
  
  // AJAX function
  
  function sort_images(){
    foreach ($_POST["itSort"] as $key=>$val)
	  {
		  $order=$key*10;
		  $this->pdo->query  ("UPDATE `".$this->prefix.$this->carusel_name."` SET `module_ord`=$key WHERE `id`=$val");
       
	  }
  }
  
  function ajax_delete_slide($id){
    $output = "";
   	if ($id){
  		$this->delete_picture($id);
  		if($q = $this->pdo->query ("DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'")){
        return 'ok';
      }else{
        return $q;
      }
    }  
        
    return 'Нет id';
  }
  
  function ajax_new_image_name(){
    if(!isset($_POST['id'])) return 'Нет id';
    if(!($_POST['id'])) return 'Нет id';
    if(!isset($_POST['name'])) return 'Нет name';
    
    $id = intval($_POST['id']);
    $name = substr(htmlspecialchars(trim($_POST['name'])), 0, 255);
    $s = "UPDATE  `".DB_PFX."all_images` SET  `title` =  '$name' WHERE  `".DB_PFX."all_images`.`id` = $id";
        
    if($q = $this->pdo->query($s)){
      return 'ok';
    }else{
      return $q;
    }
  }
  
  // END AJAX function
  
  
  // BACKEND function
  
  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          	<th style="width: 40px;">#</th>
      		  <th style="width: 50px;">Скрыть</th>
            <th style="width: 60px;">Картинка</th>
      		  <th>Название</th>
            <th>Скачать изображение</th>
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
    
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              <input type="checkbox" class="group_checkbox" name="group_item[]" value="'.$id.'"> '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
          </td>
            
            <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td>  
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
    if($img){
      $output .= '
              <a href="/images/'.$this->carusel_name.'/orig/'.$img.'" title="Скачать изображение" target = "_blank">'.$img.'</a>';
    }
    $output .= '
            </td>
            <td style="text-align: left;">'.$module.'</td>
            <td style="text-align: left;">'.$module_id.'</td>
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
        <input type="hidden" name="slideid" value="1">
    ';
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
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt3", "longtxt4"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      if( in_array($key, array("color")))    $type = 'color';
      if( in_array($key, array("date")))     $type = 'date';
      if( in_array($key, array("datetime"))) $type = 'datetime';
      if( in_array($key, array("title", "module", "module_id", "seo_h1", "seo_title", "img_alt", "img_title"))) $type = 'text';
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
    if($this->validationValue($id)){
      
      if ($name = $this->load_picture($id)){
  			$this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
  		}

      $sql_vals = $this->getUpdateSlide_SqlVals();
       
      if($this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET $sql_vals WHERE `id` = '$id'")){
        
      }else{
        echo "Произошла ошибка при ОБНОВЛЕНИИ записи в бд функсия update_slide()";
        exit;
      }
		  
      $output .= $this->edit_slide($id);
      
    }else{
      $output .= $this->edit_slide($id, $_POST);
    }
    return $output;
  }

  function delete_slide($id, $view = 'show_table'){
    $output = "";
    if (isset($id) && $id){
   	  #if (isset($_GET["deletes"])){
  		#$id = intval($_GET["deletes"]);
      $this->delete_picture($id);
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
  
  // Подключение модуля 
  function showImagesForModuleModule_id($module, $module_id){
    $output = '';
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name."`
      WHERE
        `module` = '$module' 
        AND `module_id` = $module_id
      ORDER BY `module_ord`
    ";
    #$output .= "s = $s";
    
    $q = $this->pdo->query($s);
    if($q->rowCount()){
      $output .= '
      
      <form method="post" action="'.IA_URL.$this->carusel_name.'.php" id="sortImages">
        <ul id="sortable">
      ';
      while($r = $q->fetch()){
        $output .= '
        
          <li class = "ui-state-default" style="cursor: move;">
            <input type="hidden" value="'.$r['id'].'" name="itSort[]">
            <IMG src="/images/'.$this->carusel_name.'/slide/'.$r["img"].'"  class="imgList" >
            <div class="delete_image" name = "'.$r['id'].'" onclick1="delete_image(this, '.$r['id'].')">X Удалить</div>
            <div class="edit_image" ><a href = "/'.ADM_DIR.'/all_images.php?edits='.$r['id'].'" target = "_blank" >Редактировать</a></div>
            <div title="Скрыть" onclick="star_image_check('.$r['id'].', \'hide\')" class="star_image_check '.$this->getStarValStyle($r['hide']).'" id="image_hide_'.$r['id'].'"></div>
            <input type = "text" class="image_name" value = "'.$r['title'].'" data-id = "'.$r['id'].'"> 
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
  
  function showImageForm($module, $module_id){
    if(!$module) return 'class Images function showImageForm парааметр module пустой ';
    if(!$module_id) return 'class Images function showImageForm параметр module_id пустой ';
    $output = '
    <script src="'.WA_PATH.'js/jquery.wallform.js"></script>
    ';
    $output .= '
    <script>
      var in_process = false;
      $(document).ready(function() { 
      
      //Сохранить новое название
      $(".image_name", this).keyup(function(e) {
        //if(e.which == 13) {
          var id = $(this).data("id");
          var name = $(this).val();
          //alert("You pressed enter!"+id+" "+name);
          
			    $.post("'.IA_URL.$this->carusel_name.'.php?ajx&act=new_image_name", {id:id, name:name}).done(function( data ) {
            $("#exists").html(data);
				  });
        //}
        
      
      });
      
      $(".image_name", this).click(function(e) {
        $(this).focus();
        //alert("focus");
      });
      
      
    ';
    //
    $output .= <<<HTML
      $('#photoimg').die('click').live('change', function()			{ 
        
      
        //$("#preview").html('');
        var d = new Date();
        var n = d.getTime();
        if(in_process == false){
          
  			  //alert(n+' '+in_process);
          in_process = true;
          $("#preview").html('');
  				$("#imageform").ajaxForm({target: '#preview', 
  				     beforeSubmit:function(){ 
  					
  					console.log('ttest');
  					$("#imageloadstatus").show();
  					 $("#imageloadbutton").hide();
  					 }, 
  					success:function(){ 
  				    console.log('test');
  					 $("#imageloadstatus").hide();
  					 $("#imageloadbutton").show();
HTML;
    $output .= '
      $( "#sortable" ).sortable({
          stop: function( event, ui ) {
                  $.post( "'.IA_URL.$this->carusel_name.'.php?ajx&act=sort_images&module='.$module.'&module_id='.$module_id.'", $( "#sortImages" ).serialize());
                }
        });
    ';
    $output .= <<<HTML
             in_process = false;
             
  					}, 
  					error:function(){ 
  					console.log('xtest');
  					 $("#imageloadstatus").hide();
  					$("#imageloadbutton").show();
            in_process = false;
  					} }).submit();
				}
		
			});
HTML;
    $output .= '
    
    
      $( "#sortable" ).sortable({
        stop: function( event, ui ) {
              $.post( "'.IA_URL.$this->carusel_name.'.php?ajx&act=sort_images&module='.$module.'&module_id='.$module_id.'", $( "#sortImages" ).serialize());
        }
      });
    ';
    $output .= '
      $( "#sortable" ).disableSelection();
      
    
    }); 
    $(".delete_image").live("click", function()			{
      if (1/*confirm("Удалить изображение???")*/) {
      var id = $(this).attr("name");
      $(this).parent().remove();
			$.post("'.IA_URL.$this->carusel_name.'.php?ajx&act=ajaxDeleteImage", {ajax_id: id}, 
        function(data) {
			    if (data == "ok") {
            //alert ("Ура ajax_id ="+id);
          }else{
            alert ("Возникла ошибка при удалении(" + data);
          }
			  })
		  }
    });
    
    // $(".star_image_check").live("click", function()		
    function star_image_check(id, field) 	{
  		$.post(\''.$this->carusel_name.'.php?ajx&act=star_check\', {id:id, field:field}, function(data) {
    ';
    $output .= <<<HTML
        
  			if (data == 1) {
          console.log( 'data = ' + 1 + ' ' + '#image_'+field+'_'+id + ' ' + $('#image_'+field+'_'+id).attr('title'));
  				$('#image_'+field+'_'+id).removeClass('far fa-star').addClass('fas fa-star');
  				//$('#image_'+field+'_'+id);
  			} else {
          console.log( 'data = ' + 0  + ' ' + '#image_'+field+'_'+id);
  				$('#image_'+field+'_'+id).removeClass('fas fa-star').addClass('far fa-star');
  				//$('#image_'+field+'_'+id).addClass('far fa-star');
  			}
  		});
    }
HTML;

    $output .= <<<HTML
    </script>
  <style>

  #preview
  {
    color:#cc0000;
    font-size:12px
  }
  .images_box{
    height: 150px;
    width: 150px;
    float: left;
    border:1px solid #dedede;
    padding:4px;
    margin:2px;	
    float:left;	
    text-align: center;
  }
  .images_box img{
    max-height: 150px;
    max-width: 150px;
  }
  .star_image_check{
    position: absolute;
    font-size: 25px;
    top: 3px;
    left: 3px;
    color: #f0ad4e;
    //color: yellow;
    opacity: 0.8;
    cursor: pointer;
  }
  .star_image_check:hover{
    opacity: 1;
  }
  
  #sortable { list-style-type: none; margin: 0; padding: 0;  }
  #sortable li{ 
    margin: 3px 3px 3px 0; 
    padding: 1px; 
    float: left; 
    width: 150px; 
    height: 150px; 
    font-size: 4em; 
    text-align: center; 
    position: relative;
    overflow: hidden;
  }
  
  .delete_image{
    position: absolute;
    font-size: 12px;
    top: 0px;
    right: 0px;
    color: red;
    opacity: 0.8;
    cursor: pointer;
    font-weight: 400;
  }
  
  .delete_image:hover{
    opacity: 1;
  }
  
  input[type="text"].image_name{
    position: absolute;
    font-size: 12px;
    bottom: -1px;
    left: 0px;
    color: blue;
    width: 149px;
    cursor: text;
    border-radius: 0px;
    font-weight: 400;
  }
  
  #sortable li img{
    max-height: 150px;
    max-width: 150px;
  }
  
  .edit_image{
    color: green;
    position: absolute;
    font-size: 12px;
    bottom: 35px;
    right: 0px;
    color: red;
    opacity: 0.8;
    cursor: pointer;
    font-weight: 400;
  }
  
  .edit_image a{
    color: green !important;
  }
  
  
  .edit_image:hover{
    opacity: 1;
  }
      
  </style>
  <div>

HTML;

  
  $output .='
  </div>
  <form id="imageform" method="post" enctype="multipart/form-data" 
    action="'.IA_URL.$this->carusel_name.'.php?ajx&act=ajaxImageUpload&module='.$module.'&module_id='.$module_id.'" 
  style="clear:both">
  ';
  $output .= '
  <br>
  <h4>Загрузить дополнительные изображения</h4> 
  <div id="imageloadstatus" style="display:none"><img src= "'.IA_URL.'css/img/loader.gif" alt="Загружается...."/></div>
  <div id="imageloadbutton">
  <input type="file" name="photos[]" id="photoimg" multiple="true" />
  </div>
  </form>
  <div id="preview">
  ';
  $output .= self::showImagesForModuleModule_id($module, $module_id);
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
  
  function ajaxImageUpload($module, $module_id){
    define ("MAX_SIZE","9000");
    
    $output = ''; $error = '';
    
    $valid_formats = array("jpg", "png", "gif", "bmp","jpeg");
    if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") 
    {
	    $uploaddir = "../images/".$this->carusel_name."/uploads/"; //a directory inside
      
      $i=0;
      foreach ($_FILES['photos']['name'] as $name => $value)
      {
        $filename = stripslashes($_FILES['photos']['name'][$name]);
        
        $size=filesize($_FILES['photos']['tmp_name'][$name]);
        //get the extension of the file in a lower case format
        $ext = self::getExtension($filename);
        $type = strtolower($ext);
     	
        if(in_array($type,$valid_formats))
        {
	        if ($size < (MAX_SIZE*1024))
	        {
		        $image_name=time().$filename;
		        #$output .= "<img src='".$uploaddir.$image_name."' class='imgList'>";
		        #$newname=$uploaddir.$image_name;
            $time=time().'_'.$i;
            $big_filename="../images/".$this->carusel_name."/orig/$time.".$type;
  				  $new_filename="../images/".$this->carusel_name."/temp/$time.".$type;
            $to="../images/".$this->carusel_name."/slide/$time.".$type;
            $img=$time.".".$type;
            
            if (move_uploaded_file($_FILES['photos']['tmp_name'][$name], $new_filename)) 
            {
	            copy ($new_filename,$big_filename);
              self::resize($new_filename, $to, $type, $this->img_ideal_width);
              
              $this->pdo->query(
                $s = "
                  INSERT INTO `".$this->prefix.$this->carusel_name."` (`title`,     `img`,  `img_alt`, `img_title`, `module`,  `module_id`) 
                  VALUES                                                 ('$filename', '$img', '',        '',          '$module', '$module_id')
                "
              );
              unlink($new_filename);
	            
	          }else{
	            $error .=  '<span class="imgList">Вы превысили ограничение ('.(MAX_SIZE*1024).') размера! так что перемещение неудалось! :(  </span>';
            }
             
            /*if (move_uploaded_file($_FILES['photos']['tmp_name'][$name], $newname)) 
            {
	            $time=time();
	            $this->pdo->query("INSERT INTO user_uploads(image_name,user_id_fk,created) VALUES('$image_name','$session_id','$time')");
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
      $output .= self::showImagesForModuleModule_id($module, $module_id);
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
    
    if(isset($_GET['ajx'])){
      if (isset($_SESSION["WA_USER"])){
        
        
        
        if(isset($_GET['act'])){
          if($_GET['act'] == 'star_check'){
            $carisel->star_check();
          }elseif($_GET['act'] == 'sort_item'){
            $carisel->sort_item();
          }elseif($_GET['act'] == 'ajx_pager'){
            echo $carisel->ajx_pager();
          }elseif($_GET['act'] == 'sort_images'){
            if(isset($_GET['module']) && isset($_GET['module_id']))
            echo "sort_images";
            $carisel->sort_images();
          }elseif($_GET['act'] == 'delete_item'){
            echo $carisel->delete_slide(intval($_POST['del_id']), 'ajax');
          }elseif($_GET['act'] == 'croppImg'){
            echo $carisel->croppImg();
          }elseif($_GET['act'] == 'ajaxImageUpload'){
            #echo "ajaxImageUpload";
            if(isset($_GET['module']) && isset($_GET['module_id']))
            echo $carisel->ajaxImageUpload($_GET['module'], $_GET['module_id']);
          }elseif($_GET['act'] == 'ajaxDeleteImage'){
            echo $carisel->ajax_delete_slide($_POST['ajax_id']);
          }elseif($_GET['act'] == 'new_image_name'){
            echo $carisel->ajax_new_image_name();
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