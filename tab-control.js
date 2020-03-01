var tabFocus = true;
var windowFocus = false;


var vis = (function(){
    var stateKey, 
        eventKey, 
        keys = {
                hidden: "visibilitychange",
                webkitHidden: "webkitvisibilitychange",
                mozHidden: "mozvisibilitychange",
                msHidden: "msvisibilitychange"
    };
    for (stateKey in keys) {
        if (stateKey in document) {
            eventKey = keys[stateKey];
            break;
        }
    }
    return function(c) {
        if (c) document.addEventListener(eventKey, c);
        return !document[stateKey];
    }
})();


/////////////////////////////////////////
// check if current tab is active or not


vis(function(){
                    
    if(vis()){  
        
        // the setTimeout() is used due to a delay 
        // before the tab gains focus again, very important!
          setTimeout(function(){ 
			tabFocus = true;
          
        },10);     
                                                
    } else {
			tabFocus = false;
    }
});


/////////////////////////////////////////
// check if browser window has focus        
var notIE = (document.documentMode === undefined),
    isChromium = window.chrome;
      
if (notIE && !isChromium) {

    // checks for Firefox and other  NON IE Chrome versions
    $(window).on("focusin", function () { 
        
        setTimeout(function(){      
			tabFocus = true;
        },10);

    }).on("focusout", function () {

			tabFocus = false;

    });

} else {
    
    // checks for IE and Chromium versions
    if (window.addEventListener) {

        // bind focus event
        window.addEventListener("focus", function (event) {
          
            setTimeout(function(){                 
			tabFocus = true;
              
            },10);

        }, false);

        // bind blur event
        window.addEventListener("blur", function (event) {
			tabFocus = false;

        }, false);

    } 
	else 
	{

        // bind focus event
        window.attachEvent("focus", function (event) {

            setTimeout(function(){                 
				tabFocus = true;
            },10);

        });

        // bind focus event
        window.attachEvent("blur", function (event) {
			tabFocus = false;

        });
    }
}

