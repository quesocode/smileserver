(function($) {
  var timer;
  $.fn.src = function(url, onLoad, options) {

    var iframe = $(this);
    iframe.unbind("load");
    if (timer) clearTimeout(timer);

    var defaults = {
      timeout: 0,
      onTimeout: null,
      onReady: null,
      scriptRequest: false
    }
    var opts = $.extend(defaults, options);

    if (opts.scriptRequest) {
      scriptRequest(iframe, url, onLoad, options);
      return;
    }

    opts.frameactive = true;
    var startTime = (new Date()).getTime();
    if (opts.timeout) {
      var timer = setTimeout(function() {
        opts.frameactive=false; 
        iframe.load(null);
        if (opts.onTimeout) opts.onTimeout(iframe.get(0), opts.timeout);
      }, opts.timeout);
    }
    if (opts.onReady) { 
      iframe.unbind("ready");
      iframe.ready(function() {
        if (opts.frameactive) {
          var duration=(new Date()).getTime()-startTime;
          opts.onReady(this, duration);
        }
      });
    }

    var onloadHandler = function() {
      var duration=(new Date()).getTime()-startTime;
      if (timer) clearTimeout(timer);
      if (onLoad && opts.frameactive) onLoad(this, duration);
      opts.frameactive=false;
    }
    iframe.attr("src", url);
    iframe.get()[0].onload = onloadHandler;
    opts.completeReadyStateChanges=0;
    iframe.get()[0].onreadystatechange = function() { // IE ftw
	    if (++(opts.completeReadyStateChanges)==3) onloadHandler();
    }

    function scriptRequest(iframe, url, onLoad, opts) {
      content = "<h3>Hello</h3>";
      var iframeEl = $(iframe).get()[0];
      var doc = iframeEl.contentDocument || iframeEl.contentWindow.document;
      doc.open();
      doc.writeln(content);
      doc.close();
    }

  }
})(jQuery);