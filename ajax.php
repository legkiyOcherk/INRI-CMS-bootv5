<?php
require_once('require.php');

if(isset($_POST['hide_hpv_panel'])){
  $hide_hpv_panel = $_POST['hide_hpv_panel'];
  
  if($hide_hpv_panel){
    $_SESSION['is_having_poor_vision'] = false;
    #echo $_SESSION['is_having_poor_vision'];
    echo "ok";
  }else{
    echo "error";
  }
 
}

if(isset($_POST['show_hpv_panel'])){
  $show_hpv_panel = $_POST['show_hpv_panel'];
  
  if($show_hpv_panel){
    $_SESSION['is_having_poor_vision'] = true;
    #echo $_SESSION['is_having_poor_vision'];
    echo "ok";
  }else{
    echo "error";
  }
 
}

if(isset($_POST['ajax_search']) && $_POST['ajax_search']){
  global $PDO; 
  $output = '';
  
  $ajax_search = $_POST['ajax_search'];
  $ajax_search = strip_tags($ajax_search);
  $ajax_search = trim(preg_replace('#[^a-zA-Z0-9а-яёйА-ЯЁЙ]+#ui', ' ', $ajax_search));
  
  if($sive_log = true){ # Ведем логи
    $date_log = date("Y-m-d h:i:s");
    $ip_log = $_SERVER['REMOTE_ADDR'];
    
    $arr = array(
        'title' => $ajax_search,
        'datetime' => $date_log,
        'ip' => $ip_log
    );
    
    $rres = db::insert( DB_PFX.'search_log', $arr, 0);
  }
  
  require_once(NX_PATH.'vendors/phpmorphy/phpmorphy_init.php'); // Морфология
  $searchs = str_word_count($ajax_search, 1, "АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя0123456789");
  $arr_sear = array();
  foreach($searchs as $search){
    $sear = mb_strtoupper($search, 'utf-8');
    $collection = $morphy->findWord($sear);
    
    
    if(false === $collection) { 
      $arr_sear[] = '%'.$sear.'%';
      continue;
    } else {
      
    }

    foreach($collection as $paradigm) {
      $arr_sear[] = '%'.$paradigm[0]->getWord().'%';
      break;
    }
  }
  $i=0; $orm_search_name_item = $orm_search_item = '';
  foreach ($arr_sear as $a_sear){
    $prefix = '';
    if($i++) $prefix = ' AND ';
    
    $orm_search_name_item .= $prefix." `orm_search_name`  LIKE  '$a_sear' ";
    $orm_search_item .= $prefix." `orm_search`  LIKE  '$a_sear' ";
  }
  $tbl_url = DB_PFX."url";
  $tbl_goods_cat = DB_PFX."goods_cat";
  $tbl_goods = DB_PFX."goods";
  $limit = "LIMIT 10";
  $s = "
    SELECT `$tbl_goods`.*, `$tbl_url`.`url` 
    FROM  `$tbl_goods`
    LEFT JOIN `$tbl_url`
    ON (`$tbl_url`.`module` = '$tbl_goods') AND (`$tbl_url`.`module_id` = `$tbl_goods`.`id`)
    WHERE (
      ($orm_search_name_item)
      
    ";
    /*OR
      ($orm_search_item) 
      OR
      ($article_item) 
      OR
      ($article_provider_item) 
    */
    $s .= "
    )
    AND `$tbl_goods`.`hide` = 0
    ORDER BY `$tbl_goods`.`img` DESC
    $limit
    "; #pri($s);
    
    if($q = $PDO->query($s)){
      if( $q->rowCount()){
        if(!isset($availability) || !$availability){
          $availability_items =  db::select("*", DB_PFX."availability" );
          
          foreach($availability_items as $availability_item){
            $availability[ $availability_item['id'] ] = $availability_item['title'];
          }
        }
    
        while($r = $q->fetch()){
          $output .= '
          <div class="row" style="border-bottom:1px solid #ddd; padding:5px;margin: 5px 5px 5px 5px;">
            <div class="col-1"><img style="max-height:50px; max-width:50px;" src="'.Images::static_get_img_link("images/goods/slide", $r['img'],  'images/goods/variations/50x50',  50, null, 0xFFFFFF, 95).'"></div>
            <div class="col-6">
              <a class="posit" href="/'.$r['url'].'">'.$r['title'].'</a><br>
            </div>
            <div class="col-3">'.$availability[$r['availability_id']].'</div>
            <div class="col-2">'.$r['price'].'  <span class="rouble" style = "color: #212529; font-size: 17px; height: 18px;">руб.</span></div>
          </div>';
        }
      }
    }
  echo $output;
}


if(isset($_POST['search_query'])){
  $search_quer = $_POST['search_query'];
  
  if($search_quer){
    
    if(!isset($_SESSION['search_q'])){
      $_SESSION['search_q'] = '';
    }
    
    #session_destroy();
    if($search_quer != $_SESSION['search_q']){#echo "ne sovpadaet";
      $_SESSION['search_q'] = $search_quer;
    }else{ 
    }

    echo "ok";
  }else{
    echo "error";
  }
}


