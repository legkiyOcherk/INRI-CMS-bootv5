<?php
require_once('lib/class.CatCarusel.php');

class AjaxCatCarusel extends CatCarusel{
  
  function show_table(){
    $output = "";
    $output .= '
      <div id = "'.$this->carusel_name.'_table_box">';
    $output .= parent::show_table();
    $output .= '
      </div>';
    return $output;
    
  }
  
  function create_slide(){
    $output = '';
    $output .= '
    <script>
      var ajax_updates = 0;
    </script>
    ';
    $output .= parent::create_slide();
    
    return $output;
  }
  
  function update_slide($id){
    $output = '';
    $output .= '
    <script>
      var ajax_updates = '.$id.';
    </script>
    ';
    $output .= parent::update_slide($id);
    
    return $output;
  }
  
  function get_popup_form_box(){
    $output = '';
    
    $output .= '
    <style>
      .'.$this->carusel_name.'_popup_form_box{
        position: fixed;
        top: 0;
        left: 50px;
        z-index: 1040;
        background: #F5F5F5;
        height: 100vh;
        padding: 15px;
        overflow: scroll;
        display: none;
        max-width: 100%;
        width: 700px;
        overflow-x: auto;
      }
      .'.$this->carusel_name.'_popup_form_box #submit {
        /*position: relative;
        float: right;*/
        left: 625px; 
      } 
    </style>
    <div class = "'.$this->carusel_name.'_popup_form_box">
      <div class = "'.$this->carusel_name.'_popup_form">
        
