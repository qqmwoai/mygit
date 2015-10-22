var browser = {
    versions: function() {
        var u = navigator.userAgent,
            app = navigator.appVersion;
        return {
            android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
            iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
            iPad: u.indexOf('iPad') > -1, //是否iPad
            weixin: u.indexOf('MicroMessenger') > -1,
            QQ: u.indexOf('QQ') > -1 && u.indexOf('QQBrowser') == -1//qq应用
        };
    }(),
    language: (navigator.browserLanguage || navigator.language).toLowerCase()
}
var img_url="http://s.juancdn.com/jpwebapp/images/hundred_times/share_logo.jpg";
if(IS_BUY){
    var desc = "抢购后输入我的邀请码"+invite_code+"，中奖机会立即翻倍！抽完即送最高百倍现金！";
    var link="http://wx.juanpi.com/event/baibei?id="+invite_code;
}else{
    var desc = "原价"+YUANJIA+"元"+SHANGPING+"1分钱开抢，抽完即送最高百倍现金！";
    var link="http://wx.juanpi.com/event/baibei";
}
var title= "1分钱抽奖，不中也返100倍，直发现金红包";

var rule_h;
var win_w = $(window).width();
//获取cookie
function getCookie(name) {
    var prefix = name + '=';
    var start = document.cookie.indexOf(prefix);
    if (start == -1) {
        return null;
    }
    var end = document.cookie.indexOf(';', start + prefix.length);
    if (end == -1) {
        end = document.cookie.length;
    }
    var value = document.cookie.substring(start + prefix.length, end);
    return unescape(value);
}

function full_screen(){
    var win_h = $(window).height();
    var wrap_h2 = $('#wrap').height();
    if(wrap_h2<=win_h){
        $('#wrap').css('min-height',win_h);
        $('#bottom_img3').addClass('bottom_img');
        $('#bottom_img3').css('padding-top',0);
    }
}

function alert_auto(){
    wrap_h = $('#wrap').height();
    $('#alert_area,#shadow').css('height', wrap_h);
    $('#alert_area').show();
    $('#rule_box').show();
    $('#alert_area').hide();
    $('#rule_box').hide();
}
 
