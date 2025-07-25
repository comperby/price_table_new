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

        // --- Услуги ---
        case 'add_service':
            $srv_table = $wpdb->prefix . 'wppm_services';
            $cat_table = $wpdb->prefix . 'wppm_categories';
            $pg_table  = $wpdb->prefix . 'wppm_price_groups';

            $name        = sanitize_text_field( $_POST['service_name'] );
            $description = sanitize_textarea_field( $_POST['service_description'] );
            $link        = esc_url_raw( $_POST['service_link'] );
            $price       = floatval( $_POST['service_price'] );
            $pg_name     = sanitize_text_field( $_POST['price_group'] );

            if ( isset( $_POST['service_category_id'] ) && ! empty( $_POST['service_category_id'] ) ) {
                $category_id = intval( $_POST['service_category_id'] );
            } else {
                $cat_name = sanitize_text_field( $_POST['service_category'] );
                $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $cat_name ) );
                if ( $existing_cat ) {
                    $category_id = $existing_cat->id;
                } else {
                    $wpdb->insert( $cat_table, array( 'name' => $cat_name, 'display_order' => 0 ), array( '%s', '%d' ) );
                    $category_id = $wpdb->insert_id;
                }
            }

            $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $pg_table WHERE name = %s", $pg_name ) );
            if ( $existing_pg ) {
                $price_group_id = $existing_pg->id;
            } else {
                $wpdb->insert( $pg_table, array( 'name' => $pg_name, 'default_price' => $price ), array( '%s', '%f' ) );
                $price_group_id = $wpdb->insert_id;
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
            ), array( '%s','%s','%s','%f','%d','%d','%d','%d' ) );

            if ( $result ) {
                $response = array( 'success' => true, 'message' => __( 'Услуга добавлена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка добавления услуги.', 'wp-price-manager' ) );
            }
            break;

        case 'edit_service':
            $srv_table = $wpdb->prefix . 'wppm_services';
            $cat_table = $wpdb->prefix . 'wppm_categories';
            $pg_table  = $wpdb->prefix . 'wppm_price_groups';

            $id          = intval( $_POST['service_id'] );
            $name        = sanitize_text_field( $_POST['service_name'] );
            $description = sanitize_textarea_field( $_POST['service_description'] );
            $link        = esc_url_raw( $_POST['service_link'] );
            $price       = floatval( $_POST['service_price'] );
            $pg_name     = sanitize_text_field( $_POST['price_group'] );
            $cat_name    = sanitize_text_field( $_POST['service_category'] );

            $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $cat_name ) );
            if ( $existing_cat ) {
                $category_id = $existing_cat->id;
            } else {
                $wpdb->insert( $cat_table, array( 'name' => $cat_name, 'display_order' => 0 ), array( '%s', '%d' ) );
                $category_id = $wpdb->insert_id;
            }

            $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $pg_table WHERE name = %s", $pg_name ) );
            if ( $existing_pg ) {
                $price_group_id = $existing_pg->id;
            } else {
                $wpdb->insert( $pg_table, array( 'name' => $pg_name, 'default_price' => $price ), array( '%s', '%f' ) );
                $price_group_id = $wpdb->insert_id;
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
                $response = array( 'success' => true, 'message' => __( 'Услуга обновлена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка обновления услуги.', 'wp-price-manager' ) );
            }
            break;

        // --- Поиск услуг ---
        case 'search_services':
            $srv_table = $wpdb->prefix . 'wppm_services';
            $cat_table = $wpdb->prefix . 'wppm_categories';
            $pg_table  = $wpdb->prefix . 'wppm_price_groups';
            $name        = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
            $description = isset( $_POST['description'] ) ? sanitize_text_field( $_POST['description'] ) : '';
            $price_group = isset( $_POST['price_group'] ) ? sanitize_text_field( $_POST['price_group'] ) : '';
            $category    = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
            $sql = "SELECT s.*, c.name AS category_name, pg.name AS price_group_name FROM $srv_table s LEFT JOIN $cat_table c ON s.category_id = c.id LEFT JOIN $pg_table pg ON s.price_group_id = pg.id WHERE 1=1";
            $params = array();
            if ( $name ) {
                $sql .= " AND s.name LIKE %s";
                $params[] = '%' . $wpdb->esc_like( $name ) . '%';
            }
            if ( $description ) {
                $sql .= " AND s.description LIKE %s";
                $params[] = '%' . $wpdb->esc_like( $description ) . '%';
            }
            if ( $price_group ) {
                $sql .= " AND pg.name LIKE %s";
                $params[] = '%' . $wpdb->esc_like( $price_group ) . '%';
            }
            if ( $category ) {
                $sql .= " AND c.name LIKE %s";
                $params[] = '%' . $wpdb->esc_like( $category ) . '%';
            }
            $sql .= " ORDER BY s.display_order ASC";
            $services = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

            ob_start();
            if ( $services ) {
                foreach ( $services as $srv ) {
                    ?>
                    <tr data-id="<?php echo intval( $srv['id'] ); ?>">
                        <td><?php echo esc_html( $srv['id'] ); ?></td>
                        <td><?php echo esc_html( $srv['name'] ); ?></td>
                        <td><?php echo esc_html( $srv['description'] ); ?></td>
                        <td>
                            <?php if ( ! empty( $srv['link'] ) ) : ?>
                                <a href="<?php echo esc_url( $srv['link'] ); ?>" target="_blank"><?php echo esc_html( $srv['link'] ); ?></a>
                            <?php else : ?>
                                <?php _e( 'Нет ссылки', 'wp-price-manager' ); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $srv['price'] ); ?></td>
                        <td><?php echo esc_html( $srv['category_name'] ); ?></td>
                        <td><?php echo esc_html( $srv['price_group_name'] ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin-post.php?action=wppm_edit_service_form&id=' . intval( $srv['id'] ) ); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
                            <a href="<?php echo admin_url( 'admin-post.php?action=wppm_delete_service&id=' . intval( $srv['id'] ) . '&_wpnonce=' . wp_create_nonce( 'wppm_delete_service_' . intval( $srv['id'] ) ) ); ?>" onclick="return confirm('<?php _e( 'Вы уверены?', 'wp-price-manager' ); ?>');"><?php _e( 'Удалить', 'wp-price-manager' ); ?></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr><td colspan="8"><?php _e( 'Услуги не найдены', 'wp-price-manager' ); ?></td></tr>
                <?php
            }
            $html = ob_get_clean();
            $response = array( 'success' => true, 'html' => $html );
            break;

        default:
            $response = array( 'success' => false, 'message' => __( 'Неизвестный запрос.', 'wp-price-manager' ) );
            break;
    }

    wp_send_json( $response );
}
