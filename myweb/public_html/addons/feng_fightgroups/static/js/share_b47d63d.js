function getWeChatJsSdkSignature(e,t,r,n){var a=$.ajax({url:common.getApi("wechat/js_sdk_sign?noncestr="+e+"&timestamp="+t+"&url="+encodeURIComponent(r)+"&app_id="+n),xhrFields:{withCredentials:!0},timeout:3e3,async:!1}).responseText,i=JSON.parse(a);return i.signature}function getCookie(e){return document.cookie.length>0&&(c_start=document.cookie.indexOf(e+"="),-1!=c_start)?(c_start=c_start+e.length+1,c_end=document.cookie.indexOf(";",c_start),-1==c_end&&(c_end=document.cookie.length),unescape(document.cookie.substring(c_start,c_end))):""}function GetRequest(){var e=location.search,t=new Object;if(-1!=e.indexOf("?")){var r=e.substr(1);strs=r.split("&");for(var n=0;n<strs.length;n++)t[strs[n].split("=")[0]]=unescape(strs[n].split("=")[1])}return t}function getShareIdUrl(e){var t=getCookie("UserSN"),r=e;return location.pathname.indexOf(".htm")<=0&&(r+="index.html"),r=r.indexOf("?")<=0?r+"?share_id="+t:r.indexOf("share_id=")<=0?r+"&share_id="+t:changeURLArg(r,"share_id",t)}function changeURLArg(url,arg,arg_val){var pattern=arg+"=([^&]*)",replaceText=arg+"="+arg_val;if(url.match(pattern)){var tmp="/("+arg+"=)([^&]*)/gi";return tmp=url.replace(eval(tmp),replaceText)}return url.match("[?]")?url+"&"+replaceText:url+"?"+replaceText}function wechat_share_config(){var e=MasterConfig.C("appId"),t="734618974",r=Date.now(),n=getWeChatJsSdkSignature(t,r,document.URL,e);wx.config({debug:!1,appId:e,timestamp:r,nonceStr:t,signature:n,jsApiList:["onMenuShareTimeline","onMenuShareAppMessage"]}),wx.error(function(){})}function wechat_share_override(e,t,r){wechat_share_config();var n=getShareIdUrl(r);wx.ready(function(){wx.onMenuShareTimeline({title:e.title,link:n,imgUrl:e.imgUrl,success:function(){},cancel:function(){}}),wx.onMenuShareAppMessage({title:t.title,desc:t.desc,link:n,imgUrl:t.imgUrl,type:"link",success:function(){},cancel:function(){}})})}var share={override:function(e,t){wechat_share_override(e,t,document.URL)},default_send:function(){var e={title:Config.C("share_text").default.title,imgUrl:MasterConfig.C("baseMobileUrl")+"assets/images/logo.jpg"},t={title:MasterConfig.C("shop_name")+"商城",desc:Config.C("share_text").default.desc,imgUrl:MasterConfig.C("baseMobileUrl")+"assets/images/logo.jpg"};wechat_share_override(e,t,MasterConfig.C("baseMobileUrl")+"index.html")}};