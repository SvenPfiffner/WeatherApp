
$(document).ready(function(){
	var navigation = (config && config.navigation) ? config.navigation : "navigation";

	if ($('.nav ul').length && $('.nav ul').length > 0) {
		$('.nav').prepend('<span class="before">'+navigation+'</span>');
		$(".nav .before").click(function(){
			$($(this).parent().children("ul")).toggle("fast");
		});
		
		$(".nav li ul").parent().addClass('parent');
	    $(".nav .parent li ul").parent().removeClass('parent').addClass('parent2');
	    $('.nav .parent , .nav .parent2').prepend('<span class="wp-sub"></span>');
	}

    $(".wp-sub").click(function(e){
    	e.preventDefault();
	    if($(this).parent().hasClass("isOpen")){
	        $(this).parent().children("ul").slideUp();
	        $(this).parent().removeClass("isOpen");
	        
	    }else{
	        $(this).parent().children("ul").slideDown();
	        $(this).parent().addClass("isOpen");
	    }
    });
    
	reverseMenu();

	if (window.orientation == 0){
		portraitOrientation()
	}else{
		landscapeOrientation();
	}
	
});

function preventClick(){
	// added for prevent click on android.
	$(".parent>a , .parent2>a").click(function(e) {
			e.preventDefault();
			if($(this).hasClass('open')) {
				window.location = $(this).attr('href');      
			}
			else
		   {
				$("a").removeClass("open");
				$(this).addClass('open');
				$(this).closest('li').find('ul').css('display','block');
		   }
		});
}

function  reverseMenu(){
	//added for reverse menu .
		$('.wp-menu.rtl li.parent , .wp-menu.rtl li.parent2').bind('mouseover', function(){
			var self=this;
			var callback = function() {
				var parentName = $(self).attr("class");
				var ww = $(window).width();
				var subUL = $(self).find("ul");
				var locUL = subUL.offset().left ;
				var par = subUL.parent();
				if (locUL < 0) {
					if (parentName == "parent") {
						subUL.css('left', '0');
					} else { 
						subUL.css('right', "-"+(subUL.width())+"px"); 
					}
				}
				return self;				
			};

			if ($.browser.msie && $.browser.version == "8.0") {
				setTimeout(callback, 1);	
			} else {
				callback();
			}
		});

		$('.wp-menu li.parent, .wp-menu li.parent2').bind('mouseover', function(){
			var self=this;
			var callback = function() {
				var parentName = $(self).attr("class");
				var ww = $(window).width();
				var subUL = $(self).find("ul");
				var locUL = subUL.offset().left + subUL.width();
				var par = subUL.parent();
				if (locUL > ww) {
					if ($(self).hasClass("parent")) {
						subUL.css('right', '0');
					} else { 
						subUL.css('left', "-"+(subUL.width())+"px"); 
					}
				}
				return self;				
			};

			if ($.browser.msie && $.browser.version == "8.0") {
				setTimeout(callback, 1);	
			} else {
				callback();
			}
			
		});

}

function portraitOrientation(){
	// added for prevent click on mobile.
	if( /Android/i.test(navigator.userAgent) ) {
		preventClick()
	}
	if( /mobile|webOS|BlackBerry/i.test(navigator.userAgent) ) {
		$(".parent>a ,.parent2>a").unbind("click");
	}
	$('.wp-menu li.parent , .wp-menu li.parent2 , .wp-menu.rtl li.parent , .wp-menu.rtl li.parent2').unbind('mouseover');
}
function landscapeOrientation(){
	// added for prevent click on mobile.
	if( /Android|webOS|BlackBerry/i.test(navigator.userAgent) ) {
		preventClick()
	}
}

$(window).bind('orientationchange', function(e) {
	 switch ( window.orientation ) {
	 	    case 90:
		    case -90:
		    	reverseMenu();
		    	landscapeOrientation();
		    	
		    break;
		    case 0:
        		portraitOrientation();
    		break;
		  }
});