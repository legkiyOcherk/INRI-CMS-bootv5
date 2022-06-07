<?php

require_once __DIR__."/formvalidator.php"; // Валидатор


class BaseCarusel{
  
  // Настройка модуля (меняется при установке модуля)
  var $prefix = DB_PFX; 
  
  var $max_img_width = 1920; // Максимальная ширина картинки
  var $max_img_quality = 90; // Оптимизация картинки
  // End Настройка модуля
  var $header = 'Слайдер';
  var $title;
  var $bread;
  var $cont_footer = '';
  var $admin = null;
  
  var $checkbox_array = array( 'hide' );
      
  // Инициалицация
  function create_img_dir(){
    if (!is_dir("../images")){
      mkdir("../images");
      chmod ("../images", 0777);
    }
    
    if (!is_dir("../images/".$this->carusel_name)){
      mkdir("../images/".$this->carusel_name);
      chmod ("../images/".$this->carusel_name, 0755);
    }
    
    if (!is_dir("../images/".$this->carusel_name."/orig")){
      mkdir("../images/".$this->carusel_name."/orig");
      chmod ("../images/".$this->carusel_name."/orig", 0755);
    }
    
    if (!is_dir("../images/".$this->carusel_name."/slide")){
      mkdir("../images/".$this->carusel_name."/slide");
      chmod ("../images/".$this->carusel_name."/slide", 0755);
    }
    
    if (!is_dir("../images/".$this->carusel_name."/temp")){
      mkdir("../images/".$this->carusel_name."/temp");
      chmod ("../images/".$this->carusel_name."/temp", 0755);
    }
    
    if (!is_dir("../images/".$this->carusel_name."/variations")){
      mkdir("../images/".$this->carusel_name."/variations");
      chmod ("../images/".$this->carusel_name."/variations", 0755);
    }
  }
  // End Инициалицация
  
  // Валидация введенных данных
    
  function getErrorForKey($key){
    $output = '';
    if(!empty($this->validateError_arr)){
      if(isset($this->validateError_arr[$key]))
        if($this->validateError_arr[$key]){
          $output .= '<label class="validate_rerror">'.$this->validateError_arr[$key].'</label>';
        }
    }
    return $output;
  }
  
