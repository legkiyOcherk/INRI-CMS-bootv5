<?php
require_once __DIR__."/class.BaseCarusel.php";  
 
class CatCarusel extends BaseCarusel{

  var $img_ideal_width = 960;
  var $img_ideal_height = 500;
  var $img_cat_ideal_width = 960;
  var $img_cat_ideal_height = 500;
  var $header = 'Слайдер';
  var $title;
  var $bread;
  var $cont_footer = '';
  var $admin = null;
  
  var $date_arr = array(
    'title' => 'Название',
    'cat_id' => 'Категория',	
    'img_alt' => 'Alt изображение',
    'img_title' => 'Title изображение',
    'link' => 'Ссылка',
    'txt1' => 'Текст на синем фоне',
    'txt2' => 'Текст на белом фоне',
    'tongtxt1' => 'Описание бла бла бла',
  );
  
  var $date_cat_arr = array(
    'title' => 'Cat Название',
    'parent_id' => 'Категория',
    'img_alt' => 'Cat Alt изображение',
    'img_title' => 'Cat Title изображение',
    'link' => 'Cat Ссылка',
    'txt1' => 'Cat Текст на синем фоне',
    'txt2' => 'Cat Текст на белом фоне',
    'tongtxt1' => 'Cat Описание бла бла бла',
  );
  var $pdo;
  var $pager = array(
    'perPage' => 10,
    'page' => 1,
    'url' => '',
    'items_per_page' => array( 10, 50, 100, 500, 1000, 5000)
  );
  var $filter_field       = array( 'title' );
  var $checkbox_cat_array = array( 'hide'  );

  
  
  var $url_item = null;     // Генерация url
  var $images_items = null; // Модуль картинок
  var $files_items = null;  // Модуль файлов
  var $log = null;          // Вести лог
  var $is_pager = true;     // Отображать пэйджер
  var $is_filter = null;    // Отображать фильтр
  
