<?
class Article {
  
  
  function __construct (){
    
  }
  
  static function show_simple_menu(){ 
    global $PDO;
    $output = '';
    
    $s = "
      SELECT `".DB_PFX."smpl_article`.*,  `".DB_PFX."url`.`url` 
      FROM   `".DB_PFX."smpl_article`
      LEFT JOIN `".DB_PFX."url`
      ON (`".DB_PFX."url`.`module` = '".DB_PFX."smpl_article') AND (`".DB_PFX."url`.`module_id` = `".DB_PFX."smpl_article`.`id`)
      WHERE `".DB_PFX."smpl_article`.`fl_mine_menu` = 1
      AND `".DB_PFX."smpl_article`.`hide` = 0
      ORDER BY `".DB_PFX."smpl_article`.`ord`
    "; #pri($s);
    
    if ( $q = $PDO->query($s) ){
      if($q->rowCount()){
        while($item = $q->fetch()){
          ($item['link']) ? $href = $item['link'] : $href = $item['url'];
          $output .= ' <li class = "nav-item" ><a class="nav-link" href="'.$href.'">'.$item['title'].'</a></li> ';
        }
      }
    }
    
    return $output;
  }

	static function show_head_chief_menu($site){
    $output = '';
    
    $tbl_url = DB_PFX."url";
    $tbl_articles_cat = DB_PFX."articles_cat";
    $tbl_articles = DB_PFX."articles";
    
    $s = "
      SELECT `$tbl_articles_cat`.*,  `$tbl_url`.`url`
      FROM `$tbl_articles_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_articles_cat') AND (`$tbl_url`.`module_id` = `$tbl_articles_cat`.`id`)
      WHERE `$tbl_articles_cat`.`parent_id` = 1
      AND `$tbl_articles_cat`.`hide` = 0
      ORDER BY `$tbl_articles_cat`.`ord` 
    "; #pri($s);
    if( $q = $site->pdo->query($s)){
      if($q->rowCount()){
        while($r = $q->fetch()){
          
          ($r['link']) ? $link = $r['link'] : $link = '/'.$r['url'];
          ($r['id'] == 0 ) ? $fa = '<i class="fa fa-bars" aria-hidden="true"></i> ' : $fa = '';
          ($fa) ? $cat_class = ' cat_menu_link' : $cat_class = '';
          $output .= '
              <li class="nav-item'.$cat_class.'">
                <a class="nav-link" href="'.$link.'"> '.$fa.$r['title'].'</a>
              </li>';
        }
      }
    }
    
    return $output;
  } 
  
  static function show_head_chief_menu2($site, $cat_table_name, $table_name, $url_table_name, &$sub_menu = ''){
    $output = $all_sub_menu = '';
    $arr_act_cat_items = array();
    
    $menu_items = db::select("*", $cat_table_name, "parent_id	= 1 AND hide = 0 ", "`ord`" );
    
    if($site->getModule() == $cat_table_name && $site->getModuleId()){
      $arr_act_cat_items[] = $site->getModuleId();
      $arr_act_cat_items = self::get_arr_act_cat_items($site->getModuleId(), $arr_act_cat_items); 
    }elseif($site->getModule() == $table_name && $site->getModuleId()){
      $act_cat_id = db::value('cat_id', $table_name, 'id = '.$site->getModuleId());
      $arr_act_cat_items[] = $act_cat_id;
      $arr_act_cat_items = self::get_arr_act_cat_items($act_cat_id, $arr_act_cat_items); 
    }
    /*echo "show_head_chief_menu<pre>";
    print_r($menu_items);
    echo "</pre>";*/
    $more = '';
    
    if($menu_items){

      $i = $p = 0;
      
      foreach($menu_items as $menu_item){
        extract($menu_item);
        if ($link){
          $href = $link;   
        }else{
          $href = "/".Url::getStaticUrlForModuleAndModuleId($url_table_name, $cat_table_name, $id);
        }
        
        /*(!$i) ? $active = ' active ' : $active = ''; */
        (in_array( $id, $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
        
        if( $href == "/katalog-tovarov" && ( in_array($site->module, array( DB_PFX.'goods', DB_PFX.'goods_cat'))) ){
          $active = ' active ';
        }elseif( $href == "/news" && ( in_array($site->module, array( DB_PFX.'news' ))) ){
          $active = ' active ';
        }
        
        $output .= ' 
          <li class = "nav-item mm_item '.$active.'" >
            <a class="nav-link" href="'.$href.'">'.$title.'</a>';
            
        $s_sub = "
            SELECT `$cat_table_name`.*,  `$url_table_name`.`url` 
            FROM `$cat_table_name`
            LEFT JOIN `$url_table_name`
            ON (`$url_table_name`.`module` = '$cat_table_name') AND (`$url_table_name`.`module_id` = `$cat_table_name`.`id`)
            WHERE `$cat_table_name`.`parent_id` = ".$id."
            AND `$cat_table_name`.`hide` = 0
            ORDER BY `$cat_table_name`.`ord`
          "; #pri($s_sub); 
          if( $q_sub = $site->pdo->query($s_sub))
            if($q_sub->rowCount()){
              $output .= '
                <div class = "sub_menu">
                  <ul class = "sub_list">';
              while($r_sub = $q_sub->fetch()){
                ($r_sub['link']) ? $sub_href = $r_sub['link'] : $sub_href = '/'.$r_sub['url'] ;
                (in_array( $r_sub['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
                
                $output .= '
                  <li class = "sub_list_item '.$active.'">
                    <a class = "sub_list_link" href="'.$sub_href.'">'.$r_sub['title'].'</a>
                  </li>';
              }
              $output .= '
                  </ul>
                </div>';
            }    
            
        $output .= '
          </li> ';
          
        $i++;
        
        
          $s_sub = "
            SELECT `$cat_table_name`.*,  `$url_table_name`.`url` 
            FROM `$cat_table_name`
            LEFT JOIN `$url_table_name`
            ON (`$url_table_name`.`module` = '$cat_table_name') AND (`$url_table_name`.`module_id` = `$cat_table_name`.`id`)
            WHERE `$cat_table_name`.`parent_id` = ".$id."
            AND `$cat_table_name`.`hide` = 0
            ORDER BY `$cat_table_name`.`ord`
          ";  
          
          if($q_sub = $site->pdo->query($s_sub))
            if($q_sub->rowCount()){
              $p++;
              /*<div class="mm_item_submenu_list_cell col-sm-6 col-md-4 col-lg-2">*/
              $sub_menu .= '
                <div class="mm_item_submenu_list_cell col-sm-6 col-lg-2">
                  <ul>';
              while($r_sub = $q_sub->fetch()){
                ($r_sub['link']) ? $url_sub = $r_sub['link'] : $url_sub = "/".$r_sub['url'];
                $sub_menu .= '
                  <li><a href = "'.$url_sub.'">'.$r_sub['title'].'</a></li>';
              }
              $sub_menu .= '
                  </ul>
                </div>';
              
              if($p%2 == 0 ) $sub_menu .= '<div class = "clearfix visible-sm"></div>
              ';
              /*if($p%3 == 0 ) $sub_menu .= '<div class = "clearfix visible-md"></div>
              ';*/
              if($p%4 == 0 ) $sub_menu .= '<div class = "clearfix visible-lg"></div>
              ';
            }
        
        
      }
      
      $all_sub_menu = '
          <div class = "mine_popup_menu_box">
            <div class = "mine_popup_menu">
            
              <div class="mine_popup_menu_items" style="display: none;">';
      $all_sub_menu .= $sub_menu;
      
    $all_sub_menu .= '
              
            </div>
          </div>
        </div>
        <script>
          $(document).ready(function() {
            $("html").click(function() {
             $(".mine_popup_menu_items").slideUp();
             $(".mm_item").removeClass("active");
           });
            $(".mine_popup_menu_items", this).click(function( event ){
              event.stopPropagation();
            })

            $(".mm_item", this).click(function( event ){
              event.stopPropagation();
              /*var submenu = $(this).find(".mm_item_submenu_list").html();*/
              
              if( $(".mine_popup_menu_items").is(":visible") ){
                $(".mine_popup_menu_items").slideUp();
                $(".mm_item").removeClass("active");
              }else{
                $(".mine_popup_menu_items").slideDown();
                
                /*if(submenu){
                  $(".mine_popup_menu_items").html(submenu).slideDown();  
                  $(this).addClass("active");
                }else{
                  $(".mine_popup_menu_items").slideUp(); 
                  $(".mm_item").removeClass("active"); 
                }*/
                
              }
            })
            
            
            
          });
        </script>';
      
      $sub_menu = '';
      $sub_menu = $all_sub_menu;
      
    }
    
    $output .= $more;
    
    return $output;
  }
    
  static function show_sub_menu($site, $parent_id = 1){
    $output = '';
    #$menu_items = db::select("*", DB_PFX."articles_cat", "parent_id	= $parent_id AND hide = 0 ", "`ord`" );
    
    $tbl_url = DB_PFX."url";
    $tbl_goods_cat = DB_PFX."goods_cat";
    
    $s = " 
      SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url` 
      FROM `$tbl_goods_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
      WHERE `$tbl_goods_cat`.`parent_id`	= $parent_id 
      AND `$tbl_goods_cat`.`hide` = 0
      ORDER BY `$tbl_goods_cat`.`ord`
    ";
    if($q = $site->pdo->query($s))
      if($q->rowCount())
        $i = 0;
        while($r = $q->fetch()){
          extract($r);
          (isset($r['link']) && $r['link']) ? $href = $link : $href = "/".$url;
          #if ($seo_h1) $title = $seo_h1;
          $output .= ' 
                  <div class="sub_link_box"><a class="sub_link" href="'.$href.'">'.$title.'</a></div>';
          if(++$i %4 == 0){
            $output .= ' 
                </div>
                <div class="col col-md-auto sub_menu_col">';
          }
        }
    
    return $output;
  }
  
  static function show_mine_articles(){
    $output = '';
    
    $tbl_url = DB_PFX."url";
    $tbl_articles_cat = DB_PFX."articles_cat";
    $tbl_articles = DB_PFX."articles";
    
    $s = "
      SELECT `$tbl_articles`.*,  `$tbl_url`.`url` 
      FROM `$tbl_articles`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_articles') AND (`$tbl_url`.`module_id` = `$tbl_articles`.`id`)
      WHERE `$tbl_articles`.`fl_show_mine` = 1
      AND `$tbl_articles`.`hide` = 0
      ORDER BY `$tbl_articles`.`ord`
    ";
    
    $q = mysql_query($s);
    if(mysql_num_rows($q)){
      $i = 0;
      while($r = mysql_fetch_assoc($q)){
          $m_item_txt_col = $m_item_img_col = $m_item_img_col_title = '';
          (++$i % 2 == 0 ) ? $m_item_img_col_title = 'm_item_img_col' : $m_item_img_col_title = 'm_item_img_col1';
          ($i % 2 == 0 ) ? $m_item_txt_col = 'm_item_txt_col' : $m_item_txt_col = 'm_item_txt_col1';
          
          $m_item_img_col .= '
                      <div class = "col-4 '.$m_item_img_col_title.'">
          ';
          
          if($r['img']){
            $m_item_img_col .= '
                      
                        <div class = "m_item_img_box">
                          <a href = "/'.$r['url'].'">
                            <img src = "'.Images::static_get_img_link("images/articles/orig", $r['img'],  'images/articles/variations/140x140',  140, null, 0xFFFFFF, 90).'"  alt = "'.$r['title'].'" title = "'.$r['title'].' " class="" style = ""/>
                          </a>
                        </div>
            ';
          }
          if(0/*$r['price']*/){
            $m_item_img_col .= '
                        <div class = "m_item_price">Стоимость - <b>'.$r['price'].' руб./кв.м.</b></div>
            ';
          }
          $m_item_img_col .= '
                                  <div class =  "m_item_to_order good_buy"
                                      data-toggle="modal" 
                                      data-target="#myModal"
                                      data-id="'.$r['id'].'"
                                      data-title=\'<a href = "http://'.$_SERVER["SERVER_NAME"].'/'.$r['url'].'">'.$r['title'].'</a>\' 
                        >
                          &nbsp;
                        </div>
                      </div>
          ';
          
          $output .= ' 
                    <div class = "row m_item">
          ';
        
          if ($i % 2 == 0 )
            $output .= $m_item_img_col;

                      
          $output .= '              

                      <div class = "col-8 '.$m_item_txt_col.'">
                        <div class="m_item_txt_h1">'.$r['title'].'</div>
                        <div class="m_item_txt">'.$r['longtxt1'].' <a href = "/'.$r['url'].'">Подробнее</a>
                        </div>
                      </div>
          ';
          if ($i % 2 == 1 )
            $output .= $m_item_img_col;

          $output .= '              
                      <div class = "col-12">
                        <div class = "m_item_hr">&nbsp;</div>
                      </div>
                    </div>
          ';
      }
    }
    
        
    return $output;
  }
 
  static function show_left_block(){
    $output = '';
    
    $tbl_url = DB_PFX."url";
    $tbl_articles_cat = DB_PFX."articles_cat";
    $tbl_articles = DB_PFX."articles";
    
    $s = "
      SELECT `$tbl_articles`.*,  `$tbl_url`.`url` 
      FROM `$tbl_articles`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_articles') AND (`$tbl_url`.`module_id` = `$tbl_articles`.`id`)
      WHERE `$tbl_articles`.`fl_show_left_block` = 1
      AND `$tbl_articles`.`hide` = 0
      ORDER BY `$tbl_articles`.`ord`
    ";
    
    #pri($s);
    
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        $output .= '
                  <div class="left_col_txt_box">
        ';
        while($r = mysql_fetch_assoc($q)){
          $output .= '
                    <div class="left_col_txt_item">
                      <div class="left_col_txt_h1">
                        <a href="/'.$r['url'].'">'.$r['title'].'</a>
                      </div>
                      <div class="left_col_txt">
                        '.$r['longtxt1'].'
                      </div>
                      <div class="left_col_txt_link"><a href="/'.$r['url'].'">Подробнее</a></div>
                    </div>
          ';
        }
        $output .= '
                          </div>
        ';
      }
    }
        
    return $output;
  }
  
  static function show_footer_menu(&$site, $cat_table_name, $table_name, $url_table_name){
    $output = '';
    
    $s = "
      SELECT `$cat_table_name`.*,  `$url_table_name`.`url` 
      FROM `$cat_table_name`
      LEFT JOIN `$url_table_name`
      ON (`$url_table_name`.`module` = '$cat_table_name') AND (`$url_table_name`.`module_id` = `$cat_table_name`.`id`)
      WHERE `$cat_table_name`.`parent_id` = 1
      AND `$cat_table_name`.`hide` = 0
      ORDER BY `$cat_table_name`.`ord`
    "; #pri($s);
    
    if($q = $site->pdo->query($s))
      if($q->rowCount())
        while($item = $q->fetch()){
          ($item['link']) ? $href = $item['link'] : $href = $item['url'];
          $output .= ' <li class = "nav-item" ><a class="nav-link" href="'.$href.'">'.$item['title'].'</a></li> ';
        }
    
    return $output;
  }
  
  static function get_arr_act_cat_items($sid, $arr){
    $item = db::row('*', DB_PFX.'articles_cat', 'id = '.$sid);
    
    if($item['parent_id']){
      $arr[] = $item['parent_id'];
      return self::get_arr_act_cat_items($item['parent_id'] ,$arr);
    }else{
      return $arr;
    }
  }
  
  static function show_left_menu(&$site, &$flProd = null){
    $output = '';
      $site->js_scripts .= '
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
    ';
    
    $arr_act_cat_items =array(); $c_t_parent_id = 4;
    
    $tbl_url = DB_PFX."url";
    $tbl_articles_cat = DB_PFX."articles_cat";
    $tbl_articles = DB_PFX."articles";
    
    if($site->getModule() == $tbl_articles_cat && $site->getModuleId()){
      $arr_act_cat_items[] = $site->getModuleId();
      $arr_act_cat_items = self::get_arr_act_cat_items($site->getModuleId(), $arr_act_cat_items); 
    }elseif($site->getModule() == $tbl_articles && $site->getModuleId()){
      $act_cat_id = db::value('cat_id', $tbl_articles, 'id = '.$site->getModuleId());
      $arr_act_cat_items[] = $act_cat_id;
      $arr_act_cat_items = self::get_arr_act_cat_items($act_cat_id, $arr_act_cat_items); 
    }
    
    #pri($arr_act_cat_items);
    #if(in_array(9, $arr_act_cat_items)) $c_t_parent_id = 9;
    #if(in_array(10, $arr_act_cat_items)) $c_t_parent_id = 10;
    
    $output .= '
      <div class="list-group cat-menu goods_cats">
    ';
    
    $s = "
    SELECT `$tbl_articles_cat`.*,  `$tbl_url`.`url`
    FROM `$tbl_articles_cat` 
    LEFT JOIN `$tbl_url`
    ON (`$tbl_url`.`module` = '$tbl_articles_cat') AND (`$tbl_url`.`module_id` = `$tbl_articles_cat`.`id`)
    WHERE `$tbl_articles_cat`.`hide` = 0
    AND `parent_id` = $c_t_parent_id
    ORDER BY `$tbl_articles_cat`.`ord`
    "; #pri($s);
    
    if($q = $site->pdo->query($s)){
      if($q->rowCount()){
        while($r = $q->fetch()){
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
          $s_sub = "
            SELECT `$tbl_articles`.*,  `$tbl_url`.`url`
            FROM `$tbl_articles` 
            LEFT JOIN `$tbl_url`
            ON (`$tbl_url`.`module` = '$tbl_articles') AND (`$tbl_url`.`module_id` = `$tbl_articles`.`id`)
            WHERE `$tbl_articles`.`hide` = 0
            AND `cat_id` = ".$r['id']."
            ORDER BY `$tbl_articles`.`ord`
          ";
          $q_sub = $site->pdo->query($s_sub);
          $count = $q_sub->rowCount();
          
          $glyphicon = $display = $style = $link_style = '';
          
          if($active){
            #$link_style = ' style="color: #ff6600;" ';
          }
          
          if($count){
            if($active){
              //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_close.png\') 0px 8px no-repeat;" ';
              $display = "block";
            }else{
              //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
              $style = ' style="background: url(\'css/img/lm_open.png\') 0px 8px no-repeat;" ';
              $display = "none";
            }
          }
          
          
          $glyphicon = '';
          $output .= '
            <div class="list-group-item" '.$style.'><a href="/'.$r['url'].'" '.$link_style.' >'.$glyphicon.' '.$r['title'].'</a></div>
          ';  
          
          //if($active){
            $output .= self::get_left_sub_menu($site, $s_sub, $arr_act_cat_items, $display);  
          //}

        }
      }
    }
      

    
    $output .= '
      </div><!--/span-->
    ';
    
    
    
    
    return $output;
    
  }
  
  static function get_left_sub_menu(&$site, $s_sub, $arr_act_cat_items, $display = "none"){
    $output = '';
    
    $tbl_url = DB_PFX."url";
    $tbl_articles_cat = DB_PFX."articles_cat";
    $tbl_articles = DB_PFX."articles";
    #pri ($s_sub);
    
    //<span class="glyphicon glyphicon-plus"></span>
    if($q = $site->pdo->query($s_sub)){
      if($q->rowCount()){
        $output .= '<div class = "sub_item" style = "display: '.$display.'; ">';
        while($r = $q->fetch()){
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
            
          
          $s_sub = "
            SELECT `$tbl_articles_cat`.*,  `$tbl_url`.`url`
            FROM `$tbl_articles_cat` 
            LEFT JOIN `$tbl_url`
            ON (`$tbl_url`.`module` = '$tbl_articles_cat') AND (`$tbl_url`.`module_id` = `$tbl_articles_cat`.`id`)
            WHERE `$tbl_articles_cat`.`hide` = 0
            AND `parent_id` = ".$r['id']."
            ORDER BY `$tbl_articles_cat`.`ord`
          ";
          
          $q_sub = $site->pdo->query($s_sub);
          $count = $q_sub->rowCount();
          $glyphicon = $style = $link_style = '';
          
          if($active){
            $link_style = ' style="color: #ff6600;" ';
          }
          
          if($count){
            if($active){
              //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_close.png\') 0px 8px no-repeat;" ';
              $display = "block";
            }else{
              //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_open.png\') 0px 8px no-repeat;" ';
              $display = "none";
            }
          }
          $glyphicon = '';
          $output .= '
            <div class="list-group-item  " '.$style.'><a href="/'.$r['url'].'" '.$link_style.' >'.$glyphicon.' '.$r['title'].'</a></div>
          ';
          
          //if($active){
            $output .= self::get_left_sub_menu($site, $s_sub, $arr_act_cat_items, $display);
          //}

        }
        $output .= '</div>';
      }
    }
    
    return $output;
    
  }
  
  static function get_path_link($cid, $table, $path = ''){
    #echo " $cid path = $path<br>";
    $row = db::row('*', $table, "id = $cid", null, 0);
    #pri($row); 
    if($row['id'] == 0 ){
      $path = "<a href=\"/\">Главная</a> ". $path;
    }elseif($row['id'] == 1){
      $path = "<a href=\"/\">Главная</a> ". $path;
    }else{
      
      if ($row['link']){
        $path = self::get_path_link(1, $table, $path );
      }else{
        $href = Url::getStaticUrlForModuleAndModuleId( DB_PFX.'url', $table, $row['id'] );
        $path = self::get_path_link($row['parent_id'], $table, ' → <a href="/'.$href.'">'.$row['title'].'</a> '.$path );
      }
     
    }
    
    return $path;
    
  }
  
  static function getSmplItems(&$site, $id, $table, $bread_crumbs = null, $img_dir = "smpl_article"){
    $output = '';
    global $PDO;
    
    $item = db::select("*", $table, "id = $id", null, null, 1); #pri($item);
    
    // ------------- SEO -------------
      
    if($item['seo_title']){
      $site->siteTitle = $item['seo_title'];
    }else{
      if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'lib_text_title'" )){
        $site->siteTitle = str_replace("*h1*", $item['title'], $seo_title);
      }else{
        $site->siteTitle = $item['title'];
      }
    }
    if($item['seo_description'])  $site->siteDescription = $item['seo_description']; 
    if($item['seo_keywords'])  $site->siteKeywords  = $item['seo_keywords']; 
      
    // ------------- END SEO -------------
    
    $output .= '
    <div class="mine_content_box">
      <div class="text">
    ';
    if($bread_crumbs){
      $site->bread = $bread_crumbs;
    }else{
      $site->bread = '
        <div class="bread_crumbs_box ">
          <div class="bread_crumbs border_top_button_line">
            <a href="/">Главная</a> → <span>'.$item['title'].'</span>
          </div>      
        </div>
      ';
    }
    #$output .= $site->bread;
        
    // Добавить в просмотренные страницы
    $v_img = '';
    if($item['img']) $v_img = Images::static_get_img_link("images/$img_dir/orig", $item['img'],  "images/$img_dir/variations/140x120",  140, null, 0xFFFFFF, 90);
    $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $item['title']);
    $site->addVisitedPage($site->module, $site->module_id, $v_cont );
    
    $output .= '<div class="h1_box"><h1>'.$item['title'].'</h1></div>';
    $output .= '<div>'.$item['longtxt2'].'</div>';
    
    $output .= '
        </div>
      </div>
    ';
    // Проверка присутствуют ли картинки закрепленные за статьей
    $addImages = self::getAddImages($site, $table, $item['id']);
    // Проверка присутствуют ли документы закрепленные за статьей
    $addFiles  = self::getAddFiles($site, $table, $item['id']);
    
    if($addImages) $output .= $addImages;
    if($addFiles) $output .= $addFiles;
    
    
    return $output;
  }
  
  static function getCatItems(&$site, $cid, $cat_table, $table, $img_dir = "articles"){
    $output = '';
    $tc_items = db::select("*", $cat_table, "id = $cid", null, null, true);
    // Добавить в просмотренные страницы
    $v_img = '';
    if($tc_items['img']) $v_img = Images::static_get_img_link("images/$img_dir/cat/orig", $tc_items['img'],  "images/$img_dir/cat/variations/140x120",  140, null, 0xFFFFFF, 90);
    $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $tc_items['title']);
    $site->addVisitedPage($site->module, $site->module_id, $v_cont );
      
    $items = db::select("*", $table, "cat_id = $cid", null, null, null);
    
    if(count($items) == 1){
      /*echo "<pre>";
      print_r($items);
      echo "</pre>";*/
      
      #$site->adminLink .= "?edits=".$items[0]['id'];
      
      $bread_crumbs = '
        <div class="bread_crumbs_box ">
          <div class="bread_crumbs border_top_button_line">
      ';
      $bread_crumbs .= self::get_path_link($tc_items['parent_id'], $cat_table);
      $bread_crumbs .= ' → <span>'.$tc_items['title'].'</span>';
      $bread_crumbs .= '
          </div>      
        </div>
      ';
      $output .= Article::getItems($site, $items[0]['id'], $cat_table, $table, $bread_crumbs);
      
    }else{
      // ------------- SEO -------------
      {       
      if($tc_items['seo_title']){
        $site->siteTitle = $tc_items['seo_title'];
      }else{
        if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'lib_cat_title'" )){
          $site->siteTitle = str_replace("*h1*", $tc_items['title'], $seo_title);
        }else{
          $site->siteTitle = $tc_items['title'];
        }
      }
      if($tc_items['seo_description'])  $site->siteDescription = $tc_items['seo_description']; 
      if($tc_items['seo_keywords'])  $site->siteKeywords  = $tc_items['seo_keywords']; 
      }
      // ------------- END SEO -------------
          
      $output .= '
      <div class="mine_content_box">
        <div class="text">
      ';
      $site->bread = '
        <div class="bread_crumbs_box ">
          <div class="bread_crumbs border_top_button_line">
      ';
      $site->bread .= self::get_path_link($tc_items['parent_id'], $cat_table);
      $site->bread .= ' → <span>'.$tc_items['title'].'</span>';
      $site->bread .= '
          </div>      
        </div>
      ';
      
      #$output .= $site->bread;
      if($tc_items['seo_h1']) $tc_items['title'] = $tc_items['seo_h1'];
      $output .= '<div class="h1_box"><h1>'.$tc_items['title'].'</h1></div>';
      
      // Проверка присутствуют ли картинки закрепленные за статьей
      $addImages = self::getAddImages($site, $cat_table, $cid);
      // Проверка присутствуют ли документы закрепленные за статьей
      $addFiles = self::getAddFiles($site, $cat_table, $cid);
      
      if( isset($tc_items['is_enlarge_photos']) && $tc_items['is_enlarge_photos'] ) $tc_items['longtxt2'] = self::getAddGallery($site, $tc_items['longtxt2']);
      
      if( (strpos($tc_items['longtxt2'], '%slider%') !== false) && $addImages) {
        $tc_items['longtxt2'] = str_replace('%slider%', $addImages, $tc_items['longtxt2']);
        $addImages = '';
      }
      
      $cat_descr = '';
      /*if($addImages) $cat_descr .= $addImages;
      if($addFiles) $cat_descr .= $addFiles;*/
      if($tc_items['longtxt2']) $cat_descr .= '<div>'.$tc_items['longtxt2'].'</div>';
      
      
      
      $cat_items = db::select("*", $cat_table, "parent_id = $cid", "ord", null, null);
      
      foreach($cat_items as $cat_item){
        extract($cat_item);
        $output .= '<div class="anons_line"></div>';
        $output .= '<div class="anons row">';
        $output .= '<div class="date col-12">';
        
        $output .= '</div>';
        $href = "/".Url::getStaticUrlForModuleAndModuleId(DB_PFX.'url', $cat_table, $cat_item['id']);
        $output .= '<div class="txt col-sm col-12">
                      <div class="txt_title"><a href="'.$href.'">'.$title.'</a></div>
        '.$longtxt1.'
                      </div>
        ';
        $output .= '<div class="anons_img_box col-sm-auto col-12">';
        if($img)  $output .= '<img src="/images/articles/cat/slide/'.$img.'" title="" alt=" ">';
        $output .= '</div>';
        $output .= '
                  </div>
        ';
      }  
      
      
      /*$items = db::select("*", $table, "cat_id = $cid", "ord", null, null);*/
      $s_where = "cat_id = $cid";
      $s = "
        SELECT COUNT( * ) AS count
        FROM `$table`
        WHERE cat_id = $cid
        AND hide = 0 
      ";
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
      $s_cat_sorting = " ORDER BY `date` DESC, `ord` ASC";
      
      $strPager = Article::getPager($site, $pagerPage, $cat_count, 20, $limit);
      $s = "
        SELECT `$table`.*, `".DB_PFX."url`.`url` 
        FROM `$table`
        LEFT JOIN `".DB_PFX."url`
        ON (`".DB_PFX."url`.`module` = '$table') AND (`".DB_PFX."url`.`module_id` = `$table`.`id`)
        WHERE $s_where
        AND `$table`.`hide`  = 0 
        $s_filter
        $s_cat_sorting
        $limit
      "; #pri($s);
      
      $spec = '';
      
      $output .= $strPager;
      #foreach($items as $cat_item){
      #  extract($cat_item);
      $q = $site->pdo->query($s);
      if($q->rowCount())
      while($r = $q->fetch()){
        extract($r);
        
        #if(!isset($longtxt1) || !$longtxt1) continue;
        
        $output .= '<div class="anons_line"></div>';
        $output .= '<div class="anons row">';
        $output .= '<div class="date col-12">';
        
        if(isset($date) && $date) {
          /*$date_str = new DateTime($date." 01:00:00");
          $date = $date_str->Format('d.m.Y');
          $output .= $date;*/
          $output .= sqlDateToRusDate($date);
        }
        
        $output .= '</div>';
        #$href = "/".Url::getStaticUrlForModuleAndModuleId(DB_PFX.'url', $table, $r['id']);
        $href = "/".$r['url'];
        $output .= '<div class="txt col-sm col-12">
                      <div class="txt_title"><a href="'.$href.'">'.$title.'</a></div>
        '.$longtxt1.'
                      </div>
        ';
        $output .= '<div class="anons_img_box col-sm-auto col-12">';
        if($img)  $output .= '<img src="/images/articles/slide/'.$img.'" title="" alt=" ">';
        $output .= '</div>';
        $output .= '
                  </div>
        ';
      }
      $output .= $strPager;
      $output .= $cat_descr;
      $output .= '
          </div>
        </div>';
      
      if($addImages) $output .= $addImages;
      
      if($addFiles) $output .= $addFiles;
      
    }
    
    return $output;
  }
  
  static function getItems(&$site, $id, $cat_table, $table, $bread_crumbs = null, $img_dir = "articles"){
    $output = '';
    $item = db::select("*", $table, "id = $id", null, null, true);
    
    // ------------- SEO -------------
      
    if($item['seo_title']){
      $site->siteTitle = $item['seo_title'];
    }else{
      if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'lib_text_title'" )){
        $site->siteTitle = str_replace("*h1*", $item['title'], $seo_title);
      }else{
        $site->siteTitle = $item['title'];
      }
    }
    if($item['seo_description'])  $site->siteDescription = $item['seo_description']; 
    if($item['seo_keywords'])  $site->siteKeywords  = $item['seo_keywords']; 
      
    // ------------- END SEO -------------
    
    $output .= '
    <div class="mine_content_box">
      <div class="text">
    ';
    if($bread_crumbs){
      $site->bread = $bread_crumbs;
    }else{
      $site->bread = '
        <div class="bread_crumbs_box ">
          <div class="bread_crumbs border_top_button_line">
      ';
      $site->bread .= self::get_path_link($item['cat_id'], $cat_table);
      $site->bread .= ' → <span>'.$item['title'].'</span>';
      $site->bread .= '
          </div>      
        </div>
      ';
    }
    #$output .= $site->bread;
        
    // Добавить в просмотренные страницы
    $v_img = '';
    if($item['img']) $v_img = Images::static_get_img_link("images/$img_dir/orig", $item['img'],  "images/$img_dir/variations/140x120",  140, null, 0xFFFFFF, 90);
    $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $item['title']);
    $site->addVisitedPage($site->module, $site->module_id, $v_cont );
    
    if($item['seo_h1']) $item['title'] = $item['seo_h1'];
    $output .= '<div class="h1_box"><h1>'.$item['title'].'</h1></div>';
    
    // Проверка присутствуют ли картинки закрепленные за статьей
    $addImages = self::getAddImages($site, $table, $item['id']);
    
    // Проверка присутствуют ли документы закрепленные за статьей
    $addFiles  = self::getAddFiles($site, $table, $item['id']);
    
    if( isset($tc_items['is_enlarge_photos']) && $item['is_enlarge_photos'] ) $item['longtxt2'] = self::getAddGallery($site, $item['longtxt2']);
    
    if( (strpos($item['longtxt2'], '%slider%') !== false) && $addImages) {
      $item['longtxt2'] = str_replace('%slider%', $addImages, $item['longtxt2']);
      $addImages = '';
    }
    $longtxt2 = $item['longtxt2'];
    
    $output .= '<div>'.$longtxt2.'</div>';
    
    $output .= '
        </div>
      </div>
    ';
    
    if($addImages) $output .= $addImages;
    
    if($addFiles) $output .= $addFiles;
    
    return $output;
  }
  
  static function getAddGallery(&$site, $str, $gall_name = "groupfoto"){
    $output = "";
    require_once('lib/simple_html_dom.php');
    if(!$str) return '';
    $html = str_get_html($str);
    
    $block = $html->find('img');
    foreach($html->find('img') as $p_img){
      $p_img->outertext = '
      <a class="fancyfoto fancybox.iframe" href="'.$p_img->src.'" data-fancybox="groupfoto" data-caption="">
        '.$p_img->outertext.'
      </a>';
    }
    $output = $html->outertext;
    
    return $output;
  }
  
  static function getAddImages(&$site, $table, $m_id){
    $output = '';
    
    $s = "
      SELECT *
      FROM `".DB_PFX."all_images`
      WHERE `module` = '$table'
      AND `module_id` = $m_id
      ORDER BY `module_ord`
    ";
    
    if($q = $site->pdo->query($s)){
      if($q->rowCount()){
        $output .= '
          <div class = "row gallery_box">
        ';
        while($ri = $q->fetch()){
          $output .= '
            <div class = "col-12 col-sm-6 col-md-4 col-lg-4 gallery_box_items">
              <div class = "gallery_img_box">
              <a class="fancyfoto fancybox.iframe" href="/images/all_images/orig/'.$ri['img'].'" data-fancybox="groupfoto" data-caption="'.$ri['title'].'">
                <img src="'.Images::static_get_img_link("images/all_images/orig", $ri['img'],  'images/all_images/variations/540x540',  540, null, 0xFFFFFF, 90).'" title="'.$ri['title'].'" alt="'.$ri['title'].'">
              </a>
              </div>
          ';
          $output .= '
              <div class = "gallery_box_title">
                '.$ri['title'].'
              </div>
          ';
          
          $output .= '
            </div>';
        }
        $output .= '
          </div>';
        /*$site->js_scripts .= '
          <link rel="stylesheet" href="/vendors/fancybox3/jquery.fancybox.min.css" type="text/css" media="screen">
          <script type="text/javascript" src="/vendors/fancybox3/jquery.fancybox.min.js"></script>
          
          <script type="text/javascript" charset="utf-8">
          $(function(){
            $(".fancyfoto").fancybox({
              maxWidth : 800,
              maxHeight : 600,
              fitToView : false,
              width : "70%",
              height : "70%",
              autoSize : false,
              closeClick : false,
              openEffect : "none",
              closeEffect : "none"
            });
          });
    	    </script>
        ';*/
      }
    }
    
    return $output;
  }
  
  static function getAddFiles(&$site, $table, $m_id){
    $output = '';
    
    $s = "
      SELECT *
      FROM `".DB_PFX."all_files`
      WHERE `module` = '$table'
      AND `module_id` = $m_id
      ORDER BY `module_ord`
    ";
    
    if($q = $site->pdo->query($s)){
      if($q->rowCount()){
        $output .= '
          <div class = "files_box row">';
            #<div class = "col-12"><br><br><b>Прикрепленные файлы</b><br><br></div>';
            
        while($r = $q->fetch()){
          $output .= '
            <div class = "col-12"><span class = "files_title">'.$r['title'].'</span> <a href = "/images/all_files/files/'.$r['file'].'" target="_blank" class = "button btn btn-default">Скачать</a></div>';
        }
        $output .= '
          </div>';
      }
    }
    
    return $output;
  }
  
  static function getPager(&$site, $page = 0, $countItems = 0, $itemsPerPage = 20, &$offset = 0){
    
    $output = '';
    
    $p_url = "/";
    if($site->arr_urls){
      if(is_array($site->arr_urls)){
        $p_url = '';
        foreach($site->arr_urls as $a_url){
          $p_url .= "/".$a_url;
        }
      }
    }
    if(isset($_GET['perPage'])){
      if(intval($_GET['perPage'])){
        $_SESSION['perPage'] = intval($_GET['perPage']);
      }
    }
    
    if(!isset($_SESSION['perPage'])){
      $_SESSION['perPage'] = 50;
    }
    
    $itemsPerPage = $_SESSION['perPage'];
    
    if($countItems > $itemsPerPage){
      $pageLinks = ceil($countItems / $itemsPerPage);
      
      //echo "$countItems / $itemsPerPage = $pageLinks<br>";
      
      $output .= '
        <nav class = "text-right ">
          <div style = "    float: left;     margin: 7px 0 10px; padding: 6px 12px 6px 0px;     line-height: 1.42857143;" >Всего: '.$countItems.'</div>
        <select class="form-control items-per-page" 
        
          style = "
            float: left;
            margin: 5px 0 10px;
            padding: 6px 12px 6px 0px;
            line-height: 1.42857143;
            display: inline-block;
            width: 65px;
          "
        >
      ';
      /*'
          <option value = "25" '; if($itemsPerPage == 25) $output.= 'selected'; $output .= ' >25</option>
          
      */
      $output .= '      
          <option value = "50" '; if($itemsPerPage == 50)   $output.= 'selected'; $output .= '>50</option>    
          <option value = "100" '; if($itemsPerPage == 100) $output.= 'selected'; $output .= '>100</option>
          <option value = "250" '; if($itemsPerPage == 250) $output.= 'selected'; $output .= '>250</option>
          <option value = "500" '; if($itemsPerPage == 500) $output.= 'selected'; $output .= '>500</option>
        </select>';
      $site->js_scripts .= '       
        <script type="text/javascript">
        $(document).ready(function() {  
          $(".items-per-page", this).change(function() {
            var perPage = $(this).val();
            window.location = "'.$p_url.'?perPage="+perPage;
          });
        });
        </script>';
      $output .= ' 
          <ul class="pagination justify-content-end" style = "margin: 5px 0 10px;     padding: 6px 12px 6px 0px;">
      ';
      $output .= '<li class="page-item';
      if($page <= 1){ $output .= ' disabled'; }
      $output .= '" > <a class="page-link" href="';
      if($page >= 1){ $output .= '?page='.($page-1).''; }
      
      $output .= '" aria-label="Previous">
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
      
      if(!isset($_GET['page'])) $page = 1; 
      
      for($i = 1; $i <= $pageLinks; $i++){
        if( (($i - $minusItems) <= $page) && ($page <= ($i + $plusItems) ) ){
          if($i == $page){
            $output .= '
              <li class="page-item active"><a class="page-link href = "#" >'.$i.'</a></li>
            ';
          }else{
            $output .= '
              <li class="page-item"><a class="page-link" href = "?page='.$i.'">'.$i.'</a></li>
            ';
          }
           
        }
        
      }
      
      $output .= '<li class="page-item';
      if($page >= ($pageLinks ) ){ $output .= ' disabled'; }
      $output .= '" > <a class="page-link href="';
      if($page <= ($pageLinks ) ){ $output .= '?page='.($page+1).''; }
      $output .= '
        " aria-label="Next">
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
  
}

class News {
  
  static function getNews(&$site, $id, $table){ 
    $output = '';

    $output .= '
      <div class="inner_mine_content_box"  style = "min-height: 350px;">
        <div class="text">
    ';
    
    if(!$site->module_id){
      $site->siteTitle = "Новости";
      $seo_news_title =  db::value('value', DB_PFX.'seo', "type = 'news_title'");
      if($seo_news_title) $site->siteDescription = str_replace("*h1*", $site->siteTitle, $seo_news_title);
      
      $site->bread = '
        <div class="bread_crumbs_box ">
          <div class="bread_crumbs border_top_button_line">
            <a href="/">Главная</a> → <span>Новости</span>
          </div>      
        </div>
      ';
      #$output .= $site->bread;
      
      $output .= '
        <h1>Новости</h1>';
        
      $output .= News::getNewsTable($site, $id, $table);
      
    }
    else
    {
      $s = "
        SELECT `$table`.*
        FROM `$table`
        
        WHERE `$table`.`id` = {$site->module_id}";
      
      if (!isset($_SESSION["WA_USER"])){           # Если не авторизован в админке. Проверка.
        $s .= "
        AND `$table`.`hide` = 0";
      } #pri($s); 
      
      $q = $site->pdo->query($s);
      if( !$q->rowCount() ){
        header('Location: /404.php', true, 301);
        return false;
      }
      
      $r = $q->fetch();
      
      // ------------- SEO -------------
      
      if($r['seo_title']){
        $site->siteTitle = $r['seo_title'];
      }else{
        if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'news_title'" )){
          $site->siteTitle = str_replace("*h1*", $r['title'], $seo_title);
        }else{
          $site->siteTitle = $r['title'];
        }
      }
      if($r['seo_description'])  $site->siteDescription = $r['seo_description']; 
      if($r['seo_keywords'])  $site->siteKeywords  = $r['seo_keywords']; 
      
      // ------------- END SEO -------------
        
      extract($r);
      if(!$title) $title = strip_tags($longtxt1);
      $site->bread = '
        <div class="bread_crumbs_box ">
          <div class="bread_crumbs border_top_button_line">
            <a href="/">Главная</a> → <a href="/news">Новости</a> → <span>'.$title.'</span>
          </div>      
        </div>
      ';
      
      #$output .= $site->bread;
      $output .='
        <div class="catalog_box">
      ';
      $output .= '<h1>'.$title.'</h1>';
      
      $detail_news_date = '';
      if($date){
        $date_str = sqlDateToRusDate($date);
        $dArr = explode("-", $date);
        $year = $dArr[0];  $day = $dArr[2];  $month = $dArr[1];
        $detail_news_d = $day.'.'.$month.'.'.$year;
        $detail_news_date = '<time class="detail-news-date" style = "display:none;">'.$detail_news_d.'</time>';
      }
      
      $output .= '
        <div class="news_date_box">
          '.$date_str.' г.
        </div>';
      $output .= $detail_news_date.'
        <div class = "detail-news-text">';
      #if($longtxt1) $output .= $longtxt1."<br>";
      $output .= $longtxt2;
      $output .= '
        </div>
      </div>
      <br><br>
      ';
      
      // Добавить в просмотренные страницы
      $v_img = '';
      if($r['img']) $v_img = Images::static_get_img_link("images/news/orig", $r['img'],  'images/news/variations/140x120',  140, null, 0xFFFFFF, 90);
      $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $r['title']);
      $site->addVisitedPage($site->module, $site->module_id, $v_cont );
      
    }
    
    // Проверка присутствуют ли картинки закрепленные за новостью
    
    (isset ($r['id'])) ? $m_id = $r['id'] : $m_id = 0;
    
    // Проверка присутствуют ли картинки закрепленные за новостью
    $addImages =  Article::getAddImages($site, $table, $m_id);
    
    // Проверка присутствуют ли документы закрепленные за новостью
    $addFiles  = Article::getAddFiles($site, $table, $m_id);
    
    $output .= '
        <br><br>
        </div>
      </div>
    ';
    
    if($addImages) $output .= $addImages;
    
    if($addFiles) $output .= $addFiles;
    
    return $output;
  }
  
  static function getNewsTable(&$site, $id, $table, $showFilterRow = true){
    
    $output = '';
    
    $s_where = "1";
    
    if(isset($_GET['regMinDate'])){ $_SESSION['regMinDate'] = preg_replace('/[^0-9\.]/', '', $_GET['regMinDate']); }
    #if(!isset($_SESSION['regMinDate'])){  $_SESSION['regMinDate'] = date("d.m.Y", strtotime("-1 month"));      }
    if(!isset($_SESSION['regMinDate'])){  $_SESSION['regMinDate'] = '';      }
    $regMinDate = $_SESSION['regMinDate'];
    
    if(isset($_GET['regMaxDate'])){ $_SESSION['regMaxDate'] = preg_replace('/[^0-9\.]/', '', $_GET['regMaxDate']); }
    if(!isset($_SESSION['regMaxDate'])){  $_SESSION['regMaxDate'] = date("d.m.Y"); }
    $regMaxDate = $_SESSION['regMaxDate'];
    
    if(isset($_GET['regn'])){
      $_SESSION['regn'] = intval($_GET['regn']);
    }
    
    if(!isset($_SESSION['regn'])){
      $_SESSION['regn'] = 0;
    }
    
    // Сброс фильтра
    if(isset($_GET['resetFilter'])){
      $regMinDate = $_SESSION['regMinDate'] = '';
      $regMaxDate = $_SESSION['regMaxDate'] = date("d.m.Y");
      $_SESSION['regn'] = 0;
    }
    
    $itemRegn = $_SESSION['regn'];
    
    if($showFilterRow)
      $output .= News::getRegionalNewsDateTimeFilter($site, $regMinDate, $regMaxDate);
    
    if($regMinDate){
      $datetime = DateTime::createFromFormat('d.m.Y', $regMinDate);
      $sRegMinDate = $datetime->format('Y-m-d');
      if($showFilterRow)
        $s_where .=" 
          AND `$table`.`date` >= '$sRegMinDate'";
    }
    if($regMaxDate){
      $datetime = DateTime::createFromFormat('d.m.Y', $regMaxDate);
      $sRegMaxDate = $datetime->format('Y-m-d');
      if($showFilterRow)
        $s_where .=" 
          AND `$table`.`date` <= '$sRegMaxDate'";
    }
    if($itemRegn){
      $s_where .=" 
      AND `$table`.`category_id` = '$itemRegn'";
    }
    
    $s = "
      SELECT COUNT( * ) AS count
      FROM `$table`
      WHERE $s_where
      AND hide = 0 
    "; #pri($s);
    
    $q = $site->pdo->query($s);
    $r = $q->fetch();
    $c_count = $r['count'];
    
    $pagerPage = 0;
    if(isset($_GET['page']) && $_GET['page'] ){ $pagerPage = intval($_GET['page']);}
    
    $s_filter = $limit = $s_cat_sorting = '';
    $s_cat_sorting = " ORDER BY `$table`.`date` DESC  ";
    
    $strPager = Article::getPager($site, $pagerPage, $c_count, 20, $limit);
    
    $url_table = DB_PFX.'url';
    $s = "
      SELECT `$table`.*, `$url_table`.`url` 
      FROM `$table`
      LEFT JOIN `$url_table`
      ON (`$url_table`.`module` = '$table') AND (`$url_table`.`module_id` = `$table`.`id`)
      WHERE $s_where
      AND `$table`.`hide`  = 0 
      $s_filter
      $s_cat_sorting
      $limit
    "; #pri($s); #die();
    
    
    $q = $site->pdo->query($s);
    if($q->rowCount()){
      
      $output .= $strPager;
      
      while($r = $q->fetch()){
        extract($r);
        
        $output .= '<div class="anons_line"></div>';
        $output .= '<div class="anons row">';
        $output .= '  <div class="date col-12 detail-news-date">';
        ($img) ? $st1 = "col-sm-10" : $st1 = "col-sm-12";
        
        if($date){
          $date_str = new DateTime($date." 01:00:00");
          $date = $date_str->Format('d.m.Y');
          $output .= $date;
        }
        $output .= '</div>';
        if(!$title){
          $title = $longtxt1;
          $longtxt1 = '';
        }elseif($longtxt1){            
          $longtxt1 .= '<br>';
        }
        
        if($img){
          $output .= '<div class="anons_img_box col-12 col-sm-2">';
          $output .= ' <a href="/'.$url.'"><img src="/images/news/slide/'.$img.'" title="" alt=" "></a>';
          $output .= '</div>';
        }
        $output .= '<div class="txt '.$st1.' col-12">
                      <div class="txt_title detail-news-link">
                        <a href="/'.$url.'"> '.$title.' </a>
                      </div>
                      '.$longtxt1.'
                      <a href="/'.$url.'">Читать дальше</a> 
                    </div>';
                    

          
        $output .= '
                  </div>';
      }
      $output .= $strPager;
    }else{
      $output .= "<p>Раздел пуст.</p>";
    }
    $output .= '
      
    ';
  
    return $output;
  }
  
  static function getRegionalNewsDateTimeFilter(&$site, $minDate, $maxDate){
    $output = '';
    
    $p_url = "/";
    if($site->arr_urls){
      if(is_array($site->arr_urls)){
        $p_url = '';
        foreach($site->arr_urls as $a_url){
          $p_url .= "/".$a_url;
        }
      }
    }
    
    if($maxDate == date("d.m.Y")){
      $maxDate1 = date("d.m.Y");
      $maxDate2 = $maxDate;
    }else{
      $maxDate1 = $maxDate;
      $maxDate2 = date("d.m.Y");
    }
    $minDate2 = '';
    if($minDate){
      $minDate2 =  $minDate;
    }
    
    $site->js_scripts .= '
      <link rel="stylesheet" href="/js/jquery-ui-1.12.1/jquery-ui.min.css">
      <script src="/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
      <script>
        $( function() {
          var dFormat = "dd.mm.yy";
          jQuery(function ($) {
            $.datepicker.regional["ru"] = {
                closeText: "Закрыть",
                prevText: "&#x3c;Пред",
                nextText: "След&#x3e;",
                currentText: "Сегодня",
                monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
                "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
                monthNamesShort: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
                "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
                dayNames: ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"],
                dayNamesShort: ["вск", "пнд", "втр", "срд", "чтв", "птн", "сбт"],
                dayNamesMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
                weekHeader: "Нед",
                dateFormat: dFormat,
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ""
            };
            $.datepicker.setDefaults($.datepicker.regional["ru"]);
          });
          
          $( "#from" ).datepicker({
              numberOfMonths: 1,
              onSelect : function(dateText, inst){
                window.location = "'.$p_url.'?regMinDate="+dateText;
              }
          });
          $( "#from" ).datepicker("option", "dateFormat", dFormat );
          $( "#from" ).datepicker( "setDate", "'.$minDate.'" );
          $( "#from" ).datepicker( "option", "maxDate", "'.$maxDate1.'" );
          $( "#from" ).on("change", function() {
            $( "#to" ).datepicker( "option", "minDate", getDate( this ) );
          });

          $( "#to" ).datepicker({
              numberOfMonths: 1,
              onSelect : function(dateText, inst){
                window.location = "'.$p_url.'?regMaxDate="+dateText;
              }
          });
          $( "#to" ).datepicker("option", "dateFormat", dFormat );
          $( "#to" ).datepicker( "setDate", "'.$maxDate.'" );
          $( "#to" ).datepicker( "option", "minDate", "'.$minDate2.'" );
          $( "#to" ).datepicker( "option", "maxDate", "'.$maxDate2.'" );
          $( "#to" ).on("change", function() {
            $( "#from" ).datepicker( "option", "maxDate", getDate( this ) );
          });
     
            
          function getDate( element ) {
            
            var date;
            try {
              date = $.datepicker.parseDate( dFormat, element.value );
            } catch( error ) {
              date = null;
            }
            return date;
          }
          
        } );
      </script>
    ';

    
    $output .= '
      <div class = "date_filter_box">
        <i class="fa fa-calendar" aria-hidden="true"></i>
        <label for="from">c</label>
        <input type="text" id="from" name="from" class="form-control" >
        <label for="to">по</label>
        <input type="text" id="to" name="to" class="form-control" >';
    /*$output .= '
        <div class = "filter_selec_box">'.$this->getSelectProk("filter_selects").'</div>';*/
    $output .= '    
        <label><a href = "'.$p_url.'?resetFilter=1">Сбросить</a></label>
      </div>
    ';
    return $output;
  }
  
  
  
}
  
