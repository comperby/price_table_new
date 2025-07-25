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
$wppm_db_version = '1.2';

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
        custom_table tinyint(1) NOT NULL DEFAULT 0,
        column_count smallint(4) NOT NULL DEFAULT 2,
        column_titles text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $table_services = $wpdb->prefix . 'wppm_services';
    $sql_services = "CREATE TABLE $table_services (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        link varchar(255),
        price varchar(255) DEFAULT '',
        manual_price tinyint(1) DEFAULT 0,
        category_id mediumint(9) DEFAULT 0,
        price_group_id mediumint(9) DEFAULT 0,
        display_order mediumint(9) NOT NULL DEFAULT 0,
        extras text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $table_price_groups = $wpdb->prefix . 'wppm_price_groups';
    $sql_price_groups = "CREATE TABLE $table_price_groups (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        default_price varchar(255) DEFAULT '',
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta( $sql_categories );
    dbDelta( $sql_services );
    dbDelta( $sql_price_groups );

    add_option( 'wppm_db_version', $wppm_db_version );

    // Значения стилей по умолчанию
    $default_styles = array(
        'border_color'          => '#ccc',
        'border_width'          => '1px',
        'border_style'          => 'solid',
        'border_apply'          => 'all',
        'border_radius'         => '5px',
        'header_bg_color'       => '#f1f1f1',
        'header_text_color'     => '#333',
        'header_height'         => '50px',
        'even_row_bg_color'     => '#ffffff',
        'odd_row_bg_color'      => '#f9f9f9',
        'text_font'             => 'Montserrat',
        'text_size'             => '14px',
        'text_weight'           => '400',
        'text_padding'          => '8px',
        'text_color'            => '#333',
        'header_text_size'      => '16px',
        'header_text_weight'    => '600',
        'link_color'            => '#0073aa',
        'link_hover_color'      => '#005177',
        'link_hover_speed'      => '0.3s',
        'row_height'            => '50px',
        'row_hover_bg_color'    => '#eaeaea',
        'row_hover_speed'       => '0.3s',
        'header_alignment'      => 'left',
        'row_alignment'         => 'left',
        'icon_char'             => '\u2753',
        'icon_color'            => '#fff',
        'icon_bg_color'         => '#0073aa',
        'icon_size'             => '16px',
        'icon_offset_x'         => '0px',
        'icon_offset_y'         => '0px',
        'tooltip_bg_color'      => '#333',
        'tooltip_text_color'    => '#fff',
        'tooltip_border_radius' => '4px',
        'tooltip_opacity'       => '1',
        'tooltip_shadow'        => '0 2px 8px rgba(0,0,0,0.3)',
        'show_more_text'        => 'Показать все',
        'show_less_text'        => 'Свернуть',
        'show_more_bg'          => '#0073aa',
        'show_more_color'       => '#ffffff',
        'show_more_padding'     => '8px 16px',
        'show_more_radius'      => '4px',
        'show_more_font_size'   => '14px',
        'show_more_width'       => 'auto',
        'show_more_height'      => 'auto',
        'show_more_font_family' => 'Montserrat',
        'show_more_font_weight' => '400',
        'show_more_align'       => 'left',
        'show_more_speed'       => '0.3s',
        'show_limit'            => '7',
        'use_google_font'       => '1'
    );
    add_option( 'wppm_style_settings', $default_styles );
}
register_activation_hook( __FILE__, 'wppm_install' );

