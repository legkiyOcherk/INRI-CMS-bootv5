$(function() {  
  $(".bye_count").bind("change click keyup", function () {
    
    
    var good_id = $(this).data("id");
    var good_price = $(this).data("price");
    var good_min_count = $(this).data("min");
    var good_count = $(this).val();
    var good_portion = $(this).data("portion");
    
    if(good_count < good_min_count){
      good_count = good_min_count;
      /*$(this).val(good_count);*/
    }
    
    var price =  good_count/good_portion * good_price;
    price = price.toFixed(0);
    
    $("#price_id_" + good_id).html(price);
    $("#store_id_" + good_id).data("count", good_count);
    /*console.log("count " + $("#store_id_" + good_id).data("count"));*/
    
  });
  
});
  
$(document).ready(function(){
	$(".fast-buy-but").click(function(){
		var phon=$("#inputPhone").val();
		var id=$(this).data("id");
		if (phon.length<6)
		{
			$("#input_holder").addClass("error");
			return false;
		}
		else
		{
			$("#input_holder").removeClass("error");
			$.post("/send_order.php", {phone:phon,id:id}).done(function( data ) 
			{
				if (data!="error") 
				{
					$("#fast-buy").removeClass("alert-info");
					$("#fast-buy").addClass("alert-success");
					$("#fast-buy").html("<h4>Спасибо, Ваш заказ принят</h4><p>Номер заказа <strong>"+data+"</strong><br>Наши сотрудники свяжутся с Вами в ближайшее время</p>");
				}
			});
		}
	});
  
	$('.store_amount').keyup(function() {
		var val = parseFloat($(this).val().toFixed(1))
		if (!val) val = 1
		if (val < 1) val = 1
    if (val > 99) val = 99
		$(this).val(val)
	})
  
  
	$('.store_buy').click(function() {
		var id = $(this).data('id')
		/*var amount = 1
		if (amount < 1) amount = 1*/
    
	  /*var amount = parseFloat($('#amount_'+id).val());*/
    var amount = parseFloat($(this).data('count'));
    if(isNaN(amount))amount = 1;
    
    $.post('/ajx.basket.php', {act: 'add', id: id, amount: amount}, function(data) {
			/*var callback = (data['status'] == 'ok') ? '<br><div class="alert alert-success">Добавлено <a href=/basket><u>в корзину</u></a></div>' : 'Ошибка при добавлении товара'*/
			if (data['status'] == 'ok') {
        /*location.href="/basket"*/;
        if(data['basket_head']){
          $(".basked").html(data['basket_head']);
          //alert ('Товар успешно добавлен в корзину');
        }
      }else{
        alert ('Ошибка при добавлении товара');
      }
		}, "json")
	
	})
  
	$('.store_callback').click(function() {
		$(this).html('')
	})
  
  
  	$('.store_buy_full').click(function() {
		
		var id = $(this).attr('id')
		id = id.replace('buy_', '');
		
		var size_val=$(".table-bordered input:checkbox:checked");
    
    //var size_val=$("input[name*=size_ch]:checked");
    
    console.log('size_val = '+ size_val + ' size_val.length = '+ size_val.length);
		
    if(size_val.length){
      //alert(size_val.length);
  		//return 0;
      /*if (size_val.length==0)
  		{
  			$('#sizes_'+id).tooltip({'placement':'top', "delay": { show: 500, hide: 100 }});
  			$('#sizes_'+id).tooltip('show');
  			return false;
  		}
  		else*/ 
  		{
  			var size=Array();
  			size_val.each(function(index){
  				size[index]=$(this).val();
  	    });
  		
      var amount = parseFloat($('#amount_'+id).val());
      if(isNaN(amount))amount = 1;
      
      var size_type = 'rus';
      size_type = $('.size_style option:selected').attr('name');
      //alert('size_type = '+size_type);
      $.post('/ajx.basket.php', {act: 'add', id: id, amount: amount, size:size, size_type:size_type}, function(data) {
  			var callback = (data['status'] == 'ok')  ? '<span class="label label-success">Добавлено</span>' : 'Ошибка при добавлении товара';
        
        if (data['status'] == 'ok') location.href="/basket.php";
  			$('#callback_'+id).html(callback);
  			$('#cart').html(data['basket_head']);
  			// alert(data['basket_head']);
  			setTimeout("$('#callback_"+id+"').html('')", 5000)
  		}, "json")
  		$(".chkbx").attr("checked", false);
  	  }
  	}else{
      
      $('.error_size_box').fadeIn("slow");
      
      //alert('Не выбран размер.');
    }
	$('.store_callback').click(function() {
		$(this).html('')
	})
	})
  
  
  
})


