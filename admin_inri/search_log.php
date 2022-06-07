<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Carusel.php');
require_once('lib/class.Image.php');



class SearchLog extends Carusel{
  
    function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          
      		  <td style="width: 30px;">#</td>
      		  <td style="width: 50px;">Скрыть</td>
            <td >Дата</td>
            <td >Поисковая фраза</td>
            <td >ip</td>
      		  <td style="width: 80px">Действия</td>
          </tr>';
          
    return $output;
  }
  
  function show_table_rows($item){
    $output = '';
    extract($item);
    ($hide) ? $star_val = "glyphicon glyphicon-star" : $star_val = "glyphicon glyphicon-star-empty";
        $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td style="width: 20px;">'.$id.'<input type="hidden" value="'.$id.'" name="itSort[]"></td>
            
            <td style="width: 30px;" class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$star_val.'" id="hide_'.$id.'"></div></td>
        ';
        
        if($datetime){
          $dArr = explode("-", $datetime);
          $year = $dArr[0];
          $day = $dArr[2];
          $month = $dArr[1];
          switch($dArr[1]){      
            case "01": $month = ' января ';  break;       
            case "02": $month = ' февраля '; break;       
            case "03": $month = ' марта ';   break;       
            case "04": $month = ' апреля ';  break;       
            case "05": $month = ' мая ';     break;       
            case "06": $month = ' июня ';    break;       
            case "07": $month = ' июля ';    break;       
            case "08": $month = ' августа '; break;       
            case "09": $month = ' сентября ';break;       
            case "10": $month = ' октября '; break;       
            case "11": $month = ' ноября ';  break;       
            case "12": $month = ' декобря '; break; 
          }
          
          $date_str = $day.' '.$month.' '.$year;
        }
          
        $output .= '
            <td>
              '.$date_str.'
            </td>
        '; 
        /*
        $output .= '
            <td style="width: 50px;">
        ';
        if($img){
          $output .= '
            <div class="zoomImg"><img style="width:50px" src="../images/'.$this->carusel_name.'/slide/'.$img.'"></div>  
          ';
        }else if($color){
          $output .= '
            <div class="zoomImg" style = "background-color: '.$color.'">
          ';
        }
        $output .= '
            </td>
        ';*/
        
        $output .= '
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>
        	  
      	';
        $output .= '<td>'.$ip.'</td>';
        
        /*$output .= '
            <td style="width: 60px; text-align: left; color:#000;" nowrap="" class="id">
            '.$price.'
              <!--<input type="text" class="span1" name="prices[1]" value="'.$price.'">-->
            </td>
        ';*/
        $output .= '
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
  			  </tr>
        ';
        
    
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
    $s_order = " ORDER BY `datetime` DESC, `id` DESC ";
    
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
    'title' => 'Поисковая фраза',
    'datetime' => 'Дата',
    'ip' => 'ip',
    'longtxt1' => 'Сообщение',
    'longtxt2' => 'Комментарий '
    
  );
  

$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title');

$carisel = new SearchLog('search_log', $date_arr, true, true, $pager);

$carisel->setHeader('Логи поиска');
$carisel->setIsUrl(false);
$carisel->setIsImages(false);
$carisel->setIsFiles(false);
$carisel->setIsPager(true);
$carisel->setIsFilter(false);
$carisel->setIsLog(true);
$carisel->setFilterField($arrfilterfield);
$carisel->setImg_ideal_width(750);  
$carisel->setImg_ideal_height(750); 
  
#$carisel->setDate_arr($date_arr);

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
