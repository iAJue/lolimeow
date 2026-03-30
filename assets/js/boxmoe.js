!(function ($) {
    "use strict";
    $(document).pjax("a[target!=_blank]", "#boxmoe_theme_global", {
        fragment: "#boxmoe_theme_global",
        timeout: 6000,
    });
    $(document).on("pjax:timeout", function () {
        preloader();
    });
    $(document).on("pjax:beforeReplace", function () {
        preloader();
    });
    $(document).on("pjax:start", function () {
        $('.preloader').show();
    });
    $(document).on("pjax:success", function (event, data, status, xhr) {
        var newTitle = $(data).filter("title").text();
        document.title = newTitle;
    });
    $(document).on("pjax:complete", function () {
        boxmoeload();
        theme.init();
        ajaxComt();
    });

    var preloader = function () {
        if ($(".preloader").length) {$(".preloader").delay(200).fadeOut("slow");}
    };

    $(document).ready(function () {
        if ($(".preloader").length) {$(".preloader").delay(750).fadeOut("slow");}
        boxmoeload();
        theme.init();

    });
    var boxmoeload = function () {
        $('a[href="#search"]').on("click", function (event) {
            $("#search").addClass("open");
            $('#search > form > input[type="search"]').focus();
            $(".offcanvas-start").removeClass("show");
            return false;
        });
        $("#search, #search button.close").on("click keyup", function (event) {
            if (
                event.target == this ||
                event.target.className == "close" ||
                event.keyCode == 27
            ) {
                $(this).removeClass("open");
                $(".offcanvas-start").addClass("show");
            }
        });

        $(window).scroll(function () {
            if ($(window).scrollTop() >= 250) {
                if ($("#lolijump").length) {
                    $("#lolijump").fadeIn();
                }
            } else {
                if ($("#lolijump").length) {
                    $("#lolijump").fadeOut();
                }
            }
        });

        $("#lolijump").click(function (event) {
            if ($("#lolijump").length) {
                $("html, body").animate(
                    {
                        scrollTop: 0,
                    },
                    100,
                );
            }
            return false;
        });
        // blog-post
        const colors1 = [
            "#83ea6c",
            "#1dd7c2",
            "#85b2f4",
            "#ffcf00",
            "#f4c8c6",
            "#e6f2e4",
            "#83ea6c",
            "#1dd7c2",
            "#85b2f4",
            "#0dcaf0",
            "#e8d8ff",
        ];

        const tagElements1 = $(".blog-post .tagfa");

        tagElements1.each(function (index) {
            if (index < colors1.length) {
                $(this).css("color", colors1[index]);
            }
        });
        // tag-cloud
        const colors2 = [
            "#83ea6c",
            "#1dd7c2",
            "#85b2f4",
            "#ffcf00",
            "#f4c8c6",
            "#e6f2e4",
            "#83ea6c",
            "#1dd7c2",
            "#85b2f4",
            "#0dcaf0",
            "#e8d8ff",
        ];

        const tagElements2 = $(".tag-cloud .tagfa");

        tagElements2.each(function (index) {
            if (index < colors2.length) {
                $(this).css("color", colors2[index]);
            }
        });

        var targetElement = document.getElementById("blog-sidebar");
        if (!targetElement) {
            var siblingElements = document.querySelectorAll(".d-lg-none");
            siblingElements.forEach(function(el) {
                el.style.display = "none";
            });
        }

        if ($(".prettyprint").length) {
            window.prettyPrint && prettyPrint();
        }
        var fancyboxOptions = {
            loop: true,
            arrows: true,
            animationEffect: "fade",
            transitionEffect: "slide",
            caption: function(instance, item) {
                return $(this).data('caption');
            },
            keyboard: true,
            toolbar: true,
            infobar: true,
            buttons: [
                'zoom',
                'slideShow',
                'fullScreen',
                'thumbs',
                'close'
            ],
            thumbs: {
                autoStart: true
            },
            slideShow: {
                autoStart: false,
                speed: 3000
            },
            fullScreen: {
                autoStart: false
            },
            hash: true
        };

        var initPostImageLightbox = function(){
            $(".single-content img").each(function(){
                var $img = $(this);
                if ($img.closest("a").length) {
                    return;
                }

                var realSrc =
                    $img.attr("data-src") ||
                    $img.attr("data-original") ||
                    $img.attr("data-lazy-src") ||
                    $img.attr("src");

                if (!realSrc || realSrc.indexOf("data:image") === 0) {
                    $img.removeClass("boxmoe-fancybox-image");
                    $img.removeAttr("data-boxmoe-fancybox-src");
                    return;
                }

                $img.addClass("boxmoe-fancybox-image");
                $img.attr("data-boxmoe-fancybox-src", realSrc);
                $img.css("cursor", "zoom-in");
            });

            $("body")
                .off("click.boxmoePostImageLightbox", ".single-content img.boxmoe-fancybox-image")
                .on("click.boxmoePostImageLightbox", ".single-content img.boxmoe-fancybox-image", function (event) {
                    event.preventDefault();

                    var $current = $(this);
                    var $images = $(".single-content img.boxmoe-fancybox-image");
                    var items = [];
                    var currentIndex = 0;

                    $images.each(function(index){
                        var $img = $(this);
                        var src = $img.attr("data-boxmoe-fancybox-src");
                        if (!src) {
                            return;
                        }

                        var caption = $img.attr("alt") || "";
                        items.push({
                            src: src,
                            opts: {
                                caption: caption
                            }
                        });

                        if (this === $current[0]) {
                            currentIndex = items.length - 1;
                        }
                    });

                    if (items.length && $.fancybox) {
                        $.fancybox.open(items, fancyboxOptions, currentIndex);
                    }
                });
        };
        initPostImageLightbox();

        var copycode = function(){
            for (var i = 0; i < $('#boxmoe_theme_container pre').length; i++) {
                $('#boxmoe_theme_container pre').eq(i).prepend('<div class="btn-copy"><span class="single-copy copy" data-clipboard-target="#copy'+ i +'" title="点击复制本段代码"><i class="fa fa-files-o"></i> 复制代码</span></div>');
                $('#boxmoe_theme_container pre> ol').eq(i).attr('id','copy'+ i);
            }
            var clipboard = new ClipboardJS('.copy');
            clipboard.on('success', function(e) {
                e.clearSelection();
                $(e.trigger).html('<span style="color:#32cd32"><i class="fa fa-check-square-o" aria-hidden="true"></i> 复制成功</span>');
                setTimeout(function(){$(e.trigger).html('<i class="fa fa-files-o"></i> 复制代码</span>');},3000);
            });
            clipboard.on('error', function(e) {
                console.error('Action:', e.action);
                console.error('Trigger:', e.trigger);
                alert("复制失败，请手动复制");
            });
        };copycode();


        $("[data-fancybox]").fancybox(fancyboxOptions);
        $('body').on('click','.dropdown-smilie a',
            function() {
                var ab = $(this).attr('href');// 抓取href内的值
                var abc = ab.split('\'');// 按符号/来分割字符串为几个数组
                var content = $('#comment').val(); // textarea区的id
                content += abc[1];
                $('#comment').val(content); // textarea区的id

            });
        var hide = document.getElementById('toggle-comment-author-info');
        if(!hide) {
        }else{
            $('#comment-author-info').hide();
        }
        $('body').on('click', '.comment-reply-link',
            function () {
                addComment.moveForm("comment-" + $(this).attr('data-commentid'), $(this).attr('data-commentid'), "respond", $(this).attr('data-postid'));
                return false;
            });

    };

    var navbar = document.querySelector(".navbar");
    const navOffCanvasBtn = document.querySelectorAll(".offcanvas-nav-btn"),
        navOffCanvas = document.querySelector(
            ".navbar:not(.navbar-clone) .offcanvas-nav",
        );
    let bsOffCanvas;

    function toggleOffCanvas() {
        bsOffCanvas && bsOffCanvas._isShown
            ? bsOffCanvas.hide()
            : bsOffCanvas && bsOffCanvas.show();
    }
    navOffCanvas &&
    ((bsOffCanvas = new bootstrap.Offcanvas(navOffCanvas, {
        scroll: !0,
    })),
        navOffCanvasBtn.forEach((e) => {
            e.addEventListener("click", (e) => {
                toggleOffCanvas();
            });
        }));
    

    var theme = {
        init: function () {
            theme.menu(),
                theme.popovers(),
                theme.tooltip(),
                theme.navbaronScroll();
        },

        menu: () => {
            document.querySelectorAll(".dropdown-menu a.dropdown-toggle").forEach((function(e) {
                e.addEventListener("click", (function(e) {
                    if (!this.nextElementSibling.classList.contains("show")) {
                        this.closest(".dropdown-menu").querySelectorAll(".show").forEach((function(e) {
                            e.classList.remove("show")
                        }))
                    }
                    this.nextElementSibling.classList.toggle("show");
                    const t = this.closest("li.nav-item.dropdown.show");
                    t && t.addEventListener("hidden.bs.dropdown", (function(e) {
                        document.querySelectorAll(".dropdown-submenu .show").forEach((function(e) {
                            e.classList.remove("show")
                        }))
                    })), e.stopPropagation()
                }))
            }))
        },

        popovers: () => {
            [...document.querySelectorAll('[data-bs-toggle="popover"]')].map(
                (e) => new bootstrap.Popover(e),
            );
        },
        tooltip: () => {
            [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(
                (e) => new bootstrap.Tooltip(e),
            );
        },
        navbaronScroll: () => {
            const $navbar = $(".navbar");
            const scrollThreshold = 100;
            if ($navbar.length) {
                function onScroll() {
                    if ($(window).scrollTop() > scrollThreshold) {
                        $navbar.addClass("scrolled");
                    } else {
                        $navbar.removeClass("scrolled");
                    }
                }
                $(window).on("scroll", onScroll);
                onScroll();
            }
        },
    };
})(window.jQuery);
function displayRunningTime(text) {
    const startDateStr = text.match(/{{(.*?)}}/)[1];
    const start = new Date(startDateStr);
    function updateRunningTime() {
        const now = new Date();
        const diff = now - start;
        const years = Math.floor(diff / (1000 * 60 * 60 * 24 * 365));
        const days = Math.floor((diff % (1000 * 60 * 60 * 24 * 365)) / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        const displayText = text.replace(
            /{{.*?}}/,
            `${years} 年 ${days} 天 ${hours} 小时 ${minutes} 分钟 ${seconds} 秒`
        );
        document.getElementById('runningTime').innerText = displayText;
    }
    setInterval(updateRunningTime, 1000);
}
function createSnowflake() {
    const snowflake = document.createElement('div');
    snowflake.className = 'snowflake';
    snowflake.innerHTML = ['✳', '✴', '✻', '❉', '❅', '❆', '✻', '✼', '❇', '❈', '❊', '✥', '✺'][Math.floor(Math.random() * 13)];
    snowflake.style.left = Math.random() * 100 + '%';
    const size = (Math.random() * 15) + 10;
    snowflake.style.fontSize = size + 'px';
    snowflake.style.opacity = Math.random();
    const duration = 10 + Math.random() * 30;
    snowflake.style.animationDuration = duration + 's';
    document.body.appendChild(snowflake);
    snowflake.addEventListener('animationend', () => {
        snowflake.remove();
    });
}
console.clear && console.clear();console.log("%c   %c少女祈祷~永无BUG", "padding:190px 200px 1500px;margin-left:100px;background:url('https://www.moekoe.cn/static/images/girlbg.png') no-repeat;background-size:100%;","color: pink; font-size: 20px");console.log("%c秋名山上行人稀，常有车手较高低。\n如今车道依旧在，不见当年老司机！"," color:#000;font-weight:bold;text-shadow: 0 1px 0 #ccc,0 2px 0 #c9c9c9,0 3px 0 #bbb,0 4px 0 #b9b9b9,0 5px 0 #aaa,0 6px 1px rgba(0,0,0,.1),0 0 5px rgba(0,0,0,.1),0 1px 3px rgba(0,0,0,.3),0 3px 5px rgba(0,0,0,.2),0 5px 10px rgba(0,0,0,.25),0 10px 10px rgba(0,0,0,.2),0 20px 20px rgba(0,0,0,.15);font-size:2.1em");console.log("%c%c站长%c阿珏", "line-height:28px;", "line-height:28px;padding:4px;background:#222;color:#fff;font-size:16px;margin-right:15px", "color:#3fa9f5;line-height:28px;font-size:16px;");console.log("%c%cBlog%chttps://MoeJue.cn", "line-height:28px;", "line-height:28px;padding:4px;background:#222;color:#fff;font-size:16px;margin-right:15px", "color:#ff9900;line-height:28px;font-size:16px;");console.log("%c%c社区Q群%c712473912", "line-height:28px;", "line-height:28px;padding:4px;background:#222;color:#fff;font-size:16px;margin-right:15px", "color:#008000;line-height:28px;font-size:16px;");console.log("%c%c滑稽警告%c再来偷源码&文章，劳资打死你!", "line-height:28px;", "line-height:28px;padding:4px;background:#222;color:#fff;font-size:16px;margin-right:15px", "color:#f00;font-weight:bold;line-height:28px;font-size:16px;");