$(function(){


   
    full_screen();
    

    if(win_w>640){
        rule_h =128; 
    }else{
        rule_h = win_w*0.2;
    }
    $('.rule').css('height',rule_h);

    var down_btn_h = $('.go_down_app').height();
    if(down_btn_h){
        $('#wrap').css('padding-bottom',down_btn_h);
    }

    /*选项卡效果*/
    $('nav a').click(function(){
       var tab = $(this).attr('data-name');
       $(this).siblings().find("span").removeClass('selected'); 
       $(this).find("span").addClass('selected');
       $('#main_box .tab').eq(tab).show().siblings().hide();

    });

    /*nav置顶*/
    var offset = $('nav').offset(); 
    $(window).scroll(function(){
        var offset_w = $(document).scrollTop(); 
        if(offset_w >= offset.top){
            $('nav').addClass('fdt');
            $('#wrap').css('margin-top',"46px");
        }else{
            $('nav').removeClass('fdt');
            $('#wrap').css('margin-top',0);
        }
    });

    /*点击展开收缩*/
    $('#more').click(function(){
        if($(this).find('.arrow').hasClass('arrow_down')){
            $('#letter_con').slideDown('normal');
            $('.arrow').removeClass('arrow_down');
            $('.arrow').addClass('arrow_up');
        }else{
            $('#letter_con').slideUp('slow');
            $('.arrow').removeClass('arrow_up');
            $('.arrow').addClass('arrow_down'); 
        }
    });

    /*点击弹出规则框*/
    $('#rule').click(function(){
        alert_auto();
        $('#alert_area').show();
        $('#rule_box').show();
    });



    /*已中奖弹窗*/
    $('.geted_prize_btn').click(function(){
        var html='';
        var code='';
        var reword='';
        var duijiang='';
        var date = $(this).parent().prev().find('.day_key').html();
        $(".duijiang").data('url',"http://wx.juanpi.com/event/address/"+date);
        $.ajax({
            type:"post",
            url:'http://wx.juanpi.com/event/ajax_baibei/5',
            dataType:'json',
            data:{'date':date},
            success:function (rs) {
                    for(var p in rs){
                        if(!isNaN(p)){
                         html += '<li class="clear">'+'<i class="people_head fl">'+'<img src="http://s1.juancdn.com/'+rs[p].avatar+'">'+'</i>'+'<i class="fl people_name">'+rs[p].name+'</i>'+'<i class="fr people_code">'+rs[p].code+'</i></li>'
                    }
                    }
                      if(rs.getwinner.taken==0){
                        duijiang = '<div class="act_btn get_prize_btn" id="duijiang">'+'立即兑奖'+'</div>';
                    }else{
                        duijiang = '<div class="act_btn get_prize_btn fail">'+'已兑奖'+'</div>';
                    }
                    for(var k in rs.code){
                        if($.inArray(rs.code[k], rs.getwinner.code)>=0){
                            reword +='<span class="get_prize">'+'中奖'+'</span>';
                            code += '<span class="get_prize">'+rs.code[k]+'</span>';
                        }else{
                            reword += '<span>'+'未中奖'+'</span>';
                            code += '<span>'+rs.code[k]+'</span>';
                        }
                    }
                    $('.duijiang').html(duijiang);
                    $('#duijiang').on('click',function(){
                        _paq.push(['trackEvent','event_click','click_event_btnid', 'btnid_utm', 10000013]);
                        var url = $(".duijiang").data('url');
                        window.location.href=url;
                    });
                    $('.my_code').html(code);
                    $('.my_code.my_code_getprize').html(reword);
                    $('.prize_people_list').html(html);
                    $('#alert_area').show();
                    $('#get_prize_box').show();
            },
            error:function(){
                alert('页面错误，请重新打开页面');
            }
        });
        _paq.push(['trackEvent','event_click','click_event_btnid', 'btnid_utm', 10000007]);
    });
    /*未中奖弹窗*/
    $('.unget_prize_btn').click(function(){
        var date = $(this).parent().prev().find('.day_key').html();
        var html='';
        var code='';
        var reword='';
        $.ajax({
            type:"post",
            url:'http://wx.juanpi.com/event/ajax_baibei/5',
            dataType:'json',
            data:{'date':date},
            success:function (rs) {
                for(var p in rs){
                    if(!isNaN(p)){
                    html += '<li class="clear">'+'<i class="people_head fl">'+'<img src="http://s1.juancdn.com/'+rs[p].avatar+'">'+'</i>'+'<i class="fl people_name">'+rs[p].name+'</i>'+'<i class="fr people_code">'+rs[p].code+'</i></li>';
                    }
                    }
                for(var k in rs.code){
                    code += '<span>'+rs.code[k]+'</span>';
                }
                for(var k in rs.code){
                    reword += '<span>'+'未中奖'+'</span>';
                }
                if(rs.code==null){
                    var back_money= '<p class="red">'+'您未参与抽奖'+'</p>';
                    $('.mycode').hide();
                    $('.line').hide();
                }else{
                    var back_money= '<p class="red">'+'卷皮返你'+(rs.back_money/0.01)+'倍现金，收好不谢!'+'</p>';
                }

                $('.tips1').html(back_money);
                $('.my_code').html(code);
                $('.my_code.my_code_getprize').html(reword);
                $('.prize_people_list').html(html);
                $('#alert_area').show();
                $('#unget_prize_box').show();
            },
            error:function(){
                alert('页面错误，请重新打开页面');
            }
        });
        _paq.push(['trackEvent','event_click','click_event_btnid', 'btnid_utm', 10000007]);
    });
    /*点击关闭弹窗*/
    $('.exit').click(function(){
        $(this).parent().hide();
        $('#alert_area').hide();
    });
    $('#shadow').click(function(){
        $(this).siblings().hide();
        $(this).parent().hide();
    })
});
wx.ready(function () {
    wx.onMenuShareAppMessage({//分享给朋友
        title:title, // 分享标题
        desc: desc, // 分享描述
        link: link, // 分享链接
        imgUrl: img_url, // 分享图标
        type: '', // 分享类型,music、video或link，不填默认为link
        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
        success: function () {
            _paq.push(['trackEvent','event_wx_msg','share_event_wx_msg', 'msg_utm', 10000015]);// 用户确认分享后执行的回调函数
        },
        cancel: function () {
            _paq.push(['trackEvent','event_wx_msg','share_event_wx_msg', 'msg_utm', 10000014]);// 用户取消分享后执行的回调函数
        }
    });
    wx.onMenuShareTimeline({//分享到朋友圈
        title:title, // 分享标题
        link: link, // 分享链接
        imgUrl: img_url, // 分享图标
        success: function () {
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
});

