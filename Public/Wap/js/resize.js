//;
//(function(win) {
//  var doc = win.document;
//  var docEl = doc.documentElement;
//  var tid;
//
//  function refreshRem() {
//      var width = docEl.getBoundingClientRect().width;
//      if (width > 540) { // 最大宽度
//          width = 540;
//      }
//      var rem = width / 6.4; 
//      docEl.style.fontSize = rem + 'px';
//  }
//
//  win.addEventListener('resize', function() {
//      clearTimeout(tid);
//      tid = setTimeout(refreshRem, 300);
//  }, false);
//  win.addEventListener('pageshow', function(e) {
//      if (e.persisted) {
//          clearTimeout(tid);
//          tid = setTimeout(refreshRem, 300);
//      }
//  }, false);
//
//  refreshRem();
//
//})(window);

//(function(doc, win) {
//  var docEl = doc.documentElement,
//      resizeEvt = 'onorientationchange' in window ? 'onorientationchange' : 'resize',
//      recalc = function() {
//          var clientWidth = docEl.clientWidth;
//          if (!clientWidth) return;
//          if (clientWidth >= 750) {
//              docEl.style.fontSize = '100px';
//          } else {
//              docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
//          }
//      };
//
//  if (!doc.addEventListener) return;
//  win.addEventListener(resizeEvt, recalc, false);
//  doc.addEventListener('DOMContentLoaded', recalc, false);
//})(document, window);

