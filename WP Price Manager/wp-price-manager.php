<?php
/*
Plugin Name: WP Price Manager
Description: Плагин для управления прайс-листом с возможностью группировки услуг, работы с категориями, группами цен и интеграцией в Elementor.
Version: 1.0
Author: Your Name
Text Domain: wp-price-manager
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wppm_db_version;
$wppm_db_version = '1.0';

define( 'WPPM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Функция активации плагина (создание таблиц)
function wppm_install() {
    global $wpdb, $wppm_db_version;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_categories = $wpdb->prefix . 'wppm_categories';
    $sql_categories = "CREATE TABLE $table_categories (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        display_order mediumint(9) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $table_services = $wpdb->prefix . 'wppm_services';
    $sql_services = "CREATE TABLE $table_services (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        link varchar(255),
        price decimal(10,2) DEFAULT NULL,
        manual_price tinyint(1) DEFAULT 0,
        category_id mediumint(9) DEFAULT 0,
        price_group_id mediumint(9) DEFAULT 0,
        display_order mediumint(9) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $table_price_groups = $wpdb->prefix . 'wppm_price_groups';
    $sql_price_groups = "CREATE TABLE $table_price_groups (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        default_price decimal(10,2) DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta( $sql_categories );
    dbDelta( $sql_services );
    dbDelta( $sql_price_groups );

    add_option( 'wppm_db_version', $wppm_db_version );
}
register_activation_hook( __FILE__, 'wppm_install' );

// Подключаем файлы админ-панели и обработчика форм (синхронно)
if ( is_admin() ) {
    require_once WPPM_PLUGIN_DIR . 'admin/class-price-manager-admin.php';
    require_once WPPM_PLUGIN_DIR . 'admin/wppm-handler.php';
    require_once WPPM_PLUGIN_DIR . 'admin/wppm-ajax-handler.php';
}

// Подключаем виджет для Elementor (если плагин Elementor активен)
add_action( 'init', 'wppm_check_elementor' );
function wppm_check_elementor() {
    if ( did_action( 'elementor/loaded' ) ) {
        require_once WPPM_PLUGIN_DIR . 'elementor/elementor-widget-price-list.php';
    }
}

// Подключаем стили для админки
function wppm_admin_enqueue_scripts( $hook ) {
    // Для страниц плагина можно проверять, содержит ли $hook нужное значение.
    if ( strpos( $hook, 'price-manager' ) !== false ) {
        wp_enqueue_script( 'wppm-admin-js', WPPM_PLUGIN_URL . 'js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-dialog', 'jquery-ui-autocomplete' ), '1.0', true );
        wp_enqueue_style( 'wppm-admin-css', WPPM_PLUGIN_URL . 'css/admin.css' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_localize_script( 'wppm-admin-js', 'wppm_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wppm_nonce' ),
            'confirm_price_change_title'   => __( 'Подтверждение', 'wp-price-manager' ),
            'confirm_price_change_message' => __( 'Изменение цены повлияет на все связанные услуги. Подтвердить?', 'wp-price-manager' ),
            'view_services_base' => admin_url( 'admin.php?page=price-manager-services&prefill_category=' ),
            'add_service_base'  => admin_url( 'admin-post.php?action=wppm_add_service_form&category_id=' ),
            'edit_label'        => __( 'Редактировать', 'wp-price-manager' ),
            'delete_label'      => __( 'Удалить', 'wp-price-manager' ),
            'view_label'        => __( 'Посмотреть услуги', 'wp-price-manager' ),
            'quick_add_label'   => __( 'Быстро добавить услугу', 'wp-price-manager' )
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'wppm_admin_enqueue_scripts' );

// Подключаем стили и скрипты для фронтенда
function wppm_frontend_enqueue_scripts() {
    wp_enqueue_script( 'wppm-front-end-js', WPPM_PLUGIN_URL . 'js/front-end.js', array('jquery'), '1.0', true );
    wp_enqueue_style( 'wppm-front-end-css', WPPM_PLUGIN_URL . 'css/front-end.css' );
}
add_action( 'wp_enqueue_scripts', 'wppm_frontend_enqueue_scripts' );
