//// JavaScript Document
//Common = {};
//Common.context = {};
//Common.controllers = {};
//
//var MD5KEY = '0c09bc02-c74e-11e2-a9da-00163e1210d9',dataURL,base_url,wechat_url,weixin_url;
//
//if(location.href.indexOf("h5.edaijia")<0){
//  dataURL = "http://open.d.api.edaijia.cn/";
//  base_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxeb016ce390d092ad&redirect_uri=http%3a%2f%2fh5.d.edaijia.cn%2fweixin%2findex.html&response_type=code&scope=snsapi_base&state=null#wechat_redirect";
//}else{
//  dataURL = "http://open.api.edaijia.cn/";
//  base_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx8c8df4a3218410e0&redirect_uri=http%3a%2f%2fh5.edaijia.cn%2fweixin%2findex.html&response_type=code&scope=snsapi_base&state=null#wechat_redirect";
//}
//Common.config = {
//  appkey: '51000031',
//  from:'01050050',
//  ver: 3
//};
////区域
//Common.region="全国";
////位置信息
//Common.address= "";
////设备
//Common.device = "";
////版本号
//Common.version = "3.2";
////地图类型
//Common.gpstype = "baidu";
////手机号
//Common.phone = "";
//
//if(!!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)){
//  Common.device="ios";
//}else{
//  Common.device="android";
//}
//Common.verify={
//  tel: /^[1][34578]\d{9}$/,
//  code:/^[A-Za-z0-9]+$/,
//  number:/^[0-9]*$/,
//  card:/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/,
//  text:/^[A-Za-z0-9\u4e00-\u9fa5]+$/,
//  name:/[\u4e00-\u9fa5]+/
//};
//Common.distribute = function (action) {
//  var params=arguments[1]||"";
//  setTimeout(function () {
//      if (window.currentAction) {
//          window.currentAction.destroy();
//          window.currentAction = null;
//          Common.timer && Common.timer.stop();
//      }
//      try {
//          window.currentAction = new Common.controllers[action]($("#mainBox"),params);
//      } catch (e) {
//          console.log(e.message);
//      }
//  }, 10);
//};
//Common.changeAction = function (action) {
//  location.hash=action;
//};
//function hashAction(){
//  var t=location.hash.substring(1);
//  if(t==""){
//      Common.distribute("cMyDrivers");
//  }else{
//      Common.distribute(t);
//  }
//}
//window.onpopstate = function() {
//  hashAction();
//};
//Common.increseId = function () {
//  var count = 0;
//
//  function increse() {
//      return ++count;
//  };
//  return increse;
//};
//Common.getId = Common.increseId();
//
//function isArray(obj) {
//  return Object.prototype.toString.call(obj) === '[object Array]';
//};
//
//Common.getTimestamp = function () {
//  return (new Date()).valueOf() + "";
//};
//
//var CU = Common.CU = {
//  isSucceed: function (data) {
//      if (data.code != 0) {
//          if(data.driverList&&data.driverList.length==0){
//              Common.distribute("cNoServiceError");
//              return
//          }
//
//          alert(data.message);
//
//      }
//      return data.code == 0;
//  },
//  dateFormat: function (date, format) {
//      format = format || 'yyyy-MM-dd hh:mm:ss';
//      var o = {
//          "M+": date.getMonth() + 1,
//          "d+": date.getDate(),
//          "h+": date.getHours(),
//          "m+": date.getMinutes(),
//          "s+": date.getSeconds(),
//          "q+": Math.floor((date.getMonth() + 3) / 3),
//          "S": date.getMilliseconds()
//      };
//      if (/(y+)/.test(format)) {
//          format = format.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
//      }
//      for (var k in o) {
//          if (new RegExp("(" + k + ")").test(format)) {
//              format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
//          }
//      }
//      return format;
//  },
//  getSig: function (param) {
//      var paramStr = [], paramStrSorted = [];
//      for (var n in param) {
//          paramStr.push(n);
//      }
//      paramStr = paramStr.sort();
//      $(paramStr).each(function (index) {
//          paramStrSorted.push(this + param[this]);
//      });
//      var text = paramStrSorted.join('') + MD5KEY;
//      return $.md5(text);
//  }
//};
//
//Common.stringify = function (data) {
//  var value = "";
//  for (prop in data) {
//      value += prop + "=" + data[prop] + "&";
//  }
//  return value.substr(0, value.length - 1);
//};
//
//Common.postRequest = function (model) {
//  var URL=dataURL+model.method;
//  delete model.method;
//  var model = model.params ? model.params : model;
//
//  var req = $.extend(true, {}, Common.config);
//  req.timestamp = CU.dateFormat(new Date());
//  req = $.extend(true, req, model);
//  req.sig = CU.getSig(req);
//
//  return $.ajax({
//      url: URL,
//      type: 'POST',
//      data: Common.stringify(req),
//      dataType: 'json',
//      timeout: 5000,
//      statusCode: {500: function() {
//          alert('500 服务器错误');
//      }},
//      statusCode: {404: function() {
//          alert('404 服务器无法找到被请求的页面');
//      }},
//      error: function (x, h, r) {
//          Common.errorLogger(URL, req);
//          Common.distribute("cNetworkError");
//          Common.timer && Common.timer.stop();
//      },
//      success: function (data) {
//
//      }
//  });
//};
//
//Common.getRequest = function (model) {
//  var URL=dataURL+model.method;
//  var method=model.method;
//
//  var models = model.params ? model.params : model;
//  //console.log("models",models);
//  var req = $.extend(true, {}, Common.config);
//
//  var method_list=["order/polling","driver/position","gps/location"];
//  req.timestamp = CU.dateFormat(new Date());
//  req = $.extend(true, req, models);
//  req.sig = CU.getSig(req);
//  return $.ajax({
//      url: URL,
//      type: 'GET',
//      data: Common.stringify(req),
//      crossDomain: true,
//      dataType: 'json',
//      timeout: 5000,
//      statusCode: {500: function() {
//          Common.alert('500 服务器错误');
//      }},
//      statusCode: {404: function() {
//          alert('404 服务器无法找到被请求的页面');
//      }},
//      error: function (x, h, r) {
//          Common.errorLogger(URL, req);
//
//          if(jQuery.inArray(method, method_list)<0){
//              Common.distribute("cNetworkError");
//              Common.timer && Common.timer.stop();
//          }
//      },
//      success: function (data) {
//
//      }
//  });
//};
//
//Common.errorLogger = function(queryUrl, params, res) {
//  var baseUrl = '/app/error_logger.html';
//  var data = {
//      q: queryUrl,
//      p: params ? JSON.stringify(params) : "",
//      r: res ? JSON.stringify(res) : "",
//      t: new Date().getTime()
//  };
//  $.get(baseUrl + "?" + $.param(data));
//
//};
//
//Common.dataHandle = function (data, idx, redirectAction) {
//  //hideDisablePage
//  var idx = idx || 0;
//  if (data) {
//      var _r = data;
//      if (_r.code == "0") {
//          return _r;
//      } else {
//
//          if(_r.code == 1){
//              delete window.localStorage.edaijia_token;
//
//              Common.distribute("cLocation");
//              return;
//          }
//          alert(_r.message);
//          /*switch (_r.message) {
//              case "验证失败":
//                  Common.distribute("cLogin");
//                  break;
//          }*/
//      }
//  }
//  return null;
//};
//
//Common.isDataLength = function (data) {
//  if (data) {
//      var len = data.response[0].result.data.length;
//      return len;
//  }
//  return null;
//};
//
//Common.showMsg = function () {
//
//};
//
//Common.switchL = function (el) {
//  this.local = this.local || new Local();
//  lan = lan || "C";
//  this.local.switchLInContainer(lan, el);
//};
//Interface = {};
//Interface.tabArr = [];
//Interface.email = "ok";
//
//
//(function () {
//  $.fn.html2 = function (path, obj, cbk) {
//      var _t = this;
//      $.when(_t.html(can.view(path, obj))).then(function () {
//          cbk && cbk();
//      });
//      return this;
//  };
//
//  $.fn.append2 = function (path, obj, cbk) {
//      var _t = this;
//      $.when(_t.append(can.view(path, obj))).then(function () {
//          cbk && cbk();
//      });
//      return this;
//  };
//})();
//
////计时器
//Common.TimeCounter = function (params) {
//  this.getParams(params);
//  this.times = this.second;
//};
//Common.TimeCounter.prototype.start = function () {
//  var _t = this;
//  _t.beforestart && _t.beforestart();
//  _t.timer = window.setInterval(function () {
//      if (_t.times && _t.times > 1) {
//          _t.times--;
//          _t.callback && _t.callback();
//      } else {
//          _t.end && _t.end();
//          window.clearInterval(_t.timer);
//      }
//  }, 1000);
//};
//Common.TimeCounter.prototype.getParams = function (params) {
//  this.second = params.second || 0;
//  this.beforestart = params.beforestart || null;
//  this.end = params.end || null;
//  this.callback = params.callback || null;
//};
//Common.TimeCounter.prototype.stop = function () {
//  //this.end && this.end();
//  window.clearInterval(this.timer);
//};
////截取经度
//Common.CutString = function (str, n) {
//  var arr = str.toString().split(".");
//  var tt = arr[0] + "." + arr[1].slice(0, n);
//  return tt
//}
////获取URL参数
//Common.UrlGet=function() {
//  var args = {};
//  var query = location.search.substring(1);
//  var pairs = query.split("&");
//  for (var i = 0; i < pairs.length; i++) {
//      var pos = pairs[i].indexOf('=');
//      if (pos == -1) continue;
//      var argname = pairs[i].substring(0, pos);
//      var value = pairs[i].substring(pos + 1);
//      value = decodeURIComponent(value);
//      args[argname] = value;
//  }
//  return args;
//}
////特殊字符过滤正则
//Common.strip=function(s){
//  var pattern = new RegExp("[%--`'!@#$^&*()=|{}:;,\\[\\]<>/?'！@#￥……&*（）——|{}【】‘；：”“ 。，、？]");
//  var rs = "";
//  for (var i = 0; i < s.length; i++) {
//      rs = rs+s.substr(i, 1).replace(pattern);
//  }
//  return rs;
//}
//Common.is_weixin=function(){
//  var ua = navigator.userAgent.toLowerCase();
//  if(ua.match(/MicroMessenger/i)=="micromessenger") {
//      return true;
//  } else {
//      return false;
//  }
//}
//
//Common.alert=function(msg) {
//  $('.alert-box').show().delay(2000).fadeOut(100).find("div").html(msg);
//}
//Common.confirm=function(msg) {
//  $('.alert-box').show().find("div").prepend(msg);
//}