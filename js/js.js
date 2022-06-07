$(document).ready(function() {
  $('.info_item_text_close', this).click(function(){
    $(this).parent('.info_item_text_box').hide();
  })
  
  
  /* всплывашка в катрочке товаров */
  $('.info_img_box', this).click(function(){
    $(this).parent('.info_item').children('.info_item_text_box').show();
  })
  
  // Скрываем всплывашки при клике вне их
  $(document).click(function(event) {
    if (!$(event.target).closest(".info_img_box").length){
      if (!$(event.target).closest(".info_item_text").length){
          $(".info_item_text_box").fadeOut("slow");
          event.stopPropagation();
      }
    }
  });
  
  
  // Картоска товара скрыть/показать полное описание
  $('.bt_show_more', this).click(function(){
    if($(this).data('fullView') == 0){
      $(this).html('скрыть');
      $(this).data('fullView', 1).attr('data-full-view', 1);
      $(this).parent('.bt_more').parent('.butik_more_box').parent('.col-xs-12').children('.full_text').slideDown(700);
    }else{
      $(this).html('показать больше'); 
      $(this).data('fullView', 0).attr('data-full-view', 0);
      $(this).parent('.bt_more').parent('.butik_more_box').parent('.col-xs-12').children('.full_text').slideUp(700);
    }
  });
  

  
});

$( window ).load(function() {
  // Карточка товара задать высоту картинкам одинаковую (самую большую)
  $('.bt_im_items').each(function(i, elem) {
    var arr = $(elem).find("img");
    var max_height = 0;
    arr.each(function(i1, elem1) {
      
      if( $(elem1).height() >  max_height) {
        max_height =  $(elem1).height();
      }
      console.log('bt_im_items i1 = '+i1+' height '+$(elem1).height());
    })
    console.log('max_height max_height = '+max_height);
    if(max_height){
      $(elem).find("img").height(max_height);
    }
    
    
  });
});