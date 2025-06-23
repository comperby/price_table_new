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
                        list.append('<li id="'+category.id+'">'+category.name+' <button class="edit-category" data-id="'+category.id+'">Edit</button> <button class="delete-category" data-id="'+category.id+'">Delete</button></li>');
                    });
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
});
