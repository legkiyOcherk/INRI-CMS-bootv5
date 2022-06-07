<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Carusel.php');

class AdminLogs extends Carusel{
  
  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          	<td style="width: 55px;">#</td>
            <td>Время</td>
            <td>Пользователь</td>
      		  <td>IP</td>
            <th>Изменения</th>
            <td>Скрипт</td>
          </tr>';
    
    return $output;
  }
  
  
  function show_table_rows($item){
    $output = '';
    extract($item);
    
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
            </td>
             
            <td style="text-align: left;">'.$date_time.'</td>
            <td style="text-align: left;"><b>'.$login.'</b> '.$fullname.' id('.$user_id.')</td>
            <td style="text-align: left;">'.$ip.'</td>
            <td style="text-align: left;">'.$changes.'</td> 
            <td style="text-align: left;">'.$script.'</td>
            ';
            
    /*$output .= '
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
            </td>';*/
  	$output .= '
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
    $s_order = " ORDER BY `".$this->prefix.$this->carusel_name."`.`date_time` DESC ";
    
    if(!$count_items) $output .= "<p>Раздел пуст</p>";
    if($this->is_filter &&  $count_items) $output .= $this->getFilterTable($s_filter);
    # if( $count_items) $groupOperationsCont = $this->getGroupOperations();
    if($this->is_pager && $count_items) $strPager = $this->getPager( $count_items, $s_limit);
   
    $output .= $strPager;
    
    $s = "
      SELECT `".$this->prefix.$this->carusel_name."`.*, 
             `".$this->prefix."accounts`.`fullname` AS fullname,
             `".$this->prefix."accounts`.`login`    AS login
      FROM   `".$this->prefix.$this->carusel_name."`
      LEFT JOIN  `".$this->prefix."accounts` ON `".$this->prefix."accounts`.`id` = `".$this->prefix."admin_logs`.`user_id`
      $s_filter
      $s_sorting
      $s_order
      $s_limit
    "; #pri($s); 
    
    $output .= '
      <form 
        method="post" 
        action="'.$this->carusel_name.'.php" 
        id="sortSlide"
        class="table-responsive"
      >
        <input type="hidden" name="slideid" value="1">
    ';
    if($q = $this->pdo->query($s))
      if($q->rowCount()){
        
        
    #if($items){
      
      $output .= '
  	    <table id="sortabler" class="table sortab table-condensed table-striped ">
          '.$this->show_table_header_rows();
      
      while($item = $q->fetch()){
        
        $output .= $this->show_table_rows($item);

        
      }
      
      $output .= '
        </table>
      ';
      
      
    }
    $output .= $groupOperationsCont;
    $output .= '
    <br>
  	<center><a class="btn btn-success " href="?adds" id="submit">Добавить</a></center>
    </form>';

    
    if($this->is_pager) $output .= $strPager;
    
    return $output;
    
  }
  
}

$date_arr = array(
    #'title'     => 'Название',
    'ip'      => 'Ссылка',
    'user_id'      => 'Текст',
    'date_time'  => 'Описание',
    'action'      => 'Скрыть',
    'item_id'   => 'Alt изображение',
    'changes' => 'Title изображение',
    'script' => 'Title изображение'
  );
  
$pager = array(
  'perPage'           => 50,
  'page'              => 1,
  'url'               => '',
  'items_per_page'    => array( 50, 100, 500, 1000, 5000)
);
  

$carisel = new AdminLogs('admin_logs', $date_arr, true, true, $pager);


$carisel->setHeader('Логи входа в панель');
$carisel->setIsUrl(false);
$carisel->setIsImages(false);
$carisel->setIsPager(true);
$carisel->setIsLog(false);

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
