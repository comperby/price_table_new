jQuery(document).ready(function($){
    var $tooltip = $('<div class="wppm-tooltip"><span class="wppm-content"></span><span class="wppm-close">×</span></div>').hide();
    $tooltip.data('speed',300);
    $('body').append($tooltip);
    var hideTimeout;

    function positionTooltip($icon){
        var $container = $icon.closest('.wppm-price-list-widget');
        var width = $container.data('tooltip-width') || '300px';
        var offset = $icon.offset();
        var iconW = $icon.outerWidth();
        var iconH = $icon.outerHeight();
        var left = offset.left + iconW / 2 + 15;
        $tooltip.css({display:'block', visibility:'hidden', maxWidth: width});
        var height = $tooltip.outerHeight();
        var fullWidth = $tooltip.outerWidth();
        var top = offset.top + iconH / 2 - 20 - height;
        if(left + fullWidth > $(window).width() - 10){
            left = $(window).width() - fullWidth - 10;
        }
        if(top < 0){
            top = offset.top + iconH / 2 + 20;
        }
        $tooltip.css({top: top, left: left, display:'none', visibility:''});
    }

    $(document).on('mouseenter', '.wppm-info-icon', function(){
        if($(window).width() > 768){
            var $icon = $(this);
            var $container = $icon.closest('.wppm-price-list-widget');
            var fade = parseInt($container.data('tooltip-speed'),10) || 300;
            // tooltip style now comes from global CSS, no need to swap classes
            $tooltip.data('speed', fade);
            clearTimeout(hideTimeout);
            $tooltip.find('.wppm-content').text($icon.data('description'));
            positionTooltip($icon);
            $tooltip.fadeIn(fade);
        }
    }).on('mouseleave', '.wppm-info-icon', function(){
        if($(window).width() > 768){
            var fade = parseInt($(this).closest('.wppm-price-list-widget').data('tooltip-speed'),10) || 300;
            hideTimeout = setTimeout(function(){ $tooltip.fadeOut(fade); }, fade);
        }
    });

    $tooltip.on('mouseenter', function(){
        clearTimeout(hideTimeout);
    }).on('mouseleave', function(){
        var fade = parseInt($tooltip.data('speed'),10) || 300;
        $tooltip.fadeOut(fade);
    });

    $(document).on('click', '.wppm-info-icon', function(e){
        if($(window).width() <= 768){
            e.preventDefault();
            var $icon = $(this);
            var $container = $icon.closest('.wppm-price-list-widget');
            var fade = parseInt($container.data('tooltip-speed'),10) || 300;
            // tooltip class is fixed via CSS
            $tooltip.data('speed', fade);
            $tooltip.find('.wppm-content').text($icon.data('description'));
            positionTooltip($icon);
            if($tooltip.is(':visible')){ $tooltip.fadeOut(fade); } else { $tooltip.fadeIn(fade); }
        }
    });

    $tooltip.find('.wppm-close').on('click', function(){
        var fade = parseInt($tooltip.data('speed'),10) || 300;
        $tooltip.fadeOut(fade);
    });

    function getLimit($container){
        var d = parseInt($container.data('limit'),10) || 0;
        var m = parseInt($container.data('limit-mobile'),10) || d;
        return $(window).width() <= 768 ? m : d;
    }

    function applyLimit($container){
        var rows = $container.find('tbody tr');
        var limit = getLimit($container);
        if($container.data('nohide') == 1){
            limit = rows.length;
        }
        rows.each(function(i){
            var $row = $(this);
            if(i >= limit){
                $row.addClass('wppm-hidden-row').hide();
            }else{
                $row.removeClass('wppm-hidden-row').show();
            }
        });
        var btn = $container.find('.wppm-show-more');
        if($container.data('nohide') == 1 || rows.length <= limit){
            btn.hide();
            $container.removeClass('wppm-expanded');
        }else{
            btn.show().text(btn.data('more'));
            $container.removeClass('wppm-expanded');
        }
    }

    $('.wppm-price-list-widget').each(function(){
        applyLimit($(this));
    });

    $(window).on('resize', function(){
        $('.wppm-price-list-widget').each(function(){
            applyLimit($(this));
        });
    });

    $('.wppm-show-more').on('click', function(){
        var $btn = $(this);
        var container = $btn.closest('.wppm-price-list-widget');
        var speed = container.data('speed') || '0.3s';
        var duration = parseFloat(speed);
        if(speed.indexOf('ms') === -1){ duration *= 1000; }
        var limit = getLimit(container);
        var rows = container.find('tbody tr').slice(limit);
        if(container.hasClass('wppm-expanded')){
            rows.each(function(){
                var $row = $(this);
                var height = $row.outerHeight();
                $row.css({overflow:'hidden', height:height, opacity:1})
                    .animate({height:0, opacity:0}, duration, function(){
                        $row.addClass('wppm-hidden-row').css({overflow:'', height:'', opacity:'', display:''});
                    });
            });
            $btn.text($btn.data('more'));
            container.removeClass('wppm-expanded');
        } else {
            rows.each(function(){
                var $row = $(this);
                $row.removeClass('wppm-hidden-row').css({display:'table-row'});
                var height = $row.outerHeight();
                $row.css({overflow:'hidden', height:0, opacity:0})
                    .animate({height:height, opacity:1}, duration, function(){
                        $row.css({overflow:'', height:'', opacity:''});
                    });
            });
            $btn.text($btn.data('less'));
            container.addClass('wppm-expanded');
        }
    });
});

