jQuery(document).ready(function($) {

    // ============================
    // Обработка категорий (AJAX)
    // ============================

    // Добавление категории через AJAX
    $('#wppm-category-form').on('submit', function(e) {
        e.preventDefault();
        var categoryName = $(this).find('input[name="category_name"]').val();
        $.ajax({
            url: wppm_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'wppm_ajax_action',
                nonce: wppm_ajax_obj.nonce,
                wppm_type: 'add_category',
                category_name: categoryName
            },
            success: function(response) {
                alert(response.message);
                loadCategories();
            }
        });
    });

    // Функция для загрузки категорий
    function loadCategories() {
        $.ajax({
            url: wppm_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'wppm_ajax_action',
                nonce: wppm_ajax_obj.nonce,
                wppm_type: 'get_categories'
            },
            success: function(response) {
                if(response.success) {
                    var list = $('#wppm-categories-list');
                    list.empty();
                    $.each(response.categories, function(index, category) {
                        var row = $('<tr id="'+category.id+'"></tr>');
                        row.append('<td class="wppm-drag-handle" style="cursor: move;">⇅</td>');
                        row.append('<td>'+category.id+'</td>');
                        row.append('<td>'+category.name+'</td>');
                        row.append('<td>'+category.display_order+'</td>');
                        var actions =
                            '<a href="#" class="edit-category" data-id="'+category.id+'">'+wppm_ajax_obj.edit_label+'</a> | ' +
                            '<a href="#" class="delete-category" data-id="'+category.id+'">'+wppm_ajax_obj.delete_label+'</a> | ' +
                            '<a href="'+wppm_ajax_obj.view_services_base+category.id+'">'+wppm_ajax_obj.view_label+'</a> | ' +
                            '<a href="'+wppm_ajax_obj.add_service_base+category.id+'">'+wppm_ajax_obj.quick_add_label+'</a>';
                        row.append('<td>'+actions+'</td>');
                        list.append(row);
                    });
                    if(list.hasClass('ui-sortable')){ list.sortable('refresh'); }
                }
            }
        });
    }
    loadCategories();

    // Редактирование категории
    $(document).on('click', '.edit-category', function() {
        var id = $(this).data('id');
        var newName = prompt('Введите новое название категории:');
        if(newName) {
            $.ajax({
                url: wppm_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'wppm_ajax_action',
                    nonce: wppm_ajax_obj.nonce,
                    wppm_type: 'edit_category',
                    id: id,
                    category_name: newName
                },
                success: function(response) {
                    alert(response.message);
                    loadCategories();
                }
            });
        }
    });

    // Удаление категории
    $(document).on('click', '.delete-category', function() {
        if(confirm('Вы уверены, что хотите удалить эту категорию?')) {
            var id = $(this).data('id');
            $.ajax({
                url: wppm_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'wppm_ajax_action',
                    nonce: wppm_ajax_obj.nonce,
                    wppm_type: 'delete_category',
                    id: id
                },
                success: function(response) {
                    alert(response.message);
                    loadCategories();
                }
            });
        }
    });

    // Сортировка категорий (drag-and-drop)
    $('#wppm-categories-list').sortable({
        update: function(event, ui) {
            var order = $(this).sortable('toArray');
            $.ajax({
                url: wppm_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'wppm_ajax_action',
                    nonce: wppm_ajax_obj.nonce,
                    wppm_type: 'reorder_categories',
                    order: order
                },
                success: function(response) {
                    console.log(response.message);
                }
            });
        }
    });

    // ============================
    // Сортировка услуг для выбранной категории (drag-and-drop)
    // ============================
    // Если на странице есть tbody с id "wppm-services-sortable",
    // активируем сортировку только для непосредственных строк, используя элемент-ручку.
    if ($('#wppm-services-sortable').length) {
        $('#wppm-services-sortable').sortable({
            items: '> tr',
            placeholder: "ui-state-highlight",
            handle: '.wppm-drag-handle'
        });

        // При отправке формы для обновления порядка собираем новый порядок
        $('#wppm-services-order-form').on('submit', function(e) {
            var order = [];
            $('#wppm-services-sortable tr').each(function() {
                var id = $(this).data('id');
                if (id) {
                    order.push(id);
                }
            });
        $('#wppm-new-order').val(order.join(','));
        });
    }


    function submitServiceForm(type, extra){
        var cat = $('#service_category').val();
        var pg  = $('#price_group').val();
        var data = {
            action: 'wppm_ajax_action',
            nonce: wppm_ajax_obj.nonce,
            wppm_type: type,
            service_name: $('#service_name').val(),
            service_description: $('#service_description').val(),
            service_link: $('#service_link').val(),
            service_price: $('#service_price').val(),
            price_group: pg,
            service_category: cat,
            service_category_id: $('input[name="service_category_id"]').val() || ''
        };
        $.extend(data, extra || {});
        $.post(wppm_ajax_obj.ajax_url, data, function(res){
            alert(res.message);
            if(res.success){
                location.reload();
            }
        });
    }

    $(document).on('submit', '#wppm-add-service-form', function(e){
        if (e.isDefaultPrevented()) return;
        e.preventDefault();
        submitServiceForm('add_service');
    });

    $(document).on('submit', '#wppm-edit-service-form', function(e){
        if (e.isDefaultPrevented()) return;
        e.preventDefault();
        submitServiceForm('edit_service', {service_id: $('input[name="service_id"]').val()});
    });

    // ============================
    // Confirm and submit price group forms via AJAX
    // ============================
    function sendPriceGroup(data){
        $.post(wppm_ajax_obj.ajax_url, data, function(res){
            if(res.need_confirm){
                $('<div>' + res.message + '</div>').dialog({
                    modal:true,
                    title:wppm_ajax_obj.confirm_price_change_title,
                    buttons:{
                        'Подтвердить': function(){ $(this).dialog('close'); data.confirm=1; sendPriceGroup(data); },
                        'Отмена': function(){ $(this).dialog('close'); }
                    }
                });
            } else {
                alert(res.message);
                if(res.success){ location.reload(); }
            }
        });
    }

    $(document).on('submit', '#wppm-add-price-group-form', function(e){
        e.preventDefault();
        var data = {
            action:'wppm_ajax_action',
            nonce: wppm_ajax_obj.nonce,
            wppm_type:'add_price_group',
            price_group_name: $('#price_group_name').val(),
            default_price: $('#default_price').val()
        };
        sendPriceGroup(data);
    });

    if($('#wppm-edit-price-group-form').length){
        $('#wppm-edit-price-group-form').on('submit', function(e){
            e.preventDefault();
            var data = {
                action:'wppm_ajax_action',
                nonce: wppm_ajax_obj.nonce,
                wppm_type:'edit_price_group',
                id: $('input[name="price_group_id"]').val(),
                price_group_name: $('#price_group_name').val(),
                default_price: $('#default_price').val()
            };
            sendPriceGroup(data);
        });
    }

    // ============================
    // Фильтрация услуг через AJAX
    // ============================
    $('#wppm-service-filter-form').on('submit', function(e){
        e.preventDefault();
        var data = {
            action: 'wppm_ajax_action',
            nonce: wppm_ajax_obj.nonce,
            wppm_type: 'search_services',
            name: $('#wppm-filter-name').val(),
            description: $('#wppm-filter-description').val(),
            price_group: $('#wppm-filter-price-group').val(),
            category: $('#wppm-filter-category').val()
        };
        $.post(wppm_ajax_obj.ajax_url, data, function(res){
            if(res.success){
                var tbody = $('#wppm-services-table');
                if(!tbody.length){
                    tbody = $('#wppm-services-sortable');
                }
                tbody.html(res.html);
            } else {
                alert(res.message);
            }
        });
    });
});
