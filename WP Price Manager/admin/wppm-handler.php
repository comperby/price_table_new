<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ===== Категории =====

// Обработка добавления категории
function wppm_add_category() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_category_nonce', 'wppm_category_nonce_field' );
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_categories';
    $name  = sanitize_text_field( $_POST['category_name'] );
    $order = intval( $_POST['display_order'] );
    $custom = isset( $_POST['custom_table'] ) ? 1 : 0;
    $count  = $custom ? max( 2, intval( $_POST['column_count'] ) ) : 2;
    $titles = $custom && isset( $_POST['column_titles'] ) ? wp_json_encode( array_map( 'sanitize_text_field', (array) $_POST['column_titles'] ) ) : '';
    $result = $wpdb->insert( $table, array(
        'name' => $name,
        'display_order' => $order,
        'custom_table' => $custom,
        'column_count' => $count,
        'column_titles' => $titles,
    ), array( '%s', '%d', '%d', '%d', '%s' ) );
    if ( $result ) {
        delete_transient( 'wppm_categories' );
    }
    $msg = $result ? __( 'Категория добавлена.', 'wp-price-manager' ) : __( 'Ошибка добавления категории.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-categories&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_add_category', 'wppm_add_category' );

// Форма редактирования категории
function wppm_edit_category_form() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_categories';
    $id = intval( $_GET['id'] );
    $category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
    if ( ! $category ) {
        wp_die( __( 'Категория не найдена.', 'wp-price-manager' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Редактировать категорию', 'wp-price-manager' ); ?></h1>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wppm_edit_category">
            <input type="hidden" name="category_id" value="<?php echo intval($category['id']); ?>">
            <?php wp_nonce_field( 'wppm_category_nonce', 'wppm_category_nonce_field' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="category_name"><?php _e( 'Название категории', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="category_name" name="category_name" value="<?php echo esc_attr($category['name']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="display_order"><?php _e( 'Порядок отображения', 'wp-price-manager' ); ?></label></th>
                    <td><input type="number" id="display_order" name="display_order" value="<?php echo esc_attr($category['display_order']); ?>" required></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Сохранить изменения', 'wp-price-manager' ); ?>"></p>
        </form>
    </div>
    <?php
    exit;
}
add_action( 'admin_post_wppm_edit_category_form', 'wppm_edit_category_form' );

// Обработка редактирования категории
function wppm_edit_category() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_category_nonce', 'wppm_category_nonce_field' );
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_categories';
    $id = intval( $_POST['category_id'] );
    $name = sanitize_text_field( $_POST['category_name'] );
    $order = intval( $_POST['display_order'] );
    $result = $wpdb->update( $table, array(
        'name' => $name,
        'display_order' => $order,
    ), array( 'id' => $id ), array( '%s', '%d' ), array( '%d' ) );
    if ( $result !== false ) {
        delete_transient( 'wppm_categories' );
    }
    $msg = ($result !== false) ? __( 'Категория обновлена.', 'wp-price-manager' ) : __( 'Ошибка обновления категории.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-categories&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_edit_category', 'wppm_edit_category' );

// Обработка удаления категории
function wppm_delete_category() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    $id = intval( $_GET['id'] );
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wppm_delete_category_' . $id ) ) {
        wp_die( __( 'Неверный запрос.', 'wp-price-manager' ) );
    }
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_categories';
    $result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
    if ( $result ) {
        delete_transient( 'wppm_categories' );
    }
    $msg = $result ? __( 'Категория удалена.', 'wp-price-manager' ) : __( 'Ошибка удаления категории.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-categories&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_delete_category', 'wppm_delete_category' );

// ===== Услуги =====

// Обработка добавления услуги
function wppm_add_service() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_service_nonce', 'wppm_service_nonce_field' );
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $cat_table = $wpdb->prefix . 'wppm_categories';
    $pg_table  = $wpdb->prefix . 'wppm_price_groups';

    $extras_array = isset( $_POST['extras'] ) ? array_values( array_map( 'sanitize_text_field', (array) $_POST['extras'] ) ) : array();
    $name = sanitize_text_field( $_POST['service_name'] );
    if ( $name === '' && ! empty( $extras_array ) ) {
        $name = $extras_array[0];
    }
    $description = isset( $_POST['service_description'] ) ? sanitize_textarea_field( $_POST['service_description'] ) : '';
    $link = isset( $_POST['service_link'] ) ? esc_url_raw( $_POST['service_link'] ) : '';
    $price = isset( $_POST['service_price'] ) ? sanitize_text_field( $_POST['service_price'] ) : '';
   $price_group = isset( $_POST['price_group'] ) ? sanitize_text_field( $_POST['price_group'] ) : '';
   $extras = wp_json_encode( $extras_array );

    // Определяем категорию: если передано через выпадающий список – используем его, иначе пробуем по текстовому полю
    if ( isset($_POST['service_category_id']) && !empty($_POST['service_category_id']) ) {
        $category_id = intval($_POST['service_category_id']);
    } else {
        $category_name = sanitize_text_field( $_POST['service_category'] );
        $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $category_name ) );
        if ( $existing_cat ) {
            $category_id = $existing_cat->id;
        } else {
            $wpdb->insert( $cat_table, array( 'name' => $category_name, 'display_order' => 0 ), array( '%s', '%d' ) );
            $category_id = $wpdb->insert_id;
        }
    }
    // Для группы цен аналогично
    $price_group_id = 0;
    $manual = 1;
    if ( $price_group ) {
        $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $pg_table WHERE name = %s", $price_group ) );
        if ( $existing_pg ) {
            $price_group_id = $existing_pg->id;
            if ( $price === '' ) {
                $price = $existing_pg->default_price;
                $manual = 0;
            }
        } else {
            $wpdb->insert( $pg_table, array( 'name' => $price_group, 'default_price' => $price ), array( '%s', '%s' ) );
            $price_group_id = $wpdb->insert_id;
            if ( $price === '' ) {
                $manual = 0;
            }
        }
    }
    $result = $wpdb->insert( $srv_table, array(
        'name' => $name,
        'description' => $description,
        'link' => $link,
        'price' => $price,
        'manual_price' => $manual,
        'category_id' => $category_id,
        'price_group_id' => $price_group_id,
        'display_order' => 0,
        'extras' => $extras,
    ), array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s' ) );
    $msg = $result ? __( 'Услуга добавлена.', 'wp-price-manager' ) : __( 'Ошибка добавления услуги.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_add_service', 'wppm_add_service' );

// Форма редактирования услуги
function wppm_edit_service_form() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $cat_table = $wpdb->prefix . 'wppm_categories';
    $pg_table  = $wpdb->prefix . 'wppm_price_groups';

    $id = intval( $_GET['id'] );
    $service = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $srv_table WHERE id = %d", $id ), ARRAY_A );
    if ( ! $service ) {
        wp_die( __( 'Услуга не найдена.', 'wp-price-manager' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Редактировать услугу', 'wp-price-manager' ); ?></h1>
        <form id="wppm-edit-service-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wppm_edit_service">
            <input type="hidden" name="service_id" value="<?php echo intval($service['id']); ?>">
            <?php wp_nonce_field( 'wppm_service_nonce', 'wppm_service_nonce_field' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="service_name"><?php _e( 'Название услуги', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="service_name" name="service_name" value="<?php echo esc_attr($service['name']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="service_description"><?php _e( 'Описание', 'wp-price-manager' ); ?></label></th>
                    <td><textarea id="service_description" name="service_description"><?php echo esc_textarea($service['description']); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="service_link"><?php _e( 'Ссылка', 'wp-price-manager' ); ?></label></th>
                    <td><input type="url" id="service_link" name="service_link" value="<?php echo esc_attr($service['link']); ?>"></td>
                </tr>
                <tr id="service_price_row">
                    <th><label for="service_price"><?php _e( 'Цена (BYN)', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="service_price" name="service_price" value="<?php echo esc_attr($service['price']); ?>"></td>
                </tr>
                <tr>
                    <th><label for="price_group"><?php _e( 'Группа цен', 'wp-price-manager' ); ?></label></th>
                    <td>
                        <?php
                        $pg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $pg_table WHERE id = %d", $service['price_group_id'] ) );
                        $pg_name = $pg ? $pg->name : '';
                        ?>
                        <input type="text" id="price_group" name="price_group" value="<?php echo esc_attr($pg_name); ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="service_category"><?php _e( 'Категория', 'wp-price-manager' ); ?></label></th>
                    <td>
                        <?php
                        $cat = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $cat_table WHERE id = %d", $service['category_id'] ) );
                        $cat_name = $cat ? $cat->name : '';
                        ?>
                        <input type="text" id="service_category" name="service_category" value="<?php echo esc_attr($cat_name); ?>" required>
                    </td>
                </tr>
                <tr id="wppm-extras-row" style="display:none;">
                    <th><?php _e( 'Дополнительные поля', 'wp-price-manager' ); ?></th>
                    <td id="wppm-extras-container"></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Сохранить изменения', 'wp-price-manager' ); ?>"></p>
        </form>
        <script type="text/javascript">
        <?php $decoded_extras = json_decode( $service['extras'], true ); ?>
        var wppm_initial_extras = <?php echo wp_json_encode( is_array( $decoded_extras ) ? array_values( $decoded_extras ) : array() ); ?>;
        jQuery(function($){
            var cat = $('#service_category').val();
            if(cat && window.wppm_load_extras){
                wppm_load_extras(cat, wppm_initial_extras);
            }
        });
        </script>
    </div>
    <?php
    exit;
}
add_action( 'admin_post_wppm_edit_service_form', 'wppm_edit_service_form' );

// Обработка редактирования услуги
function wppm_edit_service() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_service_nonce', 'wppm_service_nonce_field' );
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $cat_table = $wpdb->prefix . 'wppm_categories';
    $pg_table  = $wpdb->prefix . 'wppm_price_groups';

    $id = intval($_POST['service_id']);
    $extras_array = isset( $_POST['extras'] ) ? array_values( array_map( 'sanitize_text_field', (array) $_POST['extras'] ) ) : array();
    $name = sanitize_text_field( $_POST['service_name'] );
    if ( $name === '' && ! empty( $extras_array ) ) {
        $name = $extras_array[0];
    }
    $description = isset( $_POST['service_description'] ) ? sanitize_textarea_field( $_POST['service_description'] ) : '';
    $link = isset( $_POST['service_link'] ) ? esc_url_raw( $_POST['service_link'] ) : '';
    $price = isset( $_POST['service_price'] ) ? sanitize_text_field( $_POST['service_price'] ) : '';
    $price_group = isset( $_POST['price_group'] ) ? sanitize_text_field( $_POST['price_group'] ) : '';
    $category = sanitize_text_field( $_POST['service_category'] );
    $extras = wp_json_encode( $extras_array );

    // Обновляем категорию (если не существует, создаём)
    $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $category ) );
    if ( $existing_cat ) {
        $category_id = $existing_cat->id;
    } else {
        $wpdb->insert( $cat_table, array( 'name' => $category, 'display_order' => 0 ), array( '%s', '%d' ) );
        $category_id = $wpdb->insert_id;
    }
    // Аналогично для группы цен
    $price_group_id = 0;
    $manual = 1;
    if ( $price_group ) {
        $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $pg_table WHERE name = %s", $price_group ) );
        if ( $existing_pg ) {
            $price_group_id = $existing_pg->id;
            if ( $price === '' ) {
                $price = $existing_pg->default_price;
                $manual = 0;
            }
        } else {
            $wpdb->insert( $pg_table, array( 'name' => $price_group, 'default_price' => $price ), array( '%s', '%s' ) );
            $price_group_id = $wpdb->insert_id;
            if ( $price === '' ) {
                $manual = 0;
            }
        }
    }
    $result = $wpdb->update( $srv_table, array(
        'name' => $name,
        'description' => $description,
        'link' => $link,
        'price' => $price,
        'manual_price' => $manual,
        'category_id' => $category_id,
        'price_group_id' => $price_group_id,
        'extras' => $extras,
    ), array( 'id' => $id ), array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s' ), array( '%d' ) );
    $msg = ($result !== false) ? __( 'Услуга обновлена.', 'wp-price-manager' ) : __( 'Ошибка обновления услуги.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_edit_service', 'wppm_edit_service' );

// Обработка удаления услуги
function wppm_delete_service() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    $id = intval($_GET['id']);
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wppm_delete_service_' . $id ) ) {
        wp_die( __( 'Неверный запрос.', 'wp-price-manager' ) );
    }
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $result = $wpdb->delete( $srv_table, array( 'id' => $id ), array( '%d' ) );
    $msg = $result ? __( 'Услуга удалена.', 'wp-price-manager' ) : __( 'Ошибка удаления услуги.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_delete_service', 'wppm_delete_service' );

// ===== Группа цен =====

// Обработка добавления группы цен
function wppm_add_price_group() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_price_group_nonce', 'wppm_price_group_nonce_field' );
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_price_groups';
    $name = sanitize_text_field( $_POST['price_group_name'] );
    $default_price = sanitize_text_field( $_POST['default_price'] );
    $result = $wpdb->insert( $table, array(
        'name' => $name,
        'default_price' => $default_price,
    ), array( '%s', '%s' ) );
    if ( $result ) {
        delete_transient( 'wppm_price_groups' );
    }
    $msg = $result ? __( 'Группа цен добавлена.', 'wp-price-manager' ) : __( 'Ошибка добавления группы цен.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-price-groups&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_add_price_group', 'wppm_add_price_group' );

// Форма редактирования группы цен
function wppm_edit_price_group_form() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_price_groups';
    $id = intval( $_GET['id'] );
    $group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
    if ( ! $group ) {
        wp_die( __( 'Группа цен не найдена.', 'wp-price-manager' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Редактировать группу цен', 'wp-price-manager' ); ?></h1>
        <form id="wppm-edit-price-group-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wppm_edit_price_group">
            <input type="hidden" name="price_group_id" value="<?php echo intval($group['id']); ?>">
            <?php wp_nonce_field( 'wppm_price_group_nonce', 'wppm_price_group_nonce_field' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="price_group_name"><?php _e( 'Название группы цен', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="price_group_name" name="price_group_name" value="<?php echo esc_attr($group['name']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="default_price"><?php _e( 'Цена по умолчанию', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="default_price" name="default_price" value="<?php echo esc_attr($group['default_price']); ?>" required></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Сохранить изменения', 'wp-price-manager' ); ?>"></p>
        </form>
    </div>
    <?php
    exit;
}
add_action( 'admin_post_wppm_edit_price_group_form', 'wppm_edit_price_group_form' );

// Обработка редактирования группы цен
function wppm_edit_price_group() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_price_group_nonce', 'wppm_price_group_nonce_field' );
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_price_groups';
    $id = intval( $_POST['price_group_id'] );
    $name = sanitize_text_field( $_POST['price_group_name'] );
    $default_price = sanitize_text_field( $_POST['default_price'] );
    $result = $wpdb->update( $table, array(
        'name' => $name,
        'default_price' => $default_price,
    ), array( 'id' => $id ), array( '%s', '%s' ), array( '%d' ) );
    if ( $result !== false ) {
        delete_transient( 'wppm_price_groups' );
        // Обновляем связанные услуги (если цена не задана вручную)
        $srv_table = $wpdb->prefix . 'wppm_services';
        $wpdb->query( $wpdb->prepare( "UPDATE $srv_table SET price = %s WHERE price_group_id = %d AND manual_price = 0", $default_price, $id ) );
        $msg = __( 'Группа цен обновлена.', 'wp-price-manager' );
    } else {
        $msg = __( 'Ошибка обновления группы цен.', 'wp-price-manager' );
    }
    wp_redirect( admin_url( 'admin.php?page=price-manager-price-groups&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_edit_price_group', 'wppm_edit_price_group' );

// Обработка удаления группы цен
function wppm_delete_price_group() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    $id = intval($_GET['id']);
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wppm_delete_price_group_' . $id ) ) {
        wp_die( __( 'Неверный запрос.', 'wp-price-manager' ) );
    }
    global $wpdb;
    $table = $wpdb->prefix . 'wppm_price_groups';
    $result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
    if ( $result ) {
        delete_transient( 'wppm_price_groups' );
    }
    $msg = $result ? __( 'Группа цен удалена.', 'wp-price-manager' ) : __( 'Ошибка удаления группы цен.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-price-groups&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_delete_price_group', 'wppm_delete_price_group' );

function wppm_update_services_order() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_services_order_nonce', 'wppm_services_order_nonce_field' );
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $category_id = intval( $_POST['category_id'] );
    $new_order = sanitize_text_field( $_POST['new_order'] ); // ожидается строка "5,3,7,2" и т.д.
    if ( empty( $new_order ) ) {
        wp_redirect( admin_url( 'admin.php?page=price-manager-services&msg=' . urlencode( __( 'Порядок не изменен', 'wp-price-manager' ) ) ) );
        exit;
    }
    $ids = explode( ',', $new_order );
    foreach ( $ids as $order => $id ) {
        $wpdb->update( $srv_table, array( 'display_order' => $order ), array( 'id' => intval($id), 'category_id' => $category_id ), array( '%d' ), array( '%d', '%d' ) );
    }
    $msg = __( 'Порядок услуг обновлен', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&prefill_category=' . $category_id . '&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_update_services_order', 'wppm_update_services_order' );

// Сохранение настроек стиля таблицы
function wppm_save_style_settings() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    check_admin_referer( 'wppm_style_settings' );
    $device  = isset( $_POST['device'] ) ? sanitize_text_field( $_POST['device'] ) : 'desktop';
    $suffix  = $device === 'mobile' ? '_mobile' : '';
    $options = get_option( 'wppm_style_settings', array() );
    foreach ( array(
        'border_width', 'border_color', 'border_style', 'border_apply', 'border_radius',
        'header_bg_color', 'header_text_color', 'header_height', 'header_alignment',
        'even_row_bg_color', 'odd_row_bg_color', 'text_font', 'text_size', 'text_weight', 'text_padding', 'text_color', 'header_text_size', 'header_text_weight', 'link_color', 'row_height', 'row_alignment',
        'icon_char', 'icon_color', 'icon_bg_color', 'icon_size', 'icon_offset_x', 'icon_offset_y',
        'tooltip_bg_color', 'tooltip_text_color', 'tooltip_border_radius',
        'show_more_text', 'show_more_bg', 'show_more_color',
        'show_more_padding', 'show_more_radius', 'show_more_font_size', 'show_more_width', 'show_more_height', 'show_more_font_family', 'show_more_font_weight', 'show_more_align', 'show_less_text', 'show_more_speed', 'show_limit', 'use_google_font'
    ) as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            $options[ $key . $suffix ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
        }
    }
    update_option( 'wppm_style_settings', $options );
    $tab    = isset( $_POST['current_tab'] ) ? sanitize_text_field( $_POST['current_tab'] ) : 'table';
    $msg    = urlencode( __( 'Настройки сохранены', 'wp-price-manager' ) );
    wp_redirect( admin_url( 'admin.php?page=price-manager-style&tab=' . $tab . '&device=' . $device . '&msg=' . $msg ) );
    exit;
}
add_action( 'admin_post_wppm_save_style_settings', 'wppm_save_style_settings' );