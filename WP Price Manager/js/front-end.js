jQuery(document).ready(function($){
    var $tooltip = $('<div class="wppm-tooltip"><span class="wppm-content"></span><span class="wppm-close">×</span></div>').hide();
    $('body').append($tooltip);
    var hideTimeout;

    function positionTooltip($icon){
        var offset = $icon.offset();
        $tooltip.css({
            top: offset.top - $tooltip.outerHeight() - 5,
            left: offset.left + $icon.outerWidth() + 5
        });
    }

    $(document).on('mouseenter', '.wppm-info-icon', function(){
        var $icon = $(this);
        clearTimeout(hideTimeout);
        $tooltip.find('.wppm-content').text($icon.data('description'));
        positionTooltip($icon);
        $tooltip.fadeIn();
    }).on('mouseleave', '.wppm-info-icon', function(){
        hideTimeout = setTimeout(function(){ $tooltip.fadeOut(); }, 300);
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
                $row.slideUp(duration, function(){
                    $row.css('display','none').addClass('wppm-hidden-row');
                });
            });
            $btn.text($btn.data('more'));
            container.removeClass('wppm-expanded');
        } else {
            rows.each(function(){
                var $row = $(this);
                $row.removeClass('wppm-hidden-row').css('display','table-row').hide()
                    .slideDown(duration, function(){
                        $row.css('display','table-row');
                    });
            });
            $btn.text($btn.data('less'));
            container.addClass('wppm-expanded');
        }
    });
});

