
$(document).ready(function() {
if(/(iPhone|iPod|iPad)/i.test(navigator.userAgent)) { 
    if(/OS [2-4]_\d(_\d)? like Mac OS X/i.test(navigator.userAgent)) { 
        // iOS 2-4 so Do Something  
      // alert('ios[2-4]');
       $('#toppanel').removeClass('ios5');
       $('#toppanel').addClass('ios4');

        $('.sk-container').on('touchstart', function(){
           $('#toppanel').css('visibility', 'hidden');
        });


        var $myFixedDiv = $('#toppanel');
       	var iFixedDivHeight = $myFixedDiv.outerHeight({ 'margin': true });

        $(window).scroll(function() {
            var iWindowHeight = $(window).height();
            var iScrollPosition = $(document).scrollTop(); // or document.body.scrollTop
            //$myFixedDiv.css({ 'top': iWindowHeight + iScrollPosition - iFixedDivHeight + 60});
             $myFixedDiv.css({ 'bottom': 0 - iScrollPosition - iFixedDivHeight - 20});

        });


        $('.sk-container').on('touchend', function(){  
             $('#toppanel').css('visibility', 'visible');
        }); 
        			

    } else if(/CPU like Mac OS X/i.test(navigator.userAgent)) {
        // iOS 1 so Do Something 

    } else {
        // iOS 5 or Newer so Do Nothing				
				
    }
}

// Expand Panel
					$("#open").click(function(){
						$("div#panel").slideDown("slow");
					
					});	
					
					// Collapse Panel
					$("#close").click(function(){
						$("div#panel").slideUp("slow");	
					});		
					
					// Switch buttons from "Log In | Register" to "Close Panel" on click
					$("#toggle label").click(function () {
						$("#toggle label").toggle();
					});

});