// Обновление БД при изменении версии
function wppm_update_db_check() {
    global $wppm_db_version;
    if ( get_option( 'wppm_db_version' ) !== $wppm_db_version ) {
        wppm_install();
        update_option( 'wppm_db_version', $wppm_db_version );
    }
}
add_action( 'plugins_loaded', 'wppm_update_db_check' );

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
        wp_enqueue_script( 'wppm-admin-js', WPPM_PLUGIN_URL . 'js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-dialog', 'jquery-ui-autocomplete', 'wp-color-picker' ), '1.0', true );
        wp_enqueue_style( 'wppm-admin-css', WPPM_PLUGIN_URL . 'css/admin.css' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_enqueue_style( 'wp-color-picker' );
        $styles = wppm_get_style_settings();
        $is_fa  = strpos( $styles['icon_char'], 'fa' ) === 0;
        $icon_content = $is_fa ? '<i class="' . esc_attr( $styles['icon_char'] ) . '"></i>' : esc_html( $styles['icon_char'] );
        wp_localize_script( 'wppm-admin-js', 'wppm_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wppm_nonce' ),
            'confirm_price_change_title'   => __( 'Подтверждение', 'wp-price-manager' ),
            'confirm_price_change_message' => __( 'Изменение цены повлияет на все связанные услуги. Подтвердить?', 'wp-price-manager' ),
            'view_services_base' => admin_url( 'admin.php?page=price-manager-services&prefill_category=' ),
            'edit_label'        => __( 'Редактировать', 'wp-price-manager' ),
            'delete_label'      => __( 'Удалить', 'wp-price-manager' ),
            'view_label'        => __( 'Посмотреть услуги', 'wp-price-manager' ),
            'save_label'        => __( 'Сохранить', 'wp-price-manager' ),
            'save_desc_label'   => __( 'Сохранить описания', 'wp-price-manager' ),
            'desc_placeholder'  => __( 'Описание колонки', 'wp-price-manager' ),
            'icon_html'         => $icon_content
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'wppm_admin_enqueue_scripts' );

// Подключаем стили и скрипты для фронтенда
function wppm_frontend_enqueue_scripts() {
    wp_enqueue_script( 'wppm-front-end-js', WPPM_PLUGIN_URL . 'js/front-end.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'wppm-front-end-js', 'wppm_ajax_obj', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'wppm_nonce' )
    ) );
    wp_enqueue_style( 'wppm-front-end-css', WPPM_PLUGIN_URL . 'css/front-end.css' );
    $styles = wppm_get_style_settings();
    if ( empty( $styles['use_google_font'] ) || $styles['use_google_font'] === '1' ) {
        wp_enqueue_style( 'wppm-fonts', 'https://fonts.googleapis.com/css?family=Montserrat&display=swap', array(), null );
    }
}
add_action( 'wp_enqueue_scripts', 'wppm_frontend_enqueue_scripts' );

// Получить настройки стилей с учетом значений по умолчанию
function wppm_get_style_settings() {
    $defaults = array(
        'border_color'          => '#ccc',
        'border_width'          => '1px',
        'border_style'          => 'solid',
        'border_apply'          => 'all',
        'border_radius'         => '5px',
        'header_bg_color'       => '#f1f1f1',
        'header_text_color'     => '#333',
        'header_height'         => '50px',
        'even_row_bg_color'     => '#ffffff',
        'odd_row_bg_color'      => '#f9f9f9',
        'text_font'             => 'Montserrat',
        'text_size'             => '14px',
        'text_weight'           => '400',
        'text_padding'          => '8px',
        'text_color'            => '#333',
        'header_text_size'      => '16px',
        'header_text_weight'    => '600',
        'link_color'            => '#0073aa',
        'link_hover_color'      => '#005177',
        'link_hover_speed'      => '0.3s',
        'row_height'            => '50px',
        'row_hover_bg_color'    => '#eaeaea',
        'row_hover_speed'       => '0.3s',
        'header_alignment'      => 'left',
        'row_alignment'         => 'left',
        'icon_char'             => '\u2753',
        'icon_color'            => '#fff',
        'icon_bg_color'         => '#0073aa',
        'icon_size'             => '16px',
        'icon_offset_x'         => '0px',
        'icon_offset_y'         => '0px',
        'tooltip_bg_color'      => '#333',
        'tooltip_text_color'    => '#fff',
        'tooltip_border_radius' => '4px',
        'tooltip_opacity'       => '1',
        'tooltip_shadow'        => '0 2px 8px rgba(0,0,0,0.3)',
        'show_more_text'        => 'Показать все',
        'show_less_text'        => 'Свернуть',
        'show_more_bg'          => '#0073aa',
        'show_more_color'       => '#ffffff',
        'show_more_padding'     => '8px 16px',
        'show_more_radius'      => '4px',
        'show_more_font_size'   => '14px',
        'show_more_width'       => 'auto',
        'show_more_height'      => 'auto',
        'show_more_font_family' => 'Montserrat',
        'show_more_font_weight' => '400',
        'show_more_align'       => 'left',
        'show_more_speed'       => '0.3s',
        'show_limit'            => '7',
        'use_google_font'       => '1'
    );

    foreach ( array_keys( $defaults ) as $base ) {
        $defaults[ $base . '_mobile' ] = $defaults[ $base ];
    }

    $saved = get_option( 'wppm_style_settings', array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }

    return wp_parse_args( $saved, $defaults );
}
