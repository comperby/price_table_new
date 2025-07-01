<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Price_Manager_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Прайс-Менеджер', 'wp-price-manager' ),
            __( 'Прайс-Менеджер', 'wp-price-manager' ),
            'manage_options',
            'price-manager',
            array( $this, 'render_categories_page' ),
            'dashicons-list-view'
        );

        add_submenu_page(
            'price-manager',
            __( 'Категории', 'wp-price-manager' ),
            __( 'Категории', 'wp-price-manager' ),
            'manage_options',
            'price-manager-categories',
            array( $this, 'render_categories_page' )
        );

        add_submenu_page(
            'price-manager',
            __( 'Все услуги', 'wp-price-manager' ),
            __( 'Все услуги', 'wp-price-manager' ),
            'manage_options',
            'price-manager-services',
            array( $this, 'render_services_page' )
        );

        add_submenu_page(
            'price-manager',
            __( 'Группа цен', 'wp-price-manager' ),
            __( 'Группа цен', 'wp-price-manager' ),
            'manage_options',
            'price-manager-price-groups',
            array( $this, 'render_price_groups_page' )
        );

        add_submenu_page(
            'price-manager',
            __( 'Стиль', 'wp-price-manager' ),
            __( 'Стиль', 'wp-price-manager' ),
            'manage_options',
            'price-manager-style',
            array( $this, 'render_style_page' )
        );
    }

    // Страница управления категориями с поиском
    public function render_categories_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'wppm_categories';
        
        // Получаем поисковый запрос (если передан)
        $search_query = isset( $_GET['cat_search'] ) ? sanitize_text_field( $_GET['cat_search'] ) : '';
        if ( $search_query ) {
            $sql = $wpdb->prepare( "SELECT * FROM $table WHERE name LIKE %s ORDER BY display_order ASC", '%' . $search_query . '%' );
        } else {
            $sql = "SELECT * FROM $table ORDER BY display_order ASC";
        }
        $categories = $wpdb->get_results( $sql, ARRAY_A );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Категории', 'wp-price-manager' ); ?></h1>
            
            <!-- Форма добавления новой категории -->
            <h2><?php _e( 'Добавить новую категорию', 'wp-price-manager' ); ?></h2>
            <form id="wppm-category-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="wppm_add_category">
                <?php wp_nonce_field( 'wppm_category_nonce', 'wppm_category_nonce_field' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="category_name"><?php _e( 'Название категории', 'wp-price-manager' ); ?></label></th>
                        <td><input type="text" id="category_name" name="category_name" required></td>
                    </tr>
                    <tr>
                        <th><label for="display_order"><?php _e( 'Порядок отображения', 'wp-price-manager' ); ?></label></th>
                        <td><input type="number" id="display_order" name="display_order" value="0" required></td>
                    </tr>
                    <tr>
                        <th><label for="custom_table"><?php _e( 'Кастом', 'wp-price-manager' ); ?></label></th>
                        <td><input type="checkbox" id="custom_table" name="custom_table" value="1"></td>
                    </tr>
                    <tr class="wppm-custom-settings" style="display:none;">
                        <th><label for="column_count"><?php _e( 'Количество колонок', 'wp-price-manager' ); ?></label></th>
                        <td><input type="number" id="column_count" name="column_count" min="2" value="2"></td>
                    </tr>
                    <tr class="wppm-custom-settings" id="column_titles_row" style="display:none;">
                        <th><?php _e( 'Названия колонок', 'wp-price-manager' ); ?></th>
                        <td id="column_titles_container"></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Добавить категорию', 'wp-price-manager' ); ?>"></p>
            </form>
            
            <hr>
            <h2><?php _e( 'Список категорий', 'wp-price-manager' ); ?></h2>
            
            <!-- Форма поиска категорий -->
            <form method="get" action="">
                <input type="hidden" name="page" value="price-manager-categories">
                <input type="text" name="cat_search" placeholder="<?php _e( 'Поиск по категориям...', 'wp-price-manager' ); ?>" value="<?php echo esc_attr( $search_query ); ?>">
                <input type="submit" class="button" value="<?php _e( 'Найти', 'wp-price-manager' ); ?>">
            </form>
            
            <?php if ( isset($_GET['msg']) ) echo '<div class="updated"><p>' . esc_html($_GET['msg']) . '</p></div>'; ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th></th>
                        <th><?php _e( 'ID', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Название', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Порядок', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Действия', 'wp-price-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody id="wppm-categories-list">
                    <?php if ( $categories ) : ?>
                        <?php foreach ( $categories as $cat ) : ?>
                            <tr id="<?php echo intval( $cat['id'] ); ?>" data-id="<?php echo intval( $cat['id'] ); ?>" data-name="<?php echo esc_attr( $cat['name'] ); ?>">
                                <td class="wppm-drag-handle" style="cursor: move;">⇅</td>
                                <td><?php echo esc_html( $cat['id'] ); ?></td>
                                <td class="cat-name"><?php echo esc_html( $cat['name'] ); ?></td>
                                <td><?php echo esc_html( $cat['display_order'] ); ?></td>
                                <td class="cat-actions">
                                    <a href="#" class="edit-category" data-id="<?php echo intval( $cat['id'] ); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
                                    <a href="<?php echo admin_url('admin-post.php?action=wppm_delete_category&id=' . intval($cat['id']) . '&_wpnonce=' . wp_create_nonce('wppm_delete_category_' . intval($cat['id']))); ?>" onclick="return confirm('<?php _e('Вы уверены?', 'wp-price-manager'); ?>');"><?php _e( 'Удалить', 'wp-price-manager' ); ?></a> |
                                    <a href="<?php echo admin_url('admin.php?page=price-manager-services&prefill_category=' . intval($cat['id'])); ?>"><?php _e( 'Посмотреть услуги', 'wp-price-manager' ); ?></a> |
                                    <a href="<?php echo admin_url('admin-post.php?action=wppm_add_service_form&category_id=' . intval($cat['id'])); ?>"><?php _e( 'Быстро добавить услугу', 'wp-price-manager' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5"><?php _e( 'Категории не найдены', 'wp-price-manager' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Страница управления услугами (без AJAX)
    public function render_services_page() {
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $cat_table = $wpdb->prefix . 'wppm_categories';
    $pg_table  = $wpdb->prefix . 'wppm_price_groups';
    
    // Категория для отображения услуг
    $prefill_category = isset($_GET['prefill_category']) ? intval($_GET['prefill_category']) : 0;
    $categories = $wpdb->get_results( "SELECT * FROM $cat_table ORDER BY display_order ASC", ARRAY_A );

    if ( $prefill_category ) {
        $services = $wpdb->get_results( $wpdb->prepare(
            "SELECT s.*, c.name as category_name, pg.name as price_group_name, c.custom_table, c.column_titles
             FROM $srv_table s
             LEFT JOIN $cat_table c ON s.category_id = c.id
             LEFT JOIN $pg_table pg ON s.price_group_id = pg.id
             WHERE s.category_id = %d ORDER BY s.display_order ASC", $prefill_category
        ), ARRAY_A );
        $cat_info = $wpdb->get_row( $wpdb->prepare( "SELECT custom_table,column_titles FROM $cat_table WHERE id = %d", $prefill_category ), ARRAY_A );
    } else {
        $services = array();
        $cat_info = null;
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Все услуги', 'wp-price-manager' ); ?></h1>
        <form method="get" action="">
            <input type="hidden" name="page" value="price-manager-services">
            <select name="prefill_category" id="wppm-category-select">
                <option value=""><?php _e( 'Выберите категорию', 'wp-price-manager' ); ?></option>
                <?php foreach ( $categories as $cat ) : ?>
                    <option value="<?php echo intval( $cat['id'] ); ?>" <?php selected( $prefill_category, intval( $cat['id'] ) ); ?>><?php echo esc_html( $cat['name'] ); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <script>jQuery(function($){ $('#wppm-category-select').on('change', function(){ $(this).closest('form').submit(); }); });</script>
        <?php if ( ! $prefill_category ) : ?>
            <p><?php _e( 'Выберите категорию для добавления и просмотра услуг.', 'wp-price-manager' ); ?></p>
        </div>
        <?php return; endif; ?>
        <h2><?php _e( 'Добавить новую услугу', 'wp-price-manager' ); ?></h2>
        <form id="wppm-add-service-form" method="post" action="">
            <input type="hidden" name="action" value="wppm_add_service">
            <?php wp_nonce_field( 'wppm_service_nonce', 'wppm_service_nonce_field' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="service_name"><?php _e( 'Название услуги', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="service_name" name="service_name" required></td>
                </tr>
                <tr>
                    <th><label for="service_description"><?php _e( 'Описание', 'wp-price-manager' ); ?></label></th>
                    <td><textarea id="service_description" name="service_description"></textarea></td>
                </tr>
                <tr>
                    <th><label for="service_link"><?php _e( 'Ссылка', 'wp-price-manager' ); ?></label></th>
                    <td><input type="url" id="service_link" name="service_link"></td>
                </tr>
                <tr id="service_price_row">
                    <th><label for="service_price"><?php _e( 'Цена (BYN)', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="service_price" name="service_price"></td>
                </tr>
                <tr>
                    <th><label for="price_group"><?php _e( 'Группа цен', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="price_group" name="price_group"></td>
                </tr>
                <tr>
                    <th><label for="service_category"><?php _e( 'Категория', 'wp-price-manager' ); ?></label></th>
                    <td>
                        <?php
                        if ( $prefill_category ) {
                            $cat = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $cat_table WHERE id = %d", $prefill_category ) );
                            if ( $cat ) {
                                echo '<input type="hidden" name="service_category_id" value="' . intval($cat->id) . '">';
                                echo '<input type="text" value="' . esc_attr($cat->name) . '" disabled>';
                            }
                        } else {
                            echo '<input type="text" id="service_category" name="service_category" required>';
                        }
                        ?>
                    </td>
                </tr>
                <tr id="wppm-extras-row" style="display:none;">
                    <th><?php _e( 'Дополнительные поля', 'wp-price-manager' ); ?></th>
                    <td id="wppm-extras-container"></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Добавить услугу', 'wp-price-manager' ); ?>"></p>
        </form>
        <?php if ( $prefill_category ) : ?>
        <script type="text/javascript">
        jQuery(function($){ if(window.wppm_load_extras){ wppm_load_extras(<?php echo intval($prefill_category); ?>); } });
        </script>
        <?php endif; ?>
        <hr>
        <h2><?php _e( 'Список услуг', 'wp-price-manager' ); ?></h2>
        <?php if ( !$prefill_category ) : ?>
        <form id="wppm-service-filter-form" method="post" action="">
            <input type="text" id="wppm-filter-name" placeholder="<?php _e( 'Название', 'wp-price-manager' ); ?>">
            <input type="text" id="wppm-filter-description" placeholder="<?php _e( 'Описание', 'wp-price-manager' ); ?>">
            <input type="text" id="wppm-filter-price-group" placeholder="<?php _e( 'Группа цен', 'wp-price-manager' ); ?>">
            <input type="text" id="wppm-filter-category" placeholder="<?php _e( 'Категория', 'wp-price-manager' ); ?>">
            <button type="submit" class="button"><?php _e( 'Фильтровать', 'wp-price-manager' ); ?></button>
        </form>
        <?php endif; ?>
        <?php if ( isset($_GET['msg']) ) echo '<div class="updated"><p>' . esc_html($_GET['msg']) . '</p></div>'; ?>
        <?php if ( $prefill_category ) : ?>
            <p><?php _e( 'Перетащите строки для изменения порядка услуг (для выбранной категории)', 'wp-price-manager' ); ?></p>
        <?php endif; ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php if ( $prefill_category ) : ?>
                        <th><?php _e( 'Порядок', 'wp-price-manager' ); ?></th>
                    <?php endif; ?>
                    <th><?php _e( 'ID', 'wp-price-manager' ); ?></th>
                    <th><?php _e( 'Название услуги', 'wp-price-manager' ); ?></th>
                    <?php if ( $cat_info && $cat_info['custom_table'] ) : ?>
                        <?php $titles = json_decode( $cat_info['column_titles'], true ); ?>
                        <?php foreach ( (array) $titles as $title ) : ?>
                            <th><?php echo esc_html( $title ); ?></th>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <th><?php _e( 'Описание', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Ссылка', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Цена', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Категория', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Группа цен', 'wp-price-manager' ); ?></th>
                    <?php endif; ?>
                    <th><?php _e( 'Действия', 'wp-price-manager' ); ?></th>
                </tr>
            </thead>
            <tbody id="<?php echo $prefill_category ? 'wppm-services-sortable' : 'wppm-services-table'; ?>">
                <?php if ( $services ) : ?>
                    <?php foreach ( $services as $srv ) : ?>
                        <tr data-id="<?php echo intval($srv['id']); ?>" data-name="<?php echo esc_attr( $srv['name'] ); ?>" data-description="<?php echo esc_attr( $srv['description'] ); ?>" data-link="<?php echo esc_attr( $srv['link'] ); ?>" data-price="<?php echo esc_attr( $srv['price'] ); ?>" data-category="<?php echo esc_attr( $srv['category_name'] ); ?>" data-price-group="<?php echo esc_attr( $srv['price_group_name'] ); ?>" data-extras='<?php echo esc_attr( $srv['extras'] ); ?>'>
                            <?php if ( $prefill_category ) : ?>
                                <td class="wppm-drag-handle" style="cursor: move;">⇅</td>
                            <?php endif; ?>
                            <td><?php echo esc_html( $srv['id'] ); ?></td>
                            <td class="srv-name"><?php echo esc_html( $srv['name'] ); ?></td>
                            <?php if ( $cat_info && $cat_info['custom_table'] ) : ?>
                                <?php $extras = json_decode( $srv['extras'], true ); ?>
                                <?php foreach ( (array) $titles as $i => $t ) : ?>
                                    <td><?php echo esc_html( $extras[$i] ?? '' ); ?></td>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <td class="srv-description"><?php echo esc_html( $srv['description'] ); ?></td>
                                <td class="srv-link">
                                    <?php if( ! empty( $srv['link'] ) ): ?>
                                        <a href="<?php echo esc_url( $srv['link'] ); ?>" target="_blank"><?php echo esc_html( $srv['link'] ); ?></a>
                                    <?php else: ?>
                                        <?php _e( 'Нет ссылки', 'wp-price-manager' ); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="srv-price"><?php echo esc_html( $srv['price'] ); ?></td>
                                <td class="srv-category"><?php echo esc_html( $srv['category_name'] ); ?></td>
                                <td class="srv-price-group"><?php echo esc_html( $srv['price_group_name'] ); ?></td>
                            <?php endif; ?>
                            <td class="srv-actions">
                                <a href="#" class="edit-service" data-id="<?php echo intval($srv['id']); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
                                <a href="<?php echo admin_url('admin-post.php?action=wppm_delete_service&id=' . intval($srv['id']) . '&_wpnonce=' . wp_create_nonce('wppm_delete_service_' . intval($srv['id']))); ?>" onclick="return confirm('<?php _e('Вы уверены?', 'wp-price-manager'); ?>');"><?php _e( 'Удалить', 'wp-price-manager' ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <?php
                        if ( $prefill_category ) {
                            $cnt = $cat_info && $cat_info['custom_table'] ? count( json_decode( $cat_info['column_titles'], true ) ) + 2 : 8;
                        } else {
                            $cnt = 8;
                        }
                        ?>
                        <td colspan="<?php echo intval( $cnt ); ?>"><?php _e( 'Услуги не найдены', 'wp-price-manager' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ( $prefill_category ) : ?>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="wppm-services-order-form">
                <input type="hidden" name="action" value="wppm_update_services_order">
                <input type="hidden" name="category_id" value="<?php echo intval($prefill_category); ?>">
                <?php wp_nonce_field( 'wppm_services_order_nonce', 'wppm_services_order_nonce_field' ); ?>
                <input type="hidden" name="new_order" id="wppm-new-order" value="">
                <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Сохранить порядок', 'wp-price-manager' ); ?>"></p>
            </form>
        <?php endif; ?>
    </div>
    <?php
        
    }

    // Страница управления группами цен (без изменений)
    public function render_price_groups_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'wppm_price_groups';
        $price_groups = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Группа цен', 'wp-price-manager' ); ?></h1>
            <h2><?php _e( 'Добавить новую группу цен', 'wp-price-manager' ); ?></h2>
            <form id="wppm-add-price-group-form" method="post" action="">
                <input type="hidden" name="action" value="wppm_add_price_group">
                <?php wp_nonce_field( 'wppm_price_group_nonce', 'wppm_price_group_nonce_field' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="price_group_name"><?php _e( 'Название группы цен', 'wp-price-manager' ); ?></label></th>
                        <td><input type="text" id="price_group_name" name="price_group_name" required></td>
                    </tr>
                    <tr>
                        <th><label for="default_price"><?php _e( 'Цена по умолчанию', 'wp-price-manager' ); ?></label></th>
                        <td><input type="text" id="default_price" name="default_price" required></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Добавить группу', 'wp-price-manager' ); ?>"></p>
            </form>
            <hr>
            <h2><?php _e( 'Список групп цен', 'wp-price-manager' ); ?></h2>
            <?php if ( isset($_GET['msg']) ) echo '<div class="updated"><p>' . esc_html($_GET['msg']) . '</p></div>'; ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'ID', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Название', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Цена по умолчанию', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Действия', 'wp-price-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $price_groups ) : ?>
                        <?php foreach ( $price_groups as $pg ) : ?>
                            <tr data-id="<?php echo intval( $pg['id'] ); ?>" data-name="<?php echo esc_attr( $pg['name'] ); ?>" data-price="<?php echo esc_attr( $pg['default_price'] ); ?>">
                                <td><?php echo esc_html( $pg['id'] ); ?></td>
                                <td class="pg-name-cell"><?php echo esc_html( $pg['name'] ); ?></td>
                                <td class="pg-price-cell"><?php echo esc_html( $pg['default_price'] ); ?></td>
                                <td class="pg-actions">
                                    <a href="#" class="edit-price-group" data-id="<?php echo intval( $pg['id'] ); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
                                    <a href="<?php echo admin_url('admin-post.php?action=wppm_delete_price_group&id=' . intval($pg['id']) . '&_wpnonce=' . wp_create_nonce('wppm_delete_price_group_' . intval($pg['id']))); ?>" onclick="return confirm('<?php _e('Вы уверены?', 'wp-price-manager'); ?>');"><?php _e( 'Удалить', 'wp-price-manager' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4"><?php _e( 'Группы цен не найдены', 'wp-price-manager' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Страница настроек стиля вывода таблицы
    public function render_style_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'table';
        $options    = get_option( 'wppm_style_settings', array() );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Настройки стиля', 'wp-price-manager' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=price-manager-style&tab=table" class="nav-tab <?php echo $active_tab === 'table' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Таблица', 'wp-price-manager' ); ?></a>
                <a href="?page=price-manager-style&tab=header" class="nav-tab <?php echo $active_tab === 'header' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Хедер', 'wp-price-manager' ); ?></a>
                <a href="?page=price-manager-style&tab=rows" class="nav-tab <?php echo $active_tab === 'rows' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Строки', 'wp-price-manager' ); ?></a>
                <a href="?page=price-manager-style&tab=icon" class="nav-tab <?php echo $active_tab === 'icon' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Значок', 'wp-price-manager' ); ?></a>
                <a href="?page=price-manager-style&tab=tooltip" class="nav-tab <?php echo $active_tab === 'tooltip' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Описание', 'wp-price-manager' ); ?></a>
            </h2>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="wppm_save_style_settings">
                <input type="hidden" name="current_tab" value="<?php echo esc_attr( $active_tab ); ?>">
                <?php wp_nonce_field( 'wppm_style_settings' ); ?>
                <table class="form-table">
                    <?php if ( $active_tab === 'table' ) : ?>
                        <tr>
                            <th><label for="border_width"><?php _e( 'Толщина границы', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="border_width" id="border_width" value="<?php echo esc_attr( $options['border_width'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="border_color"><?php _e( 'Цвет границы', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="border_color" id="border_color" value="<?php echo esc_attr( $options['border_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="border_radius"><?php _e( 'Скругление углов', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="border_radius" id="border_radius" value="<?php echo esc_attr( $options['border_radius'] ?? '' ); ?>"></td>
                        </tr>
                    <?php elseif ( $active_tab === 'header' ) : ?>
                        <tr>
                            <th><label for="header_bg_color"><?php _e( 'Фон заголовка', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="header_bg_color" id="header_bg_color" value="<?php echo esc_attr( $options['header_bg_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="header_text_color"><?php _e( 'Цвет текста заголовка', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="header_text_color" id="header_text_color" value="<?php echo esc_attr( $options['header_text_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="header_height"><?php _e( 'Высота заголовка', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="header_height" id="header_height" value="<?php echo esc_attr( $options['header_height'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="header_text_size"><?php _e( 'Размер текста', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="header_text_size" id="header_text_size" value="<?php echo esc_attr( $options['header_text_size'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="header_text_weight"><?php _e( 'Жирность текста', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="header_text_weight" id="header_text_weight" value="<?php echo esc_attr( $options['header_text_weight'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="header_alignment"><?php _e( 'Выравнивание текста', 'wp-price-manager' ); ?></label></th>
                            <td>
                                <select name="header_alignment" id="header_alignment">
                                    <option value="left" <?php selected( $options['header_alignment'] ?? '', 'left' ); ?>><?php _e( 'Слева', 'wp-price-manager' ); ?></option>
                                    <option value="center" <?php selected( $options['header_alignment'] ?? '', 'center' ); ?>><?php _e( 'По центру', 'wp-price-manager' ); ?></option>
                                    <option value="right" <?php selected( $options['header_alignment'] ?? '', 'right' ); ?>><?php _e( 'Справа', 'wp-price-manager' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    <?php elseif ( $active_tab === 'rows' ) : ?>
                        <tr>
                            <th><label for="even_row_bg_color"><?php _e( 'Фон четных строк', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="even_row_bg_color" id="even_row_bg_color" value="<?php echo esc_attr( $options['even_row_bg_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="odd_row_bg_color"><?php _e( 'Фон нечетных строк', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="odd_row_bg_color" id="odd_row_bg_color" value="<?php echo esc_attr( $options['odd_row_bg_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="text_font"><?php _e( 'Шрифт', 'wp-price-manager' ); ?></label></th>
                            <td>
                                <select name="text_font" id="text_font">
                                    <?php $fonts = array( 'Montserrat', 'Arial', 'Georgia', 'Times New Roman' ); ?>
                                    <?php foreach ( $fonts as $font ) : ?>
                                        <option value="<?php echo esc_attr( $font ); ?>" <?php selected( $options['text_font'] ?? '', $font ); ?>><?php echo esc_html( $font ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="text_size"><?php _e( 'Размер текста (px)', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="text_size" id="text_size" value="<?php echo esc_attr( $options['text_size'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="text_weight"><?php _e( 'Жирность текста', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="text_weight" id="text_weight" value="<?php echo esc_attr( $options['text_weight'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="text_padding"><?php _e( 'Отступы текста', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="text_padding" id="text_padding" value="<?php echo esc_attr( $options['text_padding'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="text_color"><?php _e( 'Цвет текста', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="text_color" id="text_color" value="<?php echo esc_attr( $options['text_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="link_color"><?php _e( 'Цвет ссылок', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="link_color" id="link_color" value="<?php echo esc_attr( $options['link_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="row_height"><?php _e( 'Высота строк', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="row_height" id="row_height" value="<?php echo esc_attr( $options['row_height'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="row_alignment"><?php _e( 'Выравнивание строк', 'wp-price-manager' ); ?></label></th>
                            <td>
                                <select name="row_alignment" id="row_alignment">
                                    <option value="left" <?php selected( $options['row_alignment'] ?? '', 'left' ); ?>><?php _e( 'Слева', 'wp-price-manager' ); ?></option>
                                    <option value="center" <?php selected( $options['row_alignment'] ?? '', 'center' ); ?>><?php _e( 'По центру', 'wp-price-manager' ); ?></option>
                                    <option value="right" <?php selected( $options['row_alignment'] ?? '', 'right' ); ?>><?php _e( 'Справа', 'wp-price-manager' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    <?php elseif ( $active_tab === 'icon' ) : ?>
                        <tr>
                            <th><label for="icon_char"><?php _e( 'Символ значка', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="icon_char" id="icon_char" value="<?php echo esc_attr( $options['icon_char'] ?? '' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="icon_color"><?php _e( 'Цвет значка', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="icon_color" id="icon_color" value="<?php echo esc_attr( $options['icon_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="icon_bg_color"><?php _e( 'Фон значка', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="icon_bg_color" id="icon_bg_color" value="<?php echo esc_attr( $options['icon_bg_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                    <?php elseif ( $active_tab === 'tooltip' ) : ?>
                        <tr>
                            <th><label for="tooltip_bg_color"><?php _e( 'Фон описания', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="tooltip_bg_color" id="tooltip_bg_color" value="<?php echo esc_attr( $options['tooltip_bg_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="tooltip_text_color"><?php _e( 'Цвет текста описания', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="tooltip_text_color" id="tooltip_text_color" value="<?php echo esc_attr( $options['tooltip_text_color'] ?? '' ); ?>" class="wppm-color-field"></td>
                        </tr>
                        <tr>
                            <th><label for="tooltip_border_radius"><?php _e( 'Скругление описания', 'wp-price-manager' ); ?></label></th>
                            <td><input type="text" name="tooltip_border_radius" id="tooltip_border_radius" value="<?php echo esc_attr( $options['tooltip_border_radius'] ?? '' ); ?>"></td>
                        </tr>
                    <?php endif; ?>
                </table>
                <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Сохранить', 'wp-price-manager' ); ?>"></p>
            </form>
        </div>
        <?php
    }
}

new Price_Manager_Admin();
