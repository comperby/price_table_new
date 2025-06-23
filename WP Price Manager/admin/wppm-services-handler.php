<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Обработка добавления новой услуги
function wppm_handle_add_service() {
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
    $category = sanitize_text_field( $_POST['service_category'] );

    // Если указанной категории нет – создаём её
    $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $category ) );
    if ( ! $existing_cat ) {
        $wpdb->insert( $cat_table, array( 'name' => $category, 'display_order' => 0 ), array( '%s', '%d' ) );
        $category_id = $wpdb->insert_id;
    } else {
        $category_id = $existing_cat->id;
    }

    // Если указанной группы цен нет – создаём её
    $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $pg_table WHERE name = %s", $price_group ) );
    if ( ! $existing_pg ) {
        $wpdb->insert( $pg_table, array( 'name' => $price_group, 'default_price' => $price ), array( '%s', '%f' ) );
        $price_group_id = $wpdb->insert_id;
    } else {
        $price_group_id = $existing_pg->id;
    }

    $result = $wpdb->insert( $srv_table, array(
        'name'          => $name,
        'description'   => $description,
        'link'          => $link,
        'price'         => $price,
        'manual_price'  => 1,
        'category_id'   => $category_id,
        'price_group_id'=> $price_group_id,
        'display_order' => 0,
    ), array( '%s', '%s', '%s', '%f', '%d', '%d', '%d', '%d' ) );

    if ( $result ) {
        $message = urlencode( __( 'Услуга добавлена.', 'wp-price-manager' ) );
    } else {
        $message = urlencode( __( 'Ошибка добавления услуги.', 'wp-price-manager' ) );
    }
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&message=' . $message ) );
    exit;
}
add_action( 'admin_post_wppm_add_service', 'wppm_handle_add_service' );

// Обработка удаления услуги
function wppm_handle_delete_service() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wppm_delete_service_' . $id ) ) {
        wp_die( __( 'Неверный запрос', 'wp-price-manager' ) );
    }
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $result = $wpdb->delete( $srv_table, array( 'id' => $id ), array( '%d' ) );
    if ( $result ) {
        $message = urlencode( __( 'Услуга удалена.', 'wp-price-manager' ) );
    } else {
        $message = urlencode( __( 'Ошибка удаления услуги.', 'wp-price-manager' ) );
    }
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&message=' . $message ) );
    exit;
}
add_action( 'admin_post_wppm_delete_service', 'wppm_handle_delete_service' );

// Отображение формы редактирования услуги
function wppm_handle_edit_service_form() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Недостаточно прав', 'wp-price-manager' ) );
    }
    global $wpdb;
    $srv_table = $wpdb->prefix . 'wppm_services';
    $cat_table = $wpdb->prefix . 'wppm_categories';
    $pg_table  = $wpdb->prefix . 'wppm_price_groups';

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $service = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $srv_table WHERE id = %d", $id ), ARRAY_A );
    if ( ! $service ) {
        wp_die( __( 'Услуга не найдена.', 'wp-price-manager' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php _e( 'Редактировать услугу', 'wp-price-manager' ); ?></h1>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <table class="form-table">
                <tr>
                    <th><label for="service_name"><?php _e( 'Название услуги', 'wp-price-manager' ); ?></label></th>
                    <td><input type="text" id="service_name" name="service_name" value="<?php echo esc_attr( $service['name'] ); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="service_description"><?php _e( 'Описание', 'wp-price-manager' ); ?></label></th>
                    <td><textarea id="service_description" name="service_description" required><?php echo esc_textarea( $service['description'] ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="service_link"><?php _e( 'Ссылка', 'wp-price-manager' ); ?></label></th>
                    <td><input type="url" id="service_link" name="service_link" value="<?php echo esc_attr( $service['link'] ); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="service_price"><?php _e( 'Цена (BYN)', 'wp-price-manager' ); ?></label></th>
                    <td><input type="number" step="0.01" id="service_price" name="service_price" value="<?php echo esc_attr( $service['price'] ); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="price_group"><?php _e( 'Группа цен', 'wp-price-manager' ); ?></label></th>
                    <td>
                        <input type="text" id="price_group" name="price_group" value="<?php
                        $pg = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $pg_table WHERE id = %d", $service['price_group_id'] ) );
                        echo esc_attr( $pg ? $pg->name : '' );
                        ?>" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="service_category"><?php _e( 'Категория', 'wp-price-manager' ); ?></label></th>
                    <td>
                        <input type="text" id="service_category" name="service_category" value="<?php
                        $cat = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $cat_table WHERE id = %d", $service['category_id'] ) );
                        echo esc_attr( $cat ? $cat->name : '' );
                        ?>" required>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="service_id" value="<?php echo intval($service['id']); ?>">
            <input type="hidden" name="action" value="wppm_edit_service">
            <?php wp_nonce_field( 'wppm_service_nonce', 'wppm_service_nonce_field' ); ?>
            <button type="submit" class="button button-primary"><?php _e( 'Сохранить изменения', 'wp-price-manager' ); ?></button>
        </form>
    </div>
    <?php
}
add_action( 'admin_post_wppm_edit_service_form', 'wppm_handle_edit_service_form' );

// Обработка редактирования услуги
function wppm_handle_edit_service() {
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

    // Проверяем наличие категории
    $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $category ) );
    if ( ! $existing_cat ) {
        $wpdb->insert( $cat_table, array( 'name' => $category, 'display_order' => 0 ), array( '%s', '%d' ) );
        $category_id = $wpdb->insert_id;
    } else {
        $category_id = $existing_cat->id;
    }

    // Проверяем наличие группы цен
    $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $pg_table WHERE name = %s", $price_group ) );
    if ( ! $existing_pg ) {
        $wpdb->insert( $pg_table, array( 'name' => $price_group, 'default_price' => $price ), array( '%s', '%f' ) );
        $price_group_id = $wpdb->insert_id;
    } else {
        $price_group_id = $existing_pg->id;
    }

    $result = $wpdb->update( $srv_table, array(
        'name'          => $name,
        'description'   => $description,
        'link'          => $link,
        'price'         => $price,
        'manual_price'  => 1,
        'category_id'   => $category_id,
        'price_group_id'=> $price_group_id,
    ), array( 'id' => $id ), array( '%s','%s','%s','%f','%d','%d','%d' ), array( '%d' ) );

    if ( $result !== false ) {
        $message = urlencode( __( 'Услуга обновлена.', 'wp-price-manager' ) );
    } else {
        $message = urlencode( __( 'Ошибка обновления услуги.', 'wp-price-manager' ) );
    }
    wp_redirect( admin_url( 'admin.php?page=price-manager-services&message=' . $message ) );
    exit;
}
add_action( 'admin_post_wppm_edit_service', 'wppm_handle_edit_service' );
