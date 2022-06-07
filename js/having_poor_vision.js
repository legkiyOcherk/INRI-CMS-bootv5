jQuery( document ).ready(function( $ ) {
  if(!(jsCookies.check("lineHeight")))
  {
      jsCookies.set("lineHeight", 2, 7);
  }

  var fontScale = jsCookies.get("fontScale") || 2;
  fontRescale(fontScale);
  jsCookies.set("fontScale", fontScale, 7);
  
  if((jsCookies.check("colorScheme")))
  {
    var colorScheme = jsCookies.get("colorScheme");
    setColors(colorScheme);
  }
  
  if((jsCookies.check("ImageClass")))
  {
    var ImageClass = jsCookies.get("ImageClass");
    setImageClass(ImageClass);
  }
  
  $("#hide_hpv_panel", this).click(function() {
    
    $.ajax({
               type: "POST",
               url: "/ajax.php",
               data: "hide_hpv_panel=1",
               success: function(msg){
                if(msg == "ok"){
                  location.reload();
                }
               }
            });
    
    
  });
  
  
});

jQuery("#goToOriginal").on('click', function(){
  var url = decodeURIComponent(window.location.search).replace('&amp;', '&').replace("?hostname=","").replace("&path=", "");
  window.location.href = "http://" + url;
});


function fontRescale(fontScale) {
  var fontSize;
  var lineHeightScale = jsCookies.get("lineHeight");
  jQuery("*").not(".all-ignore, .ignore-font, nav.navbar-having_poor_vision, nav.navbar-having_poor_vision *").each(function(idx) {
      fontSize = jQuery(this).css('font-size');
      fontSize = parseInt(fontSize) + 2 * fontScale;

      lineHeight = fontSize* Math.pow(1.1, lineHeightScale);
      jQuery(this).animate({'line-height': lineHeight + 'px'}, 0);

      fontSize = fontSize + 'px';
      jQuery(this).animate({fontSize: fontSize}, 250);
  });

  var totalScale = parseInt(jsCookies.get("fontScale"));
  jsCookies.set("fontScale", totalScale + fontScale, 7);
}


function setColors(color) {
  jQuery("*").not(".all-ignore, .ignore-color, nav.navbar-having_poor_vision, nav.navbar-having_poor_vision *").removeClass("hpv_white hpv_black hpv_darkblue").addClass(color);
  jsCookies.set("colorScheme", color, 7);
  
}

function setImageClass(klass) {
  var selectors = "img, .finevision-img";
  jQuery(selectors).removeClass("hidden gray").addClass(klass);
  if (klass == 'hidden') {
      jQuery(selectors).css("display", "").attr('style', function(i,s) { return s + ' display: none !important' });
  } else {
      jQuery(selectors).show();
  }
  klass = typeof klass == "undefined" ? "" : klass;
  jsCookies.set("ImageClass", klass, 7);
}

function setLineHeight(diff) {
  var lineHeight;
  var totalScale = parseInt(jsCookies.get("lineHeight")) + diff;
  if ( (totalScale > 14) || (totalScale < -7)) {
      return 0
  }
  jQuery("*").not(".all-ignore, .ignore-line-height, nav.navbar-having_poor_vision, nav.navbar-having_poor_vision *").each(function(idx) {
      lineHeight = jQuery(this).css('font-size');
      lineHeight = parseFloat(lineHeight) * Math.pow(1.1, totalScale);
      lineHeight = lineHeight + 'px';
      jQuery(this).animate({'line-height': lineHeight}, 250);
  });
  jsCookies.set("lineHeight", totalScale, 7);
}

jQuery('#audio_btn').on('click', function () {
  document.getElementById("audio").play();
});