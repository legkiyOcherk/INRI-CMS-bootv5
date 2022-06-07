<?php
class Goods {
  
  function __construct (){
    
  }
  
  static function show_path_link($category_id, $title = false, $separator = "/", $line = false) {
    $output = '';
		$path = self::get_path_link($category_id, '/', $separator, $category_id);
		if ($title) $path = "$path <span>$title</span>";
		$path = "<a href=\"/\">Главная</a> $separator $path";
		$output .= '
    <div class="bread_crumbs_box ">
      <div class="bread_crumbs
    ';
    if($line) $output .= ' border_top_button_line';
    $output .= ' ">'.$path.'</div></div>';
    
    return $output;
	}
  
  static function get_path_link($cid, $str = '', $separator = "/", $sid = null) {
		$row = db::row('title, parent_id', DB_PFX.'goods_cat', "id = $cid");
		$title = $row['title'];
		$url = $href = Url::getStaticUrlForModuleAndModuleId(DB_PFX.'url', DB_PFX.'goods_cat', $cid);
		$parent_id = intval($row['parent_id']);
		if ($cid==$sid) $str = "<span>$title</span> ";
		else $str = "<a href=\"/$url\">$title</a> ".$str;
		if ($parent_id > 0) {
			$str = " $separator $str";
			$str = self::get_path_link($parent_id, $str, $separator, $sid);
		}
		return $str;
	}
   
  static function get_arr_act_cat_items($sid, $arr){
    $item = db::row('*',  DB_PFX.'goods_cat', 'id = '.$sid);
    
    if($item['parent_id']){
      $arr[] = $item['parent_id'];
      return self::get_arr_act_cat_items($item['parent_id'] ,$arr);
    }else{
      return $arr;
    }
  }
 
