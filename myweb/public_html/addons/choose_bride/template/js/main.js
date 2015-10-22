var adaptUILayout = (function(){
    
    //根据校正appVersion或userAgent校正屏幕分辨率宽度值
    var regulateScreen = (function(){
        var cache = {};
        
        //默认尺寸
        var defSize = {
            width  : window.screen.width,
            height : window.screen.height
        };
        
        var ver = window.navigator.appVersion;
        
        var _ = null;
        
        var check = function(key){
            return key.constructor == String ? ver.indexOf(key) > -1 : ver.test(key);
        };
        
        var add = function(name, key, size){
            if(name && key)
                cache[name] = {
                    key : key,
                    size : size
                };
        };
        
        var del = function(name){
            if(cache[name])
                delete cache[name];
        };
        
        var cal = function(){
            if(_ != null)
                return _;
                
            for(var name in cache){
                if(check(cache[name].key)){
                    _ = cache[name].size;
                    break;
                }
            }
            
            if(_ == null)
                _ = defSize;
            
            return _;
        };
        
        return {
            add : add,
            del : del,
            cal : cal
        };
    })();
    

    //实现缩放
    var adapt = function(uiWidth){
        var 
        deviceWidth,
        devicePixelRatio,
        targetDensitydpi,
        //meta,
        initialContent,
        head,
        viewport,
        ua;

        ua = navigator.userAgent.toLowerCase();
        //whether it is the iPhone or iPad
        isiOS = ua.indexOf('ipad') > -1 || ua.indexOf('iphone') > -1;
    
        //获取设备信息,并矫正参数值
        devicePixelRatio = window.devicePixelRatio;
        deviceWidth      = regulateScreen.cal().width; 
        
        //获取最终dpi
        targetDensitydpi = uiWidth / deviceWidth * devicePixelRatio * 160;

        //use viewport width attribute on the iPhone or iPad device
        //use viewport target-densitydpi attribute on the Android device
        initialContent   = isiOS 
            ? 'target-densitydpi=device-dpi, width=' + uiWidth + 'px, user-scalable=no'
            : 'target-densitydpi=' + targetDensitydpi + ', width=device-width, user-scalable=no';

        //add a new meta node of viewport in head node
        head = document.getElementsByTagName('head');
        viewport = document.createElement('meta');
        viewport.name = 'viewport';
        viewport.content = initialContent;
        head.length > 0 && head[head.length - 1].appendChild(viewport);                
    };
    
    return {
        regulateScreen : regulateScreen,
        adapt : adapt
    };
})();


window.onload=function(){
    $('.share').click(function(){
        $('#mcover').show()
        return false
    })
    $('#mcover').click(function(){
        $(this).hide()
    })
}
var YT = {
    alert: {}
}
YT.alert.Wechat = function(t){
    var t = t || '已收藏'
    $('#alertWechat').remove()
    clearTimeout(YT.alert.Time)
    var alertWechat = '<div id="alertWechat" style="position:fixed;bottom:100px;left:10px;right:10px;text-align:center"><span style="background-color:rgba(0,0,0,.8);padding:10px;min-width:100px;color:#fff;box-shadow:0 0 10px rgba(0,0,0,.6);display:inline-block;">'+t+'</span></div>'
    $('body').append(alertWechat)
    YT.alert.Time = setTimeout(function(){
        $('#alertWechat').remove()
    }, 3000)
}

//收藏
function favorite(id, type, domain)
{
    var url = '/'+domain+'/my/favoriteSet.html?id='+id+'&type='+type;
    $.get(url,'',function(data){
        YT.alert.Wechat('收藏成功')
    });
}

