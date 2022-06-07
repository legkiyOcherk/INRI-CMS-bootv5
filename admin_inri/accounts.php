<?php
require_once('lib/class.Admin.php');
$admin = new Admin(); 
require_once('lib/class.Carusel.php');
require_once('lib/class.Image.php');

class Accounts extends Carusel{  
  
  var $checkbox_array = array(
        'is_admin',
        'is_programmer',
        'untouchible',
        'iscontent',
        'ismanag',
        'iscatalog',
        'isjournalist'
      );
  
  function show_table_header_rows(){
    
    $output = '
          <tr class="th nodrop nodrag">
          	<td style="width: 55px;">#</td>
            <td style="width: 50px;">Скрыть</td>
            <td style="width: 60px;">Фото</td>
      		  <td>Должность</td>
            <td>Логин</td>
            <td>Полное имя</td>
            <td>Email</td>
            <td>Телефон</td>
      		  <td style="width: 80px">Действие</td>
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
    
    $output .= '
            <td style="max-width: 60px;">';
    if($img){
      $output .= '
            <div class="zoomImg" ><img style="width:50px;" src="../images/'.$this->carusel_name.'/slide/'.$img.'"></div>        ';
    }
    $output .= '
            </td>
        	  
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>
            <td>
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$login.'</a>
              
            </td>';
            
    $output .= '
            <td>'.$fullname.'</td>
            <td>'.$email.'</td>
            <td>'.$phone.'</td>';
            
    $output .= '
        	  <td style="" class="img-act">
              <a  href="../'.ADM_DIR.'/'.$this->carusel_name.'.php?edits='.$id.'" 
                  class = "btn btn-info btn-sm"
                  title = "Редактировать">
                <i class="fas fa-pencil-alt"></i>
              </a>
              
              <span >
              <span class="btn btn-danger btn-sm" 
                    title="удалить" 
                    onclick="delete_item('.$id.', \'Удалить элемент?\', \'tr_'.$id.'\')">
                <i class="far fa-trash-alt"></i>
              </span>
            </td>
  			  </tr>
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
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date"))) $type = 'date';
      if( in_array($key, array("datetime"))) $type = 'datetime';
      if( in_array($key, array("title", "login", "fullname", "email", "phone"))) { $type = 'text'; }
      if( in_array($key, array("password"))) { $type = 'password'; }
      
      
      
      // Отступы
      if($key == 'login'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('Авторизация');
        $is_open_panel_div = true;   
      }
      
      if($key == 'is_admin'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('Права');
        $is_open_panel_div = true;   
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
      
      if( in_array( $key, $this->checkbox_array) ){
        $output .= $this->show_iCheck('col_'.$key, $item, $key, $val);
        continue;  
      }
      
      if( $key == 'password' ){
        $item[$key] == '';
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
      $output .= $this->getCardPanelFooter();
    }
    
    $output .= $this->getFormPicture($id, $item);
      
    $output .= '</div>';
    
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
  
  function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    $i=0;
    #pri($sql_vals); die();  
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      if( $key == 'password' ){
        if( $_POST[$key] ){
          $user_key = $this->new_key();
          $sql_names .= $prefix.' `key`';
          $sql_vals .= $prefix.' \''.$user_key.'\'';
          
  		    $user_hash = $this->adm_password_hash($_POST[$key], $user_key);
          
          $sql_names .= $prefix.' `hash`';
          $sql_vals .= $prefix.' \''.$user_hash.'\'';  
        }
        continue;
      }
      
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    }; 
    
  }
  
  function getUpdateSlide_SqlVals(){
    $sql_vals = ''; $i=0;
    #pri($sql_vals); die(); 
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';      
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      if( $key == 'password' ){
        if( $_POST[$key] ){
          $user_key = $this->new_key();
  		    $user_hash = $this->adm_password_hash($_POST[$key], $user_key);
          
          $sql_vals .= $prefix.'  `key` = \''.$user_key.'\''; 
          $sql_vals .= $prefix.'  `hash` = \''.$user_hash.'\'';
        }
        continue;
      }
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
    
    return $sql_vals;
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
  
  
  function new_key(){
  	srand((double) microtime() * 1000000);
  	return md5(uniqid(rand()));
  }
  function adm_password_hash($password, $key){
  	return md5(md5($password).$key);
  }
}

$date_arr = array(
    /*'key' => '',
    'hash' => '',*/
    
    'title'         => 'Должность <span style = "color: red;">*</span>',
    'fullname'      => 'Полное имя',
    'email'         => 'Email адрес',
    'phone'         => 'Телефон',
    'longtxt1'      => 'Описание',
    
    'is_admin'      => 'Администратор (всемогущий)',
    
    'login'         => 'Логин',
    'password'      => 'Новый пароль</br><small style = "color: red;">(необязательно)</small>',

    'is_programmer' => 'Программист',
    'iscontent'     => 'Контент-менеджер',
    'ismanag'       => 'Менеджер',
    #'iscatalog'     => 'Менеджер каталога',
    #'isjournalist'  => 'Журналист',
    
    #'untouchible'  => 'Неприкасаемый',
  );
  
$pager = array(
  'perPage' => 50,
  'page'    => 1,
  'url'     => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title', 'login');

$carisel = new Accounts('accounts', $date_arr, false, false, $pager);

$carisel->setHeader('Пользователи');
$carisel->setIsUrl(false);
$carisel->setIsImages(false);
$carisel->setIsFiles(false);
$carisel->setIsPager(false);
$carisel->setIsFilter(false);
$carisel->setIsLog(true);
#$carisel->setFilterField($arrfilterfield); 

$carisel->setImg_ideal_width(160);  
$carisel->setImg_ideal_height(160);
#$carisel->setDate_arr($date_arr);

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}