      </div>
    </div>';
    
    
    return $output;
  }
  
  function show_table_row_action_btn($id){
    $output = '';
    
    $output .= '
              <span class = "btn btn-info btn-sm ajax_edit_item"
                  title = "Редактировать"
                  onclick="'.$this->carusel_name.'_edit_item('.$id.', \'Редактирование\', \'tr_'.$id.'\')">
                <i class="fas fa-pencil-alt"></i>
              </span>';
                        
    $output .= '          
              <span class="btn btn-danger btn-sm" 
                    title="удалить" 
                    onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
                <i class="far fa-trash-alt"></i>
              </span>';
    
    return $output;
  }  
  
  function get_add_btn_show_table(){
    $output = '';
    $output .= '
    <div><span class="btn btn-success " onclick="'.$this->carusel_name.'_create_item(  \'Добавление\' )" id="submit">Добавить</span></div>';
    
    return $output;
  }
  
  function show_cat_table_row_action_btn($id){
    $output = '';
        
        #<a  href="..'.IA_URL.$this->carusel_name.'.php?editc='.$id.'" 
        #    class = "btn btn-info btn-sm"
        #    title = "Редактировать">
        #    <i class="fas fa-pencil-alt"></i>
        #</a>';
     $output .= '
              <span class = "btn btn-info btn-sm ajax_edit_item"
                  title = "Редактировать"
                  onclick="'.$this->carusel_name.'_edit_cat_item('.$id.', \'Редактирование\', \'tr_'.$id.'\')">
                <i class="fas fa-pencil-alt"></i>
              </span>';
            
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
          <span class="btn btn-danger btn-sm" 
                title="удалить" 
                onclick="delete_cat_item('.$id.', \'Удалить элеемент?\', \'trc_'.$id.'\')">
            <i class="far fa-trash-alt"></i>
          </span>
      ';
      #<a href="..'.IA_URL.'$this->carusel_name.'.php?deletec='.$id.'" onclick="javascript: if (confirm(\'Удалить?\')) { return true;} else { return false;}">
      #      <img src="..'.IA_URL.'images/icons/b_drop.png" width="16" height="16" border="0">
      #    </a>
    }
    
    return $output;
  }
  
  function get_add_cat_btn_show_table(){
    $output = '';
    $output .= '
    <div style = "text-align: right;" ><span class="btn btn-primary" onclick="'.$this->carusel_name.'_create_cat_item(  \'Добавление\' )" >Добавить категорию</span></div>';
    
    return $output;
  }
  
  function getDeleteCatImgBtn($id){
    $output .= '';
    #href="'.IA_URL.$this->carusel_name.'.php?delete_picture_c=1&id='.$id.'"
    $output .= '[ <span  onclick = "if (confirm(\'Удалить картинку?\')) { '.$this->carusel_name.'_delete_cat_picture( '.$id.' ); return true;} else { return false;}" class = "delete_cat_img_btn"  style = "color:red;cursor:pointer;">X удалить</span> ]<BR/>';
    
    return $output;
  }
  
  function getAjaxCompleteScript(){
    $output = '';
    
    $output .= '
      <script>
        $(document).ajaxComplete(function() {
        
          var ckeditor_arr = document.querySelectorAll(".ckeditor");
          if(ckeditor_arr){
            for (let el of ckeditor_arr) {
              console.log(el.getAttribute("name"));
              ckeditor_name = el.getAttribute("name");
              
              CKEDITOR.replace( ckeditor_name );
            }  
          }
        });
      </script>';
    /*$output .= '
    <script>
      $(document).ajaxComplete(function() {
        CKEDITOR.replace( "longtxt1" );
        CKEDITOR.replace( "longtxt2" );
        CKEDITOR.replace( "longtxt3" );
      });
    </script>';*/ 
    
    return $output;
  }
  
  function getFormStyleAndScript(){
    $output = '';
    
    $output .= parent::getFormStyleAndScript();
    
    $output .= $this->getAjaxCompleteScript();
    $output .= '
    
    <script>
      
      /*var ajax_updates = 0;*/
      var is_ajx_send_form = true;
      $(document).ready(function() {
        
        $(document).mouseup(function (e){ // событие клика по веб-документу
      		var div = $(".'.$this->carusel_name.'_popup_form_box");
         
          if ($(event.target).closest(".'.$this->carusel_name.'_popup_form_box").length) return;
          if ($(event.target).closest(".ajax_edit_item").length) return;
          
          if ($(event.target).closest(".select2-results__options").length)   return;
          if ($(event.target).closest(".select2-results__option").length)    return;
          if ($(event.target).closest("input.select2-search__field").length) return;
          
          console.log("getFormStyleAndScript_Cat");
          console.log( $(event.target).closest("context") );
          
          div.hide(); // скрываем его 
          
      	});
        
        function CKEDITOR_update(){  // Обновление данных с CKEDITOR - ов
          for ( instance in CKEDITOR.instances ){
            CKEDITOR.instances[instance].updateElement();
          }
        }
        
        $( ".'.$this->carusel_name.'_popup_form_box" ).on( "click", ".submit_form", function(e){ 
          
          CKEDITOR_update();
          
          $( this ).attr( "disabled", "disabled" );
          if(!is_ajx_send_form) return;
          is_ajx_send_form = false;
          
          e.preventDefault();
          
          ajax_form_data = $( ".'.$this->carusel_name.'_popup_form_box" ).find("form").serializeArray();
          var formData = new FormData();
          var files_names =  document.getElementById("fr_picture").files;
          
          for (index = 0; index < files_names.length; ++index) {
            /*formData.append("picture[" + index + "]", files_names[index]);*/
            formData.append("picture", files_names[index]);
            console.log( files_names[index] );
          }
          
          /* console.log( " ajax_updates " + ajax_updates ); */
          /* console.log( ajax_form_data ); */
          
          /*formData.append("ajax_form_data", ajax_form_data);*/
          formData.append( "ajax_form_data", JSON.stringify(ajax_form_data) );
          formData.append("ajax_updates", ajax_updates);
          
          $.ajax({
            type: "POST",
            url: "'.$this->carusel_name.'.php?ajx&act=ajx_send_form",
            data: formData,
            /*dataType: "json",*/
            processData: false,
            contentType: false,
            error: function(msg){
              alert("error" + msg);
              /*console.log( " error = " + msg ); */
            },
            success: function(data){
              event.stopPropagation();
              if (data){ 
                $(".'.$this->carusel_name.'_popup_form").html( data["content"] );
                ajax_updates = data["edits"];
                $(".'.$this->carusel_name.'_popup_form_box").show();
                '.$this->carusel_name.'_get_table();
                is_ajx_send_form = true;
              }else{
                is_ajx_send_form = true;
              }
            }
          });
          
        });
        
        $( ".'.$this->carusel_name.'_popup_form_box" ).on( "click", ".submit_cat_form", function(e){ 
          
          CKEDITOR_update();
          
          $( this ).attr( "disabled", "disabled" );
          if(!is_ajx_send_form) return;
          is_ajx_send_form = false;
          
          e.preventDefault();
          
          ajax_form_data = $( ".'.$this->carusel_name.'_popup_form_box" ).find("form").serializeArray();
          var formData = new FormData();
          var files_names =  document.getElementById("fr_picture").files;
          
          for (index = 0; index < files_names.length; ++index) {
            formData.append("picture", files_names[index]);
            console.log( files_names[index] );
          }
          
          formData.append( "ajax_form_data", JSON.stringify(ajax_form_data) );
          formData.append("ajax_updates", ajax_updates);
          
          $.ajax({
            type: "POST",
            url: "'.$this->carusel_name.'.php?ajx&act=ajx_send_cat_form",
            data: formData,
            processData: false,
            contentType: false,
            error: function(msg){
              alert("error" + msg);
            },
            success: function(data){
              event.stopPropagation();
              if (data){ 
                $(".'.$this->carusel_name.'_popup_form").html( data["content"] );
                ajax_updates = data["edits"];
                $(".'.$this->carusel_name.'_popup_form_box").show();
                '.$this->carusel_name.'_get_table();
                is_ajx_send_form = true;
              }else{
                is_ajx_send_form = true;
              }
            }
          });
          
        });
        
      });
      
      function '.$this->carusel_name.'_get_table(){
        
        $.post( "'.$this->carusel_name.'.php?ajx&act=ajx_get_table", 
                {}, 
                function(data) {
  				        if (data){
                    $("#'.$this->carusel_name.'_table_box").html( data ); 
                    $(".group_checkbox").iCheck({
                      checkboxClass: "icheckbox_flat-red",
                      radioClass: "iradio_flat-red"
                    });
                  }else{
                    
                  }
                }
  			);
        
      }
       
      function '.$this->carusel_name.'_edit_item(ed_id, title, id_block) {
        ajax_updates = ed_id;
        $.post( "'.$this->carusel_name.'.php?ajx&act=edit_item", 
                {ed_id: ed_id}, 
                function(data) {
  				        if (data){
                    $(".'.$this->carusel_name.'_popup_form").html( data );
                    $(".'.$this->carusel_name.'_popup_form_box").show();
                    
                  }else{
                    
                  }
                }
  			);
    	}
      
      function '.$this->carusel_name.'_create_item( title ) {
        ajax_updates = 0;
        $.post( "'.$this->carusel_name.'.php?ajx&act=add_item", 
                {}, 
                function(data) {
  				        if (data){
                    $(".'.$this->carusel_name.'_popup_form").html( data );
                    $(".'.$this->carusel_name.'_popup_form_box").show();
                  }else{
                    
                  }
                }
  			);
    	}
      
      function '.$this->carusel_name.'_edit_cat_item(ed_id, title, id_block) {
        ajax_updates = ed_id;
        $.post( "'.$this->carusel_name.'.php?ajx&act=edit_cat_item", 
                {ed_id: ed_id}, 
                function(data) {
  				        if (data){
                    $(".'.$this->carusel_name.'_popup_form").html( data );
                    $(".'.$this->carusel_name.'_popup_form_box").show();
                    
                  }else{
                    
                  }
                }
  			);
    	}
      
      function '.$this->carusel_name.'_create_cat_item( title ) {
        ajax_updates = 0;
        $.post( "'.$this->carusel_name.'.php?ajx&act=add_cat_item", 
                {}, 
                function(data) {
  				        if (data){
                    $(".'.$this->carusel_name.'_popup_form").html( data );
                    $(".'.$this->carusel_name.'_popup_form_box").show();
                  }else{
                    
                  }
                }
  			);
    	}
      
      function '.$this->carusel_name.'_delete_cat_picture( ed_id ) {
        
        $.post( "'.$this->carusel_name.'.php?ajx&act=delete_cat_picture", 
                {ed_id: ed_id}, 
                function(data) {
  				        if (data){
                    $(".'.$this->carusel_name.'_popup_form").html( data );
                    $(".'.$this->carusel_name.'_popup_form_box").show();
                    '.$this->carusel_name.'_get_table();
                  }else{
                    
                  }
                }
  			);
    	}
    </script>';
    
    return $output;
  }
  
  function ajx_send_form(){
    
    $output = $id = '';
    
    if( !isset($_POST['ajax_updates']  ) ) return;
    if( !isset($_POST['ajax_form_data']) ) return;
    if( intval($_POST['ajax_updates']  ) ) $id = intval($_POST['ajax_updates']);
    
    $obj_array = json_decode( $_POST ['ajax_form_data'], false, 3);
    
    foreach($obj_array as $obj){
      
      if (strpos($obj->name, '[]') !== false) {
        $nname = str_replace('[]', '', $obj->name);
        $form_array[$nname][] = $obj->value;
        $_POST[$nname][] = $obj->value;
      }elseif(strpos($obj->name, 'art[') !== false){
        preg_match_all('|\[(.+)\]|isU', $obj->name, $arr_match );
        if(!isset($_POST['art'])) $_POST['art'] = array();
        $_POST['art'][$arr_match[1][0]][$arr_match[1][1]] = $obj->value;
      }else {
        $form_array[$obj->name] = $obj->value;
         $_POST[$obj->name] = $obj->value;
      }      
       
    }
    
    
    if(!$id){
      $output .= $this->create_slide();
      if( isset($_GET["edits"]) && $_GET["edits"] ) $res['edits'] = $_GET["edits"];
    }else{
      $output .= $this->update_slide($id);  
      $res['edits'] = $id;
    }
    
    $res['content'] = $output;
    
    foreach ($res as $k=>$v) {
      $res_output[$k] = $v;
		}
    header('Content-Type: text/x-json; charset=UTF-8');
		echo json_encode($res_output);
    #echo $output;
  }
  
  function ajx_send_cat_form(){
    
    $output = $id = ''; #pri($_POST);
    
    if( !isset($_POST['ajax_updates']  ) ) return;
    if( !isset($_POST['ajax_form_data']) ) return;
    if( intval($_POST['ajax_updates']  ) ) $id = intval($_POST['ajax_updates']);
    
    $obj_array = json_decode( $_POST ['ajax_form_data'], false, 3);
    
    foreach($obj_array as $obj){
      
      if (strpos($obj->name, '[]') !== false) {
        $nname = str_replace('[]', '', $obj->name);
        $form_array[$nname][] = $obj->value;
        $_POST[$nname][] = $obj->value;
      }elseif(strpos($obj->name, 'art[') !== false){
        preg_match_all('|\[(.+)\]|isU', $obj->name, $arr_match );
        if(!isset($_POST['art'])) $_POST['art'] = array();
        $_POST['art'][$arr_match[1][0]][$arr_match[1][1]] = $obj->value;
      }else {
        $form_array[$obj->name] = $obj->value;
         $_POST[$obj->name] = $obj->value;
      }      
       
    }
    
    if(!$id){
      $output .= $this->create_cat_slide();
      if( isset($_GET["editc"]) && $_GET["editc"] ) $res['edits'] = $_GET["editc"];
    }else{
      $output .= $this->update_cat_slide($id);  
      $res['edits'] = $id;
    }
    
    $res['content'] = $output;
    
    foreach ($res as $k=>$v) {
      $res_output[$k] = $v;
		}
    header('Content-Type: text/x-json; charset=UTF-8');
		echo json_encode($res_output);
    #echo $output;
  }
  
  function ajax_delete_cat_picture( $id ){
    $output = $this->delete_cat_picture( $id );
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
          elseif($_GET['act'] == 'edit_item'){
            echo $carisel->edit_slide( intval($_POST['ed_id']) );
          }elseif($_GET['act'] == 'add_item'){
            echo $carisel->add_slide( );
          }elseif($_GET['act'] == 'ajx_send_form'){
            echo $carisel->ajx_send_form();
          }elseif($_GET['act'] == 'ajx_get_table'){
            echo $carisel->show_table();
          }
          
          elseif($_GET['act'] == 'edit_cat_item'){
            echo $carisel->edit_cat_slide( intval($_POST['ed_id']) );
          }elseif($_GET['act'] == 'add_cat_item'){
            echo $carisel->add_cat_slide( );
          }elseif($_GET['act'] == 'ajx_send_cat_form'){
            echo $carisel->ajx_send_cat_form();
          }
          
          elseif($_GET['act'] == 'delete_cat_picture'){
            echo $carisel->ajax_delete_cat_picture($_POST['ed_id']);  
          }
          
          
          
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
        }elseif( isset($_GET["act"]) && ($_GET['act'] == 'delete_item') ){
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
        
        if( $output ) $output = $this->get_popup_form_box().$output; 
        
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
