(function($) {
    $.fn.EightycloudsFliptimer = function(params) {
        var glob = {
            element: this,
            params: params,
            interval: null,
            time: {
                now: null,
                start: null,
                end: null,
                days: null,
                hours: null,
                minutes: null,
                seconds: null
            }
        };
        this.init = function() {
            timer.build();
            timer.set.time()
        };
        var timer = {
            callback: glob.params.callback,
            addBlock: function(klass) {
                var html;
                html = '<div class="block ' + klass + '">';
                html += '<div class="timebox">';
                html += '<div class="time_top"><span class="txt"></span></div>';
                html += '<div class="time_bot"><span class="txt"></span></div>';
                html += '<div class="time_fy"></div>';
                html += '<div class="time_fy1"></div>';
                html += ' <div class="time_line"></div>';
                html += '</div>';
                html += '</div>';
                html += '<em class="symbol"></em>';
                return html
            },
            build: function() {
                var html;
                html = '<div class="EightycloudsFlipTimer">';
                html += this.addBlock("hours");
                html += this.addBlock("minutes");
                html += this.addBlock("seconds");
                html += '</div>';
                $(html).appendTo(glob.element)
            },
            set: {
                easing1: "easeInExpo",
                easing2: "easeOutExpo",
                delay: function() {
                    return 200
                },
                hours: function() {
                    var hours = ("0" + glob.time.hours).slice(-2);
                    var delay = timer.set.delay();
                    if ($(glob.element).find(".block.hours .time_top span").text() !== hours ){
                        $(glob.element).find(".block.hours .time_fy").animate({
                            top: 11,
                            height: 0,
                            left:2
                        }, {
                            easing: timer.set.easing1,
                            duration: delay / 2,
                            start: function() {
                                $(glob.element).find(".block.hours .time_fy").show();
                                $(glob.element).find(".block.hours .time_top span").text(hours)
                            },
                            complete: function() {
                                $(glob.element).find(".block.hours .time_fy").css({
                                    top: 1,
                                    height:8,
                                    left: 1
                                }).hide();
                                $(glob.element).find(".block.hours .time_fy1").animate({
                                    height: 8,
                                    left: 1
                                }, {
                                    easing: timer.set.easing2,
                                    duration: delay / 2,
                                    start: function() {
                                        $(glob.element).find(".block.hours .time_fy1").show()
                                    },
                                    complete: function() {
                                        $(glob.element).find(".block.hours .time_fy1").css({
                                            height: 0,
                                            left: 2
                                        }).hide();
                                        $(glob.element).find(".block.hours .time_bot span").text(hours)
                                    }
                                })
                            }
                        })
                    }
                },
                minutes: function() {
                    var minutes = ("0" + glob.time.minutes).slice(-2);
                    var delay = timer.set.delay();
                    if ($(glob.element).find(".block.minutes .time_top span").text() !== minutes) {
                        $(glob.element).find(".block.minutes .time_fy").animate({
                            top:11,
                            height: 0,
                            left:1
                        }, {
                            easing: timer.set.easing1,
                            duration: delay / 2,
                            start: function() {
                                $(glob.element).find(".block.minutes .time_fy").show();
                                $(glob.element).find(".block.minutes .time_top span").text(minutes)
                            },
                            complete: function() {
                                $(glob.element).find(".block.minutes .time_fy").css({
                                    top:2,
                                    height:8,
                                    left:1
                                }).hide();
                                $(glob.element).find(".block.minutes .time_fy1").animate({
                                    height:8,
                                    left: 1
                                }, {
                                    easing: timer.set.easing2,
                                    duration: delay / 2,
                                    start: function() {
                                        $(glob.element).find(".block.minutes .time_fy1").show()
                                    },
                                    complete: function() {
                                        $(glob.element).find(".block.minutes .time_fy1").css({
                                            height: 0,
                                            left: 2
                                        }).hide();
                                        $(glob.element).find(".block.minutes .time_bot span").text(minutes)
                                    }
                                })
                            }
                        })
                    }
                },
                seconds: function() {
                    var seconds = ("0" + glob.time.seconds).slice(-2);
                    var delay = timer.set.delay();
                    if ($(glob.element).find(".block.seconds .time_top span").text() !== seconds) {
                        $(glob.element).find(".block.seconds .time_fy").animate({
                            top:11,
                            height: 0,
                            left:1
                        }, {
                            easing: timer.set.easing1,
                            duration: delay / 2,
                            start: function() {
                                $(glob.element).find(".block.seconds .time_fy").show();
                                $(glob.element).find(".block.seconds .time_top span").text(seconds)
                            },
                            complete: function() {
                                $(glob.element).find(".block.seconds .time_fy").css({
                                    top:1,
                                    height: 8,
                                    left:1
                                }).hide();
                                $(glob.element).find(".block.seconds .time_fy1").animate({
                                    height: 8,
                                    left:2
                                }, {
                                    easing: timer.set.easing2,
                                    duration: delay / 2,
                                    start: function() {
                                        $(glob.element).find(".block.seconds .time_fy1").show()
                                    },
                                    complete: function() {
                                        $(glob.element).find(".block.seconds .time_fy1").css({
                                            height: 0,
                                            left: 2
                                        }).hide();
                                        $(glob.element).find(".block.seconds .time_bot span").text(seconds)
                                    }
                                })
                            }
                        })
                    }
                },
                time: function() {
                    glob.time.now = Math.floor(new Date()) / 1000;
                    glob.time.end = Math.floor(new Date(glob.params.enddate)) / 1000;
                    if (glob.time.now >= glob.time.end) {
                        clearInterval(glob.interval);
                        if (typeof timer.callback === "function") {
                            timer.callback()
                        }
                        return
                    }
                    glob.time.hours = Math.floor((glob.time.end - glob.time.now) % 86400 / 3600);
                    glob.time.minutes = Math.floor((glob.time.end - glob.time.now) % 86400 % 3600 / 60);
                    glob.time.seconds = Math.floor((glob.time.end - glob.time.now) % 86400 % 3600 % 60);
                    timer.set.hours();
                    timer.set.minutes();
                    timer.set.seconds();
                    glob.interval = setInterval(function() {
                        glob.time.now = Math.floor(new Date()) / 1000;
                        glob.time.end = Math.floor(new Date(glob.params.enddate)) / 1000;
                        if (glob.time.now >= glob.time.end) {
                            clearInterval(glob.interval);
                            if (typeof timer.callback === "function") {
                                timer.callback()
                            }
                            return
                        }
                        glob.time.hours = Math.floor((glob.time.end - glob.time.now) % 86400 / 3600);
                        glob.time.minutes = Math.floor((glob.time.end - glob.time.now) % 86400 % 3600 / 60);
                        glob.time.seconds = Math.floor((glob.time.end - glob.time.now) % 86400 % 3600 % 60);
                        timer.set.hours();
                        timer.set.minutes();
                        timer.set.seconds()
                    }, 1000)
                }
            }
        };
        this.init()
    }
})(jQuery);