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
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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
                        <th><?php _e( 'ID', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Название', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Порядок', 'wp-price-manager' ); ?></th>
                        <th><?php _e( 'Действия', 'wp-price-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $categories ) : ?>
                        <?php foreach ( $categories as $cat ) : ?>
                            <tr>
                                <td><?php echo esc_html( $cat['id'] ); ?></td>
                                <td><?php echo esc_html( $cat['name'] ); ?></td>
                                <td><?php echo esc_html( $cat['display_order'] ); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin-post.php?action=wppm_edit_category_form&id=' . intval($cat['id'])); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
                                    <a href="<?php echo admin_url('admin-post.php?action=wppm_delete_category&id=' . intval($cat['id']) . '&_wpnonce=' . wp_create_nonce('wppm_delete_category_' . intval($cat['id']))); ?>" onclick="return confirm('<?php _e('Вы уверены?', 'wp-price-manager'); ?>');"><?php _e( 'Удалить', 'wp-price-manager' ); ?></a> |
                                    <a href="<?php echo admin_url('admin.php?page=price-manager-services&prefill_category=' . intval($cat['id'])); ?>"><?php _e( 'Посмотреть услуги', 'wp-price-manager' ); ?></a> |
                                    <a href="<?php echo admin_url('admin-post.php?action=wppm_add_service_form&category_id=' . intval($cat['id'])); ?>"><?php _e( 'Быстро добавить услугу', 'wp-price-manager' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4"><?php _e( 'Категории не найдены', 'wp-price-manager' ); ?></td>
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
    
    // Если передана категория для быстрого добавления – фильтруем по ней
    $prefill_category = isset($_GET['prefill_category']) ? intval($_GET['prefill_category']) : 0;
    
    if ( $prefill_category ) {
        $services = $wpdb->get_results( $wpdb->prepare(
            "SELECT s.*, c.name as category_name, pg.name as price_group_name 
             FROM $srv_table s 
             LEFT JOIN $cat_table c ON s.category_id = c.id 
             LEFT JOIN $pg_table pg ON s.price_group_id = pg.id 
             WHERE s.category_id = %d ORDER BY s.display_order ASC", $prefill_category
        ), ARRAY_A );
    } else {
        $services = $wpdb->get_results("SELECT s.*, c.name as category_name, pg.name as price_group_name FROM $srv_table s LEFT JOIN $cat_table c ON s.category_id = c.id LEFT JOIN $pg_table pg ON s.price_group_id = pg.id ORDER BY s.display_order ASC", ARRAY_A);
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Все услуги', 'wp-price-manager' ); ?></h1>
        <h2><?php _e( 'Добавить новую услугу', 'wp-price-manager' ); ?></h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wppm_add_service">
            <?php wp_nonce_field( 'wppm_service_nonce', 'wppm_service_nonce_field' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="service_name"><?php _e( 'Название услуги', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="service_name" name="service_name" required></td>
                </tr>
                <tr>
                    <th><label for="service_description"><?php _e( 'Описание', 'wp-price-manager' ); ?></label></th>
                    <td><textarea id="service_description" name="service_description" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="service_link"><?php _e( 'Ссылка', 'wp-price-manager' ); ?></label></th>
                    <td><input type="url" id="service_link" name="service_link" required></td>
                </tr>
                <tr>
                    <th><label for="service_price"><?php _e( 'Цена (BYN)', 'wp-price-manager' ); ?></label></th>
                    <td><input type="number" step="0.01" id="service_price" name="service_price" required></td>
                </tr>
                <tr>
                    <th><label for="price_group"><?php _e( 'Группа цен', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="price_group" name="price_group" required></td>
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
                            $categories = $wpdb->get_results( "SELECT * FROM $cat_table ORDER BY display_order ASC", ARRAY_A );
                            echo '<select name="service_category_id" required>';
                            echo '<option value="">' . __( 'Выберите категорию', 'wp-price-manager' ) . '</option>';
                            if ( $categories ) {
                                foreach ( $categories as $cat_item ) {
                                    echo '<option value="' . intval($cat_item['id']) . '">' . esc_html($cat_item['name']) . '</option>';
                                }
                            }
                            echo '</select>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Добавить услугу', 'wp-price-manager' ); ?>"></p>
        </form>
        <hr>
        <h2><?php _e( 'Список услуг', 'wp-price-manager' ); ?></h2>
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
                    <th><?php _e( 'Описание', 'wp-price-manager' ); ?></th>
                    <th><?php _e( 'Ссылка', 'wp-price-manager' ); ?></th>
                    <th><?php _e( 'Цена', 'wp-price-manager' ); ?></th>
                    <th><?php _e( 'Категория', 'wp-price-manager' ); ?></th>
                    <th><?php _e( 'Группа цен', 'wp-price-manager' ); ?></th>
                    <th><?php _e( 'Действия', 'wp-price-manager' ); ?></th>
                </tr>
            </thead>
            <tbody <?php if ( $prefill_category ) echo 'id="wppm-services-sortable"'; ?>>
                <?php if ( $services ) : ?>
                    <?php foreach ( $services as $srv ) : ?>
                        <tr data-id="<?php echo intval($srv['id']); ?>">
                            <?php if ( $prefill_category ) : ?>
                                <td class="wppm-drag-handle" style="cursor: move;">⇅</td>
                            <?php endif; ?>
                            <td><?php echo esc_html( $srv['id'] ); ?></td>
                            <td><?php echo esc_html( $srv['name'] ); ?></td>
                            <td><?php echo esc_html( $srv['description'] ); ?></td>
                            <td>
                                <?php if( ! empty( $srv['link'] ) ): ?>
                                    <a href="<?php echo esc_url( $srv['link'] ); ?>" target="_blank"><?php echo esc_html( $srv['link'] ); ?></a>
                                <?php else: ?>
                                    <?php _e( 'Нет ссылки', 'wp-price-manager' ); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $srv['price'] ); ?></td>
                            <td><?php echo esc_html( $srv['category_name'] ); ?></td>
                            <td><?php echo esc_html( $srv['price_group_name'] ); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin-post.php?action=wppm_edit_service_form&id=' . intval($srv['id'])); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
                                <a href="<?php echo admin_url('admin-post.php?action=wppm_delete_service&id=' . intval($srv['id']) . '&_wpnonce=' . wp_create_nonce('wppm_delete_service_' . intval($srv['id']))); ?>" onclick="return confirm('<?php _e('Вы уверены?', 'wp-price-manager'); ?>');"><?php _e( 'Удалить', 'wp-price-manager' ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?php echo $prefill_category ? '9' : '8'; ?>"><?php _e( 'Услуги не найдены', 'wp-price-manager' ); ?></td>
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
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="wppm_add_price_group">
                <?php wp_nonce_field( 'wppm_price_group_nonce', 'wppm_price_group_nonce_field' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="price_group_name"><?php _e( 'Название группы цен', 'wp-price-manager' ); ?></label></th>
                        <td><input type="text" id="price_group_name" name="price_group_name" required></td>
                    </tr>
                    <tr>
                        <th><label for="default_price"><?php _e( 'Цена по умолчанию', 'wp-price-manager' ); ?></label></th>
                        <td><input type="number" step="0.01" id="default_price" name="default_price" required></td>
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
                            <tr>
                                <td><?php echo esc_html( $pg['id'] ); ?></td>
                                <td><?php echo esc_html( $pg['name'] ); ?></td>
                                <td><?php echo esc_html( $pg['default_price'] ); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin-post.php?action=wppm_edit_price_group_form&id=' . intval($pg['id'])); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
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
}

new Price_Manager_Admin();
