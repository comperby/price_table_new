jQuery(document).ready(function($) {
    $('.wppm-info-icon').each(function() {
        var $icon = $(this);
        var description = $icon.data('description');
        // Создаём tooltip и скрываем его
        var $tooltip = $('<div class="wppm-tooltip">' + description + '<span class="wppm-close">×</span></div>').hide();
        $('body').append($tooltip);
        
        // Функция позиционирования tooltip относительно значка:
        // Располагаем tooltip так, чтобы его нижняя левая точка была чуть выше (на 5px) и правее (на 5px) значка.
        function showTooltip() {
            var offset = $icon.offset();
            $tooltip.css({
                top: offset.top - $tooltip.outerHeight() - 5,
                left: offset.left + $icon.outerWidth() + 5
            }).fadeIn();
        }
        
        function hideTooltip() {
            $tooltip.fadeOut();
        }
        
        var hideTimeout;
        
        // При наведении на значок отменяем таймер скрытия и показываем tooltip
        $icon.on('mouseenter', function() {
            clearTimeout(hideTimeout);
            showTooltip();
        });
        
        // При уходе с значка устанавливаем таймер для скрытия tooltip
        $icon.on('mouseleave', function() {
            hideTimeout = setTimeout(hideTooltip, 300);
        });
        
        // Если курсор входит в область tooltip – отменяем скрытие
        $tooltip.on('mouseenter', function() {
            clearTimeout(hideTimeout);
        }).on('mouseleave', function() {
            hideTooltip();
        });
        
        // Для мобильных устройств – переключаем показ tooltip по клику
        $icon.on('click', function(e) {
            if ($(window).width() <= 768) {
                e.preventDefault();
                $tooltip.fadeToggle();
            }
        });
        
        // Кнопка закрытия tooltip
        $tooltip.find('.wppm-close').on('click', function() {
            hideTooltip();
        });
    });
});
