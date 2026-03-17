(function ($) {
    'use strict';

    if (!$) {
        function toNumber(value) {
            var num = parseFloat(String(value).replace(/[^\d.]/g, ''));
            return Number.isFinite(num) ? num : 0;
        }

        function slugify(value) {
            return String(value || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '-');
        }

        function loadCart() {
            try {
                var raw = localStorage.getItem('myCart');
                var parsed = raw ? JSON.parse(raw) : [];
                return Array.isArray(parsed) ? parsed : [];
            } catch (err) {
                return [];
            }
        }

        function saveCart(cart) {
            localStorage.setItem('myCart', JSON.stringify(cart));
        }

        function addToCartVanilla(item) {
            var cart = loadCart();
            var id = item.id || slugify(item.name) || ('item-' + Date.now());
            var found = false;
            cart = cart.map(function (row) {
                if (String(row.id) === String(id)) {
                    row.qty = (row.qty || 0) + 1;
                    found = true;
                }
                return row;
            });
            if (!found) {
                cart.push({
                    id: id,
                    name: item.name || 'Product',
                    price: toNumber(item.price),
                    qty: 1,
                    img: item.img || 'images/product-min-01.jpg'
                });
            }
            saveCart(cart);
            alert((item.name || 'สินค้า') + ' เพิ่มลงตะกร้าแล้ว');
        }

        window.addToCartFromBtn = function (btn) {
            var name = btn.getAttribute('data-name');
            var price = btn.getAttribute('data-price');
            var img = btn.getAttribute('data-img');
            if (!name) return;
            addToCartVanilla({ id: slugify(name), name: name, price: price, img: img });
        };

        document.addEventListener('click', function (e) {
            var target = e.target.closest ? e.target.closest('.btn-addcart-b2') : null;
            if (!target) return;
            e.preventDefault();
            e.stopPropagation();
            window.addToCartFromBtn(target);
        });

        return;
    }

    if (typeof window !== 'undefined') {
        window.__cartMiniHandler = true;
    }

    var CART_KEY = 'myCart';

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

    function loadCart() {
        try {
            var raw = localStorage.getItem(CART_KEY);
            var parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (err) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    }

    function getTotalQty(cart) {
        return cart.reduce(function (sum, item) {
            return sum + (item.qty || 0);
        }, 0);
    }

    function getSubtotal(cart) {
        return cart.reduce(function (sum, item) {
            return sum + ((item.price || 0) * (item.qty || 0));
        }, 0);
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function slugify(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '-');
    }

    function getItemKey(item) {
        if (item.id) {
            return String(item.id);
        }
        return slugify(item.name) || ('item-' + Date.now());
    }

    function normalizeItem(item) {
        var normalized = {
            id: getItemKey(item),
            name: String(item.name || 'Product').trim(),
            price: Math.max(0, toNumber(item.price)),
            qty: Math.max(1, parseInt(item.qty, 10) || 1),
            img: String(item.img || 'images/product-min-01.jpg').trim()
        };

        if (!normalized.img) {
            normalized.img = 'images/product-min-01.jpg';
        }

        return normalized;
    }

    function renderMiniCart(cart) {
        var itemsHtml = '';
        var total = getSubtotal(cart);
        var qty = getTotalQty(cart);

        if (cart.length === 0) {
            itemsHtml = '<li class="header-cart-item flex-w flex-t m-b-12"><div class="header-cart-item-txt p-t-8"><span class="header-cart-item-name m-b-18">No items in cart</span></div></li>';
        } else {
            itemsHtml = cart.map(function (item, index) {
                return [
                    '<li class="header-cart-item flex-w flex-t m-b-12" data-id="' + escapeHtml(item.id) + '" data-index="' + index + '">',
                    '  <div class="header-cart-item-img" data-id="' + escapeHtml(item.id) + '" data-index="' + index + '">',
                    '      <img src="' + escapeHtml(item.img) + '" alt="IMG">',
                    '  </div>',
                    '  <div class="header-cart-item-txt p-t-8">',
                    '      <a href="shoping-cart.html" class="header-cart-item-name m-b-18 hov-cl1 trans-04">' + escapeHtml(item.name) + '</a>',
                    '      <span class="header-cart-item-info">' + item.qty + ' x ' + formatMoney(item.price) + '</span>',
                    '  </div>',
                    '</li>'
                ].join('');
            }).join('');
        }

        $('.header-cart-wrapitem').each(function () {
            $(this).html(itemsHtml);
        });

        $('.header-cart-total').text('Total: ' + formatMoney(total));
        $('.icon-header-noti.js-show-cart').attr('data-notify', qty);
    }

    function renderCartPage(cart) {
        var $table = $('.table-shopping-cart');
        if ($table.length === 0) {
            return;
        }

        $table.find('.table_row').remove();

        if (cart.length === 0) {
            $table.append('<tr class="table_row"><td class="column-1" colspan="5" style="padding: 30px; text-align: center;">No items in cart</td></tr>');
            updateSummary(cart);
            return;
        }

        cart.forEach(function (item) {
            var rowHtml = [
                '<tr class="table_row" data-id="' + escapeHtml(item.id) + '">',
                '    <td class="column-1">',
                '        <div class="how-itemcart1 btn-remove-item">',
                '            <img src="' + escapeHtml(item.img) + '" alt="IMG">',
                '        </div>',
                '    </td>',
                '    <td class="column-2">' + escapeHtml(item.name) + '</td>',
                '    <td class="column-3">' + formatMoney(item.price) + '</td>',
                '    <td class="column-4">',
                '        <div class="wrap-num-product flex-w m-l-auto m-r-0">',
                '            <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m"><i class="fs-16 zmdi zmdi-minus"></i></div>',
                '            <input class="mtext-104 cl3 txt-center num-product" type="number" min="1" value="' + item.qty + '">',
                '            <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m"><i class="fs-16 zmdi zmdi-plus"></i></div>',
                '        </div>',
                '    </td>',
                '    <td class="column-5">' + formatMoney(item.price * item.qty) + '</td>',
                '</tr>'
            ].join('');

            $table.append(rowHtml);
        });

        updateSummary(cart);
    }

    function updateSummary(cart) {
        var subtotal = getSubtotal(cart);
        var formatted = formatMoney(subtotal);

        $('[id="subtotal-display"]').text(formatted);
        $('#total-display').text(formatted);

        renderMiniCart(cart);
    }

    function setItemQty(itemId, newQty) {
        var cart = loadCart();
        var qty = Math.max(1, parseInt(newQty, 10) || 1);

        var nextCart = cart.map(function (item) {
            if (String(item.id) === String(itemId)) {
                item.qty = qty;
            }
            return item;
        });

        saveCart(nextCart);
        renderCartPage(nextCart);
    }

    function removeItem(itemId) {
        var cart = loadCart();
        var nextCart = cart.filter(function (item) {
            return String(item.id) !== String(itemId);
        });

        saveCart(nextCart);
        renderCartPage(nextCart);
    }

    function addToCart(newItem, qty) {
        var normalized = normalizeItem(newItem);
        normalized.qty = Math.max(1, parseInt(qty, 10) || 1);

        // Global de-dupe: prevent multiple adds within a short window for the same item
        var guard = window.__cartAddById || (window.__cartAddById = {});
        var now = Date.now();
        var last = guard[normalized.id] || 0;
        if (now - last < 400) {
            return normalized;
        }
        guard[normalized.id] = now;

        var cart = loadCart();
        var found = false;

        var nextCart = cart.map(function (item) {
            if (String(item.id) === String(normalized.id)) {
                item.qty = (item.qty || 0) + normalized.qty;
                found = true;
            }
            return item;
        });

        if (!found) {
            nextCart.push(normalized);
        }

        saveCart(nextCart);
        renderMiniCart(nextCart);
        return normalized;
    }

    function addToCartFromData(item, qty, options) {
        var now = Date.now();
        if (window.__cartAddLockTime && (now - window.__cartAddLockTime) < 250) {
            return item;
        }
        window.__cartAddLockTime = now;

        if (!item.id) {
            item.id = slugify(item.name) || ('item-' + now);
        }

        var added = addToCart(item, qty || 1);
        var opts = options || {};

        if (opts.openCartPanel && $('.js-panel-cart').length) {
            $('.js-panel-cart').addClass('show-header-cart');
        }

        if (opts.notify !== false) {
            var name = added && added.name ? added.name : 'Product';
            showToast(name + ' เพิ่มลงตะกร้าแล้ว');
        }

        return added;
    }

    function ensureToastStyles() {
        if (document.getElementById('cart-toast-styles')) return;
        var style = document.createElement('style');
        style.id = 'cart-toast-styles';
        style.textContent =
            '.cart-toast{position:fixed;right:20px;bottom:20px;z-index:9999;background:#222;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.2);font-size:14px;opacity:0;transform:translateY(10px);transition:all .2s ease}' +
            '.cart-toast.show{opacity:1;transform:translateY(0)}';
        document.head.appendChild(style);
    }

    function showToast(message) {
        ensureToastStyles();
        var toast = document.createElement('div');
        toast.className = 'cart-toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        requestAnimationFrame(function () {
            toast.classList.add('show');
        });
        setTimeout(function () {
            toast.classList.remove('show');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 200);
        }, 1600);
    }

    function setupAddToCartButtons() {
        $(document).off('click.cartDetail', '.js-addcart-detail').on('click.cartDetail', '.js-addcart-detail', function (e) {
            e.preventDefault();

            var $root = $(this).closest('.p-r-50, .p-r-50.p-t-5, .col-md-6, .col-lg-5');
            var name = $.trim($root.find('.js-name-detail').first().text()) || 'Product';
            var priceText = $root.find('.mtext-106').first().text();
            var price = toNumber(priceText);
            var qty = parseInt($root.find('input.num-product').first().val(), 10) || 1;
            var img = $root.closest('.row').find('.slick3 .item-slick3 img').first().attr('src') || 'images/product-min-01.jpg';

            addToCartFromData({
                id: slugify(name),
                name: name,
                price: price,
                img: img
            }, qty, { notify: true, openCartPanel: true });
        });
    }

    function setupCartPageEvents() {
        if ($('.table-shopping-cart').length === 0) {
            return;
        }

        // Remove any pre-bound handlers from template scripts to avoid double increments
        $('.table-shopping-cart .btn-num-product-up, .table-shopping-cart .btn-num-product-down').off('click');

        $(document).on('click', '.table-shopping-cart .btn-num-product-up', function () {
            if (typeof event !== 'undefined' && event.stopImmediatePropagation) {
                event.stopImmediatePropagation();
            }
            var $row = $(this).closest('.table_row');
            var id = $row.data('id');
            var $input = $row.find('.num-product');
            var current = parseInt($input.val(), 10) || 1;
            setItemQty(id, current + 1);
        });

        $(document).on('click', '.table-shopping-cart .btn-num-product-down', function () {
            if (typeof event !== 'undefined' && event.stopImmediatePropagation) {
                event.stopImmediatePropagation();
            }
            var $row = $(this).closest('.table_row');
            var id = $row.data('id');
            var $input = $row.find('.num-product');
            var current = parseInt($input.val(), 10) || 1;
            setItemQty(id, Math.max(1, current - 1));
        });

        $(document).on('change', '.table-shopping-cart .num-product', function () {
            var $row = $(this).closest('.table_row');
            var id = $row.data('id');
            setItemQty(id, $(this).val());
        });

        $(document).on('click', '.table-shopping-cart .btn-remove-item', function () {
            var $row = $(this).closest('.table_row');
            var id = $row.data('id');
            removeItem(id);
        });

        $('button.flex-c-m.size-116').off('click').on('click', function (e) {
            e.preventDefault();
            var cart = loadCart();

            if (cart.length === 0) {
                if (typeof swal === 'function') {
                    swal('Cart is empty', 'Please add items before checkout', 'warning');
                } else {
                    alert('Cart is empty');
                }
                return;
            }

            if (typeof swal === 'function') {
                swal('Checkout', 'Ready to process ' + cart.length + ' item(s)', 'success');
            } else {
                alert('Ready to checkout ' + cart.length + ' item(s)');
            }
        });
    }

    function setupMiniCartEvents() {
        $(document).on('click', '.header-cart-item-img', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var id = $(this).attr('data-id') || $(this).closest('.header-cart-item').attr('data-id');
            var indexAttr = $(this).attr('data-index');
            var index = parseInt(indexAttr, 10);

            if (id) {
                removeItem(id);
                return;
            }

            if (!Number.isNaN(index)) {
                var cart = loadCart();
                if (index >= 0 && index < cart.length) {
                    cart.splice(index, 1);
                    saveCart(cart);
                    renderMiniCart(cart);
                    renderCartPage(cart);
                }
            }
        });
    }

    function addToCartFromBtn(btn) {
        if (!btn) return;
        var $btn = $(btn);
        var name = $btn.attr('data-name') || $btn.data('name');
        var price = toNumber($btn.attr('data-price') || $btn.data('price'));
        var img = $btn.attr('data-img') || $btn.data('img');

        if (!name) {
            console.error('No product name found');
            alert('Error: Product name not found');
            return;
        }

        var now = Date.now();
        var lastClick = parseInt(btn.getAttribute('data-cart-lock') || '0', 10);
        if (now - lastClick < 500) {
            return;
        }
        btn.setAttribute('data-cart-lock', String(now));

        var guard = window.__cartAddGuard || (window.__cartAddGuard = { time: 0, id: '' });
        var itemId = $btn.attr('data-id') || $btn.data('id') || slugify(name) || ('item-' + now);
        if (guard.id === itemId && (now - guard.time) < 350) {
            return;
        }
        guard.id = itemId;
        guard.time = now;

        addToCartFromData({id: itemId, name: name, price: price, img: img}, 1, {
            openCartPanel: true,
            notify: true
        });
    }

    function setupAddToCartFromBtn() {
        if (window.__cartBtnCaptureBound) {
            return;
        }
        window.__cartBtnCaptureBound = true;
        document.addEventListener('click', function (e) {
            var btn = e.target.closest ? e.target.closest('.btn-addcart-b2') : null;
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            }
            addToCartFromBtn(btn);
        }, true);
    }

    window.addToCartFromBtn = addToCartFromBtn;
    window.addToCartFromData = addToCartFromData;

    function getFirstProductFromPage() {
        var $card = $('.block2').first();
        if ($card.length) {
            var name = $.trim($card.find('.js-name-b2').first().text()) || 'Product';
            var priceText = $card.find('.stext-105.cl3').first().text();
            var img = $card.find('.block2-pic img').first().attr('src') || 'images/product-min-01.jpg';
            return { id: slugify(name), name: name, price: toNumber(priceText), img: img };
        }

        var $detailName = $('.js-name-detail').first();
        if ($detailName.length) {
            var detailName = $.trim($detailName.text()) || 'Product';
            var detailPrice = toNumber($('.mtext-106').first().text());
            var detailImg = $('.slick3 .item-slick3 img').first().attr('src') || 'images/product-min-01.jpg';
            return { id: slugify(detailName), name: detailName, price: detailPrice, img: detailImg };
        }

        return null;
    }

    function setupHeaderAddToCart() {
        // Header cart icon should only open the cart panel
        $(document).on('click', '.js-addcart-header', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.js-panel-cart').addClass('show-header-cart');
        });
    }

    function init() {
        var cart = loadCart().map(normalizeItem);
        saveCart(cart);

        renderMiniCart(cart);
        renderCartPage(cart);
        setupAddToCartButtons();
        setupAddToCartFromBtn();
        setupCartPageEvents();
        setupMiniCartEvents();
        setupHeaderAddToCart();
    }

    $(init);
})(jQuery);