function change_amount(id, z, cart) {
	var amount = parseFloat($('#amount_'+id).val())
	if (z == 1) {
		amount = (amount > 0) ? amount - 1 : 0
	}
	if (z == 2) {
    amount = (amount < 98) ? amount + 1 : 99
	}
	if (cart) {
		$.post('/ajx.basket.php', {act: 'change_amount', id: id, amount: amount}, function(data) {
			$('#basket_ajx').html(data)
		})
		setTimeout('refresh_basket()', 300)
	} else {
		$('#amount_'+id).val(amount)
	}
}


function refresh_basket() {
	$.post('/ajx.basket.php', {act: 'get'}, function(data) {
    if(data['basket_head']){
      $(".card_link_box").html(data['basket_head']);
    }
		/*$('#head_basket').show()
		$('#head_basket p').html(data['basket_head'])*/
	}, "json")
}


function delete_basket_item(id) {
	/*if (confirm('Удалить выбранный товар из корзины?'))*/ {
		$.post('/ajx.basket.php', {act: 'delete_item', id:id}, function(data) {
			//if (data) $('#basket_ajx').html(data)
      $('#basket_ajx').html(data);
      if (!data){
        $('#basket_ajx_box').html('<div class="page-header"><h1>Корзина</h1></div><p>Корзина пуста</p>');
      }
		})
		refresh_basket()
	}
}


function send_order() {
	var fio = $('#fio').val()
	var phone = $('#phone').val()
	var email = $('#email').val()
  var city = $('#city').val()
	var comment = $('#comment').val()
	var address = $('#address').val()
	var datetime = $('#datetime').val()
	var nalbeznal = $('input[name=nalbeznal]').val()
	var dost = $('input[name=dost]:checked').val()
	if ((!fio.length) || (!phone.length) || (!email.length) ) {
		alert ('Заполните обязательные поля')
	} else {
		var org = $('#org').val()
		$.post('/ajx.basket.php', {act: 'order', fio: fio, phone: phone,datetime:datetime, address:address, email: email, city: city, org: org, nalbeznal:nalbeznal, dost:dost, comment:comment}, function(data) {
			if (data) {
				//$('.disable_me input').attr('disabled', 'disabled')
				//$('.disable_me textarea').attr('disabled', 'disabled')

        $("#basket_ajx_box").html(data);
				//alert('Спасибо! Ваш заказ принят. Для подтверждения заказа в ближайшее время с вами свяжется оператор.');
				
        //$('#callback').html('')
				//$('#order_submit').remove();
        //$('.backet_box').html(data);
			} else {
				$('#callback').html('Ошибка.')
			}
		})
	}
	//setTimeout("$('#callback').html('')", 2000)
}
//Отмена отправки формы
   
$(function (){
  $('#basket_form').submit(function (){
    /*alert('No');*/
    return false;
  });
});



function send_order2() {
	$('.user_contacts input.radio').each(function(i) {
		if ($(this).attr('checked') == true) {
			var contacts_id = $(this).val()
			$.post('/ajx.basket.php', {act: 'order', contacts_id:contacts_id}, function(data) {
				if (data == 'ok') {
					$('.order_submit').attr('disabled', 'disabled')
					$('#callback2').html('<p style="color: #cc0000;">Ваш заказ принят. Спасибо! Для подтверждения заказа в ближайшее время с вами свяжется оператор.</p>')
					$('#head_basket p').html('Заказ принят. Корзина пуста.')
				} else {
					$('#callback').html('Ошибка.')
				}
			})
		}
	})
}


function basket_login() {
	var email = $('input#email').val()
	var passw = $('input#password').val()
	if ((!email.length) || (!passw.length)) {
		$('#callback').html('Заполните поля')
	} else {
		$.post('/ajx.basket.php', {act:'login', email:email, password:passw}, function(data) {
			if (data == 1) {
				window.location = '/basket.php'
			} else {
				$('#callback').html(data)
			}
		})
	}
	return false
}