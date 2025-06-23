<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_wppm_ajax_action', 'wppm_handle_ajax' );
function wppm_handle_ajax() {
    check_ajax_referer( 'wppm_nonce', 'nonce' );
    global $wpdb;
    $response = array();
    $wppm_type = isset( $_POST['wppm_type'] ) ? sanitize_text_field( $_POST['wppm_type'] ) : '';

    switch ( $wppm_type ) {

        // --- Категории ---
        case 'add_category':
            $name  = sanitize_text_field( $_POST['category_name'] );
            $table = $wpdb->prefix . 'wppm_categories';
            $result = $wpdb->insert( $table, array(
                'name'          => $name,
                'display_order' => 0,
            ), array( '%s', '%d' ) );
            if ( $result ) {
                $response = array( 'success' => true, 'message' => __( 'Категория добавлена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка добавления категории.', 'wp-price-manager' ) );
            }
            break;

        case 'edit_category':
            $id   = intval( $_POST['id'] );
            $name = sanitize_text_field( $_POST['category_name'] );
            $table = $wpdb->prefix . 'wppm_categories';
            $result = $wpdb->update( $table, array( 'name' => $name ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
            if ( $result !== false ) {
                $response = array( 'success' => true, 'message' => __( 'Категория обновлена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка обновления категории.', 'wp-price-manager' ) );
            }
            break;

        case 'delete_category':
            $id    = intval( $_POST['id'] );
            $table = $wpdb->prefix . 'wppm_categories';
            $result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
            if ( $result ) {
                $response = array( 'success' => true, 'message' => __( 'Категория удалена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка удаления категории.', 'wp-price-manager' ) );
            }
            break;

        case 'reorder_categories':
            $order = isset( $_POST['order'] ) ? $_POST['order'] : array();
            $table = $wpdb->prefix . 'wppm_categories';
            foreach ( $order as $display_order => $id ) {
                $wpdb->update( $table, array( 'display_order' => $display_order ), array( 'id' => intval( $id ) ), array( '%d' ), array( '%d' ) );
            }
            $response = array( 'success' => true, 'message' => __( 'Порядок категорий обновлен.', 'wp-price-manager' ) );
            break;

        case 'get_categories':
            $table = $wpdb->prefix . 'wppm_categories';
            $categories = $wpdb->get_results( "SELECT id, name FROM $table ORDER BY display_order ASC", ARRAY_A );
            $response = array( 'success' => true, 'categories' => $categories );
            break;

        // --- Группа цен ---
        case 'add_price_group':
            $name          = sanitize_text_field( $_POST['price_group_name'] );
            $default_price = floatval( $_POST['default_price'] );
            $pg_table = $wpdb->prefix . 'wppm_price_groups';
            $result = $wpdb->insert( $pg_table, array(
                'name'          => $name,
                'default_price' => $default_price,
            ), array( '%s', '%f' ) );
            if ( $result ) {
                $response = array( 'success' => true, 'message' => __( 'Группа цен добавлена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка добавления группы цен.', 'wp-price-manager' ) );
            }
            break;

        case 'edit_price_group':
            $id            = intval( $_POST['id'] );
            $name          = sanitize_text_field( $_POST['price_group_name'] );
            $default_price = floatval( $_POST['default_price'] );
            $pg_table = $wpdb->prefix . 'wppm_price_groups';
            $confirm = isset( $_POST['confirm'] ) ? intval( $_POST['confirm'] ) : 0;
            if ( ! $confirm ) {
                $srv_table = $wpdb->prefix . 'wppm_services';
                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $srv_table WHERE price_group_id = %d", $id ) );
                if ( $count > 0 ) {
                    $response = array(
                        'success'    => false,
                        'message'    => sprintf( __( 'Изменение цены группы затронет %d услуг. Подтвердите изменение.', 'wp-price-manager' ), $count ),
                        'need_confirm' => true
                    );
                    wp_send_json( $response );
                    exit;
                }
            }
            $result = $wpdb->update( $pg_table, array(
                'name'          => $name,
                'default_price' => $default_price,
            ), array( 'id' => $id ), array( '%s', '%f' ), array( '%d' ) );
            if ( $result !== false ) {
                $srv_table = $wpdb->prefix . 'wppm_services';
                $wpdb->query( $wpdb->prepare(
                    "UPDATE $srv_table SET price = %f WHERE price_group_id = %d AND manual_price = 0",
                    $default_price, $id
                ) );
                $response = array( 'success' => true, 'message' => __( 'Группа цен обновлена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка обновления группы цен.', 'wp-price-manager' ) );
            }
            break;

        case 'delete_price_group':
            $id = intval( $_POST['id'] );
            $pg_table = $wpdb->prefix . 'wppm_price_groups';
            $result = $wpdb->delete( $pg_table, array( 'id' => $id ), array( '%d' ) );
            if ( $result ) {
                $response = array( 'success' => true, 'message' => __( 'Группа цен удалена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка удаления группы цен.', 'wp-price-manager' ) );
            }
            break;

        case 'get_price_groups':
            $pg_table = $wpdb->prefix . 'wppm_price_groups';
            $price_groups = $wpdb->get_results( "SELECT id, name FROM $pg_table", ARRAY_A );
            $response = array( 'success' => true, 'get_price_groups' => $price_groups );
            break;

        default:
            $response = array( 'success' => false, 'message' => __( 'Неизвестный запрос.', 'wp-price-manager' ) );
            break;
    }

    wp_send_json( $response );
}
