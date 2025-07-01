jQuery(document).ready(function($) {

    $('.wppm-color-field').wpColorPicker();

    var wppmCategories = [];
    var wppmPriceGroups = [];

    function loadCategorySuggestions(cb) {
        $.post(wppm_ajax_obj.ajax_url, {
            action: 'wppm_ajax_action',
            nonce: wppm_ajax_obj.nonce,
            wppm_type: 'get_categories'
        }, function(res) {
            if (res.success) {
                wppmCategories = $.map(res.categories, function(c) { return c.name; });
                if (cb) cb();
            }
        });
    }

    function loadPriceGroupSuggestions(cb) {
        $.post(wppm_ajax_obj.ajax_url, {
            action: 'wppm_ajax_action',
            nonce: wppm_ajax_obj.nonce,
            wppm_type: 'get_price_groups'
        }, function(res) {
            if (res.success) {
                wppmPriceGroups = $.map(res.get_price_groups, function(pg) { return pg.name; });
                if (cb) cb();
            }
        });
    }

    function updateColumnInputs(){
        var count = parseInt($('#column_count').val()) || 2;
        if(count < 2) count = 2;
        var container = $('#column_titles_container');
        container.empty();
        for(var i=1;i<=count;i++){
            container.append('<input type="text" name="column_titles['+i+']" placeholder="'+i+'" /><br>');
        }
    }

    $('#custom_table').on('change', function(){
        if($(this).is(':checked')){
            $('.wppm-custom-settings').show();
            updateColumnInputs();
        }else{
            $('.wppm-custom-settings').hide();
            $('#column_titles_container').empty();
        }
    });

    $('#column_count').on('change', updateColumnInputs);


    // ============================
    // Обработка категорий (AJAX)
    // ============================

    // Добавление категории через AJAX
    $('#wppm-category-form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        data += '&action=wppm_ajax_action&nonce=' + wppm_ajax_obj.nonce + '&wppm_type=add_category';
        $.post(wppm_ajax_obj.ajax_url, data, function(response){
            alert(response.message);
            if(response.success){
                loadCategories();
                $('#wppm-category-form')[0].reset();
                $('.wppm-custom-settings').hide();
                $('#column_titles_container').empty();
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
                        var row = $('<tr id="'+category.id+'" data-id="'+category.id+'" data-name="'+category.name+'"></tr>');
                        row.append('<td class="wppm-drag-handle" style="cursor: move;">⇅</td>');
                        row.append('<td>'+category.id+'</td>');
                        row.append('<td class="cat-name">'+category.name+'</td>');
                        row.append('<td>'+category.display_order+'</td>');
                        var actions =
                            '<a href="#" class="edit-category" data-id="'+category.id+'">'+wppm_ajax_obj.edit_label+'</a> | ' +
                            '<a href="#" class="delete-category" data-id="'+category.id+'">'+wppm_ajax_obj.delete_label+'</a> | ' +
                            '<a href="'+wppm_ajax_obj.view_services_base+category.id+'">'+wppm_ajax_obj.view_label+'</a> | ' +
                            '<a href="'+wppm_ajax_obj.add_service_base+category.id+'">'+wppm_ajax_obj.quick_add_label+'</a>';
                        row.append('<td class="cat-actions">'+actions+'</td>');
                        list.append(row);
                    });
                    if(list.hasClass('ui-sortable')){ list.sortable('refresh'); }
                }
            }
        });
    }
    loadCategories();

    // Редактирование категории inline
    $(document).on('click', '.edit-category', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        if(row.hasClass('editing')) return;
        row.addClass('editing');
        var nameCell = row.find('.cat-name');
        var current = nameCell.text();
        nameCell.html('<input type="text" class="edit-cat-name" value="'+current+'">');
        var actions = row.find('.cat-actions');
        actions.data('orig', actions.html());
        actions.html('<button class="save-category button button-primary" data-id="'+row.data('id')+'">'+wppm_ajax_obj.save_label+'</button>');
    });

    $(document).on('click', '.save-category', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        var id = row.data('id');
        var newName = row.find('.edit-cat-name').val();
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
            success: function(response){
                alert(response.message);
                loadCategories();
            }
        });
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

    // Inline editing for services
    $(document).on('click', '.edit-service', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        if(row.hasClass('editing')) return;
        row.addClass('editing');
        row.find('.srv-name').html('<input type="text" class="srv-edit-name" value="'+row.data('name')+'">');
        row.find('.srv-description').html('<textarea class="srv-edit-description">'+row.data('description')+'</textarea>');
        row.find('.srv-link').html('<input type="url" class="srv-edit-link" value="'+row.data('link')+'">');
        row.find('.srv-price').html('<input type="text" class="srv-edit-price" value="'+row.data('price')+'">');
        row.find('.srv-category').html('<input type="text" class="srv-edit-category" value="'+row.data('category')+'">');
        row.find('.srv-price-group').html('<input type="text" class="srv-edit-price-group" value="'+row.data('price-group')+'">');
        var actions = row.find('.srv-actions');
        actions.data('orig', actions.html());
        actions.html('<button class="save-service button button-primary">'+wppm_ajax_obj.save_label+'</button>');

        row.find('.srv-edit-category').autocomplete({source:wppmCategories,minLength:0}).focus(function(){ $(this).autocomplete('search', this.value); });
        row.find('.srv-edit-price-group').autocomplete({source:wppmPriceGroups,minLength:0}).focus(function(){ $(this).autocomplete('search', this.value); });
    });

    $(document).on('click', '.save-service', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        var id = row.data('id');
        var data = {
            action: 'wppm_ajax_action',
            nonce: wppm_ajax_obj.nonce,
            wppm_type: 'edit_service',
            service_id: id,
            service_name: row.find('.srv-edit-name').val(),
            service_description: row.find('.srv-edit-description').val(),
            service_link: row.find('.srv-edit-link').val(),
            service_price: row.find('.srv-edit-price').val(),
            price_group: row.find('.srv-edit-price-group').val(),
            service_category: row.find('.srv-edit-category').val()
        };
        $.post(wppm_ajax_obj.ajax_url, data, function(res){
            alert(res.message);
            if(res.success){ location.reload(); }
        });
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

    // Inline editing for price groups
    $(document).on('click', '.edit-price-group', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        if(row.hasClass('editing')) return;
        row.addClass('editing');
        row.find('.pg-name-cell').html('<input type="text" class="pg-edit-name" value="'+row.data('name')+'">');
        row.find('.pg-price-cell').html('<input type="text" class="pg-edit-price" value="'+row.data('price')+'">');
        var actions = row.find('.pg-actions');
        actions.data('orig', actions.html());
        actions.html('<button class="save-price-group button button-primary">'+wppm_ajax_obj.save_label+'</button>');
    });

    $(document).on('click', '.save-price-group', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        var data = {
            action:'wppm_ajax_action',
            nonce: wppm_ajax_obj.nonce,
            wppm_type:'edit_price_group',
            id: row.data('id'),
            price_group_name: row.find('.pg-edit-name').val(),
            default_price: row.find('.pg-edit-price').val()
        };
        sendPriceGroup(data);
    });

    // ============================
    // Autocomplete fields
    // ============================
    if ($('#service_category').length) {
        loadCategorySuggestions(function(){
            $('#service_category').autocomplete({
                source: wppmCategories,
                minLength: 0
            }).on('focus', function(){ $(this).autocomplete('search', this.value); });
        });
    }

    if ($('#price_group').length) {
        loadPriceGroupSuggestions(function(){
            $('#price_group').autocomplete({
                source: wppmPriceGroups,
                minLength: 0
            }).on('focus', function(){ $(this).autocomplete('search', this.value); });
        });
    }

    if ($('#wppm-filter-category').length) {
        loadCategorySuggestions(function(){
            $('#wppm-filter-category').autocomplete({
                source: wppmCategories,
                minLength: 0
            }).on('focus', function(){ $(this).autocomplete('search', this.value); });
        });
    }

    if ($('#wppm-filter-price-group').length) {
        loadPriceGroupSuggestions(function(){
            $('#wppm-filter-price-group').autocomplete({
                source: wppmPriceGroups,
                minLength: 0
            }).on('focus', function(){ $(this).autocomplete('search', this.value); });
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
