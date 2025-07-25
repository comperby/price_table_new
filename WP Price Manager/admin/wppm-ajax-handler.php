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
            $display_order = isset( $_POST['display_order'] ) ? intval( $_POST['display_order'] ) : 0;
            $custom  = isset( $_POST['custom_table'] ) ? 1 : 0;
            $count   = $custom ? max( 2, intval( $_POST['column_count'] ) ) : 2;
            if ( $custom && isset( $_POST['column_titles'] ) ) {
                $titles_arr = array();
                foreach ( (array) $_POST['column_titles'] as $idx => $t ) {
                    $titles_arr[] = array(
                        'title' => sanitize_text_field( $t ),
                        'desc'  => isset( $_POST['column_desc'][ $idx ] ) ? sanitize_text_field( $_POST['column_desc'][ $idx ] ) : ''
                    );
                }
                $titles = wp_json_encode( $titles_arr );
            } else {
                $titles = '';
            }
            $table = $wpdb->prefix . 'wppm_categories';
            $result = $wpdb->insert( $table, array(
                'name'          => $name,
                'display_order' => $display_order,
                'custom_table'  => $custom,
                'column_count'  => $count,
                'column_titles' => $titles,
            ), array( '%s', '%d', '%d', '%d', '%s' ) );
            if ( $result ) {
                delete_transient( 'wppm_categories' );
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
                delete_transient( 'wppm_categories' );
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
                delete_transient( 'wppm_categories' );
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
            delete_transient( 'wppm_categories' );
            $response = array( 'success' => true, 'message' => __( 'Порядок категорий обновлен.', 'wp-price-manager' ) );
            break;

        case 'get_categories':
            $table = $wpdb->prefix . 'wppm_categories';
            $categories = get_transient( 'wppm_categories' );
            if ( false === $categories ) {
                $categories = $wpdb->get_results( "SELECT id, name, display_order FROM $table ORDER BY display_order ASC", ARRAY_A );
                set_transient( 'wppm_categories', $categories, HOUR_IN_SECONDS );
            }
            $response = array( 'success' => true, 'categories' => $categories );
            break;

        case 'get_category_info':
            $table = $wpdb->prefix . 'wppm_categories';
            if ( isset( $_POST['id'] ) ) {
                $cat = $wpdb->get_row( $wpdb->prepare( "SELECT id, custom_table,column_titles FROM $table WHERE id = %d", intval( $_POST['id'] ) ), ARRAY_A );
            } else {
                $name = sanitize_text_field( $_POST['name'] );
                $cat = $wpdb->get_row( $wpdb->prepare( "SELECT id, custom_table,column_titles FROM $table WHERE name = %s", $name ), ARRAY_A );
            }
            if ( $cat ) {
                $decoded_titles = json_decode( $cat['column_titles'], true );
                $titles = array();
                $descs  = array();
                if ( is_array( $decoded_titles ) ) {
                    foreach ( $decoded_titles as $it ) {
                        if ( is_array( $it ) ) {
                            $titles[] = $it['title'] ?? '';
                            $descs[]  = $it['desc'] ?? '';
                        } else {
                            $titles[] = $it;
                            $descs[]  = '';
                        }
                    }
                }
                $response = array( 'success' => true, 'custom' => (bool) $cat['custom_table'], 'titles' => $titles, 'descs' => $descs, 'id' => intval( $cat['id'] ) );
            } else {
                $response = array( 'success' => false );
            }
            break;

        case 'save_column_desc':
            $table = $wpdb->prefix . 'wppm_categories';
            $id    = intval( $_POST['category_id'] );
            $raw_descs = isset( $_POST['descs'] ) ? (array) $_POST['descs'] : array();
            $descs = array();
            foreach ( $raw_descs as $k => $v ) {
                $descs[ intval( $k ) ] = sanitize_text_field( $v );
            }
            $cat = $wpdb->get_row( $wpdb->prepare( "SELECT column_titles FROM $table WHERE id = %d", $id ), ARRAY_A );
            if ( $cat ) {
                $titles = json_decode( $cat['column_titles'], true );
                if ( ! is_array( $titles ) ) {
                    $titles = array();
                }
                foreach ( $descs as $idx => $val ) {
                    if ( isset( $titles[ $idx ] ) ) {
                        if ( is_array( $titles[ $idx ] ) ) {
                            $titles[ $idx ]['desc'] = $val;
                        } else {
                            $titles[ $idx ] = array( 'title' => $titles[ $idx ], 'desc' => $val );
                        }
                    }
                }
                $result = $wpdb->update( $table, array( 'column_titles' => wp_json_encode( $titles ) ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
                if ( $result !== false ) {
                    delete_transient( 'wppm_categories' );
                    $response = array( 'success' => true, 'message' => __( 'Описания сохранены', 'wp-price-manager' ) );
                } else {
                    $response = array( 'success' => false, 'message' => __( 'Ошибка сохранения', 'wp-price-manager' ) );
                }
            } else {
                $response = array( 'success' => false, 'message' => __( 'Категория не найдена', 'wp-price-manager' ) );
            }
            break;

        // --- Группа цен ---
        case 'add_price_group':
            $name          = sanitize_text_field( $_POST['price_group_name'] );
            $default_price = sanitize_text_field( $_POST['default_price'] );
            $pg_table = $wpdb->prefix . 'wppm_price_groups';
            $result = $wpdb->insert( $pg_table, array(
                'name'          => $name,
                'default_price' => $default_price,
            ), array( '%s', '%s' ) );
            if ( $result ) {
                delete_transient( 'wppm_price_groups' );
                $response = array( 'success' => true, 'message' => __( 'Группа цен добавлена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка добавления группы цен.', 'wp-price-manager' ) );
            }
            break;

        case 'edit_price_group':
            $id            = intval( $_POST['id'] );
            $name          = sanitize_text_field( $_POST['price_group_name'] );
            $default_price = sanitize_text_field( $_POST['default_price'] );
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
            ), array( 'id' => $id ), array( '%s', '%s' ), array( '%d' ) );
            if ( $result !== false ) {
                delete_transient( 'wppm_price_groups' );
                $srv_table = $wpdb->prefix . 'wppm_services';
                $wpdb->query( $wpdb->prepare(
                    "UPDATE $srv_table SET price = %s WHERE price_group_id = %d AND manual_price = 0",
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
                delete_transient( 'wppm_price_groups' );
                $response = array( 'success' => true, 'message' => __( 'Группа цен удалена.', 'wp-price-manager' ) );
            } else {
                $response = array( 'success' => false, 'message' => __( 'Ошибка удаления группы цен.', 'wp-price-manager' ) );
            }
            break;

        case 'get_price_groups':
            $pg_table = $wpdb->prefix . 'wppm_price_groups';
            $price_groups = get_transient( 'wppm_price_groups' );
            if ( false === $price_groups ) {
                $price_groups = $wpdb->get_results( "SELECT id, name FROM $pg_table", ARRAY_A );
                set_transient( 'wppm_price_groups', $price_groups, HOUR_IN_SECONDS );
            }
            $response = array( 'success' => true, 'get_price_groups' => $price_groups );
            break;

        // --- Услуги ---
        case 'add_service':
            $srv_table = $wpdb->prefix . 'wppm_services';
            $cat_table = $wpdb->prefix . 'wppm_categories';
            $pg_table  = $wpdb->prefix . 'wppm_price_groups';

            $extras_array = isset( $_POST['extras'] ) ? array_values( array_map( 'sanitize_text_field', (array) $_POST['extras'] ) ) : array();
            $name        = sanitize_text_field( $_POST['service_name'] );
            if ( $name === '' && ! empty( $extras_array ) ) {
                $name = $extras_array[0];
            }
            $description = isset( $_POST['service_description'] ) ? sanitize_textarea_field( $_POST['service_description'] ) : '';
            $link        = isset( $_POST['service_link'] ) ? esc_url_raw( $_POST['service_link'] ) : '';
           $price       = isset( $_POST['service_price'] ) ? sanitize_text_field( $_POST['service_price'] ) : '';
           $pg_name     = isset( $_POST['price_group'] ) ? sanitize_text_field( $_POST['price_group'] ) : '';
           $extras      = wp_json_encode( $extras_array );

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

            $price_group_id = 0;
            $manual = 1;
            if ( $pg_name ) {
                $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $pg_table WHERE name = %s", $pg_name ) );
                if ( $existing_pg ) {
                    $price_group_id = $existing_pg->id;
                    if ( $price === '' ) {
                        $price = $existing_pg->default_price;
                        $manual = 0;
                    }
                } else {
                    $wpdb->insert( $pg_table, array( 'name' => $pg_name, 'default_price' => $price ), array( '%s', '%s' ) );
                    $price_group_id = $wpdb->insert_id;
                    if ( $price === '' ) {
                        $manual = 0;
                    } else {
                        $manual = 1;
                    }
                }
            }

           $result = $wpdb->insert( $srv_table, array(
                'name'          => $name,
                'description'   => $description,
                'link'          => $link,
                'price'         => $price,
                'manual_price'  => $manual,
                'category_id'   => $category_id,
                'price_group_id'=> $price_group_id,
                'display_order' => 0,
                'extras'        => $extras,
            ), array( '%s','%s','%s','%s','%d','%d','%d','%d','%s' ) );

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
            $extras_array = isset( $_POST['extras'] ) ? array_values( array_map( 'sanitize_text_field', (array) $_POST['extras'] ) ) : array();
            $name        = sanitize_text_field( $_POST['service_name'] );
            if ( $name === '' && ! empty( $extras_array ) ) {
                $name = $extras_array[0];
            }
            $description = isset( $_POST['service_description'] ) ? sanitize_textarea_field( $_POST['service_description'] ) : '';
            $link        = isset( $_POST['service_link'] ) ? esc_url_raw( $_POST['service_link'] ) : '';
            $price       = isset( $_POST['service_price'] ) ? sanitize_text_field( $_POST['service_price'] ) : '';
           $pg_name     = isset( $_POST['price_group'] ) ? sanitize_text_field( $_POST['price_group'] ) : '';
           $cat_name    = sanitize_text_field( $_POST['service_category'] );
           $extras      = wp_json_encode( $extras_array );

            $existing_cat = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $cat_table WHERE name = %s", $cat_name ) );
            if ( $existing_cat ) {
                $category_id = $existing_cat->id;
            } else {
                $wpdb->insert( $cat_table, array( 'name' => $cat_name, 'display_order' => 0 ), array( '%s', '%d' ) );
                $category_id = $wpdb->insert_id;
            }

            $price_group_id = 0;
            $manual = 1;
            if ( $pg_name ) {
                $existing_pg = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $pg_table WHERE name = %s", $pg_name ) );
                if ( $existing_pg ) {
                    $price_group_id = $existing_pg->id;
                    if ( $price === '' ) {
                        $price = $existing_pg->default_price;
                        $manual = 0;
                    }
                } else {
                    $wpdb->insert( $pg_table, array( 'name' => $pg_name, 'default_price' => $price ), array( '%s', '%s' ) );
                    $price_group_id = $wpdb->insert_id;
                    if ( $price === '' ) {
                        $manual = 0;
                    } else {
                        $manual = 1;
                    }
                }
            }

            $result = $wpdb->update( $srv_table, array(
                'name'          => $name,
                'description'   => $description,
                'link'          => $link,
                'price'         => $price,
                'manual_price'  => $manual,
                'category_id'   => $category_id,
                'price_group_id'=> $price_group_id,
                'extras'        => $extras,
            ), array( 'id' => $id ), array( '%s','%s','%s','%s','%d','%d','%d','%s' ), array( '%d' ) );

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
                    <tr data-id="<?php echo intval( $srv['id'] ); ?>" data-name="<?php echo esc_attr( $srv['name'] ); ?>" data-description="<?php echo esc_attr( $srv['description'] ); ?>" data-link="<?php echo esc_attr( $srv['link'] ); ?>" data-price="<?php echo esc_attr( $srv['price'] ); ?>" data-category="<?php echo esc_attr( $srv['category_name'] ); ?>" data-price-group="<?php echo esc_attr( $srv['price_group_name'] ); ?>">
                        <td class="srv-name"><?php echo esc_html( $srv['name'] ); ?></td>
                        <td class="srv-description"><?php echo esc_html( $srv['description'] ); ?></td>
                        <td class="srv-link">
                            <?php if ( ! empty( $srv['link'] ) ) : ?>
                                <a href="<?php echo esc_url( $srv['link'] ); ?>" target="_blank"><?php echo esc_html( $srv['link'] ); ?></a>
                            <?php else : ?>
                                <?php _e( 'Нет ссылки', 'wp-price-manager' ); ?>
                            <?php endif; ?>
                        </td>
                        <td class="srv-price"><?php echo esc_html( $srv['price'] ); ?></td>
                        <td class="srv-category"><?php echo esc_html( $srv['category_name'] ); ?></td>
                        <td class="srv-price-group"><?php echo esc_html( $srv['price_group_name'] ); ?></td>
                        <td class="srv-actions">
                            <a href="#" class="edit-service" data-id="<?php echo intval( $srv['id'] ); ?>"><?php _e( 'Редактировать', 'wp-price-manager' ); ?></a> |
                            <a href="<?php echo admin_url( 'admin-post.php?action=wppm_delete_service&id=' . intval( $srv['id'] ) . '&_wpnonce=' . wp_create_nonce( 'wppm_delete_service_' . intval( $srv['id'] ) ) ); ?>" onclick="return confirm('<?php _e( 'Вы уверены?', 'wp-price-manager' ); ?>');"><?php _e( 'Удалить', 'wp-price-manager' ); ?></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr><td colspan="7"><?php _e( 'Услуги не найдены', 'wp-price-manager' ); ?></td></tr>
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
