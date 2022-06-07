// create my jsCookies function
var jsCookies = {

	// this gets a cookie and returns the cookies value, if no cookies it returns blank ""
	get: function(c_name) {
		if (document.cookie.length > 0) {
			var c_start = document.cookie.indexOf(c_name + "=");
			if (c_start != -1) {
				c_start = c_start + c_name.length + 1;
				var c_end = document.cookie.indexOf(";", c_start);
				if (c_end == -1) {
					c_end = document.cookie.length;
				}
				return unescape(document.cookie.substring(c_start, c_end));
			}
		}
		return "";
	},

	// this sets a cookie with your given ("cookie name", "cookie value", "good for x days")
	set: function(c_name, value, expiredays) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + expiredays);
		document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : "; expires=" + exdate.toUTCString()) + "; path=/";
	},

	// this checks to see if a cookie exists, then returns true or false
	check: function(c_name) {
		c_name = jsCookies.get(c_name);
		if (c_name != null && c_name != "") {
			return true;
		} else {
			return false;
		}
	}

};
// end my jsCookies function

// USAGE - get    ::   jsCookies.get("cookie_name_here");  [returns the value of the cookie]
// USAGE - set    ::   jsCookies.set("cookie_name", "cookie_value", 5 );  [give name, val and # of days til expiration]
// USAGE - check  ::   jsCookies.check("cookie_name_here");  [returns only true or false if the cookie exists or not]