  var $carusel_name;
  var $sqlTable;
  var $cat_carusel_name;
  var $cat_sqlTable;
  var $validateError_arr = array();
  var $validateCatError_arr = array();
  
  
  
  
  // конструктор
  function __construct($carusel_name = null, $date_arr = null, $date_cat_arr = null, $genSqlTable = false, $genImgDir = false, $pager = null) {

    //Для пересоздания раскоментить
    #$_SESSION[$carusel_name]['is_table'] = 0;
    #$_SESSION[$carusel_name]['img_dir'] = 0;
    //END Для пересоздания раскоментить
    
    $this->pdo = db_open();
    $this->carusel_name = "carusel_01";
    
    
    if($carusel_name){ 
      $this->carusel_name = $carusel_name;
      $this->cat_carusel_name = $this->prefix.$carusel_name.'_cat';
    }else{
      $this->carusel_name = "carusel_01"; 
      $this->cat_carusel_name = "carusel_01_cat"; 
    }
        
    $this->sqlTable = $this->prefix.$this->carusel_name;  
    $this->cat_sqlTable = $this->prefix.$this->carusel_name.'_cat'; 
    
    if($date_arr) $this->date_arr = $date_arr;
    if($date_cat_arr) $this->date_cat_arr = $date_cat_arr;
  
        
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
      if( is_array($pager) ) $this->pager = $pager;
      $this->setPagerParamers();
    }
    
  }
  
  // Инициалицация
  function create_sql_table(){
    
    #$arr_varchar_255_default_null = array( ); // varchar(255) DEFAULT NULL
    
    $arr_varchar_255_not_null = array(         // varchar(255) NOT NULL
      'article' );
    
    $arr_varchar_10_default_null = array(      // varchar(10) DEFAULT NULL
      'date', 'datetime' );
    
    $arr_text = array(                         // text
      'longtxt1', 'longtxt2', 'longtxt3', 'orm_search' );
    
    $arr_tinyint_1_default_null = array(       // tinyint(1) DEFAULT NULL      
      'fl1', 'fl2', 'fl3', 'is_hit', 'is_new', 'is_sale'  );
    
    $arr_int_11_default_null = array(          // int(11) DEFAULT NULL
      'price', 'amount', 'old_price', 'oldprice', 'userPhone', 'gross_price', 'gross_count',
      'availability_id', 'country_id', 'brand_id', 'units_id');
    
    $arr_ignore = array(                       // ignore field
      'title', 'cat_id', 'parent_id', 'img', 'hide', 'ord', 'ignore' );
      
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$this->sqlTable.'` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `cat_id` int(11) DEFAULT 0,
      `title` varchar(255) NOT NULL,
      `img` varchar(255) NOT NULL,
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
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ';
    $sql_table = $sql;
    
    //Таблица категрий
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$this->cat_sqlTable.'` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `parent_id` int(11) DEFAULT 0,
      `title` varchar(255) NOT NULL,
      `img` varchar(255) NOT NULL,
      ';
    foreach($this->date_cat_arr as $key=>$val){
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
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ';
    $sql_cat_table = $sql;
    //---
    if($q = $this->pdo->query($sql_table)){
      echo "База данных ".$this->sqlTable." Успешно созданна<br>";
      
      if($cat_q = $this->pdo->query($sql_cat_table)){
        echo "База данных ".$this->cat_sqlTable." Успешно созданна<br>";
        return true;
      }else{
        echo "Ошибка при создании базы данных ".$this->cat_sqlTable." : ".$q;
        echo "<pre>".$sql_cat_table."</pre>";
        exit;
        return false;
      }
    }else{
      echo "Ошибка при создании базы данных ".$this->sqlTable." : ".$q;
      echo "<pre>".$sql_table."</pre>";
      exit;
      return false;
    }
  }
  
  function create_img_dir(){
    
    parent::create_img_dir();
    
    // Категории
    if (!is_dir("../images/".$this->carusel_name."/cat")){
      mkdir("../images/".$this->carusel_name."/cat");
      chmod ("../images/".$this->carusel_name."/cat", 0755);
    }
    
    if (!is_dir("../images/".$this->carusel_name."/cat/orig")){
      mkdir("../images/".$this->carusel_name."/cat/orig");
      chmod ("../images/".$this->carusel_name."/cat/orig", 0755);
    }
    
    if (!is_dir("../images/".$this->carusel_name."/cat/slide")){
      mkdir("../images/".$this->carusel_name."/cat/slide");
      chmod ("../images/".$this->carusel_name."/cat/slide", 0755);
    }
    
    if (!is_dir("../images/".$this->carusel_name."/cat/temp")){
      mkdir("../images/".$this->carusel_name."/cat/temp");
      chmod ("../images/".$this->carusel_name."/cat/temp", 0755);
    }
        if (!is_dir("../images/".$this->carusel_name."/cat/variations")){
      mkdir("../images/".$this->carusel_name."/cat/variations");
      chmod ("../images/".$this->carusel_name."/cat/variations", 0755);
    }
  }
  // End Инициалицация
  
  // Валидация введенных данных
  
  function getCatErrorForKey($key){
    $output = '';
    if(!empty($this->validateCatError_arr)){
      if(isset($this->validateCatError_arr[$key]))
        if($this->validateCatError_arr[$key]){
          $output .= '<label class="validate_rerror">'.$this->validateCatError_arr[$key].'</label>';
        }
    }
    return $output;
  }

  function validationCatValue($id){ 
    $is_validation = true;
    
    $validator = new FormValidator();
    $validator->addValidation("title","req","Пожалуйста, заполните ".$this->date_cat_arr['title']);
    
    if($validator->ValidateForm())
    {
        //echo "<h2>Validation Success!</h2>";
    }
    else
    {
        //echo "<B>Validation Errors:</B>";

        $error_hash = $validator->GetErrors();
        $this->validateCatError_arr = $error_hash;
        $is_validation = false;
        /*foreach($error_hash as $inpname => $inp_err)
        {
            echo "<p>$inpname : $inp_err</p>\n";
        }*/       
    }
    
    return $is_validation;
  }

  // End Валидация введенных данных
  
  // All Method
  
  function getCatImg_ideal_width() {
    return $this->img_cat_ideal_width;
  }
  
  function setCatImg_ideal_width($width) {
    $this->img_cat_ideal_width = $width;
  }

  function getCatImg_ideal_height() {
    return $this->img_cat_ideal_height;
  }
  
  function setCatImg_ideal_height($height) {
    $this->img_cat_ideal_height = $height;
  }
  
  function setIsUrl($is_url) {
    require_once('lib/class.Url.php');
    if($is_url) $this->url_item = new Url('url');
  }
  
  function setIsImages($is_images) {
    require_once('lib/class.Images.php');
    if($is_images) $this->images_items = new Images('all_images');
  }
  
  function setIsFiles($is_files) {
    require_once('lib/class.Files.php');
    if($is_files) $this->files_items = new Files('all_files');
  }
  
  function setIsLog($is_log) {
    require_once('lib/class.Log.php');
    if($is_log) $this->log = new Log('all_log');
  }
  // END All Method
  
  
  // AJAX function
  
  function star_cat_check(){
    if (!isset($_POST['id']) or !intval($_POST['id']) or !$_POST['field']) return;
		
    $fields = array('hide', 'star1', 'star3', 'fl_show_mine');
		$id = intval($_POST['id']);
		$field = str_replace(' ', '', $_POST['field']);
		if (array_search($field, $fields) === false) return;
    
    $q = $this->pdo->query("SELECT `$field` FROM `".$this->prefix.$this->carusel_name.'_cat'."` WHERE `id` = $id");
    $r = $q->fetch();
    $state = $r[$field];
		
    $new_state = ($state == 1) ? 0 : 1;
    
    $sql = "
      UPDATE `".$this->prefix.$this->carusel_name.'_cat'."` 
      SET `$field`=:$field
      WHERE `".$this->prefix.$this->carusel_name.'_cat'."`.`id` = $id
    ";
    $values = array($field=>$new_state);
    
    $stm = $this->pdo->prepare($sql);
    $res = $stm->execute($values);
		
    if (!$res) return;
		
    echo $new_state;
    
  }
  
  function sort_cat_item(){
    foreach ($_POST["itCatSort"] as $key=>$val){
		  $order_main=$key*10;
		  $this->pdo->query ("UPDATE `".$this->prefix.$this->carusel_name.'_cat'."` SET `ord`=$key WHERE `id`=$val");
	  }
  }
  
  function ajx_pager(){
    if(isset($_POST['pager_act'])){
      
      if($_POST['pager_act'] == 'set_page'){
        if(isset($_POST['page']) && intval($_POST['page'])){
          $_SESSION['pager'][$this->carusel_name]['page'] = intval($_POST['page']);
          $_SESSION['pager'][$this->carusel_name]['url'] = intval($_POST['id_cat']);
          /*pri($_SESSION['pager']);*/
          return 'ok';
        }else{
          return 'error';
        }
        
      }elseif($_POST['pager_act'] == 'set_per_page'){
        if(isset($_POST['per_page']) && intval($_POST['per_page'])){
          $_SESSION['pager'][$this->carusel_name]['perPage'] = intval($_POST['per_page']);
          $_SESSION['pager'][$this->carusel_name]['url'] = intval($_POST['id_cat']);
          $_SESSION['pager'][$this->carusel_name]['page'] = 1;
          return 'ok';
        }else{
          return 'error';
        }
      }
    
    }
  }
  // END AJAX function
  
  
  // BACKEND function
  function show_bread_crumbs($c_id = null){
    if(!$c_id) $c_id = $_SESSION[$this->carusel_name]['c_id'];
    $output = '<div class="cat_bread_crumbs">';
    $output .= '<a href="'.$this->carusel_name.'.php?c_id=root">КАТАЛОГ</a> ';
    $this->bread[ucfirst_utf8($this->header)] = $this->carusel_name.'.php?c_id=root'; 
    $output .= $this->get_bread_crumbs($c_id);
    $output .= '</div>';
    return $output;
  }
  
  function get_bread_crumbs($cid){
  	$output = '';
	  if ($cid){
	  	if ($query = $this->pdo->query("SELECT * FROM `".$this->prefix.$this->carusel_name.'_cat'."` WHERE `id` = '$cid'")){
	  		if ( $cat = $query->fetch() ){
	  			$output = $this->get_bread_crumbs($cat['parent_id']);
	  			$output .= ' → <a href="'.IA_URL.$this->carusel_name.'.php?c_id='.$cat['id'].'">'.
	  			$cat['title'].'</a> ';
          $this->bread[$cat['title']] = ''.IA_URL.$this->carusel_name.'.php?c_id='.$cat['id']; 
	  		}
	  	}
	  }
	  return $output;
  }
  
  function get_category_option($cid, $parent = null, $padding = '', $output = '') {
		$where = ($parent) ? "parent_id = $parent" : "parent_id = 0 ";
    $s = "
      SELECT `id`, `title`
      FROM `".$this->cat_carusel_name."`
      WHERE $where
      ORDER BY `ord`
    ";
    $q = $this->pdo->query($s);
    #$list = db::select('id, title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', $where, "`ord`");
		
		if ($padding == '') {
			$output .= '<option value="0">Корень</option>'."\r\n";
		}
		if ($q->rowCount()) {
			$padding .= '&nbsp;&nbsp;&nbsp;';
			while ($category = $q->fetch()) {
				extract($category);
				$output .= '<option '.(($cid == $id) ? 'selected="selected" ' : '').'value="'.$id.'">'.$padding.$title.'</option>'."\r\n";
				$output .= $this->get_category_option($cid, $id, $padding);
			}
    }
    
    return $output;
  }
  
  function show_cat_table_header_rows(){
    $output = '
                <tr class="tth">
            		  <th style="width: 55px;">#</th>
            		  <th style="width: 60px;">Скрыть</th>
            		  <th colspan = "2">Название</th>
            		  <th style="width: 80px">Действие</th>
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
              	  
                  <td class = "zoomImg_box" style="">
              ';
              if($img){
                $output .= '
                  <div class="zoomImg"><img style="width:50px" src="../images/'.$this->carusel_name.'/cat/slide/'.$img.'"></div>  
                ';
              }else if( isset($color) && $color ){
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
                  
                  <td style="" class="action_btn_box">
                    '.$this->show_cat_table_row_action_btn($id).'
                  </td>
        			  </tr>
              ';
    
    return $output;
  }
  
  function show_cat_table_row_action_btn($id){
    $output = '';
    
    $output .= '
        
        <a  href="..'.IA_URL.$this->carusel_name.'.php?editc='.$id.'" 
            class = "btn btn-info btn-sm"
            title = "Редактировать">
            <i class="fa fa-pencil"></i>
        </a>';
            
    $val_is_cildren = $val_is_items = '';
    
    $s = "SELECT id FROM `".$this->prefix.$this->carusel_name.'_cat'."` WHERE parent_id = $id";
    $q = $this->pdo->query($s);
    if($q->rowCount()) {
      $r = $q->fetch();
      $val_is_cildren = $r['id'];
    }
    $s = "SELECT id FROM `".$this->prefix.$this->carusel_name."` WHERE cat_id = $id";
    $q = $this->pdo->query($s);
    if($q->rowCount()) {
      $r = $q->fetch();
      $val_is_items = $r['id'];
    }
    
    if(!$val_is_cildren && !$val_is_items){
      $output .= '
          <a href="..'.IA_URL.$this->carusel_name.'.php?deletec='.$id.'" onclick="javascript: if (confirm(\'Удалить?\')) { return true;} else { return false;}"
                class="btn btn-danger btn-sm" 
                title="удалить" 
                onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
            <i class="fa fa-trash-o"></i>
          </a>
      ';
      #<a href="..'.IA_URL.'$this->carusel_name.'.php?deletec='.$id.'" onclick="javascript: if (confirm(\'Удалить?\')) { return true;} else { return false;}">
      #      <img src="..'.IA_URL.'images/icons/b_drop.png" width="16" height="16" border="0">
      #    </a>
    }
    $output .= '
      </td>';
    
    return $output;
  }
  
  function show_cat_table($c_id = 0){
    $output = "";
    
    $this->title = ucfirst_utf8($this->header);
    if(intval($c_id))
      if($title = db::value( 'title' ,$this->prefix.$this->carusel_name.'_cat', "id = $c_id"))
        $this->title = $title;
        
    if(!$c_id) $c_id = '0';
    
    if($c_id){
      $this->title .= '
        <a href="/'.ADM_DIR.'/'.$this->carusel_name.'.php?editc='.$c_id.'" class="text-light btn btn-info btn-sm px-1 py-0 mt-1" title = "Редактировать категорию">
          <i class="fas fa-pencil-alt"></i> 
        </a>'; 
    }
    
    $where = "`parent_id` =  $c_id";
    $s_order = "`ord` ASC";
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name.'_cat'."`
      WHERE $where
      ORDER BY $s_order
    "; #pri($s);
    $output .= '
      <form 
        method="post" 
        action="'.$this->carusel_name.'.php" 
        id="sortCatSlide"
        class="table-responsive"
      >';
    
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        $output .= '
  	      <table id="sorcattabler" class="table sorcattab table-sm table-striped">
            <thead>'.$this->show_cat_table_header_rows().'</thead>
            <tbody>'; 
        $i = 0; 
        while($item = $q->fetch()){
          $output .= $this->show_cat_table_rows($item, $i++); 
        }
        $output .= '
            <tbody>
          </table>';
      }
    }
    $output .= '
            </form>';
    
    $output .= $this->get_add_cat_btn_show_table();
    
    return $output;
    
  }
  
  function get_add_cat_btn_show_table(){
    $output = '';
    $output .= '
    <div style = "text-align: right;" ><a class="btn btn-primary" href="?addc">Добавить категорию</a></div>';
    
    return $output;
  }
  
  
  function getFormStyleAndScript(){
    $output = '';
    
    $output .= parent::getFormStyleAndScript();
    $output .= '
      <script>
        function delete_cat_item(del_id, title, id_block) {
      		if (confirm( title )) {
      			$.post(\''.$this->carusel_name.'.php?ajx&act=delete_cat_item\', {del_id: del_id}, function(data) {
      				if (data == "ok") {
                
      					$("#"+id_block).fadeOut("slow").remove();
      				}
      			})
      		}
      	}
        
        $(document).ready(function() {
         
            // Initialise the first table (as before)
            $(".sortab").tableDnD({
        	  onDrop: function() {
        	    $.post( "'.$this->carusel_name.'.php?ajx&act=sort_item", $( "#sortSlide" ).serialize());
        	  }
        	
        	});

          $(".sorcattab").tableDnD({
        	  onDrop: function() {
        	    $.post( "'.$this->carusel_name.'.php?ajx&act=sort_cat_item", $( "#sortCatSlide" ).serialize());
        	  }
        	});    
        });
        function star_cat_check(id, field) {

    	  	$.post(\''.$this->carusel_name.'.php?ajx&act=star_cat_check\', {id:id, field:field}, function(data) {
        ';
        $output .= <<<HTML
    	  		if (data == 1) {
      				$('#'+field+'_'+id).removeClass('far fa-star')
      				$('#'+field+'_'+id).addClass('fas fa-star')
      			} else {
      				$('#'+field+'_'+id).removeClass('fas fa-star')
      				$('#'+field+'_'+id).addClass('far fa-star')
    	  		}
    	  	})
          
    	  }