if(isset($_POST['good_buy'])){
  global $PDO;
  #echo "test";
  #pri($_POST);
  
  #if(!$_POST['user_title']) return 'Нет user_title';
	#if(!$_POST['userName']) return 'Не заданно имя';
  #if(!$_POST['userMail']) return 'Не задана почта';
  if(!$_POST['userPhone']) return 'Не задана телефон';
  
  #if(!isset($_POST['id_good'])) return 'Нет id_good';
  #if(!isset($_POST['userText'])) return 'Нет userText';
  
       /* var  id_good = '.$arr['id'].';
        var  userName = $("#UserName").val();
        var  userPhone = $("#UserPhone").val();
        var  userMail = $("#UserMail").val();*/
        
  $output = '';
  $uFio = $uPhone = $uMail = $uText = $uTextMess = $error = $is_send = '';
  $error_arr = array();
  
  $send_link = $_SERVER ['HTTP_REFERER'];
  if ($send_link){
    $send_link .= ' <a href = "'.$send_link.'" target = "_blank">Перейти</a>';
  }
  
  $mail = EMail::Factory();
  $email_order = db::value('val', DB_PFX.'config', "name = 'email_order'");
  
  $id_good = 0;
  #$id_good = intval($_POST['id_good']);
  $uTitle = substr(addslashes(trim($_POST['user_title'])), 0, 1000);
  #$uFio = substr(htmlspecialchars(trim($_POST['userName'])), 0, 1000);
  $uPhone = substr(htmlspecialchars(trim($_POST['userPhone'])), 0, 1000);
  #$uPostIndex = substr(htmlspecialchars(trim($_POST['userPostIndex'])), 0, 1000);
  #$uAddress = substr(htmlspecialchars(trim($_POST['userAddress'])), 0, 1000);
  #$uMail = substr(htmlspecialchars(trim($_POST['userMail'])), 0, 1000);
  #$uText = substr(htmlspecialchars(trim($_POST['userText'])), 0, 5000);
  
  if(isset($_POST['userName'])){
    $uFio = substr(htmlspecialchars(trim($_POST['userName'])), 0, 1000);
  }
  if(isset($_POST['userMail'])){
    $uMail = substr(htmlspecialchars(trim($_POST['userMail'])), 0, 1000);
  }  
  if(isset($_POST['userText'])){
    $uText = substr(htmlspecialchars(trim($_POST['userText'])), 0, 2000);
    $uText = addslashes(trim($_POST['userText']));
    $uText .= '
          <br>Страница заявки: '.$send_link.'<br>
    ';
    $uTextMess = trim($_POST['userText']);
    $uTextMess .= '
          <br>Страница заявки: '.$send_link.'<br>
    ';
  }  

  
  /*if (empty($uFio)){
    $error_arr['fio'] = 'Не введенно имя'; 
    $error = 1;
  }*/
  if (empty($uPhone)){
    $error_arr['phone'] = 'Не введенн телефон'; 
    $error = 1;
  }
  /*if (empty($uMail)){
    $error_arr['mail'] = 'Не введена почта'; 
    $error = 1;
  }*/
  //Ставим капчу
  /*require_once 'recaptcha-master/src/autoload.php';
  // Register API keys at https://www.google.com/recaptcha/admin
  $siteKey = '6LeclzkUAAAAALjr8-he8iludg6DwFZD_vEymWTF';
  $secret = '6LeclzkUAAAAADQoLudnksZt0wISdxEbN29peKoJ';
  
  if (isset($_POST['g-recaptcha-response'])){
    $recaptcha = new \ReCaptcha\ReCaptcha($secret);
    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
  }
  if ($resp->isSuccess()){
    #echo "кангратулейшенс";
  }else{
    $error_arr['captcha'] = 'Не праильно введена капча!';
    $error = 1;
    #foreach ($resp->getErrorCodes() as $code) {
    #  echo '<tt>' , $code , '</tt> ';
    #}
  }*/
  
  if($error != 1){
    $date = date("Y-m-d h:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $goodPrice = 0;
    $userGoodId = $id_good;
    $uTitle;
    
    if($id_good){
      $sg = "
        SELECT `".DB_PFX."goods`.*, `".DB_PFX."url`.`url` 
        FROM `".DB_PFX."goods`
        LEFT JOIN `".DB_PFX."url`
        ON (`".DB_PFX."url`.`module` = '".DB_PFX."goods') AND (`".DB_PFX."url`.`module_id` = `".DB_PFX."goods`.`id`)
        WHERE `".DB_PFX."goods`.`id` = $id_good
      ";
      //echo $sg;
      $qg = mysql_query($sg);
      
      $rg = mysql_fetch_assoc($qg); 
      
      /*echo "<pre>";
      print_r($rg);
      echo "</pre>";*/
      
      $userGoodId = $rg['id'];
      $goodPrice = $rg['price'];
        
    }

    
    $st = $PDO->prepare("
          INSERT INTO `".DB_PFX."reservations` 
              (`title`, `userStatus`, `date`, `userPhone`, `userName`, `userMail`, `longTxt1`, `userIp`, `hide`) 
      VALUES  (:title,  'Новая',      :date,  :userPhone,  :userName,  :userMail,  :longTxt1,  :userIp,   0    )
    ");
        if (!$st->execute(array( 
                            'title'=>$uTitle, 
                            'date'=>$date,                                 
                            'userPhone'=>$uPhone,
                            'userName'=>$uFio,
                            'userMail'=>$uMail,
                            'longTxt1'=>$uText,
                            'userIp'=>$ip,
                            )
                          )
              ) {
            
            die('cant rew'); #ошибка
        }
    
    $nuber = $PDO->lastInsertId();
     
     
    $subject = "Поступила заявка ".$_SERVER['HTTP_REFERER'];
    $message = '
        Заявка: '.$uTitle.'<br><br>
        № заявки: '.$nuber.'<br>
        Дата: '.$date.'<br>
        Имя: '.$uFio.'<br>
        Телефон: '.$uPhone.'<br>
    ';
    if($uMail){
      $message .= '
      Почта: '.$uMail.'<br><br>';
    }
    if($uTextMess){
      $message .= '
      Текст: '.$uTextMess.'<br><br><br>';
    }
    if($goodPrice)
      $message .= 'Цена: '.$goodPrice.'<br>';
      
    $message .= '    
        <br><br>
        
        IP: '.$_SERVER['REMOTE_ADDR'].'<br>
     
        <a href = "http://'.$_SERVER["HTTP_HOST"].'/'.ADM_DIR.'/reservations.php?edits='.$nuber.'">Перейти в админку</a><br><br>
    ';
        
    $tosend = $message;
        
  	if(isset($_SESSION['city_id']) && $_SESSION['city_id']  ){
      $default_phone = db::value("val", DB_PFX."config", "name = 'phone'", 0 );
    }
        
    $res = $mail->smtpmail ($email_order, $subject, $tosend);
    //$headers = 'From: test <'.$from.'>' . "\r\n";
    
    
    /*$headers = "Content-type: text/html; charset=\"utf-8\"";
    
    $res = mail($email_order, $subject, $tosend, $headers);
    */
        
    if($res){
      $is_send = true;
    }else{
      //$output = '<script>alert("Ошибка!");</script>';
      $error_arr['send'] = "Ошибка при отправке сообщения";
    }
    
    if($is_send){
      echo "ok";
      
    }else{
      echo "Возникли сложности при отправке заявки. Вы можете позвонить по телефону";
    }
    
  }else{
    
    foreach($error_arr as $err){
      echo $err."<br>";
    }
    
    echo $output;
    
  }
  
}

if(isset($_POST['feedback'])){
	if(!$_POST['requestName']) return 'Не заданно имя';
  if(!$_POST['requestPhone']) return 'Не задана почта';
  $output = '';
  $fio = $phone = $error = $is_send = '';
  
  $mail = EMail::Factory();
  $email_order = db::value('val', DB_PFX.'config', "name = 'email_order'");
  $fio = substr(htmlspecialchars(trim($_POST['requestName'])), 0, 1000);
  $phone = substr(htmlspecialchars(trim($_POST['requestPhone'])), 0, 1000);
  
  if (empty($fio)){
    $error_arr['fio'] = 'Не введенно имя'; 
    $error = 1;
  }
  if (empty($phone)){
    $error_arr['fio'] = 'Не введенн телефон'; 
    $error = 1;
  }
  
  if($error != 1){
    $date = date("Y-m-d");
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $s = "
      INSERT INTO `".DB_PFX."feedback` 
              (`title`, `txt3`,  `date`,  `phone`,  `txt1`, `hide`) 
      VALUES  ('$fio',  'Новая', '$date', '$phone', '$ip',   0);
    ";
    
    //echo "s = $s";
      
    mysql_query($s);
    $nuber = mysql_insert_id();
     
     
    $subject = "Заказ обратного звонка ".$_SERVER['HTTP_REFERER'];
    $message = '
        № заявки: '.$nuber.'<br>
        Дата: '.date("d.m.Y h:i:s").'<br>
        Имя: '.$fio.'<br>
        Телефон: '.$phone.'<br>
        IP: '.$_SERVER['REMOTE_ADDR'].'<br>
     
        <a href = "http://'.$_SERVER["HTTP_HOST"].'/'.ADM_DIR.'/feedback.php?edits='.$nuber.'">Перейти в админку</a><br><br>
    ';
        
    $tosend = $message;
        
  	$res = $mail->send($email_order, $subject, $tosend);
        
    if($res){
      //echo "Ваше заявка на получение карты отправленна.<br> В ближайшее время мы с вами свяжемся!";
      $is_send = true;
    }else{
      //$output = '<script>alert("Ошибка!");</script>';
      $error_arr['send'] = "Ошибка при отправке сообщения";
    }
    
    if($is_send){
      //echo "Ваша заявка принята. Наш менеджер свяжется и рассказет как забрать вашу карту";
      echo "ok";
      
    }else{
      echo "Возникли сложности при отправке заявки. Вы можете позвонить по телефону";
    }
    
  }else{
    
    foreach($error_arr as $err){
      echo $err."<br>";
    }
    
    echo $output;
    
  }
  
}
