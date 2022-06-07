// --- carousel ---
var isWork = 0;
var interval = 200;
var timePause = 10000;

function resetWork(){
	isWork = 0;
}

$(function(){
	var list = $('.carouselItem');
	var listImg = $('.carouselImg');
	var listPage = $('.carouselPg');
	
	//var page_w = $(window).width();
  var page_w = $('#carousel').width();
  
  //console.log('ready page_w '+page_w);

  for (var i = 0; i < list.length; i++) {
		leftImg = page_w /2 - listImg[i].width /2;
		$('#'+listImg[i].id).css("marginLeft", leftImg+"px");
		
		if(i == 0){
			//$('#'+list[i].id).css("zIndex","5");
			//alert ($('#'+listPage[i].id).attr('class'));
			$('#'+listPage[i].id).attr('class', "carouselPg active")
		}else{
			$('#'+list[i].id).css("display","none");
		}
  }
	
	//dump( page_w );
	
	//Таймер
	var timerMulti = window.setInterval("carouselNext();", timePause); 
	
	$("#carouselNext").click(function(){ 
		clearInterval(timerMulti);
		carouselNext();
	})
	
	$("#carouselPrevious").click(function(){ 
		clearInterval(timerMulti);
		carouselPrevious();
	})
	
	
	$(".carouselPg").click(function(){ 
	if(!isWork){
		isWork = 1;
		setTimeout(resetWork, interval);	
		
		clearInterval(timerMulti);
		var list = $('.carouselItem');
		var listPage = $('.carouselPg');
		var thLi = 0;
		var nxtLi = 0;
		
		
		for (var i = 0; i < list.length; i++) {
			if ($('#'+list[i].id).css("display") != "none"){
				thLi = i;
				//alert("Этот "+thLi);
			}
		}
		
		for (var i = 0; i < listPage.length; i++) {
			if (listPage[i].id == $(this).attr('id')){
				nxtLi = i;
			}
		}
		$('#'+listPage[thLi].id).attr('class', "carouselPg");
		$('#'+listPage[nxtLi].id).attr('class', "carouselPg active");
		
    console.log($('#'+list[thLi].id).attr('class'));
		$('#'+list[thLi].id).fadeOut(interval);
		
		
		$('#'+list[nxtLi].id).fadeIn(interval);
	
	}

		
	})

});

/*$( window ).resize(function() {*/
$(window).on("resize", function (e) {
	var list = $('.carouselItem');
	var listImg = $('.carouselImg');
	//var page_w = $(window).width();
  var page_w = $('#carousel').width();

  for (var i = 0; i < list.length; i++) {
		leftImg = page_w /2 - listImg[i].width /2;
		$('#'+listImg[i].id).css("marginLeft", leftImg+"px");
  }
	
});

$(window).on("load", function (e) {
	var list = $('.carouselItem');
	var listImg = $('.carouselImg');
	//var page_w = $(window).width();
  var page_w = $('#carousel').width();

  for (var i = 0; i < list.length; i++) {
		leftImg = page_w /2 - listImg[i].width /2;
		$('#'+listImg[i].id).css("marginLeft", leftImg+"px");
  }
});



function carouselNext(){
	if(!isWork){
		isWork = 1;
		setTimeout(resetWork, interval);
		
		var list = $('.carouselItem');
		var listPage = $('.carouselPg');
		var thLi = 0;
		
		for (var i = 0; i < list.length; i++) {
			if ($('#'+list[i].id).css("display") != "none"){
				var thLi = i;
			}
		}
		
		if( thLi <  list.length-1){
			$('#'+list[thLi].id).fadeOut(interval);
			$('#'+listPage[thLi].id).attr('class', "carouselPg");
			
			$('#'+list[thLi+1].id).fadeIn(interval);
			$('#'+listPage[thLi+1].id).attr('class', "carouselPg active");
		}else{
			$('#'+list[thLi].id).fadeOut(interval);
			$('#'+listPage[thLi].id).attr('class', "carouselPg");
			
			$('#'+list[0].id).fadeIn(interval);
			$('#'+listPage[0].id).attr('class', "carouselPg active");
		}
	}
}

function carouselPrevious(){
	if(!isWork){
		isWork = 1;
		setTimeout(resetWork, interval);
	
		var list = $('.carouselItem');
		var listPage = $('.carouselPg');
		var thLi = 0;
		
		for (var i = 0; i < list.length; i++) {
			if ($('#'+list[i].id).css("display") != "none"){
				var thLi = i;
			}
		}
		
		if( thLi >  0){
			$('#'+list[thLi].id).fadeOut(interval);
			$('#'+listPage[thLi].id).attr('class', "carouselPg");
			
			$('#'+list[thLi-1].id).fadeIn(interval);
			$('#'+listPage[thLi-1].id).attr('class', "carouselPg active");
		}else{
			$('#'+list[thLi].id).fadeOut(interval);
			$('#'+listPage[thLi].id).attr('class', "carouselPg");
			
			$('#'+list[list.length-1].id).fadeIn(interval);
			$('#'+listPage[list.length-1].id).attr('class', "carouselPg active");
		}
	}
}

function dump(obj) {
    var out = "";
    if(obj && typeof(obj) == "object"){
        for (var i in obj) {
            out += i + ": " + obj[i] + "\t";//"\n";
        }
    } else {
        out = obj;
    }
		//return out;
    alert(out);
}
//Math.round

// --- /carousel ---