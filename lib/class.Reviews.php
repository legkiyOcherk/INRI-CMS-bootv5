<?
class Reviews{
  
  function __construct(){
    global $PDO;
    $this->pdo = $PDO;
  }
  
  function bani_select($bani_id){
    global $otzlink;
    
    $otzlink[0] = '-';
    $otzlink['ekat'] = 'Екатеринбург';
    $otzlink['svobl']='Свердловская область';


    $select='<select name="bani_id" class="form-control">';
   
    foreach ($otzlink as $key => $value) {
      $selected='';
      if ($bani_id == $key) { $selected='selected'; }
      $select.= '<option class="form-control" value="'.$key.'" '.$selected.'>'.$value.'</option>';
    }
    $select.='</select>';	

    return $select;
  }
  
  function showForm(&$site) {
  $output = '';
	global $title;
  $gbookname = $gbookaddress = $gbookemail = $gbooktel = $gbookmessage = ''; $bani_id = 0;
  if(isset($_REQUEST['gbookname']) && $_REQUEST['gbookname']) $gbookname = $_REQUEST['gbookname'];
  #if(isset($_REQUEST['gbookaddress']) && $_REQUEST['gbookaddress']) $gbookaddress = $_REQUEST['gbookaddress'];
  #if(isset($_REQUEST['bani_id']) && $_REQUEST['bani_id']) $bani_id = $_REQUEST['bani_id'];
  if(isset($_REQUEST['gbookemail']) && $_REQUEST['gbookemail']) $gbookemail = $_REQUEST['gbookemail'];
  if(isset($_REQUEST['gbooktel']) && $_REQUEST['gbooktel']) $gbooktel = $_REQUEST['gbooktel'];
  if(isset($_REQUEST['gbookmessage']) && $_REQUEST['gbookmessage']) $gbookmessage = $_REQUEST['gbookmessage'];
  
$output .= '   
<big><b>Оставьте Ваш отзыв:</b></big><br><br>
<div class="form-table">
  <form action="" method=post class="form-horizontal" role="form">
  
    <div class="form-group">
      <label class="col-sm-2 control-label">Представьтесь: <span>*</span></label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        <input type="text" class="form-control" name="gbookname" value="'.$gbookname.'">
      </div>
    </div>';
    
    /*<div class="form-group">
      <label class="col-sm-2 control-label"><B>Город, адрес:</B> <span>*</span><BR><small>Улица, дом</small></label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        <input type="text" class="form-control" name="gbookaddress" value="'.$gbookaddress.'">     
      </div>
    </div>
    
    <div class="form-group">
      <label class="col-sm-2 control-label">Выберите&nbsp;регион:</label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        '.$this->bani_select($bani_id).'
      </div>
    </div>*/
$output .= ' 
    <div class="form-group">
      <label class="col-sm-2 control-label"><B>Ваш е-майл:</B><BR><small>не публикуется на сайте</small></label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        <input type="text" class="form-control" name="gbookemail" value="'.$gbookemail.'">      
      </div>
    </div>
    
    <div class="form-group">
      <label class="col-sm-2 control-label"><B>Телефон:</B><BR><small>не публикуется на сайте</small></label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        <input type="text" class="form-control" name="gbooktel" value="'.$gbooktel.'">      
      </div>
    </div>
    
    <div class="form-group">
      <label class="col-sm-2 control-label"><B>Отзыв:</B> <span>*</span></label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        <TEXTAREA name="gbookmessage" class="form-control" cols=50 rows=6 wrap=virtual>'.$gbookmessage.'</TEXTAREA>      
      </div>
    </div>';
/* <TR>
		<td style="padding-bottom:10px;" align="TOP" ><img align="bottom" src="modules/captcha/imagekey.php" border="0" alt="Защитный код" /> <span>*</span></td>
		<td style="padding-bottom:10px;"><input type="text" name="captcha" size="5" value=""> <B CLASS="TR">(Введите число на картинке слева. Для обновления нажмите F5)</B></td>		</tr>
   </TR> */
   
$output .= ' 

    <div class="form-group">
      <label class="col-sm-2 control-label">Капча: <sup><font color="#800000;">*</font></sup></label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <div class="g-recaptcha" data-sitekey="6LfPZzUUAAAAAAAGkT7bXJMZaUXq-UeNXo6VYCk2"></div>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">&nbsp;</label>
      <div class="col-sm-8 col-md-7 col-lg-5">
        <input class="btn btn-primary button" type="submit" name="gbooksendmessage" value="Отправить">
      </div>
    </div>

		<p><BR><span>*</span>&nbsp;&mdash;&nbsp;поля, обязательные для заполения</p>
		<div class="clear">&nbsp;</div>
      
  </form>
</div>
';
  return $output;

}

function getPaginator($number, $lim, $all_number){
  $output = '';
  if ($number > 1) {
  	$output .= '
      <div align="right"> 
        <ul class="pagination">';
  	
    for($i = 1; $i <= $number; $i++) {
  		if ($i == ($lim / $all_number + 1)) {
        $output .= '<li class="active"><a href="/reviews?lim='.($all_number * ($i - 1)).'"><b>'.$i.'</b></a></li>';  
      }else{
        $output .= '<li class=""><a href="/reviews?lim='.($all_number * ($i - 1)).'">'.$i.'</a></li>';
      }
  	}
    
  	$output .= "
        </ul>
      </div>";
  }
  return $output;
}

function showMessages(&$site, $lim = 0) {
  $output = '';
  /*$output .= <<<HTML
  <script type="text/javascript" >
    $(document).ready(function(){
    $("a[rel^='prettyPhoto']").prettyPhoto();
  });
  </script>
HTML;*/

  $all_number = 20;

	// NUMBER OF PHOTOS INTO SELECTED ALBUM
	
	$query = $this->pdo->query("SELECT `id` FROM `".DB_PFX."reviews` WHERE hide='0'");
	$count = $query->rowCount();

	if ($count % $all_number == 0) $number = $count / $all_number;
	else $number = ($count / $all_number + 1);		

	// PAGES
  #$output .= self::getPaginator($number, $lim, $all_number);
	
  //Извлекаем из базы открытые  сообщения
  #$string="SELECT DATE_FORMAT(date,\"%d.%m.%y\") as date, title, address, longtxt1, img, answer, id FROM ".DB_PFX."reviews WHERE hide='0' ORDER BY id desc LIMIT $lim, $all_number";
  $string="SELECT date, title, address, longtxt1, img, answer, id FROM ".DB_PFX."reviews WHERE hide='0' ORDER BY date desc LIMIT $lim, $all_number";
  
  $res= array();
  $i=0;
  if($q = $this->pdo->query($string)){
    while($row = $q->fetch()){
      $res[$i]['id']=$row['id'];
      $res[$i]['date']=$row['date'];      
      $res[$i]['name']=$row['title'];
      $res[$i]['address']=$row['address'];
      $res[$i]['text']=$row['longtxt1'];   
      $res[$i]['img']=$row['img'];              
      $res[$i]['answer']=$row['answer'];
      ++$i;
    }
  }  
  
  //Вывод сообщений
         
  foreach ($res as $v){
    
    $output .= '
      <a name="a'.$v['id'].'"></a>
      <hr size="1">
      <div style = "overflow: hidden;">';
    /*if($v['img']){
      $output .= '<a href="/images/reviews/orig/'.$v['img'].'" rel="prettyPhoto[gallery_rew]">';
      $output .= '<img style = "float: right;" src = "/images/reviews/slide/'.$v['img'].'">';
      $output .= '</a>';
    }*/
    $output .= '
      <p><I>'.sqlDateToRusDate($v['date']).'</I> &nbsp; <B>'.$v['name'].'</B></p>';
	  $output .= "$v[text]";
    $output .= "</div>";           
  }
 
  // PAGES
  $output .= self::getPaginator($number, $lim, $all_number);

  
  return $output;
}

function checkForm(&$site)
{
  $output = '';
  global $adminmail;    

// Если  не ввели сообщение, ругаемся
  if (isset($_REQUEST['gbooksendmessage'])){
		/*if (md5(md5($_POST['captcha'])) != $_SESSION['captcha']) {
			print "<B><FONT COLOR=RED> Неверный защитный код! (Для обновления нажмите F5)</Font></B><BR><BR>";
			showForm();
			return 0;
		}*/
    
    // Ставим рекапчу
    $recaptcha = $_REQUEST['g-recaptcha-response'];
    $secret = '6LfPZzUUAAAAAAAbQ8Vpt5W0YcY4oN9uBJ7FKgsz';
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=".$secret ."&response=".$recaptcha."&remoteip=".$_SERVER['REMOTE_ADDR'];
    $status = 1;
    if(!empty($recaptcha)) {
    $curl = curl_init();
      if(!$curl) {
      $status = 2;    
      } else {
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $curlData = curl_exec($curl);
        curl_close($curl);    
        $curlData = json_decode($curlData, true);
        if($curlData['success']) {
          $status = 0;
        }
      }
    } 
    if($status === 0) {  
      //Все хорошо
      #print "<B><FONT COLOR='#FF0000'>Все хорошо! В человек.</FONT></B><BR><BR>";
    } else if($status === 1) {  
      //Неверный код
      $output .= "<B><FONT COLOR='#FF0000'>Ошибка! А вы точноно не Андроид?</FONT></B><BR><BR>";
  	  $output .= $this->showform($site);
      return $output;
    } else if($status === 2) {  
      //Ошибка CURL
      $output .= "<B><FONT COLOR='#FF0000'>Ошибка CURL</FONT></B><BR><BR>";
  	  $output .= showform($site);
      return $output;
    }
		
    if ($_REQUEST['gbookmessage'] == '' ){
      $output .= "<B><FONT COLOR=RED>Пожалуйста, введите текст отзыва!</FONT></B><BR><BR>";
      $output .= $this->showForm($site);     
      return $output;
    }
	  if ($_REQUEST['gbookname'] == '' ){
      $output .= "<B><FONT COLOR=RED> Пожалуйста, введите Имя!</FONT></B><BR><BR>";
      $output .= $this->showForm($site);     
      return $output;
    }
     
     // ввели  сообщение
     
        
        // сохраняем в базе
        $name= $_REQUEST['gbookname'];    
        #$address=$_REQUEST['gbookaddress'];
        $email= $_REQUEST['gbookemail'];
        $tel= $_REQUEST['gbooktel'];
        $text= $_REQUEST['gbookmessage'];
        #$bani_id= $_REQUEST['bani_id'];
        $date= date('Y-m-d');
        $user_ip = $_SERVER['REMOTE_ADDR'];


#$name=mysql_real_escape_string($name);
#$address=mysql_real_escape_string($address);
#$email=mysql_real_escape_string ($email);
#$tel=mysql_real_escape_string ($tel);
$hide = 1;
#$bani_id=mysql_real_escape_string ($bani_id);
        
        //echo "name=$name\n";
        //echo "email=$email\n";
        //echo "text=$text\n";
        //echo "date=$date\n";
        //echo "user_ip=$user_ip\n";
        
        $st = $site->pdo->prepare("
          INSERT INTO `".DB_PFX."reviews` 
                  (`id`, `title`, `phone`, `email`,  `date`, `ip`, `longtxt1`, `hide`) 
          VALUES  (NULL, :title,  :phone,  :email,   :date,  :ip,  :longtxt1,  1     )
        ");
            if (!$st->execute([ 
                                'title'=>$name, 
                                'phone'=>$tel, 
                                'email'=>$email, 
                                'date'=>$date, 
                                'ip'=>$user_ip,                                 
                                'longtxt1'=>$text])) {
                //ошибка
                die('cant rew');
            }
            
        $last_id = $this->pdo->lastInsertId();
        
        /*$string="INSERT INTO ".DB_PFX."reviews (date,ip, title, tel, email, address, longtxt1, hide, bani_id, is_favorite) VALUES ('$date','$user_ip','$name', '$tel', '$email', '$address', '$text', '1','$bani_id','off')";
        //echo "string=$string";
        $query=mysql_query($string) or die('cant query '.mysql_error());
        $last_id= mysql_insert_id();*/
        
        //Посылаем письмо
        
        $text="
С сайта http://ivolga-ural.ru поступил новый отзыв:

Имя, Фамилия:           $_REQUEST[gbookname]
Тел:                    $_REQUEST[gbooktel]
Е-майл:                 $_REQUEST[gbookemail]

Текст сообщения:        $_REQUEST[gbookmessage]
            
         
Зайти в админку:
http://".$_SERVER['SERVER_NAME']."/".ADM_DIR."/reviews.php?edits=$last_id\n 
        
";

 

  $subject="Новый отзыв с сайта ivolga-ural.ru";

  #1@in-ri.ru
  #balkoncity@gmail.com
  mail('1@in-ri.ru', $subject, $text,'Content-type: text/plain; charset=utf8');

 
$output .= '<BR><BR><B>Спасибо! Ваш отзыв принят.<BR></B><BR><BR>';
$output .= '<a href="/reviews?message=1" class = "btn btn-primary" style="text-transform: uppercase;">добавить еще отзыв</a><BR>';
        
       
      
  }else $output .= $this->showForm($site);   
  
  return $output;
}  

  function showRev(&$site){
    $output = '';
    if (isset($_GET['message'])){
      $output .= $this->checkForm($site);
    
    }else {

    	$output .= '<a href="/reviews?message=1" class = "btn btn-primary btn_order" style="text-transform: uppercase;">Оставьте Ваш отзыв</a>';
      $lim = 0;
      if(isset($_GET['lim']) && $_GET['lim']) $lim = intval($_GET['lim']);
    	$output .= $this->showMessages($site, $lim);
    }
    
    return $output;
  }
}

