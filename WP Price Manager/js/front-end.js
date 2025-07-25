jQuery(document).ready(function($){
    var $tooltip = $('<div class="wppm-tooltip"><span class="wppm-content"></span><span class="wppm-close">×</span></div>').hide();
    $('body').append($tooltip);
    var hideTimeout;

    function positionTooltip($icon){
        var offset = $icon.offset();
        var spaceRight = $(window).width() - offset.left - $icon.outerWidth() - 10;
        var width = 300;
        if(spaceRight < 300){
            width = spaceRight > 0 ? spaceRight : 300;
        }
        $tooltip.css({display:'block', visibility:'hidden', width: width});
        var height = $tooltip.outerHeight();
        var fullWidth = $tooltip.outerWidth();
        var top = offset.top - height;
        var left = offset.left + $icon.outerWidth();
        if(left + fullWidth > $(window).width() - 10){
            left = $(window).width() - fullWidth - 10;
        }
        if(top < 0){
            top = offset.top + $icon.outerHeight();
        }
        $tooltip.css({top: top, left: left, display:'none', visibility:''});
    }

    $(document).on('mouseenter', '.wppm-info-icon', function(){
        if($(window).width() > 768){
            var $icon = $(this);
            clearTimeout(hideTimeout);
            $tooltip.find('.wppm-content').text($icon.data('description'));
            positionTooltip($icon);
            $tooltip.fadeIn();
        }
    }).on('mouseleave', '.wppm-info-icon', function(){
        if($(window).width() > 768){
            hideTimeout = setTimeout(function(){ $tooltip.fadeOut(); }, 300);
        }
    });

    $tooltip.on('mouseenter', function(){
        clearTimeout(hideTimeout);
    }).on('mouseleave', function(){
        $tooltip.fadeOut();
    });

    $(document).on('click', '.wppm-info-icon', function(e){
        if($(window).width() <= 768){
            e.preventDefault();
            var $icon = $(this);
            $tooltip.find('.wppm-content').text($icon.data('description'));
            positionTooltip($icon);
            $tooltip.fadeToggle();
        }
    });

    $tooltip.find('.wppm-close').on('click', function(){
        $tooltip.fadeOut();
    });

    $('.wppm-show-more').on('click', function(){
        var $btn = $(this);
        var container = $btn.closest('.wppm-price-list-widget');
        var speed = container.data('speed') || '0.3s';
        var duration = parseFloat(speed);
        if(speed.indexOf('ms') === -1){ duration *= 1000; }
        var limit = parseInt(container.data('limit'), 10) || 0;
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

