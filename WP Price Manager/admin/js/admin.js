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

    // ============================
    // Autocomplete for service form
    // ============================
    var catNames = [];
    var pgNames  = [];

    function loadAutocomplete() {
        $.ajax({
            url: wppm_ajax_obj.ajax_url,
            method: 'POST',
            data: {
                action: 'wppm_ajax_action',
                nonce: wppm_ajax_obj.nonce,
                wppm_type: 'get_categories'
            },
            success: function(res){
                if(res.success){
                    catNames = $.map(res.categories, function(c){ return c.name; });
                    $('#service_category').autocomplete({ source: catNames, minLength: 0 });
                }
            }
        });

        $.ajax({
            url: wppm_ajax_obj.ajax_url,
            method: 'POST',
            data: {
                action: 'wppm_ajax_action',
                nonce: wppm_ajax_obj.nonce,
                wppm_type: 'get_price_groups'
            },
            success: function(res){
                if(res.success){
                    var groups = res.get_price_groups || res.price_groups || res.priceGroups;
                    pgNames = $.map(groups, function(pg){ return pg.name; });
                    $('#price_group').autocomplete({ source: pgNames, minLength: 0 });
                }
            }
        });
    }

    loadAutocomplete();

    $(document).on('focus', '#service_category, #price_group', function(){
        $(this).autocomplete('search', '');
    });

    $(document).on('submit', '#wppm-add-service-form, #wppm-edit-service-form', function(e){
        var cat = $('#service_category').val();
        var pg  = $('#price_group').val();
        if(cat && $.inArray(cat, catNames) === -1){
            if(!confirm('Создать новую категорию "' + cat + '"?')) {
                e.preventDefault();
                return false;
            }
        }
        if(pg && $.inArray(pg, pgNames) === -1){
            if(!confirm('Создать новую группу цен "' + pg + '"?')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