  function validationValue($id){ 
    $is_validation = true;
    
    $validator = new FormValidator();
    if(isset($this->date_arr['title'])){
      $validator->addValidation("title", "req", "Пожалуйста, заполните ".$this->date_arr['title']);  
    }
    
    if($validator->ValidateForm())
    {
        #echo "<h2>Validation Success!</h2>";
    }
    else
    {
        #echo "<B>Validation Errors:</B>";

        $error_hash = $validator->GetErrors();
        $this->validateError_arr = $error_hash;
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
  function setForName($name, $value){
    $this->$name = $value;
  }
  
  function getForName($name){
    return $this->$name;
  }  
  
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
  
  // AJAX function
  function star_check(){
    if (!isset($_POST['id']) or !intval($_POST['id']) or !$_POST['field']) return;
		
    $fields = array('hide', 'fl_is_fixed', 'star1', 'star3', 'flShowMine', 'fl_show_mine', 'fl_show_mine_header', 'fl_show_left_block', 'is_new', 'is_sale', 'is_hit');
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
  
  function sort_item(){
    foreach ($_POST["itSort"] as $key=>$val)
	  {
		  $order_main=$key*10;
		  $this->pdo->query ("UPDATE `".$this->prefix.$this->carusel_name."` SET `ord`=$key WHERE `id`=$val");
       
	  }
  }
  
  function ajx_pager(){
    if(isset($_POST['pager_act'])){
      
      if($_POST['pager_act'] == 'set_page'){
        if(isset($_POST['page']) && intval($_POST['page'])){
          $_SESSION['pager'][$this->carusel_name]['page'] = intval($_POST['page']);
          return 'ok';
        }else{
          return 'error';
        }
        
      }elseif($_POST['pager_act'] == 'set_per_page'){
        if(isset($_POST['per_page']) && intval($_POST['per_page'])){
          $_SESSION['pager'][$this->carusel_name]['perPage'] = intval($_POST['per_page']);
          $_SESSION['pager'][$this->carusel_name]['page'] = 1;
          return 'ok';
        }else{
          return 'error';
        }
      }
    
    }
  }
  
  // END AJAX function
  
  function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
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
  }
  
  function getUpdateSlide_SqlVals(){
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
  }
 
  function add_slide($item = null){
    $output = $title = "";
    
    $output = "    
    <style>.validate_rerror{   color: red;  font-size: 12px; }</style>";
    
    $header ='<a href="'.IA_URL.''.$this->carusel_name.'.php">'.$this->header.'</a>';
    $this->header = $header;
    
    (!is_null($this->admin)) ?  : $output .=  '<h1>'.$header.'</h1>';
    
    $this->bread[ucfirst_utf8($this->header)] = $this->carusel_name.'.php'; 
    $title .=' Добавление записи';
    $this->title  = $title;
    
    if($item){
      $output .='<label class="validate_rerror">Изменения не внесены!</label>';
    }
    $output .= '<div style="/*margin:25px;*/">
                <FORM 
                  method="post" 
                  enctype="multipart/form-data" 
                  action="'.IA_URL.''.$this->carusel_name.'.php?creates" 
                  class="form-horizontal form-label-left"
                >';

    $output .= $this->show_form($item);

    $output .= ' <BR/><BR/><INPUT type="submit" value="сохранить" class="btn btn-success btn-large submit_form" id="submit" >';
    $output .= '</FORM></div>';
    
    $sql="SHOW TABLE STATUS LIKE '".$this->prefix.$this->carusel_name."'";
    $result = $this->pdo->query($sql);
    $arr = $result->fetch();
    $nextid=$arr['Auto_increment'];
    
    return $output;
  }
  
  function edit_slide($id, &$item = null){
    $output = "    
    <style>.validate_rerror{   color: red;  font-size: 12px; }</style>";
    
    $header ='<a href="'.IA_URL.''.$this->carusel_name.'.php">'.$this->header.'</a>';
    $this->header = $header;
    (!is_null($this->admin)) ?  : $output .=  '<h1>'.$header.'</h1>';
    
    $this->bread[ucfirst_utf8($this->header)] = $this->carusel_name.'.php'; 
    $title =' Редактирование записи';
    $this->title  = $title;
    
    
    if(is_null($item)){
       $s = "SELECT * FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'";
       $q = $this->pdo->query($s);
       $item = $q->fetch();
    }else{
      $output .='<label class="validate_rerror">Изменения не внесены!</label>';
    }
    
	  if ($item){
  		$output .='<form 
                  method="post" 
                  enctype="multipart/form-data" 
                  action="'.IA_URL.$this->carusel_name.'.php?updates='.$item["id"].'"
                  class="form-horizontal form-label-left"
                >';
      
      //Генерация Url
      if($this->url_item && $id && !isset($_POST['url'])){
        $_POST['url'] = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      $output .= $this->show_form($item, '', $id);

      
      $output .= '<input type="submit" value="сохранить" class="btn btn-success btn-large submit_form" id="submit">';
      $output .= '</form>';
      
      /*//Модуль картинок
      if($this->images_items && $id ){
        $output .= $this->images_items->showImageForm($this->prefix.$this->carusel_name, $id);
      }
      
      //Модуль файлов
      if($this->files_items && $id ){
        $output .= $this->files_items->showFilesForm($this->prefix.$this->carusel_name, $id);
      }*/
    }
    
    return $output;
  }
  
  
  function getStarValStyle($isStarOn){
    ($isStarOn) ? $star_val = "fas fa-star" : $star_val = "far fa-star"; 
    return $star_val;
  }
  
    function getFormStyle(){
    $output = '';
     
    $output .= '
    <style>
     table td { text-align: left; }
     table.catalogue { width: 70%; border: none; }
     table.catalogue .th td { text-align: center; }
     table.catalog_items td { border-color: #ccc; border-width: 0 0 1px 0; border-style: solid; }
     table td a { color: #00f; }
     table td a:visited { color: #6e006f; }
     table.edit { width: 1000px; }
     table.edit td, table.edit th, table.catalogue input.price { border: 1px #ccc solid; border-collapse: collapse; margin: 0; padding: 0; empty-cells: show; }
     table.edit input.text, table.edit textarea { border: 1px #111111 solid; }
     table.edit input.small { width: 500px; }
     table.edit input.medium { width: 300px; }
     table.edit input.large { width: 450px; }
     table.edit textarea { width: 450px; height: 100px; }
     table.catalogue input.price { width: 100px; }
     table.edit input.button { margin: 5px 3px; cursor: pointer; background: #fff; border: 2px #395ed0 solid; font-weight: bolder; color: #1d1d1d; }
     span.ajx { cursor: pointer; border-style: dashed; border-width: 0 0 1px 0; }
     span.ajx:hover { border-style: solid; }
     img.ajx { cursor: pointer; }
     span.del { color: #f00; border-color: #f00; }
     .star_check { /*width: 25px; height: 25px;*/ font-size: 25px; color: #f0ad4e; cursor: pointer; margin: 0 auto; }
     .for_img { position: relative; }
     div.img_preview { display: none; position: absolute; z-index: 99; border: 1px #ccc dotted; }
     .img-act { text-align: right; }
     table td.img-act a:visited { color: #ffffff; }
     .cat_img_box{
       
     }
     .cat_img_item{
       max-width: 100%;
     }
    </style>
    ';
    
    return $output;
  }
  
  function getFormStyleAndScript(){
    $output = '';
    
    $output .= $this->getFormStyle();
    
    $output .= '
    <script type="text/javascript" src="js/tablednd.js"></script>
    <script>
    $(document).ready(function() {
      // Initialise the first table (as before)
      $(".sortab").tableDnD({
    	  onDrop: function() {
    	    $.post( "'.$this->carusel_name.'.php?ajx&act=sort_item", $( "#sortSlide" ).serialize());
    	  }
    	});
      
      //Увеличение картинки zoomImg при наведении
      $(document).on("mouseover", ".zoomImg", function(){
            $(this).children("img").stop().animate({width:"200px"}, 400);
          }
      );
      $(document).on("mouseout", ".zoomImg", function(){
            $(this).children("img").stop().animate({width:"50px"}, 400); 
          }
      );

    });
    
    function star_check(id, field) {
  		$.post(\''.$this->carusel_name.'.php?ajx&act=star_check\', {id:id, field:field}, function(data) {
    ';
    $output .= <<<HTML
			if (data == 1) {
				$('#'+field+'_'+id).removeClass('far fa-star')
				$('#'+field+'_'+id).addClass('fas fa-star')
			} else {
				$('#'+field+'_'+id).removeClass('fas fa-star')
				$('#'+field+'_'+id).addClass('far fa-star')
			}
		});
	}
HTML;
  $output .= '
  function delete_item(del_id, title, id_block) {
		if (confirm( title )) {
			$.post(\''.$this->carusel_name.'.php?ajx&act=delete_item\', {del_id: del_id}, function(data) {
				if (data == "ok") {
					$("#"+id_block).fadeOut("slow").remove();
				}
			})
		}
	}
  </script>
  ';
  
    if($this->is_pager) $output .= $this->getPagerScript();

    return $output;
  }
  
  function getFilterTableSelect(&$filter_field_name){
    $output = '';
    
    $data_filter_arr = $this->filter_field; # array("title", "date", "longtxt1", "longtxt2");  
    
    foreach($this->date_arr as $k => $v){
      if(in_array($k, $data_filter_arr)){
        ($k == $filter_field_name) ? $selected = 'selected' : $selected = '';
        $output .= '    
            <option value = "'.$k.'" '.$selected.' >'.$v.'</option>';
      }  
    }
    return $output;
  }
  
  function getFilterTable(&$s_filter){
    $output = '';
    
    $filter_field_name = $filter_field_val = '';
    if( (isset($_POST['filter_field_reset'])) && ($_POST['filter_field_reset']) ){
      unset ($_SESSION[$this->carusel_name]);
      unset ($_POST['filter_field']);
    }#pri($_POST);
    
    if((isset($_POST['filter_field'])) && ($_POST['filter_field'])){
      foreach($_POST['filter_field'] as $fk => $fv){
        $_SESSION[$this->carusel_name]['filter_field'][$fk] = $fv;
      } 
    }
    
    if(!isset($_SESSION[$this->carusel_name])){
      $_SESSION[$this->carusel_name]['filter_field'] = array();
    }
    
    if ( !empty($_SESSION[$this->carusel_name]['filter_field']) ){
      foreach($_SESSION[$this->carusel_name]['filter_field'] as $fval ){
        #pri($fval);
        $filter_field_name = $fval['name'];
        $filter_field_val  = $fval['val'];
        if($filter_field_val){
          (!$s_filter) ? $s_filter .= "WHERE " : $s_filter .= " AND ";
          $s_filter .= "
            `".$this->prefix.$this->carusel_name."`.`".$filter_field_name."`
            LIKE '%".$filter_field_val."%'
          ";    
        }
        
      }
      
    }
    
    $output .= '
      <form method="post" action="'.$this->carusel_name.'.php" class="form-horizontal form-label-left">
        <div syle = "padding-left: 5px;">
          <div class = "filter_tbl_box alert alert-info ">  
            <div class="row"> 
              <label class="col-12 col-md-auto c_title control-label">Фильтр</label>
              <div   class="col-12 col-md-auto">
                <select class = "filter_tbl form-control" name = "filter_field[1][name]">';
    $output .= $this->getFilterTableSelect($filter_field_name);
    $output .= '
                </select>
              </div>
              <div  class="col-12 col-md">
                <input type = "text" class = "form-control" name = "filter_field[1][val]" value = "'.$filter_field_val.'">
              </div>
              <div   class="col-12 col-md-auto">
                <button class="btn btn-sm btn-primary">Применить</button>
                <button class="btn btn-sm btn-warning" name = "filter_field_reset" value = "1">Сбросить</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    ';
    
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
    
    switch($group_action){
      
      case 'group_hide':
        if ( !empty($group_items) ){
          $i = 0; $str_item_id = '';
          foreach ($group_items as $g_item_id){
            if($i++)$str_item_id .= ", ";
            $str_item_id .= intval( $g_item_id );
          }
          $s = "
            UPDATE  `".$this->prefix.$this->carusel_name."` 
            SET     `".$this->prefix.$this->carusel_name."`.`hide` =  '1'
            WHERE   `".$this->prefix.$this->carusel_name."`.`id` IN ( $str_item_id );
          "; #pri($s);
          $this->pdo->query( $s );
        }
        break;
      case 'group_show':
        if ( !empty($group_items) ){
          $i = 0; $str_item_id = '';
          foreach ($group_items as $g_item_id){
            if($i++)$str_item_id .= ", ";
            $str_item_id .= intval( $g_item_id );
          }
          $s = "
            UPDATE  `".$this->prefix.$this->carusel_name."` 
            SET     `".$this->prefix.$this->carusel_name."`.`hide` =  '0'
            WHERE   `".$this->prefix.$this->carusel_name."`.`id` IN ( $str_item_id );
          ";#pri($s);
          $this->pdo->query( $s );
        }
        break;
      case 'group_del':
        if ( !empty($group_items) ){
          foreach ($group_items as $g_item_id){
            $output .= $this->delete_slide(intval($g_item_id), 'ajax');
          }
        }
        break;
    }
    
    return $output;
    
  }
  
  function getGroupOperations(){
    $output = '';
    #pri($_POST);
    
    $this->makeGroupOperations();
    
    $output .= '
    <script type="text/javascript">
      $(document).ready(function() {  
        $("#group_check_all", this).click(function(){
          /*$(".group_checkbox").attr("checked", "checked");*/
          $(".group_checkbox").iCheck("check");
        });
        $("#group_check_off_all", this).click(function(){
          /*$(".group_checkbox").removeAttr("checked");*/
          $(".group_checkbox").iCheck("uncheck");
        });
      });
    </script>
    <style>
      .group_checkbox{
        display: inline-block;  
      }
    </style>
    ';
    $output .= '
    <div class = "group_operation_box">
      <span class="fas fa-angle-double-up"></span>      
      <span class="btn btn-sm btn-default" id = "group_check_all" ><span class="far fa-check-square"></span> Отметить все</span>
      <span class="btn btn-sm btn-default" id = "group_check_off_all" ><span class="far fa-square"></span> Снять выделение</span>
      
      <button type="submit" class="btn btn-sm  btn-warning" name = "group_action" value="group_hide" onclick="javascript: if (confirm(\'Скрыть выделеные?\')) { return true;} else { return false;}">
        <span class="far fa-star"></span> Скрыть
      </button>
      
      <button type="submit" class="btn btn-sm  btn-warning" name = "group_action" value="group_show"  onclick="javascript: if (confirm(\'Показать выделеные?\')) { return true;} else { return false;}">
        <span class="fas fa-star"></span> Показать
      </button>
      
      <button type="submit" class="btn btn-sm  btn-danger" name = "group_action"  value="group_del"   onclick="javascript: if (confirm(\'Удалить выделеные?\')) { return true;} else { return false;}">
        <span class="fas fa-trash"></span> Удалить
      </button>
    </div>
    ';

    
    return $output;
  }
  
  function show_form_row($title = '', $cont = ''){
    $output = '
      <div class="form-group row">
        <label class="col-12 col-sm-12 col-md-3 col-lg-2 c_title control-label">'.$title.'</label>
        <div class = "col-12 col-sm-12 col-md-9 col-lg-10 c_cont">'.$cont.'</div>
      </div>
    ';
    
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
  
  
  function getFormPicture($id, $item = null){
    $output = '';
    
    $output .='
        <div class="card"> 
          <div class="card-header">Основное изображение</div> 
          <div class="card-body">';
        
    $output .= $this->show_form_row( 
            ' Изображение  (Иделальный размер '.$this->img_ideal_width.' x '.$this->img_ideal_height.'):', 
            ' <input type="file" name="picture" id = "fr_picture" value="" class="form-control">'
          );
      
    $item_img = $item['img'];
    
    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $rand = rand(0, 1000);
    if ($item_img){
      $orig_path = '/images/'.$this->carusel_name.'/orig/'.$item_img;
      $slide_path = '/images/'.$this->carusel_name.'/slide/'.$item_img;
      $output .= $this->show_form_row( 
        'Загружено', 
        $item_img.' <BR/>
        <a target = "_blank" href = "'.$protocol.$_SERVER["HTTP_HOST"].$orig_path.'?'.$rand.'">Оригинал</a>
        <span id = "orig_imageimg_path" data-orig_path = "'.$orig_path.'">'.$protocol.$_SERVER["HTTP_HOST"].$orig_path.'</span><BR/>
        <a target = "_blank" href = "'.$protocol.$_SERVER["HTTP_HOST"].$slide_path.'?'.$rand.'">Слайд</a>
        <span id = "slide_imageimg_path">'.$protocol.$_SERVER["HTTP_HOST"].$slide_path.'</span><br/>      
        <input type = "hidden" id = "imgCorpScriptPath" value = "'.IA_URL.$this->carusel_name.'.php?ajx&act=croppImg&id='.$id.'">  
        '
      );
      
      
      #
      
      $output .= $this->show_form_row( 
        'Изображение',
        '<IMG  id="imageimg" src="/images/'.$this->carusel_name.'/slide/'.$item_img.'?'.$rand.'" style = "max-width: 100%;">
        '
      );
      #<textarea class="form-control" id = "corp_imageimg" name="corp_imageimg"></textarea>
      
      $output .= $this->show_form_row( 
        'Действия', 
        ' <button id="edit_picture_btn" type="button" class="btn btn-primary" data-target="#img_modal" data-toggle="modal">
            <span class = "glyphicon glyphicon-pencil"></span>  Редактировать
          </button>
          
          <button id="edit_orig_picture_btn" type="button" class="btn btn-primary" data-target="#img_modal" data-toggle="modal">
            <span class = "glyphicon glyphicon-picture"></span>  Оригинал
          </button>
          
          <a class = "btn btn-danger" href="'.IA_URL.$this->carusel_name.'.php?delete_picture=1&id='.$id.'" 
             onClick="javascript: if (confirm('."'Удалить картинку?')) { return true;} else { return false;}\"".'>
             <span class = "glyphicon glyphicon-remove"></span> Удалить</a> '
      );
      
    }
    $output .= '
      </div>
    </div>';
    
    //Обрезка изображения
    $output .= Image::corpImg();
    
    return $output;
  }
  
  function base64_to_img($base64_string, $output_file) {
    $ifp = fopen($output_file, "wb");

    $data = explode(',', $base64_string);

    fwrite($ifp, base64_decode($data[1]));
    fclose($ifp);

    return $output_file;
}

  function croppImg(){
    if(!isset($_GET['id']) || !$_GET['id']) return 'error id croppImg';
    $id = intval($_GET['id']);
    
    $picture_uploaded = false;
    if (isset($_FILES["croppedImage"])){
      $filename = $_FILES["croppedImage"]["name"];
			$tmpname =  $_FILES["croppedImage"]["tmp_name"];
			$exts = explode('.', $filename);
      
      if (is_uploaded_file($tmpname)){
		  
      	if ($_FILES['croppedImage']['name']) {
          
  				$string="select img from `".$this->prefix.$this->carusel_name."` where `id`=$id";	
          $q = $this->pdo->query($string);
          $r = $q->fetch();
          $pic_filename = $r['img'];
          
          $c_path="../images/".$this->carusel_name."/slide/";
          if (is_file($c_path.$pic_filename)){
      			unlink($c_path.$pic_filename);
      		}
          $this->deleteAllValidation($id);
          
          $name=$pic_filename;
          $e=explode(".",$_FILES['croppedImage']['name']);
         	$type=end($e);
  				$new_filename="../images/".$this->carusel_name."/temp/".$name;
          $to="../images/".$this->carusel_name."/slide/".$name;
  				
  				move_uploaded_file($_FILES['croppedImage']['tmp_name'], $new_filename);
          #copy ($new_filename, $to);
          $this->resize($new_filename, $to, $type, $this->img_ideal_width, 100);
          unlink($new_filename);
  				$picture_uploaded = true;
  			}
	  	}
    }
    
    if($picture_uploaded){
      return 'ok';
    }else{
      return 'error croppImg';
    }
  }
  
  function load_picture($id){
    $picture_uploaded = FALSE;
    
    #pri($_POST["corp_imageimg"]);
    #  die();
		
    /*if(isset($_POST["corp_imageimg"]) && $_POST["corp_imageimg"]){
      $c_path="../images/".$this->carusel_name."/slide/";
      $string="select img from `".$this->prefix.$this->carusel_name."` where `id`=$id";	
      $q = $this->pdo->query($string);
      $r = $q->fetch();
      $pic_filename = $r['img'];
      #$name=$time.".".$type;
      $name=$pic_filename;
      
    	if (is_file($c_path.$pic_filename)){
        $this->deleteAllValidation($pic_filename);
    		unlink($c_path.$pic_filename);
    	}
      
      $to="../images/".$this->carusel_name."/slide/".$name;
      $this->base64_to_img($_POST["corp_imageimg"], $to);
  		$picture_uploaded = TRUE;
      
    }else*/
    
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
          $this->delete_picture($id);
          
  				$time=time();
  				$e=explode(".",$_FILES['picture']['name']);
  				$type=strtolower(end($e));
  				$big_filename="../images/".$this->carusel_name."/orig/$time.".$type;
  				$new_filename="../images/".$this->carusel_name."/temp/$time.".$type;
          $to="../images/".$this->carusel_name."/slide/$time.".$type;
  				$name=$time.".".$type;
  				move_uploaded_file($_FILES['picture']['tmp_name'], $new_filename);
  				#copy ($new_filename,$big_filename);
          $this->resize($new_filename, $big_filename, $type, $this->max_img_width);
  				$this->resize($new_filename, $to, $type, $this->img_ideal_width);
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
  
  function deleteAllValidation($pic_filename){
		if (file_exists('../images/'.$this->carusel_name.'/variations/') && $handle = opendir('../images/'.$this->carusel_name.'/variations/')) 
		{
   		while (false !== ($file = readdir($handle))) 
   		{ 
      	if ($file != "." && $file != ".." &&
						is_dir('../images/'.$this->carusel_name.'/variations/'.$file) &&
        		file_exists('../images/'.$this->carusel_name.'/variations/'.$file.'/'.$pic_filename)) 
      	{ 
          if (is_file('../images/'.$this->carusel_name.'/variations/'.$file.'/'.$pic_filename)){
       		  unlink('../images/'.$this->carusel_name.'/variations/'.$file.'/'.$pic_filename);
          }
      	} 
   		}
   		closedir($handle); 
		}
	}

  function delete_picture($id){
    $output = "";
    
	  $c_path="../images/".$this->carusel_name."/orig/";
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
    
    $output = $this->edit_slide($id);
    
    return $output;
  }
  
  function resize($filename, $to, $type, $x, $quality = null) {
    
    if( is_null( $quality )) $quality = $this->max_img_quality;
    #print '$filename, $to, $type, $x'."$filename, $to, $type, $x<BR>"; 
  	list($width, $height) = getimagesize($filename);
  	if ($width > $x) {
  		$percent = $width / $x;
  		$newwidth = $x;
  		$newheight = $height / $percent;
      
  		$thumb = imagecreatetruecolor($newwidth, $newheight);

      
  		if ($type=="jpg" or $type=="jpeg") {
  			$source = imagecreatefromjpeg($filename);
        
  		} else if ($type=="gif") {
  			$source = imagecreatefromgif($filename);
        $transparent_source_index=imagecolortransparent($source); //Получаем прозрачный цвет
        if($transparent_source_index!==-1){                       //Проверяем наличие прозрачности
	        $transparent_color=imagecolorsforindex($source, $transparent_source_index);
          $transparent_destination_index=imagecolorallocate($thumb, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);                                          //Добавляем цвет в палитру нового изображения, и устанавливаем его как прозрачный
	        imagecolortransparent($thumb, $transparent_destination_index);
          imagefill($thumb, 0, 0, $transparent_destination_index);//На всякий случай заливаем фон этим цветом
        }
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);//Ресайз
        imagegif($thumb, $to); //Сохранение
        
        return 1;
  		} else if ($type=="png" or $type=="blob") {
  			$source = imagecreatefrompng($filename);
        imagealphablending($thumb, false);                                                        //Отключаем режим сопряжения цветов
        imagesavealpha($thumb, true);                                                             //Включаем сохранение альфа канала
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagepng($thumb, $to);                                                                    //Сохранение
        
  			return 1;
  		} else {
  			return 0;
  		}
  		imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
  		imagejpeg($thumb, $to, $quality);
  	} else {
  		copy($filename, $to);
  	}
  }
  
  function getCardPanelHeader($title){
    $output = '';
    
    $output = '
          <div class="card my-3"> 
            <div class="card-header">'.$title.'</div> 
            <div class="card-body">';
            
    return $output;
  }
  
  function getCardPanelFooter(){
    $output = '';
    
    $output = '
            </div>
          </div>';
          
    return $output;
  }
  
  function setPagerParamers(){
    
    if(!isset($_SESSION['pager'][$this->carusel_name])){
      $_SESSION['pager'][$this->carusel_name]['perPage'] = $this->pager['perPage'];
      $_SESSION['pager'][$this->carusel_name]['page'] = $this->pager['page'];
      $_SESSION['pager'][$this->carusel_name]['url'] = $this->pager['url'];
    }
  }
 
  function resetPagerParamers(){
    unlink($_SESSION['pager'][$this->carusel_name]);
    $this->setPagerParamers();
  }
  
  function getPagerParamers(){
    $this->pager['perPage'] = $_SESSION['pager'][$this->carusel_name]['perPage'];
    $this->pager['page'] = $_SESSION['pager'][$this->carusel_name]['page'];
    $this->pager['url'] = $_SESSION['pager'][$this->carusel_name]['url'];
  }
  
  function getPagerScript(){
    $output = '
      <script type="text/javascript">
        $(document).ready(function() {  
        
          $(".items-per-page", this).change(function() {
            var perPage = $(this).val();
            $.post( "'.$this->carusel_name.'.php?ajx&act=ajx_pager", 
                    { pager_act: "set_per_page", per_page: perPage}, 
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
            $.post( "'.$this->carusel_name.'.php?ajx&act=ajx_pager", 
                    { pager_act: "set_page", page: page}, 
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
              <option value = "'.$v.'" '; if($itemsPerPage == $v) $output.= 'selected'; $output .= ' >'.$v.'</option>
        ';
      }
      
      $output .= '
            </select>
          </div>
      ';

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
        </li>
      ';
      
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
                <li class="page-item active"><span class = "page-link">'.$i.'</span></a></li>
            ';
          }else{
            $output .= '
                <li class="page-item"><a class="page-link set_pager_page" data-page = '.$i.' data-id_cat = '.$id_cat.' style = "cursor: pointer;">'.$i.'</a></li>
            ';  
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
          </li>
      ';
      $output .= '
            </ul>
          </div> 
        </div>
      '; 
      
      if($page){
        $offset = ' LIMIT '.($page - 1)*$itemsPerPage.', '.$itemsPerPage.' ';
      }else{
        $offset = ' LIMIT '.$itemsPerPage.' ';
      }
      
    }
    
    return $output;
  }  
  
  
  function getPager_old($countItems, &$offset = 0, $show_pager_always = true){
    $output = '';
    
    $this->getPagerParamers();
    
    $itemsPerPage = $this->pager['perPage'];
    $page = $this->pager['page'];
    
    if(!$itemsPerPage) return $output;
    if( ($countItems > $itemsPerPage) || $show_pager_always){
      $pageLinks = ceil($countItems / $itemsPerPage);
      
      $output .= '
        <nav class = "text-right ">
          <div style = "    float: left;     margin: 20px 0 20px; padding: 6px 12px 6px 0px;     line-height: 1.42857143;" >Всего: '.$countItems.'</div>
        <select class="form-control items-per-page" 
        
          style = "
            float: left;
            margin: 20px 0 20px;
            padding: 6px 12px 6px 0px;
            line-height: 1.42857143;
            display: inline-block;
            width: 65px;
          "
        >
      ';
      foreach($this->pager['items_per_page'] as $k => $v){
        $output .= '  
          <option value = "'.$v.'" '; if($itemsPerPage == $v) $output.= 'selected'; $output .= ' >'.$v.'</option>
        ';
      }
      
      $output .= '
        </select>
      ';

      $output .= '
          <ul class="pagination ">
      ';
      $output .= '<li ';
      if($page <= 1){ $output .= ' class="page-item disabled" '; }
      $output .= ' > <a ';
      if($page >= 1){ $output .= ' class="page-item set_pager_page" data-page = '.($page-1).' style = "cursor: pointer;" '; /*$output .= '?page='.($page-1).'';*/ }
      
      $output .= ' aria-label="Previous">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      ';
      
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
              <li class="active"><span>'.$i.'</span></li>
            ';
          }else{
            $output .= '
              <li ><a class="set_pager_page" data-page = '.$i.' style = "cursor: pointer;">'.$i.'</a></li>
            ';  
          }
        }
      }
      
      $output .= '<li ';
      if($page >= ($pageLinks ) ){ $output .= ' class="disabled" '; }
      $output .= ' > <a ';
      if($page <= ($pageLinks ) ){ $output .= ' class="set_pager_page" data-page = '.($page+1).' style = "cursor: pointer;" '; /*$output .= '?page='.($page+1).'';*/ } 
      $output .= '
         aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      ';
      $output .= '
          </ul>
        </nav>
      ';
      
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
          }elseif($_GET['act'] == 'delete_item'){
            echo $carisel->delete_slide(intval($_POST['del_id']), 'ajax');
          }elseif($_GET['act'] == 'croppImg'){
            echo $carisel->croppImg();
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
        $output .=  '<div style ="text-align: center;">
        <h3> Кончилость время ссесии <a href = "'.IA_URL.'">Повторите авторизацию</a>    </h3>';
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
  
  function getAdminSourceLink($module = '', $module_id = ''){
    
    $source_link = '';
    
    if($module){
      $is_cat = false;
      if( strpos ( $module, '_cat') ){
        $is_cat = true;
        $source_link = str_replace( $this->prefix, '', $module );
        $source_link = IA_URL.str_replace( '_cat', '', $source_link ).'.php';
      }else{
        $source_link = IA_URL.str_replace( $this->prefix, '', $module ).'.php';
      }
      
      if($module_id){
        ( $is_cat ) ? $source_link .= '?editc=' : $source_link .= '?edits=';
        $source_link .= $module_id;
      }
      
    }
    
    return $source_link;
  }
  
 
}



?>