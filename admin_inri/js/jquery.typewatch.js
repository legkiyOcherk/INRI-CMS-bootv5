/*
*	TypeWatch 2.0 - Original by Denny Ferrassoli / Refactored by Charles Christolini
*
*	Examples/Docs: www.dennydotnet.com
*
*  Copyright(c) 2007 Denny Ferrassoli - DennyDotNet.com
*  Coprright(c) 2008 Charles Christolini - BinaryPie.com
*
*  Dual licensed under the MIT and GPL licenses:
*  http://www.opensource.org/licenses/mit-license.php
*  http://www.gnu.org/licenses/gpl.html
*
* Ti пофиксил пару багов:
* 1. с captureLength 0 при пустом поле
* 2. возращает this
* 3. callback вызывается от элемента DOMElement в котором сработало событие
*
*/

(function(jQuery) {
	jQuery.fn.typeWatch = function(o){

		// Options
		var options = jQuery.extend({
			wait : 750,
			callback : function() { },
			highlight : true,
			captureLength : 2
		}, o);

		function checkElement(timer) {
			var elTxt = jQuery(timer.el).val();

			if (elTxt.length >= options.captureLength && elTxt.toUpperCase() != timer.text) {
				timer.text = elTxt.toUpperCase();
				timer.cb.call(timer.el, elTxt);
			}
		};

		function watchElement(elem) {
			// Must be text or textarea
			if (elem.type.toUpperCase() == "TEXT" || elem.nodeName.toUpperCase() == "TEXTAREA") {

				// Allocate timer element
				var timer = {
					timer : null,
					text : jQuery(elem).val().toUpperCase(),
					cb : options.callback,
					el : elem,
					wait : options.wait
				};

				// Set focus action (highlight)
				if (options.highlight) {
					jQuery(elem).focus(
					function() {
						this.select();
					});
				}

				var active = false

				// Key watcher / clear and reset the timer
				var startWatch = function(e) {
					var timerWait = timer.wait;
					
					if (e.keyCode == 13 && active) return checkElement(timer)

					var timerCallbackFx = function() {
						if (active) checkElement(timer)
					}

					// Clear timer
					clearTimeout(timer.timer);
					timer.timer = setTimeout(timerCallbackFx, timerWait);
				};

				jQuery(elem).keydown(startWatch).blur(function(){
					active = false
					checkElement(timer)
				}).focus(function(){active = true}).keypress(function(e) { return e.keyCode != 13 })
			}
		};

		// Watch Each Element
		return this.each(function(index){
			watchElement(this);
		});
		return this
	};

})(jQuery)