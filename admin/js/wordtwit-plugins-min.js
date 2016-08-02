//Cookie
jQuery.cookie = function(name, value, options) {
if (typeof value != 'undefined') {
options = options || {};
if (value === null) {
value = '';
options.expires = -1;
}
var expires = '';
if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
var date;
if (typeof options.expires == 'number') {
date = new Date();
date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
} else {
date = options.expires;
}
expires = '; expires=' + date.toUTCString();
}
var path = options.path ? '; path=' + (options.path) : '';
var domain = options.domain ? '; domain=' + (options.domain) : '';
var secure = options.secure ? '; secure' : '';
document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
} else {
var cookieValue = null;
if (document.cookie && document.cookie != '') {
var cookies = document.cookie.split(';');
for (var i = 0; i < cookies.length; i++) {
var cookie = jQuery.trim(cookies[i]);
if (cookie.substring(0, name.length + 1) == (name + '=')) {
cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
break;
}
}
}
return cookieValue;
}
};
//Box Height, Shake
(function(jQuery) {
jQuery.fn.equalHeights = function(minHeight, maxHeight) {
tallest = (minHeight) ? minHeight : 0;
this.each(function() {
if(jQuery(this).height() > tallest) {
tallest = jQuery(this).height();
}
});
if((maxHeight) && tallest > maxHeight) tallest = maxHeight;
return this.each(function() {
jQuery(this).height(tallest).css("overflow","hidden");
});
}
})(jQuery);
jQuery.fn.shake = function(intShakes , intDistance , intDuration ) {
this.each(function() {
jQuery(this).css({position:'relative'});
for (var x=1; x<=intShakes; x++) {
jQuery(this).animate({left:(intDistance*-1)}, (((intDuration/intShakes)/4)))
.animate({left:intDistance}, ((intDuration/intShakes)/2))
.animate({left:0}, (((intDuration/intShakes)/4)));
}
});
return this;
};