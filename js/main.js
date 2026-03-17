
(function ($) {
    "use strict";

    function setupImageLoading() {
        var viewportBottom = window.innerHeight * 1.2;
        var images = document.querySelectorAll('img');

        for (var i = 0; i < images.length; i++) {
            var img = images[i];

            if (!img.getAttribute('decoding')) {
                img.setAttribute('decoding', 'async');
            }

            if (img.getAttribute('loading')) {
                continue;
            }

            var rect = img.getBoundingClientRect();
            var isNearViewport = rect.top < viewportBottom;
            img.setAttribute('loading', isNearViewport ? 'eager' : 'lazy');

            if (!isNearViewport && !img.getAttribute('fetchpriority')) {
                img.setAttribute('fetchpriority', 'low');
            }
        }
    }

    function toNumber(value) {
        var num = parseFloat(String(value).replace(/[^\d.]/g, ''));
        return Number.isFinite(num) ? num : 0;
    }

    function formatMoney(value) {
        return '\u0e3f' + value.toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function slugify(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '-');
    }

    function getItemKey(item, index) {
        if (item && item.id) {
            return String(item.id);
        }
        return slugify((item && item.name) || '') || ('item-' + index);
    }

    function loadSharedCart() {
        try {
            var raw = localStorage.getItem('myCart');
            var parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (err) {
            return [];
        }
    }

    function renderSharedMiniCart() {
        var cart = loadSharedCart();
        var total = 0;
        var qty = 0;
        var itemsHtml = '';

        for (var i = 0; i < cart.length; i++) {
            var item = cart[i] || {};
            var itemQty = Math.max(0, parseInt(item.qty, 10) || 0);
            var itemPrice = Math.max(0, toNumber(item.price));
            var itemId = getItemKey(item, i);
            qty += itemQty;
            total += (itemQty * itemPrice);
        }

        if (cart.length === 0) {
            itemsHtml = '<li class="header-cart-item flex-w flex-t m-b-12"><div class="header-cart-item-txt p-t-8"><span class="header-cart-item-name m-b-18">ไม่มีสินค้าในตะกร้า</span></div></li>';
        } else {
            itemsHtml = cart.map(function (item, index) {
                var img = String(item.img || 'images/product-min-01.jpg').trim();
                var name = String(item.name || 'สินค้า').trim();
                var itemQty = Math.max(1, parseInt(item.qty, 10) || 1);
                var itemPrice = Math.max(0, toNumber(item.price));
                var itemId = getItemKey(item, index);

                return [
                    '<li class="header-cart-item flex-w flex-t m-b-12" data-id="' + itemId + '" data-index="' + index + '">',
                    '  <div class="header-cart-item-img" data-id="' + itemId + '" data-index="' + index + '"><img src="' + img + '" alt="IMG"></div>',
                    '  <div class="header-cart-item-txt p-t-8">',
                    '    <a href="shoping-cart.html" class="header-cart-item-name m-b-18 hov-cl1 trans-04">' + name + '</a>',
                    '    <span class="header-cart-item-info">' + itemQty + ' x ' + formatMoney(itemPrice) + '</span>',
                    '  </div>',
                    '</li>'
                ].join('');
            }).join('');
        }

        if (window.__cartMiniHandler) {
            return;
        }

        $('.header-cart-wrapitem').html(itemsHtml);
        $('.header-cart-total').text('รวมทั้งหมด: ' + formatMoney(total));
        $('.icon-header-noti.js-show-cart').attr('data-notify', qty);
    }

    function removeCartMenuFromHeader() {
        $('.main-menu a[href="shoping-cart.html"], .main-menu-m a[href="shoping-cart.html"]').each(function () {
            $(this).closest('li').remove();
        });
    }

    function watchHeaderMenuChanges() {
        if (!window.MutationObserver) {
            return;
        }

        var observer = new MutationObserver(function () {
            removeCartMenuFromHeader();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /*[ Load page ]
    ===========================================================*/
    var allowAnimsition = document.body && document.body.dataset && document.body.dataset.enableAnimsition === 'true';

    if (allowAnimsition && $.fn.animsition) {
        $(".animsition").animsition({
            inClass: 'fade-in',
            outClass: 'fade-out',
            inDuration: 300,
            outDuration: 200,
            linkElement: '.animsition-link',
            loading: false,
            loadingParentElement: 'html',
            loadingClass: 'animsition-loading-1',
            loadingInner: '<div class="loader05"></div>',
            timeout: false,
            timeoutCountdown: 5000,
            onLoadEvent: true,
            browser: [ 'animation-duration', '-webkit-animation-duration'],
            overlay : false,
            overlayClass : 'animsition-overlay-slide',
            overlayParentElement : 'html',
            transition: function(url){ window.location.href = url; }
        });
    }
    
    /*[ Back to top ]
    ===========================================================*/
    var windowH = $(window).height()/2;
    var ticking = false;

    $(window).on('scroll',function(){
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(function(){
            if ($(window).scrollTop() > windowH) {
                $("#myBtn").css('display','flex');
            } else {
                $("#myBtn").css('display','none');
            }
            ticking = false;
        });
    });

    $('#myBtn').on("click", function(){
        $('html, body').animate({scrollTop: 0}, 300);
    });


    /*==================================================================
    [ Fixed Header ]*/
    var headerDesktop = $('.container-menu-desktop');
    var wrapMenu = $('.wrap-menu-desktop');

    if($('.top-bar').length > 0) {
        var posWrapHeader = $('.top-bar').height();
    }
    else {
        var posWrapHeader = 0;
    }
    

    if($(window).scrollTop() > posWrapHeader) {
        $(headerDesktop).addClass('fix-menu-desktop');
        $(wrapMenu).css('top',0); 
    }  
    else {
        $(headerDesktop).removeClass('fix-menu-desktop');
        $(wrapMenu).css('top',posWrapHeader - $(this).scrollTop()); 
    }

    $(window).on('scroll',function(){
        if($(this).scrollTop() > posWrapHeader) {
            $(headerDesktop).addClass('fix-menu-desktop');
            $(wrapMenu).css('top',0); 
        }  
        else {
            $(headerDesktop).removeClass('fix-menu-desktop');
            $(wrapMenu).css('top',posWrapHeader - $(this).scrollTop()); 
        } 
    });


    /*==================================================================
    [ Menu mobile ]*/
    $('.btn-show-menu-mobile').on('click', function(){
        $(this).toggleClass('is-active');
        $('.menu-mobile').slideToggle();
    });

    var arrowMainMenu = $('.arrow-main-menu-m');

    for(var i=0; i<arrowMainMenu.length; i++){
        $(arrowMainMenu[i]).on('click', function(){
            $(this).parent().find('.sub-menu-m').slideToggle();
            $(this).toggleClass('turn-arrow-main-menu-m');
        })
    }

    $(window).resize(function(){
        if($(window).width() >= 992){
            if($('.menu-mobile').css('display') == 'block') {
                $('.menu-mobile').css('display','none');
                $('.btn-show-menu-mobile').toggleClass('is-active');
            }

            $('.sub-menu-m').each(function(){
                if($(this).css('display') == 'block') { console.log('hello');
                    $(this).css('display','none');
                    $(arrowMainMenu).removeClass('turn-arrow-main-menu-m');
                }
            });
                
        }
    });


    /*==================================================================
    [ Show / hide modal search ]*/
    $('.js-show-modal-search').on('click', function(){
        $('.modal-search-header').addClass('show-modal-search');
        $(this).css('opacity','0');
    });

    $('.js-hide-modal-search').on('click', function(){
        $('.modal-search-header').removeClass('show-modal-search');
        $('.js-show-modal-search').css('opacity','1');
    });

    $('.container-search-header').on('click', function(e){
        e.stopPropagation();
    });


    /*==================================================================
    [ Isotope ]*/
    var $topeContainer = $('.isotope-grid');
    var $filter = $('.filter-tope-group');

    // filter items on button click
    $filter.each(function () {
        $filter.on('click', 'button', function () {
            var filterValue = $(this).attr('data-filter');
            $topeContainer.isotope({filter: filterValue});
        });
        
    });

    // init Isotope
    $(window).on('load', function () {
        var $grid = $topeContainer.each(function () {
            $(this).isotope({
                itemSelector: '.isotope-item',
                layoutMode: 'fitRows',
                percentPosition: true,
                animationEngine : 'best-available',
                masonry: {
                    columnWidth: '.isotope-item'
                }
            });
        });
    });

    var isotopeButton = $('.filter-tope-group button');

    $(isotopeButton).each(function(){
        $(this).on('click', function(){
            for(var i=0; i<isotopeButton.length; i++) {
                $(isotopeButton[i]).removeClass('how-active1');
            }

            $(this).addClass('how-active1');
        });
    });

    /*==================================================================
    [ Filter / Search product ]*/
    $('.js-show-filter').on('click',function(){
        $(this).toggleClass('show-filter');
        $('.panel-filter').slideToggle(400);

        if($('.js-show-search').hasClass('show-search')) {
            $('.js-show-search').removeClass('show-search');
            $('.panel-search').slideUp(400);
        }    
    });

    $('.js-show-search').on('click',function(){
        $(this).toggleClass('show-search');
        $('.panel-search').slideToggle(400);

        if($('.js-show-filter').hasClass('show-filter')) {
            $('.js-show-filter').removeClass('show-filter');
            $('.panel-filter').slideUp(400);
        }    
    });




    /*==================================================================
    [ Cart ]*/
    $('.js-show-cart').on('click',function(){
        $('.js-panel-cart').addClass('show-header-cart');
    });

    $('.js-hide-cart').on('click',function(){
        $('.js-panel-cart').removeClass('show-header-cart');
    });

    $(document).on('click', '.header-cart-item-img', function (e) {
        if (window.__cartMiniHandler) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        var indexAttr = $(this).attr('data-index');
        var index = parseInt(indexAttr, 10);
        if (Number.isNaN(index)) {
            index = parseInt($(this).closest('.header-cart-item').attr('data-index'), 10);
        }

        var cart = loadSharedCart();
        if (!Number.isNaN(index) && index >= 0 && index < cart.length) {
            cart.splice(index, 1);
        } else {
            var id = $(this).attr('data-id') || $(this).closest('.header-cart-item').attr('data-id');
            if (id) {
                cart = cart.filter(function (item) {
                    return String(item.id || '') !== String(id);
                });
            }
        }

        try {
            localStorage.setItem('myCart', JSON.stringify(cart));
        } catch (err) {}

        renderSharedMiniCart();
    });

    $(document).on('click', '.header-cart-buttons a, .header-cart-wrapitem a', function (e) {
        e.preventDefault();
        window.location.href = 'shoping-cart.html';
    });

    /*==================================================================
    [ Cart ]*/
    $('.js-show-sidebar').on('click',function(){
        $('.js-sidebar').addClass('show-sidebar');
    });

    $('.js-hide-sidebar').on('click',function(){
        $('.js-sidebar').removeClass('show-sidebar');
    });

    /*==================================================================
    [ +/- num product ]*/
    $('.btn-num-product-down').on('click', function(){
        var numProduct = Number($(this).next().val());
        if(numProduct > 0) $(this).next().val(numProduct - 1);
    });

    $('.btn-num-product-up').on('click', function(){
        var numProduct = Number($(this).prev().val());
        $(this).prev().val(numProduct + 1);
    });

    /*==================================================================
    [ Rating ]*/
    $('.wrap-rating').each(function(){
        var item = $(this).find('.item-rating');
        var rated = -1;
        var input = $(this).find('input');
        $(input).val(0);

        $(item).on('mouseenter', function(){
            var index = item.index(this);
            var i = 0;
            for(i=0; i<=index; i++) {
                $(item[i]).removeClass('zmdi-star-outline');
                $(item[i]).addClass('zmdi-star');
            }

            for(var j=i; j<item.length; j++) {
                $(item[j]).addClass('zmdi-star-outline');
                $(item[j]).removeClass('zmdi-star');
            }
        });

        $(item).on('click', function(){
            var index = item.index(this);
            rated = index;
            $(input).val(index+1);
        });

        $(this).on('mouseleave', function(){
            var i = 0;
            for(i=0; i<=rated; i++) {
                $(item[i]).removeClass('zmdi-star-outline');
                $(item[i]).addClass('zmdi-star');
            }

            for(var j=i; j<item.length; j++) {
                $(item[j]).addClass('zmdi-star-outline');
                $(item[j]).removeClass('zmdi-star');
            }
        });
    });
    
    /*==================================================================
    [ Show modal1 ]*/
    $(document).off('click', '.js-show-modal1').on('click', '.js-show-modal1', function(e){
        e.preventDefault();

        var $card = $(this).closest('.block2');
        var imgSrc = $card.find('.block2-pic img').attr('src');
        var name = $.trim($card.find('.js-name-b2').text());
        var price = $.trim($card.find('.stext-105').first().text());
        var $modal = $('.js-modal1');
        var $slick = $modal.find('.slick3');

        if (imgSrc) {
            $modal.find('.item-slick3').attr('data-thumb', imgSrc);
            $modal.find('.slick3 .slick-slide img').attr({
                src: imgSrc,
                alt: name || 'IMG-PRODUCT'
            });
            $modal.find('.slick3 .slick-slide a').attr('href', imgSrc);
            $modal.find('.slick3-dots img').attr('src', imgSrc);

            if ($slick.hasClass('slick-initialized')) {
                $slick.slick('slickGoTo', 0, true);
                $slick.slick('setPosition');
            }
        }

        if (name) {
            $modal.find('.js-name-detail').text(name);
        }

        if (price) {
            $modal.find('.mtext-106').text(price);
        }

        $modal.addClass('show-modal1');
    });

    $('.js-hide-modal1').on('click',function(){
        $('.js-modal1').removeClass('show-modal1');
    });

    setupImageLoading();
    removeCartMenuFromHeader();
    watchHeaderMenuChanges();
    renderSharedMiniCart();

    $(window).on('storage', function (event) {
        if (!event.originalEvent || event.originalEvent.key === 'myCart') {
            renderSharedMiniCart();
        }
    });


})(jQuery);
