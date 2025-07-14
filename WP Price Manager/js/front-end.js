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
        var table = container.find('table');
        var cat = container.data('cat');
        var limit = container.data('limit');
        var offset = $btn.data('offset');
        $.post(wppm_ajax_obj.ajax_url, {
            action:'wppm_ajax_action',
            nonce:wppm_ajax_obj.nonce,
            wppm_type:'load_more_services',
            cat_id:cat,
            offset:offset,
            limit:limit
        }, function(res){
            if(res.success){
                table.find('tbody').append(res.html);
                offset += res.count;
                $btn.data('offset', offset);
                if(!res.has_more){
                    $btn.remove();
                }
            }
        });
    });
});