HTML;
  $output .= '
  </script>
  ';

    if($this->is_pager) $output .= $this->getPagerScript();

    return $output;
  }
  
  
  function show_table_header_rows(){
    $output = '
          <tr class="tth nodrop nodrag">
          	<th style="width: 55px;">#</th>
      		  <th style="width: 50px;">Скрыть</th>
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
            
            <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).'
            </td>
  			  </tr>
          ';
    
    return $output;
  }
  
  function show_table_row_action_btn($id){
    $output = '';
    
    $output .= '
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" 
                  class = "btn btn-info btn-sm my-1"
                  title = "Редактировать">
                <i class="fas fa-pencil-alt"></i>
              </a>
              
              <span class="btn btn-danger btn-sm my-1" 
                    title="удалить" 
                    onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
                <i class="far fa-trash-alt"></i>
              </span>
    ';
    return $output;
  }
  
  function get_show_table_menu_btn( $c_id = ''){
    $output = '';
    $btn_class = 'btn btn-default btn-sm mb-1';
    
    $output .=  '
    <div class="expansion_table_box py-2">
      <a href="/'.ADM_DIR.'/'.$this->carusel_name.'.php" class="'.$btn_class.'" title = "Каталог"><i class="fas fa-home"></i></a>
      <a href="?view_tree" class="'.$btn_class.'"><i class="fas fa-tree"></i> Дерево всех категорий</a>
      <a href="?full_tree" class="'.$btn_class.'"><i class="fas fa-sitemap"></i></i> Полный каталог</a>
    </div>';
    
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
        
      case 'remuve_cat':
        if ( !empty($group_items) ){
          if(isset($_POST['remuve_cat_id']) && $_POST['remuve_cat_id']){
            $remuve_cat_id = $_POST['remuve_cat_id'];
            
            $i = 0; $str_item_id = '';
            foreach ($group_items as $g_item_id){
              if($i++)$str_item_id .= ", ";
              $str_item_id .= intval( $g_item_id );
            }
            $s = "
              UPDATE  `".$this->prefix.$this->carusel_name."` 
              SET     `".$this->prefix.$this->carusel_name."`.`cat_id` = '$remuve_cat_id'
              WHERE   `".$this->prefix.$this->carusel_name."`.`id` IN ( $str_item_id );
            "; # pri($s);
            $res = $this->pdo->query( $s ); 
            if( $res ){
              $cat_name = db::value( 'title', $this->prefix.$this->carusel_name.'_cat', 'id = '.$remuve_cat_id );
              $output .= $this->getNotiseMobalWindow('Успешно!', 'Товары успешно перенесены в Категорию<br/><a href="/'.ADM_DIR.'/'.$this->carusel_name.'.php?c_id='.$remuve_cat_id.'">'.$cat_name.'</a>');
            }
          }
        }
        break;
        
    }
    
    return $output;
    
  }
  
  function getNotiseMobalWindow($mob_tit, $mob_content){
    $output = '';
    
    $output .= '
      <div class="modal fade show" id="notice_ad_modal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true" style="display: block;">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="noticeModalLabel">'.$mob_tit.'</h5>
              <button type="button" class="close notice_ad_modal_close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              '.$mob_content.'
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary notice_ad_modal_close" data-dismiss="modal">Close</button>';
              #<button type="button" class="btn btn-primary">Save changes</button>
    $output .= '
            </div>
          </div>
        </div>
      </div>';
    
    $this->admin->adminFooterScripts .= '
    <script>
      $(document).ready(function() {
        $(".notice_ad_modal_close").click(function() { 
          $("#notice_ad_modal").hide();
          
          /*$("#notice_ad_modal").modal("show");*/
        });  
      });
    </script>';
    
    return $output;
  }
  
  function getGroupOperations(){
    $output = '';
    #pri($_POST);
    
    $output .= $this->makeGroupOperations();
    
    $output .= parent::getGroupOperations();
    
    $tmp  = '
    
    <div class = "group_operation_box">
      <div class="remuve_cat_box pt-1 pl-1">
        <div class="btn-group">
          <select name  = "remuve_cat_id" 
                  class = "form-control remuve_cat_id" 
                  style = "max-width: 320px; display: inline-block; float: left;"
                  >
            '.$this->get_category_option(0).'
          </select>
          <button type    = "submit" 
                  class   = "btn btn-info btn-sm" 
                  name    = "group_action" 
                  value   = "remuve_cat"  
                  style   = "vertical-align: top; width: 170px;"
                  onclick = "javascript: if (confirm(\'Переместить выделеные?\')) { return true;} else { return false;}">
            <span class="fas fa-file-import"></span>&nbsp;Переместить
          </button>
        </div>  
      </div>
    </div>';
      
    
    
    $output .= $tmp;
    
    $this->admin->adminFooterScripts .= '
      <script src  = "'.IA_URL.'admin_style/vendor/select2-4.0.6-rc.1/dist/js/select2.js"></script>
      <link   href = "'.IA_URL.'admin_style/vendor/select2-4.0.6-rc.1/dist/css/select2.css" rel="stylesheet" />
      <style>
      .select2-container--default .select2-results>.select2-results__options {
        max-height: 350px;
        overflow-y: auto;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
          color: #fff;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__choice {
          background-color: #3c8dbc;
          border: 1px solid #3c8dbc;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__choice {
          background-color: #3c8dbc;
          border-color: #367fa9;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__rendered {
          /*padding-bottom: 5px;*/
      }
      .select2-container--default .select2-selection--single {
        height: 34px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
      }
      .select2-container--default.select2-container--open {
          float: left;
          border-color: #3c8dbc;
      }
      .select2-container {
            box-sizing: border-box;
            display: inline-block;
            margin: 0;
            position: relative;
            vertical-align: middle;
            float: left;
        }
      .select2-container{
        width: 400px;
        float: left;
      }
      </style>
      
      <script>
        $(document).ready(function() {
          $(".remuve_cat_id").select2({
            width: \'500\'
          });
        });
      </script>';
    
    return $output;
  }
  
  function show_table(){
    $output = "";
    
    if(isset($_GET['c_id'])){
      // Запоминаем новую позициию в дереве категорий
      if($_GET['c_id'] != 'root'){
        $_SESSION[$this->carusel_name]['c_id'] = $_GET['c_id'];  
      }else{
        $_SESSION[$this->carusel_name]['c_id'] = 0;
      }
    }
    
    $c_id = $_SESSION[$this->carusel_name]['c_id']; 
    
    $output .= $this->getFormStyleAndScript();
    $header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    
    $this->header = $header;
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    $output .= $this->get_show_table_menu_btn( $c_id );
    
    $bread_crumbs = $this->show_bread_crumbs();
    (!is_null($this->admin))?  : $output .=  $bread_crumbs;
    
    $output .=  '
    <h2 class = "cat_header">Категории</h2>';
    
    $output .= $this->show_cat_table($c_id); // Список категорий
    
    #$output .= $this->getFormStyleAndScript(); 
    #$output .= '<h1>'.$this->header.'</h1>';
    $s_sorting = $s_limit = $strPager = $groupOperationsCont = '';
    $s_order = " ORDER BY `ord` ASC ";
    $where = "WHERE `cat_id` =  $c_id";
    
    $s = "
      SELECT COUNT( * ) AS count
      FROM `".$this->prefix.$this->carusel_name."`
      $where
    "; #pri($s);
    $q = $this->pdo->query($s); $r = $q->fetch(); $count_items = $r['count'];
    
    $output .= '
      <h2 class = "items_header">Содержание</h2>';
      
    if($this->is_filter && $count_items) $output  .= $this->getFilterTable($where);
    
    $s = "
      SELECT COUNT( * ) AS count
      FROM `".$this->prefix.$this->carusel_name."`
      $where
    "; #pri($s);
    $q = $this->pdo->query($s); $r = $q->fetch(); $count_items = $r['count'];
    
    if($this->is_pager  && $count_items) $strPager = $this->getPager( $count_items, $s_limit);
    if( $count_items ) $groupOperationsCont = $this->getGroupOperations(); 
    
    if($this->is_pager) $output .= $strPager;
    
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name."`
      $where
      $s_sorting
      $s_order
      $s_limit
    "; #pri($s);

    if(!$count_items) $output .= "<p>Отсутствует</p>";
    
    $output .= '
      <form 
        method="post" 
        action="'.$this->carusel_name.'.php" 
        id="sortSlide"
        class="table-responsive"
      >
        <input type="hidden" name="slideid" value="1">
    ';
    $filter_count_items = 0;
    if($q = $this->pdo->query($s)){
      if($filter_count_items = $q->rowCount()){
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
    <br>';
    $output .=  $this->get_add_btn_show_table();
  	#<center><a class="btn btn-success" href="?adds" id="submit">Добавить</a></center>
    $output .= '
    </form>';
    if($this->is_pager) $output .= $strPager;
    
    return $output;
    
  }
  
  function get_add_btn_show_table(){
    $output = '';
    $output .= '
    <center><a class="btn btn-success " href="?adds" id="submit">Добавить</a></center>';
    
    return $output;
  }
  
  function show_form($item = null, $output = '', $id = null){
    $title = '';
    $output .= '<div class = "c_form_box">';
    
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a href="/'.$url.'" class="btn btn-sm btn-info float-right" style="color:#fff"><i class="icon-eye-open icon-white"></i> Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }
      
      $output .= $this->show_form_row( 'ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']) );
    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; 
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt2"))) $class_input = ' class="ckeditor" '; 
      
      $type = '';
      $create_val = '';
      if( in_array($key, array("color"))) {$type = 'color'; $create_val = '#FFFFFF';} 
      if( in_array($key, array("date"))) { $type = 'date'; $class_input = ' class="form-control" style = "max-width: 180px;" '; }
      if( in_array($key, array("title", "link", "seo_h1", "seo_title", "seo_keywords", "img_alt", "img_title" ))) $type = 'text'; 
      
      if($key == 'cat_id'){
        $tmp  = '<select name="cat_id" class="form-control">';
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
        if($item) {
          $_SESSION[$this->carusel_name]['c_id'] = htmlspecialchars($item[$key]);
          $this->bread = array();
          $this->show_bread_crumbs($item_cat_id);
          if($_SESSION[$this->carusel_name]['c_id']){
            $c_title = db::value('title', '`'.$this->cat_carusel_name.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
            $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
          }
          $title .=' редактирование записи';
          
          $this->title = $title;
        }
        if(!$item_cat_id) $item_cat_id = 0;
        $tmp .= $this->get_category_option($item_cat_id);
        $tmp .= '</select>';
        
        $output .= $this->show_form_row( $val, $tmp);
        continue;
      }
      
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
      $output .= '
        </div>
      </div>
      ';
    }
    $output .= $this->getFormPicture($id, $item);
      
    $output .= ' </div> ';
    
    return $output;
    
  }
   
  function add_slide($item = null){
    $output = $title = "";
    
    $header ='<h1><a href="'.IA_URL.$this->carusel_name.'.php">'.$this->header.'</a></h1>';
    $this->header = $header;
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    $bread_crumbs = $this->show_bread_crumbs();
    (!is_null($this->admin))?  : $output .=  $bread_crumbs;
    
    if($_SESSION[$this->carusel_name]['c_id']){
      $c_title = db::value('title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
      $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
    }
    $title .=' добавление записи';
    #$this->title  = 'Добавление записи';
    $this->title  = $title;
    
    (!is_null($this->admin)) ?  : $output .=  '<h3>'.$title.'</h3><br><br>';
    
    
    if($item){
      $output .='<label class="validate_rerror">Изменения не внесены!</label>';
    }
    $output .= '<div style="/*margin:25px;*/">
                <FORM 
                  method="post" 
                  enctype="multipart/form-data" 
                  action="'.IA_URL.$this->carusel_name.'.php?creates"
                  class="form-horizontal form-label-left"
                >';
    
    if( isset($_GET['copyid']) && intval($_GET['copyid']) ){
      $copyid = intval($_GET['copyid']);
      $copy_item = db::row( '*', $this->prefix.$this->carusel_name, 'id = '.$copyid, null, 0 ); #pri($copy_item);
      $item = $copy_item;
    }
    
    $output .= $this->show_form($item);

    $output .= ' <BR/><BR/><INPUT type="submit" value="сохранить" class="btn btn-success btn-large submit_form" id="submit">';
    $output .= '</FORM></div>';
    
    $sql="SHOW TABLE STATUS LIKE '".$this->prefix.$this->carusel_name."'";
		$result = $this->pdo->query($sql);
    $arr = $result->fetch();
    $nextid=$arr['Auto_increment'];
    
    return $output;
  }
  
  /*function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    $i=0;
      
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
  }*/
  
  function create_slide(){
    $output = "
    <style>
    img {max-width: 992px;}
    .validate_rerror{color: red;font-size: 12px;}
    </style>        
    ";
    
    //if (isset($_POST['title'])){
    $id = '';
    if($this->validationValue($id)){
      
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
        
        $id = $this->pdo->lastInsertId();
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Создание", "create", $this->prefix.$this->carusel_name, $id, ''/*, addslashes($s)*/);
        
      }else{
        echo "s = $s";
        $carusel_error = "Произошла ошибка при СОЗДАНИИ записи в бд функсия create_slide()";
        echo $carusel_error;
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord($carusel_error, "error", $this->prefix.$this->carusel_name, 0, '', addslashes($s) ); 
        exit;
      }
      
			
      if ($name = $this->load_picture($id)){
  		  $this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Загрузка изображения", "load_picture", $this->prefix.$this->carusel_name, $id );
  	  }
      

      $_GET["edits"] = $id;
      $output .= $this->edit_slide($id);
      
    }else{
      $output .= $this->add_slide($_POST);
    }
    
    

  	/*if (!$_POST["save_view"]){
      #header("Location: ?edits=$id");
      $_GET["edits"] = $id;
      $output .= self::edit_slide($id);
    }else header("Location: /");
    */
    
    return $output;
  }
  
  function edit_slide($id, &$item = null){
    $output = $title = "";
    
    $output = "
    <style>
    img {max-width: 992px;}
    .validate_rerror{color: red;font-size: 12px;}
    </style>        
    ";
    
    #$id = intval($_GET["edits"]);
    $header ='<h1><a href="'.IA_URL.$this->carusel_name.'.php">'.$this->header.'</a></h1>';
    $this->header = $header;
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    $bread_crumbs = $this->show_bread_crumbs();
    (!is_null($this->admin))?  : $output .=  $bread_crumbs;
    
    if($_SESSION[$this->carusel_name]['c_id']){
      $c_title = db::value('title', '`'.$this->cat_carusel_name.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
      $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
    }
    $title .=' редактирование записи';
    
    $this->title = $title;
    
    (!is_null($this->admin)) ?  : $output .=  '<h3>'.$title.'</h3><br><br>';
    
    if(is_null($item)){
       $s = "SELECT * FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'";
       $q = $this->pdo->query($s);
       $item = $q->fetch();
    }else{
      $output .='<label class="validate_rerror">Изменения не внесены!</label>';
    }
    
		if($item){
      
      
  		$output .='<FORM method="post" 
                  enctype="multipart/form-data" 
                  action="'.IA_URL.$this->carusel_name.'.php?updates='.$id.'"
                  class="form-horizontal form-label-left"
                >';
      
      //Генерация Url
      if($this->url_item && $id && !isset($_POST['url'])){
        $_POST['url'] = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      $output .= $this->show_form($item, '', $id);
      
      $output .= ' <BR/><BR/><INPUT type="submit" value="сохранить" class="btn btn-success btn-large submit_form" id="submit">';
      $output .= '</FORM>';
      
      //Модуль картинок
      if($this->images_items && $id ){
        $output .= $this->images_items->showImageForm($this->prefix.$this->carusel_name, $id);
      }
      
      //Модуль файлов
      if($this->files_items && $id ){
        $output .= $this->files_items->showFilesForm($this->prefix.$this->carusel_name, $id);
      }
      
    }
    
    return $output;
  }
  
  /*function getUpdateSlide_SqlVals(){
    $sql_vals = ''; $i=0;
    
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
  }*/
  
  function update_slide($id){
    $output = "";
    
		#$id = intval($_GET["updates"]);
    // Если форма прошла валидацию
    if($this->validationValue($id)){

      if ($name = $this->load_picture($id)){
  			$this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Загрузка изображения", "load_picture", $this->prefix.$this->carusel_name, $id, $name );
  		}
		
      $sql_vals = $this->getUpdateSlide_SqlVals();
    
      if($this->log)  // Ведение лога
        $backUpItem = serialize ( db::row("*", $this->prefix.$this->carusel_name, "id = ".$id) );
      
      $s = "UPDATE `".$this->prefix.$this->carusel_name."` SET $sql_vals WHERE `id` = '$id'";
      
      if($this->pdo->query($s)){
        
        if($this->log){ // Ведение лога
          $newBackUpItem = serialize ( db::row("*", $this->prefix.$this->carusel_name, "id = ".$id) );
          if($backUpItem != $newBackUpItem){
            $res_log = $this->log->addLogRecord("Редактирование", "update", $this->prefix.$this->carusel_name, $id, $backUpItem/*, addslashes($s)*/);
          }else{
            $res_log = $this->log->addLogRecord("Просмотр/изменения", "view", $this->prefix.$this->carusel_name, $id/*, $backUpItem/*, addslashes($s)*/);
          }
        }
        
      }else{
        #echo "s = $s <br>";
        $carusel_error = "Произошла ошибка при ОБНОВЛЕНИИ записи в бд функсия update_slide() UPDATE `".$this->prefix.$this->carusel_name."` SET $sql_vals WHERE `id` = '$id'";
        echo $carusel_error;
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord($carusel_error, "error", $this->prefix.$this->carusel_name, $id, $backUpItem, addslashes($s) );
          
        exit;
      }
		
      $output .= $this->edit_slide($id);
      
    }else{
      $output .= $this->edit_slide($id, $_POST);
    }
    
    return $output;
  }
  
  function delete_slide($id, $view = 'show_table' ){
    $output = "";
   	if (isset($id) && $id){
  		#$id = intval($_GET["deletes"]);
      $this->delete_picture($id);
  		
      // Удаление Url, если подключен
      if($this->url_item && $id){
        $this->url_item->deleteUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      // Удаление Дополнительных картинок, если подключены
      if($this->images_items && $id){
        $this->images_items->deleteImageForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      // Удаление Дополнитеьных файлов, если подключен
      if($this->files_items && $id){
        $this->files_items->deleteImageForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      $s = "DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'";
      
      if($this->log){ // Ведение лога
        $backUpItem = serialize ( db::row("*", $this->prefix.$this->carusel_name, "id = ".$id) );
        $res_log = $this->log->addLogRecord("Удаление", "delete", $this->prefix.$this->carusel_name, $id, $backUpItem, addslashes($s));
      }
      
      $this->pdo->query($s);
      
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
    #$output = $this->show_table();
    
    return $output;
  }
  
  // Категории
  function show_cat_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      $url = $this->url_item->getUrlForModuleAndModuleId($this->cat_carusel_name, $id);
      if($url){
        $tmp = '<a class="btn btn-sm btn-info float-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      } 
      $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->cat_carusel_name, $id, $item['title']));
    }
       
    foreach($this->date_cat_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt2"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      $create_val = '';
      if( in_array($key, array("color"))) { $type = 'color'; $create_val = '#FFFFFF'; }
      if( in_array($key, array("date")))  { $type = 'date'; $class_input = ' class="form-control" style = "max-width: 180px;" '; }
      if( in_array($key, array("title", "link", "seo_h1", "seo_title", "seo_keywords", "img_alt", "img_title" ))) $type = 'text';
      
      if($key == 'parent_id'){
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
        if($item) {
          $_SESSION[$this->carusel_name]['c_id'] = htmlspecialchars($item[$key]);
          $this->bread = array();
          $this->show_bread_crumbs($item_cat_id);
          $this->admin->setForName('bread', $this->getForName('bread')); 
        }
        if(!$item_cat_id) $item_cat_id = 0;
        $output .= '<input type = "hidden" name = "parent_id" value = "'.$item_cat_id.'">';
        continue;
      }
      
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
      
      if($item){
        if($type){
          
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'">'
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
      $output .= '
        </div>
      </div>
      ';
    }
    
    $output .= ' </div> ';
    
    $output .= ' Изображение  (Иделальный размер '.$this->img_cat_ideal_width.' x '.$this->img_cat_ideal_height.'):';
    $output .= '<BR/><INPUT type="file" name="picture" id = "fr_picture" value="" class="w100"><BR/>';
    
    return $output;
    
  }
   
  function add_cat_slide($item = null){
    $output = $title = "";
    
    $header ='<h1><a href="'.IA_URL.$this->carusel_name.'.php">'.$this->header.'</a></h1>';
    $this->header = $header;
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    if($_SESSION[$this->carusel_name]['c_id']){
      $c_title = db::value('title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
      $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
    }
    $title .=' добавление каталога ';
    $this->title  = $title;
    
    (!is_null($this->admin)) ?  : $output .=  '<h3>'.$title.'</h3><br><br>';
    
    if($item){
      $output .='<label class="validate_rerror">Изменения не внесены!</label>';
    }
    $output .= '<div style="/*margin:25px;*/">
                <FORM method="post" 
                  enctype="multipart/form-data" 
                  action="'.IA_URL.$this->carusel_name.'.php?createc"
                  class="form-horizontal form-label-left"
                >';

    $output .= $this->show_cat_form();

    $output .= ' <BR/><BR/><INPUT type="submit" value="сохранить" class="btn btn-success btn-large submit_cat_form" id="submit">';
    $output .= '</FORM></div>';
    
    $sql="SHOW TABLE STATUS LIKE '".$this->cat_carusel_name."'";
 		$result = $this->pdo->query($sql);
    $arr = $result->fetch();
    $nextid=$arr['Auto_increment'];
    
    return $output;
  }
  
  function getCreateCatSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    $i=0;
      
    foreach($this->date_cat_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      if( in_array( $key, $this->checkbox_cat_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
  }
  
  function create_cat_slide(){
    $output = "
    <style>
    img {max-width: 992px;}
    .validate_rerror{color: red;font-size: 12px;}
    </style>        
    ";
    
    //if (isset($_POST['title'])){
    if(!isset($id)) $id = '';
    if($this->validationCatValue($id)){
      
      $sql_names = ''; $sql_vals = ''; 
      
      $this->getCreateCatSlide_SqlNames_SqlVals($sql_names, $sql_vals);
      
      if(
        $this->pdo->query(
          $s = "
            INSERT INTO `".$this->cat_carusel_name."` ($sql_names) 
            VALUES                                    ($sql_vals)
          "
        )
      ){
        $id = $this->pdo->lastInsertId();
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Создание", "create", $this->cat_carusel_name, $id, ''/*, addslashes($s)*/);
        
      }else{
        echo "<pre>s = $s</pre>";
        $carusel_error = "Произошла ошибка при СОЗДАНИИ записи в бд функсия create_cat_slide()";
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord($carusel_error, "error", $this->cat_carusel_name, 0, '', addslashes($s) );
          
        exit;
      }
      

      if ($name = $this->load_cat_picture($id)){
  		  $this->pdo->query("UPDATE `".$this->cat_carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
        
        if($this->log)// Ведение лога
          $res_log = $this->log->addLogRecord("Загрузка изображения", "load_picture", $this->cat_carusel_name, $id );
      
  	  }
      

      $_GET["editc"] = $id;
      $output .= $this->edit_cat_slide($id);
      
    }else{
      $output .= $this->add_cat_slide($_POST);
    }
    
    

  	/*if (!$_POST["save_view"]){
      #header("Location: ?edits=$id");
      $_GET["edits"] = $id;
      $output .= self::edit_slide($id);
    }else header("Location: /");
    */
    
    return $output;
  }
  
  function edit_cat_slide($id, $item = null){
    $output = $title = "";
    
    $output = "
    <style>
    img {max-width: 992px;}
    .validate_rerror{color: red;font-size: 12px;}
    </style>        
    ";
    
    #$id = intval($_GET["edits"]);
    if( !isset($header) ) $header = '';
    $header .='<h1><a href="'.IA_URL.$this->carusel_name.'.php">'.$this->header.'</a></h1>';
    $this->header = $header;
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    /*if($_SESSION[$this->carusel_name]['c_id']){
      $c_title = db::value('title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
      $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
    }*/
    
    if($id){
      $c_title = db::value('title', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "id = ".$id );
      $title .='<a href="?c_id='.$id.'"> '.$c_title.' </a> → '; 
    }
    
    $title .=' редактирование каталога';
    
    $this->title  = $title;
    (!is_null($this->admin)) ?  : $output .=  '<h3>'.$title.'</h3><br><br>';
    
    if(is_null($item)){
       #$item =  mysql_fetch_array(mysql_query("SELECT * FROM `".$this->cat_carusel_name."` WHERE `id` = '$id'"));
       $s = "SELECT * FROM `".$this->cat_carusel_name."` WHERE `id` = '$id'";
       $q = $this->pdo->query($s);
       $item = $q->fetch();
    }else{
      $output .='<label class="validate_rerror">Изменения не внесены!</label>';
    }
    
		if($item){
      
      
  		$output .='<FORM 
                  method="post" enctype="multipart/form-data" 
                  action="'.IA_URL.$this->carusel_name.'.php?updateс='.$id.'"
                  class="form-horizontal form-label-left"
                >';
      
      //Генерация Url
      if($this->url_item && $id && !isset($_POST['url'])){
        $_POST['url'] = $this->url_item->getUrlForModuleAndModuleId($this->cat_carusel_name, $id);
      }
      
      $output .= $this->show_cat_form($item, '', $id);
      
      $item_img = '';
      if( isset($item['img']) ) $item_img = $item['img'];
      
      if(!$item_img){
        #$item_img = db::value('img', $this->cat_carusel_name, "id = $id");
        $s = "SELECT img FROM `".$this->cat_carusel_name."` WHERE `id` = $id";
        $q = $this->pdo->query($s);
        $r = $q->fetch();
        $item_img = $r['img'];
        
      }
      
      if ($item_img !== ''){
        $output .= '<p>загружено:'.$item_img.'</p>
        <div class = "cat_img_box">
          <img class = "cat_img_item" src="/images/'.$this->carusel_name.'/cat/slide/'.$item_img.'" >
        </div>';
  		  $output .= $this->getDeleteCatImgBtn($id);
      }
      
      $output .= ' <BR/><BR/><INPUT type="submit" value="сохранить" class="btn btn-success btn-large submit_cat_form" id="submit">';
      $output .= '</FORM>';
      
      //Модуль картинок
      if($this->images_items && $id ){
        $output .= $this->images_items->showImageForm($this->cat_carusel_name, $id);
      }
      
      //Модуль файлов
      if($this->files_items && $id ){
        $output .= $this->files_items->showFilesForm($this->cat_carusel_name, $id);
      }
    }
    
    return $output;
  }
    
  function getDeleteCatImgBtn($id){
    $output .= '';
    
    $output .= '[ <A href="'.IA_URL.$this->carusel_name.'.php?delete_picture_c=1&id='.$id.'" onClick="javascript: if (confirm('."'Удалить картинку?')) { return true;} else { return false;}\"".' class = "delete_cat_img_btn"  style = "color:red;">X удалить</A> ]<BR/>';
    
    return $output;
  }
  
  function getUpdateCatSlide_SqlVals(){
    $sql_vals = ''; $i=0;
    
    foreach($this->date_cat_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      if( in_array( $key, $this->checkbox_cat_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
  }
  
  function update_cat_slide($id){
    $output = "";
    
		#$id = intval($_GET["updateс"]);
    // Если форма прошла валидацию
    if($this->validationCatValue($id)){

      if ($name = $this->load_cat_picture($id)){
  			$this->pdo->query("UPDATE `".$this->cat_carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Загрузка изображения", "load_picture", $this->cat_carusel_name, $id, $name );
          
  		}
  		
      $sql_vals = $this->getUpdateCatSlide_SqlVals();
      
      if($this->log)  // Ведение лога
        $backUpItem = serialize ( db::row("*", $this->cat_carusel_name, "id = ".$id) );
       
      if($this->pdo->query("UPDATE `".$this->cat_carusel_name."` SET $sql_vals WHERE `id` = '$id'")){
        
        if($this->log){ // Ведение лога
          $newBackUpItem = serialize ( db::row("*", $this->cat_carusel_name, "id = ".$id) );
          if($backUpItem != $newBackUpItem){
            $res_log = $this->log->addLogRecord("Редактирование", "update", $this->cat_carusel_name, $id, $backUpItem/*, addslashes($s)*/);
          }else{
            $res_log = $this->log->addLogRecord("Просмотр/изменения", "view", $this->cat_carusel_name, $id/*, $backUpItem/*, addslashes($s)*/);
          }
        }
        
      }else{
        $carusel_error = "Произошла ошибка при ОБНОВЛЕНИИ записи в бд функсия update_slide()";
        echo $carusel_error;
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord($carusel_error, "error", $this->cat_carusel_name, $id, $backUpItem, addslashes($s) );
          
        exit;
      }
		
      $output .= $this->edit_cat_slide($id);
      
    }else{
      $output .= $this->edit_cat_slide($id, $_POST);
    }
    
    return $output;
  }
  
  function load_cat_picture($id){
    $picture_uploaded = FALSE;
		
    if (isset($_FILES["picture"])){
		  $filename = $_FILES["picture"]["name"];
			$tmpname =  $_FILES["picture"]["tmp_name"];
			$exts = explode('.', $filename);
		
    	if (count($exts)){
				$new_filename = 'slide_'.$id.'.'.$exts[count($exts)-1];
			}else{
				$new_filename = 'slide_'.$id;
			}
		
    	if (is_uploaded_file($tmpname)){
		
      	if ($_FILES['picture']['name']) {
  				$time=time();
  				$e=explode(".",$_FILES['picture']['name']);
  				$type=end($e);
  				$big_filename="../images/".$this->carusel_name."/cat/orig/$time.".$type;
  				$new_filename="../images/".$this->carusel_name."/cat/temp/$time.".$type;
          $to="../images/".$this->carusel_name."/cat/slide/$time.".$type;
  				$name=$time.".".$type;
  				move_uploaded_file($_FILES['picture']['tmp_name'], $new_filename);
  				copy ($new_filename,$big_filename);
  				$this->resize($new_filename, $to, $type, $this->img_cat_ideal_width);
  				unlink($new_filename);
  				$picture_uploaded = TRUE;
  			}
	  	}
	  }
    if($picture_uploaded){
      return $name;
    }else{
      return $picture_uploaded;
    }
  }
  
  function delete_cat_picture($id){
    $output = "";
    
	  # удаляем картинку в категории
	  $c_path="../images/".$this->carusel_name."/cat/orig/";
	  #$id=intval($_GET[id]);
	  # select filename
	  # delete pic_filename
	  # update
		$string="select img from `".$this->cat_carusel_name."` where `id`=$id";	
		$q = $this->pdo->query($string);
    $r = $q->fetch();
    $pic_filename = $r['img'];
    #$pic_filename= mysql_result(mysql_query($string),0);
		if (is_file($c_path.$pic_filename)){
			unlink($c_path.$pic_filename);
		}
    $c_path="../images/".$this->carusel_name."/cat/slide/";
    if (is_file($c_path.$pic_filename)){
			unlink($c_path.$pic_filename);
		}
		$string="update `".$this->cat_carusel_name."` set img='' where `id`='$id'";
		$this->pdo->query($string);
		#print	"<B>Картинка $pic_filename удалена</B><BR>";
    
    $output = $this->edit_cat_slide($id);
    
    return $output;
  }
  
  function delete_cat_slide($id, $view = 'show_table'){
    $output = "";
    if (isset($id) && $id){
  		#$id = intval($_GET["deletec"]);
      $this->delete_picture($id);
  		
      // Удаление Url, если подключен
      if($this->url_item && $id){
        $this->url_item->deleteUrlForModuleAndModuleId($this->cat_carusel_name, $id);
      }
      
      // Удаление Дополнительных картинок, если подключены
      if($this->images_items && $id){
        $this->images_items->deleteImageForModuleAndModuleId($this->cat_carusel_name, $id);
      }
      
      // Удаление Дополнитеьных файлов, если подключен
      if($this->files_items && $id){
        $this->files_items->deleteImageForModuleAndModuleId($this->cat_carusel_name, $id);
      }
      $s = "DELETE FROM `".$this->cat_carusel_name."` WHERE `id` = '$id'";
      if($this->log){ // Ведение лога
        $backUpItem = serialize ( db::row("*", $this->cat_carusel_name, "id = ".$id) );
        $res_log = $this->log->addLogRecord("Удаление", "delete", $this->cat_carusel_name, $id, $backUpItem, addslashes($s));
      }
      
      $this->pdo->query($s);
    }
    
    switch($view){
      case 'show_table':
        $output .= $this->show_table();
        break;
        
      case 'ajax':
        $output .= "ok";
        break;
    }
    
    return $output;
  }
 
  // END Категории
  
  function view_tree(){
    $output = '';
    
    if( isset($_SESSION[$this->carusel_name]['c_id']) && $_SESSION[$this->carusel_name]['c_id'] ){
      $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
      $this->bread = array();
      $this->show_bread_crumbs($item_cat_id);
      $this->admin->setForName('bread', $this->getForName('bread')); 
    }
    $this->title = 'Дерево всех категорий'; 
    $this->header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    
    $output .= $this->get_show_table_menu_btn();
    $output .= '<table class="catalog" width="990"><tr><td style="text-align: left;">';
		$output .= '<div class="well">';
		$output .= $this->show_tree_catalog();
		$output .= '</table>';
    
    return $output;
  }
  
  function show_tree_catalog(){
    
    $output = $count_items_arr = $category_arr = '';
    $total_cat = $total_item = 0;
    
    $tbl_cat = $this->cat_carusel_name;
    $tbl     = $this->prefix.$this->carusel_name;
    
    $s_count = "
      SELECT    `cat_id`, count(*) AS `cnt`
      FROM      `$tbl`
      GROUP BY  `cat_id`
    "; #pri($s_count); 
    
    if($q_count = $this->pdo->query($s_count)){
      if ( $q_count->rowCount() ){
        $count_items_arr = array();
        while($r = $q_count->fetch()){
          $count_items_arr[$r['cat_id']] = $r['cnt'];
          $total_item += $r['cnt'];
        }
      }
    }
    
    $s = "
      SELECT    `$tbl_cat`.*
      FROM      `$tbl_cat`
      ORDER BY  `$tbl_cat`.`parent_id` ASC,
                `$tbl_cat`.`ord` ASC
    "; #pri($s);
    
    
    if($q = $this->pdo->query($s)){
      if ( $q->rowCount() ){
        $category_arr = array();
        while($row = $q->fetch()){
          if($row["parent_id"] === NULL) $row["parent_id"] = 0; 
          $category_arr[$row["parent_id"]][] = $row;
          $total_cat++;
        }
      }
    } 
    
    $output .= '
      <div class = "py-2"><b>Всего:</b> разделов: <b>'.$total_cat.'</b>; позиций: <b>'.$total_item.'</b>;</div>
    ';
    $output .= $this->out_tree(0, 0, $category_arr, $count_items_arr);
     
    return $output;
  }
  
  function out_tree($parent_id, $level, &$category_arr, &$count_items_arr) {
    $output = '';
           
    if (isset($category_arr[$parent_id])) {              
        foreach ($category_arr[$parent_id] as $value) {  
          $count = 0;
          $lnk = '?c_id='.$value['id'];
          
          if(isset($count_items_arr[$value['id']]) && $count_items_arr[$value['id']]){
            $count = $count_items_arr[$value['id']];
          }
          #<div style = "margin-left:'.($level * 25).'px;">
          #$output .= '
          #  <div style = "">';
          #for($i=0; $i < $level; $i++ ){
          #  if($i == ($level - 1 ) ){
          #    $output .= ' &rarr; ';
          #  }else{
          #    $output .= ' &mdash; ';
          #  }
          #}
          $output .= '
            <div style = "margin-left:'.($level * 25).'px;">';
          if($level){
            $output .= '
              <sub style = "color: #ccc;">'.$level.'</sub>';
          }
          $output .= '
              <span style = "color: #aaa;">'.$value['id'].'</span> 
              <a href = "'.$lnk.'" target = "_blank" title = "Перейти в категорию">'.$value['title'].'</a> ('.$count.')
              <a href="?editc='.$value['id'].'" class="btn btn-info btn-sm px-1 py-0 mt-1" title = "Редактировать категорию">
                <i class="fas fa-pencil-alt"></i>  
              </a>';   
          if( isset($value['markup']) && $value['markup'] ) $output .= '(<span style = "color: red;">Наценка: '.$value['markup'].' %</span> )';
          $output .= '
            </div>';
            
          $level   = $level + 1;                            
          $output .= $this->out_tree($value["id"], $level, $category_arr, $count_items_arr);
          $level   = $level - 1;                             
        }
    }
    
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
    
    $output .= $this->full_tree_content();
    
    return $output;
  }
  
  function full_tree_content(){
    $output = '';
    
    $output .= '
		  <script>
		  $(function(){
        
			  $("#article").keyup(function(){
			    var q=$(this).val();
			    $.post("'.$this->carusel_name.'.php?ajx&act=search", {que:q}).done(function( data ){
				  	$("#exists").html(data);
				  });
		    });

      });
      
      function star_check(id, field) {
    		$.post(\''.$this->carusel_name.'.php?ajx&act=star_check\', {id:id, field:field}, function(data) {
    			if (data == 1) {
    				$("#"+field+"_"+id).removeClass("far fa-star");
    				$("#"+field+"_"+id).addClass("fas fa-star");
    			} else {
    				$("#"+field+"_"+id).removeClass("fas fa-star");
    				$("#"+field+"_"+id).addClass("far fa-star");
    			}
    		});
	    };
      
		  </script>

      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-12 col-md-7 col-lg-8">
            <div class="row">
              <div class="col-xs-12">';
    $output .= $this->show_entrie_catalog_content();
    #$output .= $this->show_entrie_catalog(); 
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
      var popover = new bootstrap.Popover(document.querySelector(".posit"), {
        container: "body"
      }); 
      $(function(){
        $(".posit").popover({
          trigger:   "hover",
          placement: "left",
          html:      true,
          offset:    1 
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
      .popover.fade:not(.show) {
        opacity: 1; 
      }
      
      </style>
    ';
    
    return $output;
  }
  
    function show_entrie_catalog_content( ){
    $output = '';
    $tbl_cat      = $this->cat_carusel_name;
    $tbl          = $this->prefix.$this->carusel_name;
    $category_arr = $items_arr  = array();
    $total_cat    = $total_item = 0;
    
    $s_item = "
      SELECT    *
      FROM      `$tbl`
      ORDER BY  `$tbl`.`cat_id` ASC,
                `$tbl`.`ord` ASC
    "; #pri($s_count); 
    
    if($q_item = $this->pdo->query($s_item)){
      if ( $q_item->rowCount() ){
        while($r = $q_item->fetch()){
          $items_arr[$r['cat_id']][] = $r;
          $total_item++; #pri($r['cat_id']);
        }
      }
    } # pri($items_arr[357]);
    
    $s = "
      SELECT    `$tbl_cat`.*
      FROM      `$tbl_cat`
      ORDER BY  `$tbl_cat`.`parent_id` ASC,
                `$tbl_cat`.`ord` ASC
    "; #pri($s);
    
    
    if($q = $this->pdo->query($s)){
      if ( $q->rowCount() ){
        while($row = $q->fetch()){
          if($row["parent_id"] === NULL) $row["parent_id"] = 0; 
          $category_arr[$row["parent_id"]][] = $row;
          $total_cat++;
        }
      }
    }
    
    $output .= '
      <div class = "py-2"><b>Всего:</b> разделов: <b>'.$total_cat.'</b>; позиций: <b>'.$total_item.'</b>;</div>';
      
    $output .= $this->show_entrie_catalog_v2( 0, 0, $category_arr, $items_arr );
    
    return $output;
  }
  
    function show_entrie_catalog_v2( $parent_id, $level, &$category_arr, &$items_arr ) {
    
		$output = '';
    
    if (isset($category_arr[$parent_id])) {              
      foreach ($category_arr[$parent_id] as $value) {  
        $count = 0; 
        $itms_str = $liclass = "";
        $lnk = '?c_id='.$value['id'];
        
        if(isset($items_arr[$value['id']]) && $items_arr[$value['id']]){
          $count = count($items_arr[$value['id']]); #pri($count);
          
          $itms_str .= '
            <div class = "listitem_box" style = "margin-left: 15px;">';
    			foreach ( $items_arr[$value['id']] as $k_item => $v_item ){ 
            #$count++;
    				$itms_str .= $this->get_full_tree_line($v_item);
    			}
    			$itms_str .= '
            </div>'; 
        }
        
        if ($value['hide']) $liclass = "subhide";
        
        $output .= '
          <div class = "listingb" style = "margin-left:'.($level * 25).'px;">';
        
        $output .= '
            <div class = "'.$liclass.'">';
        if($level) $output .= '<sub style = "color: #ccc;">'.$level.'</sub>';
        
        $output .= '
            <span class = "label">'.$value['id'].'</span>';
        if($value['img']){
          $output .= '
            <span class = "posit label" 
                  data-content = \'<img style="max-height:150px; max-width:150px;" src = "/images/'.$this->carusel_name.'/cat/slide/'.$value['img'].'">\'>
              image
            </span> &nbsp;
          ';
        }
        $output .= '
            <a href = "'.$lnk.'" target = "_blank">
              <strong>'.$value['title'].'</strong>
            </a> 
            ('.$count.')';   
        $output .= '
            </div>';
        $output .= $itms_str;
        
        $output .= '
          </div>';
          
        $level   = $level + 1;                            
        $output .= $this->show_entrie_catalog_v2($value["id"], $level, $category_arr, $items_arr);
        $level   = $level - 1;                             
      }
    }
    
    return $output;
	}
  
    function get_full_tree_line( $item ){
    $output = '';
    
    $output .= '
      <div style = "listitem_item">
        <span class = "label" >
          '.$item['id'].'
        </span> 
    ';
    if($item['img']){
      $output .= '
        <span class = "posit label" 
              data-container = "body" 
              data-toggle    = "popover"
              data-placement = "top" 
              data-content   = \'<img style="max-height:150px; max-width:150px;" src = "/images/'.$this->carusel_name.'/slide/'.$item['img'].'">\' 
        > 
        
          image
        </span> &nbsp;
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
    if(isset($item['price'])){
      $output .= '
        <span class="badge badge-success">'.$item['price'].'</span>';
    }
    if(isset($item['price_ye'])){
      $output .= '
        <span class="badge badge-success">'.$item['price_ye'].'</span>';
    }
    $output .= '
      </div>';
    
    return $output;
  }
  
  
  function show_tree_catalog_old($parent = 0, $output = '') {
		
		#$list = db::select('id, title', '`'.$this->cat_carusel_name.'`', "parent_id = $parent", "`ord`");
    $s = "
      SELECT `id`, `title`
      FROM `".$this->cat_carusel_name."`
      WHERE `parent_id` = $parent
      ORDER BY `ord`
    ";
	  $q = $this->pdo->query($s);
    $list = $q->fetchAll();
		if (!$list) return; #mysql_error(); 
		
    $output .= '
      <ul style = "padding-left: 15px;">';
      #<ul style = "margin: 0 0 0px 15px;">';
		foreach ($list as $item) {
			extract($item);
			if (!$title) continue;
			$output .= '
        <li style="list-style:none;  padding-left: 15px;">
      ';
			#$c = db::value('count(*)', '`'.$this->prefix.$this->carusel_name.'`', "cat_id = $id");
      $c = 0;
      $s = "
        SELECT count(*) as count
        FROM `".$this->prefix.$this->carusel_name."`
        WHERE `cat_id` = $id
      ";
      $q = $this->pdo->query($s);
      if($q->rowCount()){
        $r = $q->fetch();
        $c = $r['count'];
      }
			$lnk = "?c_id=$id";
			$output .= '<a href="'.$lnk.'">'.$title.'</a> ('.$c.') ';
      $output .= '
        </li>
      ';
			$output .= $this->show_tree_catalog($id, '');
			
		}
		$output .= '
      </ul>
    ';
    return $output;
	}
  
  function full_tree_old(){
    $output = '';
    
    if( isset($_SESSION[$this->carusel_name]['c_id']) && $_SESSION[$this->carusel_name]['c_id'] ){
      $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
      $this->bread = array();
      $this->show_bread_crumbs($item_cat_id);
      $this->admin->setForName('bread', $this->getForName('bread')); 
    }
    $this->title = 'Полный каталог'; 
    $this->header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    
    $output .= $this->get_show_table_menu_btn( );
     
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
              <div class="col-12 alert alert-info">';
	  $output .= $this->show_entrie_catalog_old();
    $output .= '
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-5 col-lg-4">
            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label>Поиск</label>
                  <input type="text" class="text form-control" name="article" id="article" placeholder="Запрос..."> 
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-12" id="exists"></div>
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
      .container{
        width: 100%;
      }
      </style>
    ';
    
    return $output;
  }
  
  function show_entrie_catalog_old($parent = 0) {
    $output = '';
		#$list = db::select('id, title, `hide`, `img`', '`'.$this->cat_carusel_name.'`', "parent_id = $parent", "`ord`");
    
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
			foreach ($listing as $posi)
			{
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
            <a href="?edits='.$posi['id'].'">'.$posi['title'].'</a>';
        if(isset($item['price'])){
          $output .= '<span class="badge badge-success">'.$posi['price'].'</span>';
        }
        if(isset($item['price_ye'])){
          $output .= '
            <span class="badge badge-success">'.$posi['price_ye'].'</span>';
        }
        $output .= '
          </li>
        ';
			}
			$output .= '</ul>';
			$output .= $this->show_entrie_catalog_old($id);
			$output .= '</li>';
		}
		$output .= '</ul>'; 
    
    return $output;
	}
  
  function count_goods($id, $c = 0){
    if(!$id) return;
    $s = "
      SELECT count(*) as count
      FROM ".$this->prefix.$this->carusel_name."
      WHERE `cat_id` = $id
    ";
    $q = $this->pdo->query($s);
    $r = $q->fetch();
    #$count=$c+intval(db::value('count(*)', '`'.$this->prefix.$this->carusel_name.'`', "cat_id = $id"));
    
    $count=$c+intval($r['count']);
    
    /*$s = "
      SELECT count(*) as count
      FROM `".$this->prefix.$this->carusel_name.'_cat'."`
      WHERE `parent_id` = $id
    ";
    $q = $this->pdo->query($s);
		#$inn=db::select('*', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "`parent_id` = $id");
    $inn = $q->fetchAll();
		// print_r($inn);
		if (count ($inn)) 
      foreach ($inn as $va){
		  	$count = $this->count_goods($va[id], $count);
		  }*/
      
		return $count;
	}
  
  function search(){
    $output = "";
    $que=trim($_POST["que"]);
  	$zap=explode(" ",$que);

  	// die();
  	if (count ($zap))
  	{
  		foreach ($zap as $v)
  		{
  			$h[]="`".$this->prefix.$this->carusel_name."`.`title` LIKE '%$v%'";
  		}
  		$where=implode (" AND ",$h);
  	
  	
    	// $que=mysql_real_escape_string($_POST["que"]);
    	$s = "
      SELECT 
          `".$this->prefix.$this->carusel_name."`.`title` AS it_title,
          `".$this->prefix.$this->carusel_name."`.`img`,
          `".$this->prefix.$this->carusel_name."`.`cat_id`, 
          `".$this->prefix.$this->carusel_name."`.`hide`,  
          `".$this->prefix.$this->carusel_name."`.`longtxt2`, 
          `".$this->prefix.$this->carusel_name."`.`id` AS id,
          `".$this->cat_carusel_name."`.`title` AS cat_title
        FROM `".$this->prefix.$this->carusel_name."` 
        LEFT JOIN `".$this->cat_carusel_name."` 
        ON `".$this->prefix.$this->carusel_name."`.`cat_id`=`".$this->cat_carusel_name."`.`id` 
        WHERE 
          $where 
        LIMIT 30
      ";
      #echo "<pre>".$s."</pre>";
      
    
    	$b = $this->pdo->query( $s ) or die(mysql_error());
        
    	$zap=implode("|",$zap);
    	if ( $b->rowCount() ){
        $output .= '
        <table id="carusel_search_table" class="table table-striped ">
          <tbody>
            <th style="max-width: 100px;">#</th>
            <th style="width: 60px;">Карт.</th>
      		  <th>Результат</th>
          </tbody>';
        
      	while ($a = $b->fetch()){
      		extract ($a);
      		// foreach ($zap as $v)
      		// {
      			// $it_title=str_ireplace($v,"<b>$v</b>",$it_title); 
      		// }
      		//$it_title=preg_replace("/($zap)/su","<b>\\1</b>",$it_title);
      		if ($hide) $hid="style='opacity:0.5'"; else $hid="";
      		$descr=strlen(strip_tags($longtxt2))." символов";
          $path_link = '<a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a> '.$this->get_bread_crumbs($cat_id);
          
      		$output .= '
            <tr>		
              <td>'.$id.'</td>
              <td>';
          if($img)$output .= '      <img style = "max-height:50px; max-width:50px;" src="/images/'.$this->carusel_name.'/slide/'.$img.'">';
          $output .= "
              </td>
              <td>
                категория: $path_link<br>
                <a  $hid class='posit' href='?edits=$id'>$it_title</a><br>
                $descr
              </td>
            </tr>";
      	}
        $output .= '
          </tbody>
        </table>';
    	}
  	}
    
     
    return $output;
    
  }
  
  function getPagerScript(){
    $output = '
      <script type="text/javascript">
        $(document).ready(function() {  
        
          $(".items-per-page", this).change(function() {
            var perPage = $(this).val();
            var id_cat = $(this).data("id_cat");
            $.post( "'.$this->carusel_name.'.php?ajx&act=ajx_pager", 
                    { pager_act: "set_per_page", per_page: perPage, id_cat: id_cat}, 
                    function(data) {
                      if (data == "ok") {
                        location.reload();
                      }else{
                        alert("getPagerScript error");
                      }
                    }
                  );
          });
          
          $(".set_pager_page", this).click(function() {
            var page = $(this).data("page");
            var id_cat = $(this).data("id_cat");
            $.post( "'.$this->carusel_name.'.php?ajx&act=ajx_pager", 
                    { pager_act: "set_page", page: page, id_cat: id_cat}, 
                    function(data) {
                      if (data == "ok") {
                        location.reload();
                      }else{
                        alert("getPagerScript error");
                      }
                    }
                  );
          });
          
        });
      </script>
    ';
    return $output;
  }
  
  function getPager($countItems, &$offset = 0, $id_cat = 0, $show_pager_always = true){
    $output = '';
    
    #$this->resetPagerParamers();
    #$_SESSION['pager'][$this->carusel_name]['perPage'] = 5;
    
    $this->getPagerParamers();
    #pri($this->pager);
    #return;
    $itemsPerPage = $this->pager['perPage'];
    
    if($this->pager['url'] != $id_cat){
      $_SESSION['pager'][$this->carusel_name]['page'] = $this->pager['page'] = 1;
      $_SESSION['pager'][$this->carusel_name]['url'] = $this->pager['url'] = $id_cat;
      $this->pager['page'] = 1;
    }
      
    
    $page = $this->pager['page'];
    #echo " page = ".$page;
    
    if(($countItems > $itemsPerPage) || $show_pager_always) {
      $pageLinks = ceil($countItems / $itemsPerPage);
      
      $output .= '
        <div class = "pagination_row row align-items-center ">
          <div class = "col-auto" >Всего: '.$countItems.'</div>
          <div class = "col">
            <select class="form-control items-per-page"  data-id_cat = '.$id_cat.'
            
              style = "
                width: 65px;
                height: 33px;
                padding: 2px 5px;
              "
            >';
       
      foreach($this->pager['items_per_page'] as $k => $v){
        $output .= '  
              <option value = "'.$v.'" '; if($itemsPerPage == $v) $output.= 'selected'; $output .= ' >'.$v.'</option>';
          
      }
      
      $output .= '
            </select>
          </div>';

      $output .= '
          <div class = "col-auto" >
            <ul class="pagination pagination-sm">
      ';
      $output .= '<li ';
      if($page <= 1){ $output .= ' class="page-item disabled" '; }
      $output .= ' > <a ';
      if($page >= 1){ $output .= ' class=" set_pager_page" data-page = '.($page-1).' data-id_cat = '.$id_cat.' style = "cursor: pointer;" '; /*$output .= '?page='.($page-1).''; */}
      
      $output .= '" aria-label="Previous">
                  <span class = "page-link"">&laquo;</span>
                </a>
              </li>';
      
      $minusItems = $plusItems = 5;
      if($page <= 6 ){
        $minusItems = 10 - $page;
      }
      if($page >= ($pageLinks - 6) ){
        $plusItems = 10 - ($pageLinks - $page) ;
      }
      
      for($i = 1; $i <= $pageLinks; $i++){
        if( (($i - $minusItems) <= $page) && ($page <= ($i + $plusItems) ) ){
          if($i == $page){
            $output .= '
                <li class="page-item active"><span class = "page-link">'.$i.'</span></a></li>';
              
          }else{
            $output .= '
                <li class="page-item"><a class="page-link set_pager_page" data-page = '.$i.' data-id_cat = '.$id_cat.' style = "cursor: pointer;">'.$i.'</a></li>';  
                
          }
        }
      }
      
      $output .= '<li ';
      if($page >= ($pageLinks ) ){ $output .= ' class="page-item disabled" '; }
      $output .= ' > <a  ';
      if($page <= ($pageLinks ) ){ $output .= ' class="page-link set_pager_page" data-page = '.($page+1).' data-id_cat = '.$id_cat.' style = "cursor: pointer;" '; /*$output .= '?page='.($page+1).''; */}
      $output .= '
               aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>';
      $output .= '
            </ul>
          </div> 
        </div>'; 
      
      if($page){
        $offset = ' LIMIT '.($page - 1)*$itemsPerPage.', '.$itemsPerPage.' ';
      }else{
        $offset = ' LIMIT '.$itemsPerPage.' ';
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
    
    if(isset($_GET['ajx'])){
      if (isset($_SESSION["WA_USER"])){
        
        if(isset($_GET['act'])){
          if($_GET['act'] == 'star_check'){
            $carisel->star_check();
          }elseif($_GET['act'] == 'sort_item'){
            $carisel->sort_item();
          }elseif($_GET['act'] == 'ajx_pager'){
            echo $carisel->ajx_pager();
          }
          // Категории
          elseif($_GET['act'] == 'star_cat_check'){
            $carisel->star_cat_check();
          }elseif($_GET['act'] == 'sort_cat_item'){
            $carisel->sort_cat_item();
          }elseif($_GET['act'] == 'delete_item'){
            echo $carisel->delete_slide(intval($_POST['del_id']), 'ajax');
          }elseif($_GET['act'] == 'delete_cat_item'){
            echo $carisel->delete_cat_slide(intval($_POST['del_id']), 'ajax');
          }elseif($_GET['act'] == 'croppImg'){
            echo $carisel->croppImg();
          }
          // END Категории
          elseif($_GET['act'] == 'search'){
            echo $carisel->search();
          }
        }

      }
      
    }else{
      
      #$output='<div style="padding:20px 0;">';
        
      if (isset($_SESSION["WA_USER"])){
        
        if(isset($_GET['view_tree'])){
          $output .= $carisel->view_tree();
        }elseif(isset($_GET['full_tree'])){
          $output .= $carisel->full_tree();
        }
        
        // Запись
        elseif(isset($_GET['adds'])){
          $output .= $carisel->add_slide();
        }elseif(isset($_GET['creates'])){
          $output .= $carisel->create_slide();  
        }elseif(isset($_GET["edits"])){
          $output .= $carisel->edit_slide(intval($_GET["edits"]));  
        }elseif(isset($_GET["updates"]) && isset($_POST['title'])){
          $output .= $carisel->update_slide(intval($_GET["updates"]));  
        }elseif($_GET['act'] == 'delete_item'){
          echo "test";
          die();
          echo $carisel->delete_slide(intval($_POST['del_id']), 'ajax');
        }elseif(isset($_GET["delete_picture"])&&isset($_GET['id'])){
          $output .= $carisel->delete_picture(intval($_GET['id']));  
        }elseif(isset($_GET["deletes"])){
          $output .= $carisel->delete_slide(intval($_GET['deletes']));  
        }
        // END Запись
     
        // Категории
        elseif(isset($_GET['addc'])){
          $output .= $carisel->add_cat_slide();
        }elseif(isset($_GET['createc'])){
          $output .= $carisel->create_cat_slide();  
        }elseif(isset($_GET["editc"])){
          $output .= $carisel->edit_cat_slide(intval($_GET["editc"]));  
        }elseif(isset($_GET["updateс"]) && isset($_POST['title'])){
          $output .= $carisel->update_cat_slide(intval($_GET["updateс"]));  
        }elseif(isset($_GET["delete_picture_c"])&&isset($_GET['id'])){
          $output .= $carisel->delete_cat_picture(intval($_GET['id']));  
        }elseif(isset($_GET["deletec"])){
          $output .= $carisel->delete_cat_slide(intval($_GET['deletec']));  
        }
        // END Категории
        else{
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
  
  // END BACKEND function

   
 	// FRONTEND function
    
  // END FRONTEND function
  
}



?>