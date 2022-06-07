<?

class HavingPoorVision{
  
  function __construct(){

  }

  function showPanel(){
    $output = '';
    
    $output .= '
    <nav class="navbar navbar-default navbar-having_poor_vision">
      <div class="container-fluid">
      
        <ul class="nav navbar-nav navbar-left pull-left">
          <li>
            <div class="frameForSingle">
              <button type="button" id="hide_hpv_panel" class="hpv_btn hpv_btn-default">Обычная версия</button>
            </div>
          </li>
        </ul>
        
        <div class="navbar-collapse">
          <ul class="nav-having_poor_vision navbar-nav-having_poor_vision">
            <li><div class="frame">
                <span class="bold text-center">Размер шрифта</span>
              </div>
              <div class="text-center">
                <button type="button" id="font-sm" class="hpv_btn hpv_btn-default" onclick="fontRescale(-1)">A-</button>
                <button type="button" id="font-lg" class="hpv_btn hpv_btn-default" onclick="fontRescale(1)">A+</button>
            </div></li>
            <li><div class="frame">
              <span class="bold text-center">Интервал</span>
              </div>
              <div class="text-center">
              <button type="button" id="line-height-sm" class="hpv_btn hpv_btn-default" onclick="setLineHeight(-1)">-</button>
              <button type="button" id="line-height-lg" class="hpv_btn hpv_btn-default" onclick="setLineHeight(1)">+</button>
            </div></li>
            <li><div class="frame">
                <span class="bold text-center">Цвет сайта</span>
              </div>
              <div class="text-center">
                <button type="button" id="black" class="hpv_btn hpv_btn-default hpv_white bold" onclick="setColors(\'hpv_white\')">Ц</button>
                <button type="button" id="white" class="hpv_btn hpv_btn-default hpv_black bold" onclick="setColors(\'hpv_black\')">Ц</button>
                <button type="button" id="blue" class="hpv_btn hpv_btn-default hpv_darkblue bold" onclick="setColors(\'hpv_darkblue\')">Ц</button>
            </div></li>
            <li><div class="frame">
                <span class="bold text-center">Изображения</span>
              </div>
              <div class="btn-group" role="group" aria-label="...">
                <button type="button" class="hpv_btn hpv_btn-default" onclick="setImageClass(\'hidden\')">Выкл</button>
                <button type="button" class="hpv_btn hpv_btn-default" onclick="setImageClass(\'gray\')">Ч/б</button>
                <button type="button" class="hpv_btn hpv_btn-default" onclick="setImageClass()">Цвет</button>
              </div>
            </li>
    ';
            /*<li>
              <div class="frame">
                <span class="bold text-center">Звук</span>
              </div>
              <div class="text-center">
                <button id="audio_btn" type="button" class="hpv_btn hpv_btn-default">
                  <span class="glyphicon glyphicon-volume-up" aria-hidden="true"></span>
                </button>
    
                <audio id="audio" src="/static/audio/info.mp3"></audio>
              </div>
            </li>*/
    $output .= '
            
          </ul>
        </div>
      </div>
    </nav>
    ';
    
    return $output;
  }  
  
  function getJs(){
    $output = '';
    
    $output .= '
    <script src="/js/jsCookies.js"></script>
    <script src="/js/having_poor_vision.js"></script>
    ';
    
    /*
    function getParameterByName(name, url) {
      if (!url) url = window.location.href;
      name = name.replace(/[\[\]]/g, "\\$&");
      var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
          results = regex.exec(url);
      if (!results) return '';
      if (!results[2]) return '';
      return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    jQuery("#robokassaPay").val("Оплатить подписку для " + getParameterByName('hostname'));
    jQuery("#robokassaPayHostname").val(getParameterByName('hostname') + getParameterByName('path'));
    */
    
    return $output;
  }
  
  function getCss(){
    $output = '';
    $output .= '
    <link href="css/having_poor_vision.css" rel="stylesheet">
    ';
    
    return $output;
  }

}

