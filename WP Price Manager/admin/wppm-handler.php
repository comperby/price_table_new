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
    $name = sanitize_text_field( $_POST['category_name'] );
    $order = intval( $_POST['display_order'] );
    $result = $wpdb->insert( $table, array(
        'name' => $name,
        'display_order' => $order,
    ), array( '%s', '%d' ) );
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

    $name = sanitize_text_field( $_POST['service_name'] );
    $description = sanitize_textarea_field( $_POST['service_description'] );
    $link = esc_url_raw( $_POST['service_link'] );
    $price = floatval( $_POST['service_price'] );
    $price_group = sanitize_text_field( $_POST['price_group'] );

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
    $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $pg_table WHERE name = %s", $price_group ) );
    if ( $existing_pg ) {
        $price_group_id = $existing_pg->id;
    } else {
        $wpdb->insert( $pg_table, array( 'name' => $price_group, 'default_price' => $price ), array( '%s', '%f' ) );
        $price_group_id = $wpdb->insert_id;
    }
    $result = $wpdb->insert( $srv_table, array(
        'name' => $name,
        'description' => $description,
        'link' => $link,
        'price' => $price,
        'manual_price' => 1,
        'category_id' => $category_id,
        'price_group_id' => $price_group_id,
        'display_order' => 0,
    ), array( '%s', '%s', '%s', '%f', '%d', '%d', '%d', '%d' ) );
    $msg = $result ? __( 'Услуга добавлена.', 'wp-price-manager' ) : __( 'Ошибка добавления услуги.', 'wp-price-manager' );
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&msg=' . urlencode( $msg ) ) );
    exit;
}
add_action( 'admin_post_wppm_add_service', 'wppm_add_service' );

// Форма быстрого добавления услуги из категории
function wppm_add_service_form() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    global $wpdb;
    $cat_table = $wpdb->prefix . 'wppm_categories';

    $prefill_category = isset( $_GET['category_id'] ) ? intval( $_GET['category_id'] ) : 0;
    $category = null;
    if ( $prefill_category ) {
        $category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $cat_table WHERE id = %d", $prefill_category ) );
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Добавить услугу', 'wp-price-manager' ); ?></h1>
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
                        <?php if ( $category ) : ?>
                            <input type="hidden" name="service_category_id" value="<?php echo intval( $category->id ); ?>">
                            <input type="text" value="<?php echo esc_attr( $category->name ); ?>" disabled>
                        <?php else : ?>
                            <input type="text" id="service_category" name="service_category" required>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Добавить услугу', 'wp-price-manager' ); ?>"></p>
        </form>
    </div>
    <?php
    exit;
}
add_action( 'admin_post_wppm_add_service_form', 'wppm_add_service_form' );

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
        <form id="wppm-edit-service-form" method="post" action="">
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
                    <td><textarea id="service_description" name="service_description" required><?php echo esc_textarea($service['description']); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="service_link"><?php _e( 'Ссылка', 'wp-price-manager' ); ?></label></th>
                    <td><input type="url" id="service_link" name="service_link" value="<?php echo esc_attr($service['link']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="service_price"><?php _e( 'Цена (BYN)', 'wp-price-manager' ); ?></label></th>
                    <td><input type="number" step="0.01" id="service_price" name="service_price" value="<?php echo esc_attr($service['price']); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="price_group"><?php _e( 'Группа цен', 'wp-price-manager' ); ?></label></th>
                    <td>
                        <?php
                        $pg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $pg_table WHERE id = %d", $service['price_group_id'] ) );
                        $pg_name = $pg ? $pg->name : '';
                        ?>
                        <input type="text" id="price_group" name="price_group" value="<?php echo esc_attr($pg_name); ?>" required>
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
            </table>
            <p class="submit"><input type="submit" class="button button-primary" value="<?php _e( 'Сохранить изменения', 'wp-price-manager' ); ?>"></p>
        </form>
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
    $name = sanitize_text_field( $_POST['service_name'] );
    $description = sanitize_textarea_field( $_POST['service_description'] );
    $link = esc_url_raw( $_POST['service_link'] );
    $price = floatval( $_POST['service_price'] );
    $price_group = sanitize_text_field( $_POST['price_group'] );
    $category = sanitize_text_field( $_POST['service_category'] );

    // Обновляем категорию (если не существует, создаём)
    $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $category ) );
    if ( $existing_cat ) {
        $category_id = $existing_cat->id;
    } else {
        $wpdb->insert( $cat_table, array( 'name' => $category, 'display_order' => 0 ), array( '%s', '%d' ) );
        $category_id = $wpdb->insert_id;
    }
    // Аналогично для группы цен
    $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $pg_table WHERE name = %s", $price_group ) );
    if ( $existing_pg ) {
        $price_group_id = $existing_pg->id;
    } else {
        $wpdb->insert( $pg_table, array( 'name' => $price_group, 'default_price' => $price ), array( '%s', '%f' ) );
        $price_group_id = $wpdb->insert_id;
    }
    $result = $wpdb->update( $srv_table, array(
        'name' => $name,
        'description' => $description,
        'link' => $link,
        'price' => $price,
        'manual_price' => 1,
        'category_id' => $category_id,
        'price_group_id' => $price_group_id,
    ), array( 'id' => $id ), array( '%s', '%s', '%s', '%f', '%d', '%d', '%d' ), array( '%d' ) );
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
    $default_price = floatval( $_POST['default_price'] );
    $result = $wpdb->insert( $table, array(
        'name' => $name,
        'default_price' => $default_price,
    ), array( '%s', '%f' ) );
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
        <form id="wppm-edit-price-group-form" method="post" action="">
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
                    <td><input type="number" step="0.01" id="default_price" name="default_price" value="<?php echo esc_attr($group['default_price']); ?>" required></td>
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
    $default_price = floatval( $_POST['default_price'] );
    $result = $wpdb->update( $table, array(
        'name' => $name,
        'default_price' => $default_price,
    ), array( 'id' => $id ), array( '%s', '%f' ), array( '%d' ) );
    if ( $result !== false ) {
        // Обновляем связанные услуги (если цена не задана вручную)
        $srv_table = $wpdb->prefix . 'wppm_services';
        $wpdb->query( $wpdb->prepare( "UPDATE $srv_table SET price = %f WHERE price_group_id = %d AND manual_price = 0", $default_price, $id ) );
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