  static function get_left_menu(&$site){
  	
    $output = '';
    
    /*$output .= '
      <script type="text/javascript">
        $(document).ready(function() {
  	  	
        $(".list-group-item").click(function(e) {
  	  	  //e.preventDefault();
          if(!$(this).children("a").is(":focus")){
            if( $(this).next(".sub_item").is(":visible") ){
              if($(this).next(".sub_item").length){
              $(this).next(".sub_item").slideUp();
              $(this).next(".sub_item").find(".sub_item").slideUp();
              $(this).css("background", "url(\'css/img/lm_open.png\') no-repeat 0px 14px ");
              }
    	  	  }else{
              if($(this).next(".sub_item").length){
                
              $(this).next(".sub_item").slideDown();
              $(this).css("background", "url(\'css/img/lm_close.png\') no-repeat 0px 14px ");  
              
              }
    		    }
          }
  	  	
  	  	});
  	  	

  	  });
      </script>
    ';*/
    
    $arr_act_cat_items =array();
    $tbl_url = DB_PFX."url";
    $tbl_goods_cat = DB_PFX."goods_cat";
    $tbl_goods = DB_PFX."goods";
    
    if($site->getModule() == $tbl_goods_cat && $site->getModuleId()){
      $arr_act_cat_items[] = $site->getModuleId();
      $arr_act_cat_items = self::get_arr_act_cat_items($site->getModuleId(), $arr_act_cat_items); 
    }elseif($site->getModule() == $tbl_goods && $site->getModuleId()){
      $act_cat_id = db::value('cat_id', $tbl_goods, 'id = '.$site->getModuleId());
      $arr_act_cat_items[] = $act_cat_id;
      $arr_act_cat_items = self::get_arr_act_cat_items($act_cat_id, $arr_act_cat_items); 
    }
    
    #  <div class = "left_menu_header">Каталог товаров</div>
    $output .= '
      <div class="list-group cat-menu goods_cats">
        <div class="left_menu_header">Каталог</div>
    ';
    
    $s = "
    SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url`
    FROM `$tbl_goods_cat` 
    LEFT JOIN `$tbl_url`
    ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
    WHERE `$tbl_goods_cat`.`hide` = 0
    AND `parent_id` = 1
    ORDER BY `$tbl_goods_cat`.`ord`
    ";
    
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
          
          $s_sub = "
            SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url`
            FROM `$tbl_goods_cat` 
            LEFT JOIN `$tbl_url`
            ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
            WHERE `$tbl_goods_cat`.`hide` = 0
            AND `parent_id` = ".$r['id']."
            ORDER BY `$tbl_goods_cat`.`ord`
          ";
          $count = mysql_num_rows(mysql_query($s_sub));
          
          $glyphicon = $display = $style = '';
          
          if($count){
            if($active){
              //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_close.png\') 0px 14px no-repeat;" ';
              $display = "block";
            }else{
              //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
              $style = ' style="background: url(\'css/img/lm_open.png\') 0px 14px no-repeat;" ';
              $display = "none";
            }
          }
          
          
          $glyphicon = '';
          $output .= '
            <div class="list-group-item" '.$style.'><a href="/'.$r['url'].'" >'.$glyphicon.' '.$r['title'].'</a></div>
          ';  
          
          //if($active){
            $output .= self::get_left_sub_menu($s_sub, $arr_act_cat_items, $display);  
          //}

        }
      }
    }
      

    
    $output .= '
      </div><!--/span-->
    ';
    
    
    
    return $output;
  }
  
  static function get_left_sub_menu($s_sub, $arr_act_cat_items, $display = "none"){
    $output = '';
    $tbl_goods_cat = DB_PFX."goods_cat";
    $tbl_url = DB_PFX."url";
    //echo "s_sub = $s_sub";
    
    //<span class="glyphicon glyphicon-plus"></span>
    
    if($q = mysql_query($s_sub)){
      if(mysql_num_rows($q)){
        $output .= '<div class = "sub_item" style = "display: '.$display.'; ">';
        while($r = mysql_fetch_assoc($q)){
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
            
          
          $s_sub = "
            SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url`
            FROM `$tbl_goods_cat` 
            LEFT JOIN `$tbl_url`
            ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
            WHERE `$tbl_goods_cat`.`hide` = 0
            AND `parent_id` = ".$r['id']."
            ORDER BY `$tbl_goods_cat`.`ord`
          ";
          
          $count = mysql_num_rows(mysql_query($s_sub));
          $glyphicon = $style = '';
          
          if($count){
            if($active){
              //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_close.png\') 0px 14px no-repeat;" ';
              $display = "block";
            }else{
              //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_open.png\') 0px 14px no-repeat;" ';
              $display = "none";
            }
          }
          $glyphicon = '';
          $output .= '
            <div class="list-group-item  " '.$style.'><a href="/'.$r['url'].'" >'.$glyphicon.' '.$r['title'].'</a></div>
          ';
          
          //if($active){
            $output .= self::get_left_sub_menu($s_sub, $arr_act_cat_items, $display);
          //}

        }
        $output .= '</div>';
      }
    }
    
    return $output;
    
  }
  
  static function show_mine_menu($site){
    
    $output = '';
    
    $arr_act_cat_items =array();
    $tbl_url = DB_PFX."url";
    $tbl_goods_cat = DB_PFX."goods_cat";
    $tbl_goods = DB_PFX."goods";
    
    if($site->getModule() == $tbl_goods_cat && $site->getModuleId()){
      $arr_act_cat_items[] = $site->getModuleId();
      $arr_act_cat_items = self::get_arr_act_cat_items($site->getModuleId(), $arr_act_cat_items); 
    }elseif($site->getModule() == $tbl_goods && $site->getModuleId()){
      $act_cat_id = db::value('cat_id', $tbl_goods, 'id = '.$site->getModuleId());
      $arr_act_cat_items[] = $act_cat_id;
      $arr_act_cat_items = self::get_arr_act_cat_items($act_cat_id, $arr_act_cat_items); 
    }
    
    $s = "
      SELECT  `$tbl_goods_cat` . * ,  `$tbl_url`.`url` 
      FROM  `$tbl_goods_cat` 
      LEFT JOIN  `$tbl_url` ON (  `$tbl_url`.`module` =  '$tbl_goods_cat' ) 
      AND (
      `$tbl_url`.`module_id` =  `$tbl_goods_cat`.`id`
      )
      WHERE  `$tbl_goods_cat`.`parent_id` = 1
      ORDER BY  `$tbl_goods_cat`.`ord` 
    ";
    
    if($q = mysql_query($s)){
      if( mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          $sub_menu = '';
          
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
          if($sub_menu = self::show_sub_mine_menu($id)){
            $output .= '
              <li class="dropdown '.$active.'">
                <a href="'.$url.'" class="dropdown-toggle" data-toggle="dropdown">'.$title.' <b class="caret"></b></a>
              '.$sub_menu.'
              </li>
            ';
          }else{
            $output .= '
              <li class="'.$active.'" ><a href="'.$url.'">'.$title.'</a></li>
            ';
          }
          
        }
      }
    }
    
    return $output;
    
  }
  
  static function get_chaild_cat_arr($cat_id, &$arr){
    
    $tbl_goods_cat = DB_PFX."goods_cat";
    
    $s = "
      SELECT `$tbl_goods_cat`.*
      FROM   `$tbl_goods_cat`
      WHERE  `$tbl_goods_cat`.`hide` = 0
      AND    `$tbl_goods_cat`.`parent_id` = $cat_id
      ORDER BY `ord`
    ";
    
    if($q = mysql_query($s))
      if(mysql_num_rows($q))
        while($r = mysql_fetch_assoc($q)){
          $arr[] = $r['id'];
          self::get_chaild_cat_arr($r['id'], $arr);
        }
      
  }
  
  static function show_mine_goods_cat(&$site ){
    $output = '';
    
    
    $tbl_goods_cat = DB_PFX.'goods_cat';
    $tbl_url = DB_PFX.'url';
    $s = "
      SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url` 
      FROM `$tbl_goods_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
      WHERE `$tbl_goods_cat`.`fl_show_mine` = 1
      AND `$tbl_goods_cat`.`hide` = 0
      ORDER BY `$tbl_goods_cat`.`ord`
    "; #pri($s);
    
    $output .= self::show_sub_cats($site, null, $s);
    
    return $output;
  }
  
  static function show_mine_goods(&$site, $parent_id ){
    $output = '';
    
    $tbl_goods_cat = DB_PFX.'goods_cat';
    $tbl_goods = DB_PFX.'goods';
    $tbl_url = DB_PFX.'url';
    $s = "
      SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url` 
      FROM `$tbl_goods_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
      WHERE `$tbl_goods_cat`.`parent_id` = $parent_id
      AND `$tbl_goods_cat`.`hide` = 0
      ORDER BY `$tbl_goods_cat`.`ord`
    "; #pri($s);
    
    
    if($q = $site->pdo->query($s)){
      if ( $q->rowCount() ){
        
      	while ($r = $q->fetch()){
          $s_g  = "
            SELECT `$tbl_goods`.*, `$tbl_url`.`url` 
            FROM `$tbl_goods`
            LEFT JOIN `$tbl_url`
            ON (`$tbl_url`.`module` = '$tbl_goods') AND (`$tbl_url`.`module_id` = `$tbl_goods`.`id`)
            WHERE `$tbl_goods`.`cat_id` = {$r['id']}
            AND `$tbl_goods`.`fl_show_mine`  = 1 
            AND `$tbl_goods`.`hide`  = 0 
            ORDER BY `ord`
          "; #pri( $s_g );
          
          $q_g = $site->pdo->query($s_g);
          $cat_count = $q_g->rowCount();
          
          if($cat_count){
            $output .= '<p class="c_h1">'.$r['title'].'</p>';
            $output .= Goods::show_catalog_items($site, $s_g, $cat_count, $filter );
          }
          
          $output .= Goods::show_mine_goods( $site, $r['id'] );
          
          
        }
      }
    }
    
    return $output;
  }
  
  static function show_sub_mine_menu($parent_id){
    $output = '';
    
    $tbl_url = DB_PFX."url";
    $tbl_goods_cat = DB_PFX."goods_cat";
    
    $s = "
      SELECT  `$tbl_goods_cat` . * ,  `$tbl_url`.`url` 
      FROM  `$tbl_goods_cat` 
      LEFT JOIN  `$tbl_url` ON (  `$tbl_url`.`module` =  '$tbl_goods_cat' ) 
      AND (
      `$tbl_url`.`module_id` =  `$tbl_goods_cat`.`id`
      )
      WHERE  `$tbl_goods_cat`.`parent_id` = $parent_id
      ORDER BY  `$tbl_goods_cat`.`ord` 
    ";
    
    if($q = mysql_query($s)){
      if( mysql_num_rows($q)){
        $output .= '
          <ul class="dropdown-menu navmenu-nav">
        ';
        
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          $output .= '
            <li><a href="'.$url.'">'.$title.'</a></li>
          ';
        }
        $output .= '
          </ul>
        ';
    
      }
    }
    return $output;
  }
  
  static function get_arr_sub_cat_items($sid, &$cat_arr, &$arr){
    
    foreach($cat_arr as $item){
      if($item['parent_id'] == $sid){
        $arr[] = $item['id'];
        self::get_arr_sub_cat_items($item['id'], $cat_arr, $arr);
      }
    }
    
  }
  
  static function get_arr_parent_cat_items($sid, &$cat_arr, &$arr){
    
    foreach($cat_arr as $item){
      if($item['id'] == $sid){
        $arr[] = $item['id'];
        self::get_arr_parent_cat_items($item['parent_id'], $cat_arr, $arr);
      }
    }
    
  }
 
  static function show_sub_cats(&$site, $sid, $s = null){
    
    $output = '';
    
    if(!$s){
      $s = "
        SELECT  `".DB_PFX."goods_cat` . * , 
                `".DB_PFX."url`.`url` 
        FROM  `".DB_PFX."goods_cat` 
        
        LEFT JOIN  `".DB_PFX."url` ON (  `".DB_PFX."url`.`module` =  '".DB_PFX."goods_cat' ) 
        AND (
        `".DB_PFX."url`.`module_id` =  `".DB_PFX."goods_cat`.`id`
        )
        WHERE `".DB_PFX."goods_cat`.`parent_id` =  $sid 
        AND `".DB_PFX."goods_cat`.`hide` = 0
        ORDER BY  `".DB_PFX."goods_cat`.`ord`
      "; #pri($s);
    }
    
    if($q = $site->pdo->query($s)){
      if( $q->rowCount()){
        $output .= '
          <div class = "m_catalog">
            <div class="catalog_dir card-deck">
        '; 
        while($r = $q->fetch()){
          $output .= self::show_goods_cat_card($r);
        }
        $output .= '
            </div>
          </div>
        ';
      }
    }
    
    return $output;
    
  }
  
  static function show_goods_cat_card($goods_cat_arr){
    $output = '';
    $r = $goods_cat_arr;
    ($r['img']) ? $image = '/images/goods/cat/slide/'.$r['img'] : $image = '/css/img/nofoto.gif' ;
    $output .= '
      <div class="card">
        <a href="/'.$r['url'].'">
          <div class="card_img_box">  
            <img class="" src = "'.$image.'" alt = "Изображение - '.$r['title'].'"   title = "'.$r['title'].'">
            <div class="card_img_shadow"></div>
          </div>
        </a> 
        <div class="card-body">
        </div>
        <div class="card-footer">
          <p class="card-title"><a href="/'.$r['url'].'">'.$r['title'].'</a></p> 
        </div>
      </div>';
      
    return $output;
  }
  
  static function show_no_photo_goods(&$site){
    $output = '';
    
    $tbl_goods_cat = DB_PFX.'goods_cat';
    $tbl_goods = DB_PFX.'goods';
    $tbl_url = DB_PFX.'url';
    
    $s = "
      SELECT `$tbl_goods`.*, `$tbl_url`.`url` 
      FROM `$tbl_goods`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods') AND (`$tbl_url`.`module_id` = `$tbl_goods`.`id`)
      WHERE 1
      ORDER BY `$tbl_goods`.`id` DESC
    ";
    
    $output .= "<h1>Товары</h1>";
    
    $i = 1;
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          if(!$img){
            $output .=  $i++.'. Фото нет в карточке товара <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
            continue;
          }
          
           
          $filename = 'images/goods/orig/'.$img;
          
          // пробуем открыть файл для чтения
          if (@fopen($filename, "r")) {
            //$output .= "Файл $filename существует<br>";
          } else {
            $output .= $i++.'. Фото отсутствует на сервере, но есть карточке товара <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
          }

                      
          
        }
      }
    }
    
    $s = "
      SELECT `$tbl_goods_cat`.*, `$tbl_url`.`url` 
      FROM `$tbl_goods_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
      ORDER BY `$tbl_goods_cat`.`id` DESC
    ";
    
    $output .= "<h1>Разделы</h1>";
    
    $i = 1;
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          if(!$img){
            $output .=  $i++.'. Фото нет в карточке раздела <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
            continue;
          }
          
          $filename = 'images/goods/cat/orig/'.$img;

            if (@fopen($filename, "r")) {
              //$output .= "Файл $filename существует<br>";
            } else {
              $output .= $i++.'. Фото отсутствует на сервере, но есть карточке раздела <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
            }
          
        }
      }
    }
    
    
    return $output;
    
  }
  
  static function show_cat_all_goods(&$site, $cat_id = 1, $indent = '' ){
    $output = "";
    
    $tbl_goods_cat = DB_PFX.'goods_cat';
    $tbl_goods = DB_PFX.'goods';
    $tbl_url = DB_PFX.'url';
    
    $s = "
      SELECT `$tbl_goods_cat`.*, `$tbl_url`.`url`
      FROM `$tbl_goods_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
      WHERE `$tbl_goods_cat`.`parent_id` = $cat_id
      ORDER BY `$tbl_goods_cat`.`title`
      
    ";
    #$output .= "<pre>$s</pre>";
    $indent .= "-&nbsp;".$indent;
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          
          $bread_crumbs = '
          <div class="bread_crumbs_box ">
            <div class="bread_crumbs">
              <a href="\">Главная</a> →
          ';
          #$bread_crumbs .= self::get_path_link($cat_id, $str = '', "→",  null);
          $bread_crumbs .= ' → '.$title.'
            </div>
          </div>
          ';
          #$output .= $bread_crumbs;
          
          $output .= '<h2> '.$indent.' <a href = "/'.$url.'" > '.$title.'</a> </h2>';
          $output .= self::show_all_goods($site, $id);
          $output .= self::show_cat_all_goods($site, $id, $indent);
          $output .= '<br><br>';
        }
      }
    }
    
    
    return $output;
  }
  
  static function show_cat_items(&$site, $cid, $cat_table, $table){
    
    $goods_tbl = DB_PFX.'goods';
    $goods_cat_tbl = DB_PFX.'goods_cat';
    $all_images_tbl = DB_PFX.'all_images';
    $all_files_tbl = DB_PFX.'all_files';
    
    $cat_item = db::select("*", $goods_cat_tbl, "id = ".$cid, null, null, 1, 0 );
    $cat_full = array();
    $cat_full = $cat_item;
    #pri($cat_item);
    
    // Вывод товаров включая подразделы
    /*$arr_sub_cat_id = array($cid); $str_sub_cat_id = '';
    self::get_arr_sub_cat_items( $cid, $site->cat_arr, $arr_sub_cat_id);
    #pri($arr_sub_cat_id);
    
    $i = 0;
    foreach($arr_sub_cat_id as $c_id){
      if( $i++ ) $str_sub_cat_id .= ' ,';
      $str_sub_cat_id .= $c_id;
    }
    
        
    #echo "str_sub_cat_id = $str_sub_cat_id";
    $s_where = ' `'.$goods_tbl.'`.`cat_id` IN ( '.$str_sub_cat_id.' ) ';
    */
    $s_where = ' `'.$goods_tbl.'`.`cat_id` = '.$cid.' ';
    
    // ------------- SEO -------------
    {

      if($cat_item['seo_title']){
        $site->siteTitle = $cat_item['seo_title'];
      }else{
        if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'goods_cat_title'" )){
          $site->siteTitle = str_replace("*h1*", $cat_item['title'], $seo_title);
        }else{
          $site->siteTitle = $cat_item['title'];
        }
      }
      
      if($cat_item['seo_description']) $site->setSiteDescription($cat_item['seo_description']);
      
      if($cat_item['seo_keywords'])    $site->setSiteKeywords($cat_item['seo_keywords']);
    }
    // ------------- END SEO -------------
    
    
    // ------------- SEO Images -------------
    {   
      $count_seo_img_alt = 0;
      $img_alt = $cat_item['img_alt'];
      $title = $cat_item['title'];
      if($img_alt){
        $seo_img_alt_str = $img_alt;
      }else{
        if($seo_img_alt_str =  db::value('value', DB_PFX.'seo', "type = 'img_alt'" )){
          #$img_alt_txt= str_replace("*h1*", $title.' '.$article, $seo_img_alt);
        }else{
          $seo_img_alt_str = $title;
          #$img_alt_txt[0] = $title.' '.$article;
        }
      
      }
      $seo_img_alt_arr = explode("/", $seo_img_alt_str);
      $i = 0;
      foreach($seo_img_alt_arr as $seo_img_alt_item){
        $img_alt_txt[$i] = trim(str_replace("*h1*", $title, $seo_img_alt_item));
        $count_seo_img_alt = $i;
        $i++;
      }
      
      $img_title = $cat_item['img_title'];
      if($img_title){
        $seo_img_title_str = $img_title;
        #$img_title_txt = $img_title;
      }else{
        if($seo_img_title_str =  db::value('value', DB_PFX.'seo', "type = 'img_title'" )){
          #$img_title_txt= str_replace("*h1*", $title.' '.$article, $seo_img_title);
        }else{
          $seo_img_title_str = $title;
          #$img_title_txt = $title.' '.$article;
        }
      }
      
      $seo_img_title_arr = explode("/", $seo_img_title_str);
      $i = 0;
      foreach($seo_img_title_arr as $seo_img_title_item){
        $img_title_txt[$i] = trim(str_replace("*h1*", $title, $seo_img_title_item));
        $count_seo_img_title = $i;
        $i++;
      }
    }
    // ------------- END SEO -------------
    
    
    $output = '';
    
    
    $site->bread = self::show_path_link($cid, '', "→", false); // Хлебные крошки 
    //$cat_full['bread'] = $site->bread;
    
    // Добавить в просмотренные страницы
    $v_img = '';
    if($cat_item['img']) $v_img = Images::static_get_img_link("images/goods/cat/orig", $cat_item['img'],  'images/goods/cat/variations/140x120',  140, null, 0xFFFFFF, 90);
    $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $cat_item['title']);
    $site->addVisitedPage($site->module, $site->module_id, $v_cont );
    
    $carousel = $carousel_fierst_img = $carousel2 = $carousel_img = '';
    
    $img = $cat_item['img'];
    
    $img_exists = file_exists ("images/goods/cat/orig/$img");
    
    if (0 && $img && $img_exists){ 
      $item_full['image'] = '
      <section class="slider">
        <div id="slider1" class="flexslider">
          <ul class="slides">
            <li>
              <a href="/images/goods/cat/orig/'.$img.'" title="" rel="prettyPhoto[item_big_img]">
                <img src = "'.Images::static_get_img_link("images/goods/cat/orig/", $img,  'images/goods/cat/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = ""/>
              </a>
            </li>
      ';
      
      $s  = "
        SELECT * 
        FROM `".DB_PFX."all_images`
        WHERE `module` = '$goods_cat_tbl'
        AND `module_id` = {$cat_item['id']}
        AND `hide` = 0
        ORDER BY  `".DB_PFX."all_images`.`module_ord` 
      ";  #pri($s);
      
      if($q = $site->pdo->query($s)){
        if($q->rowCount()){
          
          $carousel = '<div id="carousel1" class="flexslider" >
            <ul class="slides">
          ';
          $carousel_fierst_img = '
            <li>
              <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $img,  'images/goods/cat/variations/90x90',  90, null, 0xFFFFFF, 90).'"  class="slide_img_mini" alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' "  style = ""/>
            </li>
          ';
          $carousel2 = '
            </ul>
          </div>
          ';
          $i = $p = 0;
          while($r = $q->fetch()){
            if($i > $count_seo_img_alt) $i = 0;
            if($p > $count_seo_img_title) $p = 0;
            $item_full['image'] .= '
              <li>
                <a href="/images/all_images/orig/'.$r['img'].'" title="" rel="prettyPhoto[item_big_img]">
                  <img src = "'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/500x500',  500, null, 0xFFFFFF, 100).'" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].' " class="slide_img" style = ""/>
                </a>
              </li>
            ';
            
            $carousel_img .= '
              <li>
                <img src="'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/90x90',  90, null, 0xFFFFFF, 90).'" class="slide_img_mini" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].'"/>
              </li>
            ';
            
            
            $i++; $p++;
          }
          
        $item_full['image'] .= '
            </ul>
          </div>
        ';
        $item_full['image'] .= $carousel.$carousel_fierst_img.$carousel_img.$carousel2;
        $item_full['image'] .= '
          </section>
        ';
        }else{
          
          $item_full['image'] =  '
                      <a href="/images/goods/cat/orig/'.$img.'" title="" rel="prettyPhoto[item_big_img]">
                <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $img,  'images/goods/cat/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = "/*float: left;*/ padding: 0 15px 15px 0;"/>
              </a>
          ';
        }
        
        
      }else{

      }

      
    /*$item_full['image'] .=  '
      <script src="/flexslider/jquery.flexslider.js"></script>
      <script type="text/javascript" charset="utf-8">
        $(window).load(function() {
          $("#slider1").flexslider({
            animation: "slide",
            controlNav: false,
            prevText: "",
            nextText: "",
            animationLoop: false,
            slideshow: false
          });
        });
      </script>  
    ';*/ 
    
    $site->js_scripts .=  '
    <!-- FlexSlider -->
    <script defer src="/vendors/flexslider/jquery.flexslider.js"></script>
    <script type="text/javascript">
      $(window).ready(function(){
        $("#carousel1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          itemWidth: 90,
          itemMargin: 5,
          asNavFor: "#slider1"
        });

        $("#slider1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          sync: "#carousel1",
          start: function(slider){
            $("body").removeClass("loading");
            $(".slide_img").each(function(){
              $(this).css("margin-top", ($("#slider1").height() - $(this).height())/2+"px");
            });
            $(".slide_img_mini").each(function(){
              $(this).css("margin-top", ($("#carousel1").height() - $(this).height())/2+"px");
            });
          }
        });
      });
    </script>
    ';
      
    }else{
      /*$item_full['image'] ='
            <img src = "/css/img/no_photo.jpg" style = "float: left; padding: 0 15px 15px 0;"/>
      ';*/
      $item_full['image'] = '';
    }
    
    
    $cat_full['image'] = $item_full['image'];
    
    $cat_full['sub_cats'] = self::show_sub_cats($site, $cid);
    
    //$s_where = ' `'.$goods_tbl.'`.`cat_id` IN ( '.$str_sub_cat_id.' ) ';
       
    $s = "
    SELECT COUNT( * ) AS count
    FROM `$goods_tbl`
    WHERE $s_where
    AND hide = 0 
    ";
    //echo "s = $s<br>";
    #$r = mysql_fetch_assoc(mysql_query($s));
    $q = $site->pdo->query($s);
    $r = $q->fetch();
    $cat_count = $r['count'];
    
    $pagerPage = 0;
    if(isset($_GET['page'])){
      if($_GET['page']){
        $pagerPage = intval($_GET['page']);
        //echo "pagerPage = $pagerPage<br>";
      }
    }
    
    
    $s_filter = $limit = $s_cat_sorting = '';
    $s_cat_sorting = " ORDER BY `ord` ";
    //$s_cat_sorting = " ORDER BY `img` DESC ";
    //$limit = 'LIMIT 20';
    
    $strPager = Article::getPager($site, $pagerPage, $cat_count, 20, $limit);
    
    //echo "limit = $limit<br>";
    
    
    
    $s = "
    SELECT `$goods_tbl`.*, `".DB_PFX."url`.`url` 
    FROM `$goods_tbl`
    LEFT JOIN `".DB_PFX."url`
    ON (`".DB_PFX."url`.`module` = '$goods_tbl') AND (`".DB_PFX."url`.`module_id` = `$goods_tbl`.`id`)
    WHERE $s_where
    AND `$goods_tbl`.`hide`  = 0 
    $s_filter
    $s_cat_sorting
    $limit
    "; #pri($s);
    
    $cat_full['cat_items'] = '';
    
    if($cat_count){
      $cat_full['cat_items'] .= '';
    }
    
    //echo "<pre>s = $s</pre><br>";
    $cat_full['cat_items'] .= $strPager;
    
    $cat_full['cat_items'] .= '
      <div class="catalog_box">
    ';
    $cat_full['cat_items'] .= self::show_catalog_items($site, $s, $cat_count, $filter );

    $cat_full['cat_items'] .= '
      </div>';
    $cat_full['cat_items'] .= $strPager;
    
    $output = self::tmp_cat_page($cat_full);

    return $output;
    
  }
  
  static function show_catalog_items(&$site, $s, $filter_count, &$filter = null, $prefix_url = ''){
    $output = "";
    #echo "<pre>s = $s</pre>";
    $q = $site->pdo->query($s);
    if($filter_count){
      
      $filter_item = '';
      
      $seo_img_alt_str_wed =  db::value('value', DB_PFX.'seo', "type = 'img_alt'");
      $seo_img_title_str_wed =  db::value('value', DB_PFX.'seo', "type = 'img_title'" );
      
      $output .= '
        <div class="cat_box">
          <div class="catalog_items card-deck">';
      
      $availability_arr = db::select('*', DB_PFX."availability", "hide = 0");
      $site->goods_availability = array();
      foreach($availability_arr as $k=>$v){
        $site->goods_availability[$v['id']] = $v['title'];
      } #pri($availability_arr);
      
      $j = 0;
      while($r = $q->fetch()){
        extract($r);

        // ------------- SEO -------------
        {
          $count_seo_img_alt = 0;
          if($img_alt){
            $seo_img_alt_str = $img_alt;
          }else{
            if($seo_img_alt_str_wed =  db::value('value', DB_PFX.'seo', "type = 'img_alt'" )){
              $seo_img_alt_str = $seo_img_alt_str_wed;
            }else{
              $seo_img_alt_str = $title.' '.$article;
            }
          
          }
          $seo_img_alt_arr = explode("/", $seo_img_alt_str);
          $i = 0;
          foreach($seo_img_alt_arr as $seo_img_alt_item){
            $img_alt_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_alt_item));
            if($i++) break;
          }
          
          
          if($img_title){
            $seo_img_title_str = $img_title;
          }else{
            if($seo_img_title_str_wed){
              $seo_img_title_str = $seo_img_title_str_wed;
            }else{
              $seo_img_title_str = $title.' '.$article;
            }
          }
          
          $seo_img_title_arr = explode("/", $seo_img_title_str);
          $i = 0;
          foreach($seo_img_title_arr as $seo_img_title_item){
            $img_title_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_title_item));
            if($i++) break;
          }
        }
        // ------------- END SEO -------------
        $cat_item = array();
        $cat_item = $r;
        $cat_item['url'] =  $prefix_url.$cat_item['url'];
        $cat_item['img_alt_txt'] = $img_alt_txt[0];
        $cat_item['img_title_txt'] = $img_title_txt[0];
        $cat_item['availability'] = $site->goods_availability[$r['availability_id']];
        //$cat_item['price'] = number_format(Goods::getPrice( $site, $cat_item['price'], $cat_item['price_ye'] ), 2, ',', ' ')." KZT";
        
        $img_exists = file_exists ("images/goods/orig/$img");
        
        if ($img && $img_exists){
          $cat_item['image'] = '
            <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/450x450',  450, null, 0xFFFFFF, 95).'" title = "'.$img_title_txt[0].'" alt = "'.$img_alt_txt[0].'">
          ';
        }elseif( $cat_item['cat_image'] = db::value("img", DB_PFX."goods_cat", "id = ".$cat_id) ){
          $cat_item['image'] = '
            <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $cat_item['cat_image'],  'images/goods/cat/variations/100x100',  100, null, 0xFFFFFF, 95).'" title = "'.$img_title_txt[0].'" alt = "'.$img_alt_txt[0].'">
          ';  
        }else{
          $cat_item['image'] ='
            <img src = "/css/img/no_photo.jpg">
          ';
        }
        $output .= self::tmp_cat_item($cat_item, ++$j);
        
        
      }
      $output .= '
          </div>
        </div>
      ';

    }else{
      #$output .= "Нет таких товаров";
      $output .= "";
    }
    
    return $output;
  }
  
  static function getPrice(&$site, $rub, $ye){
    if($rub > 0){
      $output = $rub;
    }else{
      $output = $ye * $site->ye;
    }
    return $output;
  }
  
  static function show_item_full(&$site, $item, $bread_crumbs = null) {
		if (!$item) return;
		extract($item);
    
    $output = '';
    $goods_tbl = DB_PFX.'goods';
    $goods_cat_tbl = DB_PFX.'goods_cat';
    $all_images_tbl = DB_PFX.'all_images';
    $all_files_tbl = DB_PFX.'all_files';
      
    $item_full = array();
    $item_full = $item;
    $item_full['url'] = Url::getStaticUrlForModuleAndModuleId(DB_PFX."url", DB_PFX."goods", $id);
    /*Добавление id товара в массив просмотренных товаров*/{
    
    if(!isset($_SESSION['item_history'])){
      $_SESSION['item_history'] = array();
    }
    
    $historyArr = $_SESSION['item_history'];

    $newHistoriArr[] = $id;
    foreach ($historyArr as $his) {
        if ($his != $id) {
            $newHistoriArr[] = $his;
        }
    }

    $_SESSION['item_history'] = $newHistoriArr;
      
      //print_r($_SESSION['item_history']);

    }/* End Добавление id товара в массив просмотренных товаров*/
    
    /*
    $q = $site->pdo->query("SELECT * FROM `".DB_PFX."basket_price` WHERE `item_id` = $id ORDER BY `from`") or die(mysql_error()); 
    while ($rows = $q->fetch()) {
      $prices[] = $rows;
    }*/
    
    // ------------- SEO -------------
    {
      if($item['seo_title']){
        $site->siteTitle = $item['seo_title'];
      }else{
        if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'goods_title'" )){
          $site->siteTitle = str_replace("*h1*", $item['title'], $seo_title);
        }else{
          $site->siteTitle = $item['title'];
        }
      }

      if($item['seo_description']) $site->setSiteDescription($item['seo_description']);
      
      if($item['seo_keywords'])    $site->setSiteKeywords($item['seo_keywords']);
        
      $count_seo_img_alt = 0;
      if($img_alt){
        $seo_img_alt_str = $img_alt;
      }else{
        if($seo_img_alt_str =  db::value('value', DB_PFX.'seo', "type = 'img_alt'" )){
        }else{
          $seo_img_alt_str = $title.' '.$article;
        }
      
      }
      $seo_img_alt_arr = explode("/", $seo_img_alt_str);
      $i = 0;
      foreach($seo_img_alt_arr as $seo_img_alt_item){
        $img_alt_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_alt_item));
        $count_seo_img_alt = $i;
        $i++;
      }
      
      
      if($img_title){
        $seo_img_title_str = $img_title;
      }else{
        if($seo_img_title_str =  db::value('value', DB_PFX.'seo', "type = 'img_title'" )){
        }else{
          $seo_img_title_str = $title.' '.$article;
        }
      }
      
      $seo_img_title_arr = explode("/", $seo_img_title_str);
      $i = 0;
      foreach($seo_img_title_arr as $seo_img_title_item){
        $img_title_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_title_item));
        $count_seo_img_title = $i;
        $i++;
      }
    }
    // ------------- END SEO -------------
    
    if($bread_crumbs){
      $site->bread = $bread_crumbs;
    }else{
      $item_full['bread_crumbs'] = '<a href="\">Главная</a> → '.self::get_path_link($cat_id, $str = '', "→",  null) ; // Хлебные крошки 
      $site->bread = '
      <div class="bread_crumbs_box ">
        <div class="bread_crumbs">
          '.$item_full['bread_crumbs'].' → '.$item_full['title'].'
        </div>
      </div>
      ';
    }
    //$output .= $site->bread ;
    //$output .= $site->search->showSearchLine(); 
    
    // Добавить в просмотренные страницы
    $v_img = '';
    if($item_full['img']) $v_img = Images::static_get_img_link("images/goods/orig", $item_full['img'],  'images/goods/variations/140x120',  140, null, 0xFFFFFF, 90);
    $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $item_full['title']);
    $site->addVisitedPage($site->module, $site->module_id, $v_cont );
    
    $carousel = $carousel_fierst_img = $carousel2 = $carousel_img = '';
    
    $img_exists = file_exists ("images/goods/orig/$img");
    if ($img && $img_exists){ 
      $item_full['image'] = '
      <section class="slider">
        <div id="slider1" class="flexslider">
          <ul class="slides">
            <li>
              <a class="fancyfoto fancybox.iframe" href="/images/goods/orig/'.$img.'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = ""/>
              </a>
            </li>
      ';
      
      
      $s  = "
        SELECT * 
        FROM `$all_images_tbl`
        WHERE `module` = '$goods_tbl'
        AND `module_id` = $id
        AND `hide` = 0
        ORDER BY  `$all_images_tbl`.`module_ord` 
      "; 
      
      if($q = $site->pdo->query($s)){
        if($q->rowCount()){
          
          $carousel = '<div id="carousel1" class="flexslider" >
            <ul class="slides">
          ';
          $carousel_fierst_img = '
            <li>
              <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/90x90',  90, null, 0xFFFFFF, 90).'"  class="slide_img_mini" alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' "  style = ""/>
            </li>
          ';
          $carousel2 = '
            </ul>
          </div>
          ';
          $i = $p = 0;
          while($r = $q->fetch()){
            if($i > $count_seo_img_alt) $i = 0;
            if($p > $count_seo_img_title) $p = 0;
            $item_full['image'] .= '
              <li>
                <a class="fancyfoto fancybox.iframe" href="/images/all_images/orig/'.$r['img'].'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                  <img src = "'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/500x500',  500, null, 0xFFFFFF, 100).'" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].' " class="slide_img" style = ""/>
                </a>
              </li>
            ';
            
            $carousel_img .= '
              <li>
                <img src="'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/90x90',  90, null, 0xFFFFFF, 90).'" class="slide_img_mini" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].'"/>
              </li>
            ';
            
            
            $i++; $p++;
          }
          
        $item_full['image'] .= '
            </ul>
          </div>
        ';
        $item_full['image'] .= $carousel.$carousel_fierst_img.$carousel_img.$carousel2;
        $item_full['image'] .= '
          </section>
        ';
        }else{
          
          $item_full['image'] =  '
            <div class="full_img_box">
              <a class="fancyfoto fancybox.iframe" href="/images/goods/orig/'.$img.'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = "max-width: 100%;"/>
              </a>
            </div> 
          ';
        }
        
      }else{

      }
      
    /*$item_full['image'] .=  '
      <script src="/flexslider/jquery.flexslider.js"></script>
      <script type="text/javascript" charset="utf-8">
        $(window).load(function() {
          $("#slider1").flexslider({
            animation: "slide",
            controlNav: false,
            prevText: "",
            nextText: "",
            animationLoop: false,
            slideshow: false
          });
        });
      </script>  
    ';*/
    
    $site->js_scripts .=  '
    <!-- FlexSlider -->
    <script defer src="/vendors/flexslider/jquery.flexslider.js"></script>
    <link href="/vendors/flexslider/flexslider.css" rel="stylesheet" >
    <script type="text/javascript">
      $(window).ready(function(){
        $("#carousel1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          itemWidth: 90,
          itemMargin: 5,
          asNavFor: "#slider1"
        });

        $("#slider1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          sync: "#carousel1",
          start: function(slider){
            $("body").removeClass("loading");
            $(".slide_img").each(function(){
              $(this).css("margin-top", ($("#slider1").height() - $(this).height())/2+"px");
            });
            $(".slide_img_mini").each(function(){
              $(this).css("margin-top", ($("#carousel1").height() - $(this).height())/2+"px");
            });
          }
        });
      });
    </script>
    ';
      
    }elseif( $cat_item['cat_image'] = db::value("img", $goods_cat_tbl, "id = ".$cat_id) ){
          /*$item_full['image'] = '
            <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $cat_item['cat_image'],  'images/goods/cat/variations/130x130',  130, null, 0xFFFFFF, 95).'" title = "'.$img_title_txt[0].'" alt = "'.$img_alt_txt[0].'">
          '; */
          $item_full['image'] = '
          <a class="fancyfoto fancybox.iframe" href="/images/goods/cat/orig/'.$cat_item['cat_image'].'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $cat_item['cat_image'],  'images/goods/cat/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = ""/>
              </a>';
    }else{
      $item_full['image'] ='
            <img src = "/css/img/no_photo.jpg" style = "max-width: 100%"/>
      ';
    }

    $i = 0;
    $s_files  = "
    SELECT * 
    FROM `$all_files_tbl`
    WHERE `module` = '$goods_tbl'
    AND `module_id` = $id
    AND `hide` = 0
    "; #pri($s_files)";
    
    $item_full['files'] = '';
    if($q_files = $site->pdo->query($s_files)){
      if($q_files->rowCount()){
        $item_full['files'] .= '
                            <div class = "doc_box">
                              <ul class = "doc">
        ';
        while($r_files = $q_files->fetch()){
          if(file_exists('images/all_files/files/'.$r_files['file'])){
            $item_full['files'] .= '
              <li><a href = "/images/all_files/files/'.$r_files['file'].'" target = "_blank">'.$r_files['title'].'</a></li>
            ';
          }

 
        }
        $item_full['files'] .= '
                              </ul>
                            </div>
        ';
      }
    }
    
      
    if($item_full['seo_h1']){
      $item_full['title'] = $item_full['seo_h1'];
    }
    
    
    if(!isset($site->units) || !$site->units){
      $unit_items =  db::select("*", DB_PFX."units" );
      
      foreach($unit_items as $unit_item){
        $site->units[ $unit_item['id'] ] = $unit_item['reduction'];
      }
    }
    
    if( isset($site->units[ $item_full['units_id'] ]) && $site->units[ $item_full['units_id'] ] ){
      $item_full['units'] = $site->units[ $item_full['units_id'] ];   
    }else{
      $item_full['units'] = "шт";
    }
    
    ($site->whatsap_phone) ? $item_full['whatsap_phone'] = $site->whatsap_phone : $item_full['whatsap_phone'] = '';
    
    $output .= self::tmp_full_item($item_full);
    
    return $output;
	}
  
  static function show_feedback(&$site){
    $output = '';
    
    $fio = $email = $phone = $comment = $error = $is_send = '';
    $error_arr = array();
    
    if (isset ($_POST['email'])) {
      $mail = EMail::Factory();
      //$to = "1@in-ri.ru"; // поменять на свой адрес
      $email_order = db::value('val', DB_PFX.'config', "name = 'email_order'");
      $fio = substr(htmlspecialchars(trim($_POST['fio'])), 0, 1000);
      $email = substr(htmlspecialchars(trim($_POST['email'])), 0, 1000);
      $phone = substr(htmlspecialchars(trim($_POST['phone'])), 0, 1000);
      $comment = substr(htmlspecialchars(trim($_POST['comment'])), 0, 10000);
      if(!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $email)){
        $error = 1;
        $error_arr['email'] = 'Не верно введен email';
      }
      if (empty($fio)){
        $error_arr['fio'] = 'Не введенно имя'; 
        $error = 1;
      }
        
         
      $subject = "Добавлен новый коментарий в разделе `Обратная связь` ".$_SERVER['HTTP_REFERER'];
      $message = "
      Имя: ".$fio."<br>
      Email: ".$email."<br>
      Телефон: ".$phone."<br>
      IP: ".$_SERVER['REMOTE_ADDR']."<br>
      Сообщение: <br>".$comment.'<br><br>';

       
      if($error != 1){
        $tosend = $message;
  		  $res = $mail->send($email_order, $subject, $tosend);
        
        if($res){
          //$output = '<script>alert("Ваше сообщение получено, спасибо!");</script>';  
          $is_send = true;
          $date = date("Y-m-d");
          $ip = $_SERVER['REMOTE_ADDR'];
          $s = "
            INSERT INTO `".DB_PFX."feedback` 
                    (`title`, `date`,  `longtxt1`, `phone`,  `email`,  `txt1`, `hide`) 
            VALUES  ('$fio',  '$date', '$comment', '$phone', '$email', '$ip',  1);
          ";
          mysql_query($s);
          
        }else{
          //$output = '<script>alert("Ошибка!");</script>';
          $error_arr['send'] = "Ошибка при отправке сообщения";
        }
      }
      
      

    }
    if($is_send){
      $output .= '
        <div class="row">
          <div class="col-xs-12" style = "min-height: 200px;">
            <h2>Благодарим за ваш отзыв!</h2>
            <p>Ваша отзыв успешно отправлен. После того как мы сформируем ответ он появиться в разделе.</p>
          </div>
        </div>
      ';
    }else{
    
      $output .= '
        <div class="row">
    
          
          <div class="col-xs-12">
          
            <p>Мы не останавливаемся в развитии и постоянно стремимся сделать наш Торговый Центр удобнее для посетителей. Ваши отзывы, советы, комментарии  здорово помогают нам в этом. Спасибо! </p>
            <p><b>ТЦ «КОСМОС»</b></p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp; - <a href = "tel:83432394386"> 239-43-86 </a> (общий)</p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp; - <a href = "tel:83432394396">239-43-96</a>  (Деж. администратор)</p><br><br>
            
          </div>

        </div>
        
        
        <div class="col-xs-12 obr_line"></div>
        
        <form id="obr_form" class="obr_form" role="form" method="post" enctype="multipart/form-data" >
        <div class="row">
          <div class="col-xs-12 col-sm-5">
          
          <h1><span class = "sosa">C</span> Обратаная связь: </h1>
          
          <div class="form-group">
            <label for="fio" class="control-label">Имя <span class="z">*</span></label>
            <input type="text" class="text form-control" name="fio" id="fio" placeholder="Имя Фамилия Отчество" required="" value="">
          </div>
          
          <div class="form-group">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.3.1/jquery.maskedinput.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function() {
               $("#phone").mask("+7 (999) 999-9999");
            });
            </script>
            <label for="phone" class="control-label">Телефон <span class="z">*</span></label>
            <input type="text" class="text form-control" name="phone" id="phone" required="" value="" placeholder="+7 (___) ___-____">
          </div>
          
          <div class="form-group">
            <label for="email" class="control-label">E-mail <span class="z">*</span></label>
            <input type="email" placeholder="your@email.com" class="text form-control" name="email" id="email" value="">
          </div>
          <div class="form-group" style = "    margin-bottom: 0px;">
                <label for="comment" class="control-label">Сообщение</label>
          </div>

          </div>
          
          <div class="col-sm-7 hidden-xs feedback_dog" ></div>
          
          <div class="col-xs-12" >
              <div class="form-group">
                <textarea name="comment" class="text form-control" id="comment" style = "min-height: 90px;"></textarea>
              </div>
              
              <div class="form-group">
                  <button type="submit" class="obr_submit btn btn-default btn_backed_remove" id="obr_submit" value="" >Отправить</button>
              </div>
          </div>
        </div>
        </form>
          
       
        <div class="col-xs-12 obr_line"></div>
        
        <br><br>
    ';
    }
    
    $output .= '
        <div class="row">
          <div class="col-xs-12">
            <h2>Популярные вопросы</h2>
          </div>
    ';
    $obr_items = db::select("*", DB_PFX."feedback", "hide = 0", "`date` DESC  ",  null, null, 0);
    
    foreach ($obr_items as $item){
      extract($item);
      $str_date = date("d", strtotime($date))." ";
      $month = date("m", strtotime($date));
      switch( $month ){
        case  1: $month = "Янаваря"; break;
        case  2: $month = "Февраля"; break;
        case  3: $month = "Марта"; break;
        case  4: $month = "Апреля"; break;
        case  5: $month = "Майя"; break;
        case  6: $month = "Июня"; break;
        case  7: $month = "Июля"; break;
        case  8: $month = "Августа"; break;
        case  9: $month = "Сентября"; break;
        case 10: $month = "Октября"; break;
        case 11: $month = "Ноября"; break;
        case 12: $month = "Декабря"; break;

      }
      
      $str_date .=  $month." ".date("Y", strtotime($date))." г.";
      
      $output .= '
          <div class="col-xs-12 obr_question_box">
            <div class="col-xs-12 obr_question">
              <div class="col-xs-12 obr_date"> '.$str_date.' </div>
              <div class="col-xs-12 obr_user"> '.$title.' </div>
              <div class="col-xs-12 obr_quest_txt">
                <p> '.$longtxt1.' </p>
              </div>
      ';
      if($longtxt2){
        $output .= '
              <div class="col-xs-12 obr_answer">
                <p><span class = "sosa obr_answer_sosa">C</span> Команда сайта<br>
                <span class = "obr_answer_indent" >&nbsp;</span>'.$longtxt2.'</p>
              </div> 
        ';
      }

      $output .= '
            </div>
          </div>
      ';
      
    }
    
    $output .= '
        </div>
    ';
    
    return $output;
  }
  
  static function tmp_cat_item(&$arr, $i = 1){
    
    $output = '';
    
    if(!isset($site->units) || !$site->units){
      $unit_items =  db::select("*", DB_PFX."units" );
      
      foreach($unit_items as $unit_item){
        $site->units[ $unit_item['id'] ] = $unit_item['reduction'];
      }
    }
    
    if( isset($site->units[ $arr['units_id'] ]) && $site->units[ $arr['units_id'] ] ){
      $arr['units'] = $site->units[ $arr['units_id'] ];   
    }else{
      $arr['units'] = "шт";
    }
    /*
    $output .= '
            <tr>
              <th scope="row">'.$i.'</th>
              <td>
                <a href="/'.$arr['url'].'">
                  <div class="card_img_box">  
                    '.$arr['image'].'
                    <div class="card_img_shadow"></div>
                  </div>
                </a> 
              </td>
              <td>
                <a href="/'.$arr['url'].'">'.$arr['title'].'</a>
                <div>
                  <table class = "ttc">';
    $output .= self::get_item_characteristic('Артикул', $arr['article']);
    $output .= '
                  </table>
                </div>
              </td>
              <td>
                <div class = "item_unit">  
                  <span class="buy_price cost">'.$arr['price'].'</span> <i class="fa fa-rub" aria-hidden="true"></i> / <span class = "units">'.$arr['units'].'</span>
                </div>
              </td>
              
              <td>
                <div class = "buy_count_cont"> 
                  <input  class = "store_buy_input bye_count" 
                        data-id = "'.$arr['id'].'" 
                        data-min = "'.$arr['min_count'].'" 
                        data-price = "'.$arr['price'].'"
                        data-portion = "'.$arr['portion'].'" 
                        type="number" 
                        value = "'.$arr['min_count'].'"  /> 
                </div>
              </td>
              <td  >
                <div class="store_buy_price" style = "margin-right: 15px;">
                  <span class="buy_price cost" id = "price_id_'.$arr['id'].'" data-id = "'.$arr['id'].'" >'.$arr['price'].'</span> 
                                          <i class="fa fa-rub" aria-hidden="true"></i>
                </div>
              </td>
              <td>
              	<div class = "store_buy_order_box">
                  <button class="buy_btn good_buy store_buy"
                          id = "store_id_'.$arr['id'].'" 
                          data-id="'.$arr['id'].'" 
                          data-count = "'.$arr['min_count'].'"
                          >В корзину</button>
                </div>
              </td>
              
            </tr>
    ';*/
    $output .= '
            <div class="card tac">
              <a href="/'.$arr['url'].'">
                <div class="card_img_box " >  
                  '.$arr['image'].'
                  <div class="card_img_shadow"></div>
                </div>
              </a> 
              <div class="card-body">
                <p class="card-title"><a href="/'.$arr['url'].'">'.$arr['title'].'</a></p> 
              </div>
              <div class="card-footer">
                <div class="available">'.$arr['availability'].'</div>
                <div class="npr">
                  <span class="price">'.number_format( $arr['price'], 0, ',', ' ').' </span><span class="rouble">руб.</span>
                </div>
                <div class = "card_btn_box">
                  <button class="buy_btn good_buy store_buy btn btn-primary btn-md" data-id="'.$arr['id'].'" >Купить</button>
                </div>
              </div>
            </div>
    ';
    
    return $output;
    
  }
  
  static function tmp_cat_page(&$arr){
    
    $output = '';

    $output .= '
                  <div class = "content_box full_page" >
                    <h1 class = "c_h1">'.$arr['title'].'</h1>';
    if( isset($arr['image'])     && $arr['image']     ) $output .= $arr['image'];
    if( isset($arr['longtxt2'])  && $arr['longtxt2']  ) $output .= '<div class = "clt1">'.$arr['longtxt2'].'</div>';
    if( isset($arr['cat_items']) && $arr['cat_items'] ) $output .= $arr['cat_items'];
    if( isset($arr['sub_cats'])  && $arr['sub_cats']  ) $output .= $arr['sub_cats'];
    if( isset($arr['longtxt3'])  && $arr['longtxt3']  ) $output .= '<div class = "clt2">'.$arr['longtxt3'].'</div>';
    $output .= '
                  </div>  
    ';
    
    return $output;
    
  }
  
  static function tmp_full_item(&$arr){
    #pri($arr);
    
    $output = '';
    
    $output .= '

                        <div class = "full_item_box">
                          <div class = "full_item">
                          
                            <div class="row">
                              <div class="col-12 col-md-5 full_item_left">
                                '.$arr['image'].'
                              </div>
                              <div class="col-12 col-md-7">
                                <h1>'.$arr['title'].'</h1> 
                                
                                <div class = "store_buy_box">
                                  <div class = "row align-items-center ">
                                    
                                    <div class = "col-12 col-md-auto ">
                                      <div class="store_buy">
                                        <div class="store_buy_price">
                                          <span class="buy_price cost price" id = "price_id_'.$arr['id'].'" data-id = "'.$arr['id'].'" >'.number_format( $arr['price'], 0, ',', ' ').'</span> <span class="rouble">руб.</span>';
                                          #<span class = "units">'.$arr['units'].'</span>
                                          #<i class="fa fa-rub" aria-hidden="true"></i>
    $output .= '                          
                                        </div>
                                      </div>
                                    </div>
                                    
                                    <div class = "col-12 col-md-auto store_buy_order_box">
                                      <button class="buy_btn good_buy store_buy btn btn-primary btn-lg"
                                              id = "store_id_'.$arr['id'].'" 
                                              data-id="'.$arr['id'].'" 
                                              ><i class="fas fa-cart-plus fa-md"></i>&nbsp; В корзину</button>
                                      
                                    </div>
                                  </div>
                                  
                                  <div class = "row align-items-center ">
                                    <div class = "col-12 store_buy_info">
                                      <div class="row characteristic_box">
                                        <div class="col-12 col-md">
                                          <table>';
    $output .= self::get_item_characteristic('Артикул', $arr['article']);
    $output .= '
                                          </table>';
    if( isset($arr['longtxt1']) && $arr['longtxt1']){
      $output .= ''.$arr['longtxt1'].'';
    }
    $output .= '
                                        </div>
                                        
                                      </div>

                                    </div>
                                  </div>';
    #$fast_title = '<a href = \''.$_SERVER["REQUEST_URI"].'\' target = \'_blank\'>'.preg_replace('#[^A-ZА-Яa-zа-я\d\s\+\-\_\,\«\/\»\(\)]#u', '', $arr['title'])."</a>";
    $fast_title = preg_replace('#[^A-ZА-Яa-zа-я\d\s\+\-\_\,\«\/\»\(\)]#u', '', $arr['title']);
    $output .= '
                                  <div class = "row align-items-center ">
                                    <div class = "col-12">
                                      <button class="btn btn-success btn-lg flmenu1 " data-id="0" data-target="#myModal" data-title="Быстрый заказ <br>'.$fast_title.'" data-toggle="modal"><i class="fas fa-fighter-jet fa-md"></i>&nbsp; Быстрый заказ</button>
                                    </div>
                                  </div>';
    if( isset($arr['whatsap_phone']) && $arr['whatsap_phone'] ){
      $output .= '
                                  <div class = "row align-items-center ">
                                    <div class = "col-12">
                                      <a href="https://wa.me/'.$arr['whatsap_phone'].'" target = "_blank" class = "whatsapp_link btn-success btn-lg" ><i class="fab fa-whatsapp"></i>&nbsp; '.$arr['whatsap_phone'].'</a> &nbsp; Вопрос по whatsapp 
                                    </div>
                                  </div>';                     
    }
    
    $output .= '
                                </div> 
                              </div>
                            </div>
    ';

    
    /*if( isset($arr['longtxt2']) && $arr['longtxt2']){
      
      $output .= '
                            <div class="full_item_descr_box">
                              <div class="full_item_descr_title_box">
                                <div class="full_item_descr_title">Описание</div>
                              </div>  
                              <div class="full_item_descr_cont">
                                '.$arr['longtxt2'].'
                              </div>
                            </div>';
    }*/
    $output .= '
                            '.$arr['files'].'
                            
                          </div>
                        </div>
    ';
    $nav_item = $tab_pane = '';
    if( isset($arr['longtxt2']) && $arr['longtxt2']){
      $nav_item .= '
      <li class="nav-item">
        <a class="nav-link active" id="circumscribing-tab" data-toggle="tab" href="#circumscribing" role="tab" aria-controls="circumscribing" aria-selected="true">Описание</a>
      </li>';
      $tab_pane .= '
      <div class="tab-pane fade show active" id="circumscribing" role="tabpanel" aria-labelledby="circumscribing-tab">
        '.$arr['longtxt2'].'
      </div>';
      
      if( isset($arr['longtxt3']) && $arr['longtxt3']){
        $nav_item .= '
      <li class="nav-item">
        <a class="nav-link" id="specifications-tab" data-toggle="tab" href="#specifications" role="tab" aria-controls="specifications" aria-selected="false">Характеристики</a>
      </li>';
        $tab_pane .= '
      <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
        '.$arr['longtxt3'].'
      </div>';
      }
      $output .= '
                            <div class="full_item_descr_box"> 
                              <div class="full_item_descr_cont">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                  '.$nav_item.'
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                  '.$tab_pane.'
                                </div>
                              </div>
                            </div>';
      
    }
    
    /*
    
    
                                            <span class = "buy_count">1500</span> 
                                            <span>шт</span> 
                                      <button class="buy_btn good_buy" 
                                                data-toggle="modal" 
                                                data-target="#myModal"
                                                data-id="'.$arr['id'].'"
                                                data-title=\'<a href = "http://'.$_SERVER["SERVER_NAME"].'/'.$arr['url'].'">'.$arr['title'].'</a>  \' 
                                              > 
                                      Купить
                                      </button>
    */
    
    return $output;
  }
  
  static function get_item_characteristic($title, $val){
    $output = '';
    
    if($val){
      $output .= '
      <tr>
        <td class = "first_col"> <span>'.$title.' </span> <div class="first_col_line"></div> </td>
        <td>'.$val.'</td></tr>
      ';
    }
    
    return $output;
  }
}