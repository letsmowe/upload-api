
/* JSONP tests */

function jsonp(url, callback) {

	var callbackName = 'jsonp_callback_foo' + Math.round(100000 * Math.random());

	window[callbackName] = function(data) {

		delete window[callbackName];
		document.body.removeChild(script);
		callback(data);

	};

	var script = document.createElement('script');
	script.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'callback=' + callbackName;
	document.body.appendChild(script);

}

//var url = 'http://localhost/upload-api/api/';

//jsonp(url, function (data) {
//
//	console.log(data);
//
//});