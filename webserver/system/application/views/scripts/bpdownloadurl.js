// BpDownloadUrl 0.13 Copyright 2006 BitPerfect http://www.gmaptools.com - All rights reserved.
function BpDownloadUrl(a,b,c,d,f){var g=arguments.length<=4?true:!f;var h=BpDownloadUrl.create();if(BpBrowser.type!=BpBrowser.MSIE)h.i=false;if(typeof(c)=='string'){var j=c;c=function(){alert(j)};}else if(typeof(c)!='function'){c=function(){alert('There has been a server error.')};}if(typeof(d)=='object'){d=BpDownloadUrl.serialize(d);}if(typeof(d)=='string'){h.open('POST',a,g);h.setRequestHeader('Content-Type','application/x-www-form-urlencoded;');}else{h.open('GET',a,g);d=null;}if(g){h.onreadystatechange=function(){if(h.readyState==4){var k=h.status==200||h.status==304;if(BpBrowser.type!=BpBrowser.MSIE)k=k&&!h.i;if(k){b(h.responseText,h);}else if(BpBrowser.type!=BpBrowser.MSIE||h.status){c(h);}h.onreadystatechange=function(){};}};}else{h.onreadystatechange=function(){};}h.send(d);if(BpBrowser.type!=BpBrowser.MSIE){var l=h.abort;h.abort=function(){h.i=true;l.apply(h);};}return h;}BpDownloadUrl.serialize=function(d){var m=new Array();for(var n in d){if(typeof d[n]=='object'){for(var i=0;i<d[n].length;i++)m.push(encodeURIComponent(n)+'[]='+encodeURIComponent(d[n][i]));}else{m.push(encodeURIComponent(n)+'='+encodeURIComponent(d[n]));}}return m.join('&');};BpDownloadUrl.create=function(){try{if(typeof ActiveXObject!="undefined"){return new ActiveXObject("Microsoft.XMLHTTP");}else if(window.XMLHttpRequest){return new XMLHttpRequest;}}catch(e){}return null;};