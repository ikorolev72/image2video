/*
  get_screenshot.js 
  version 1.3 20170821
  This file is a helper script for use with phantomJS (http://phantomjs.org/) and 
  allowing you to rasterize elements to pdf.
  Author - Korolev Igor 
  GitHub - https://github.com/ikorolev72
  
*/
//'use strict';
var system = require('system');
var args = system.args;




if ( args.length < 3 || args.length > 5 ) {
  console.log('Check arguments for cli!');
  console.log('Usage: phantomjs get_screenshot.js url output_file ');
  phantom.exit(2);
}


var page = require('webpage').create(),

	delay = 2000,
    ntop = 0,
    left = 0,
    width = 1920,
	height = 1080,
   viewpw = 1920,
	viewph = 1080,
    zoom =1,
	address = args[1],
	outfile = args[2], 
    server = address,
	data=''
	;

page.viewportSize = { width: viewpw, height: viewph };
page.zoomFactor = zoom;
page.settings.loadImages = true;
page.devicePixelRatio = zoom;
page.settings.userAgent = 'Mozilla/5.0 (Windows NT 5.1; rv:8.0) Gecko/20100101 Firefox/7.0';

page.onError = function (msg, trace) {
  console.log(msg);
  trace.forEach(function(item) {
    console.log('  ', item.file, ':', item.line);
  });
};



page.open(server, 'post', data, function (status) {
    if (status !== 'success') {
        console.log('Unable to load the address: '+ server+data);
        phantom.exit(1);
    } else {	
        window.setTimeout( function () {
            page.render( outfile, { format: 'png' });
			phantom.exit(0);
		}, delay ) ;		
	}
});

