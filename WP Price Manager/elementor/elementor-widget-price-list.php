<?php
if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! function_exists( 'wppm_hex_to_rgba' ) ) {
    function wppm_hex_to_rgba( $hex, $opacity ) {
        $hex = str_replace( '#', '', $hex );
        if ( strlen( $hex ) === 3 ) {
            $r = hexdec( $hex[0] . $hex[0] );
            $g = hexdec( $hex[1] . $hex[1] );
            $b = hexdec( $hex[2] . $hex[2] );
        } else {
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );
        }
        $opacity = is_numeric( $opacity ) ? $opacity : 1;
        return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $opacity . ')';
    }
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Elementor_Price_List_Widget extends Widget_Base {

	public function get_name() {
		return 'price_list_widget';
	}

	public function get_title() {
		return __( 'Прайс-лист', 'wp-price-manager' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function _register_controls() {

		// Вкладка "Содержимое"
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Содержимое', 'wp-price-manager' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'selected_category',
			[
				'label'   => __( 'Выберите категорию', 'wp-price-manager' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_categories_options(),
				'default' => '',
			]
		);

		$this->end_controls_section();

		// Вкладка "Стиль"
		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Стиль', 'wp-price-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'table_width',
			[
				'label'   => __( 'Ширина таблицы', 'wp-price-manager' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '100%',
			]
		);

		$this->add_control(
			'header_bg_color',
			[
				'label'   => __( 'Цвет фона заголовка', 'wp-price-manager' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#f1f1f1',
			]
		);

		$this->add_control(
			'header_text_color',
			[
				'label'   => __( 'Цвет текста заголовка', 'wp-price-manager' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#333',
			]
		);

		$this->add_control(
			'even_row_bg_color',
			[
				'label'   => __( 'Цвет фона четных строк', 'wp-price-manager' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#ffffff',
			]
		);

		$this->add_control(
			'odd_row_bg_color',
			[
				'label'   => __( 'Цвет фона нечетных строк', 'wp-price-manager' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#f9f9f9',
			]
		);

		$this->add_control(
			'row_height',
			[
				'label'   => __( 'Высота строк', 'wp-price-manager' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '50px',
			]
		);

		$this->add_control(
			'typography',
			[
				'label'   => __( 'Типографика', 'wp-price-manager' ),
				'type'    => Controls_Manager::FONT,
				'default' => [
					'family' => 'Arial',
					'size'   => '14',
					'weight' => '400',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label'   => __( 'Цвет границ', 'wp-price-manager' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#ccc',
			]
		);

		$this->add_control(
			'border_width',
			[
				'label'   => __( 'Толщина границ', 'wp-price-manager' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '1px',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label'   => __( 'Скругление углов', 'wp-price-manager' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '5px',
			]
		);

                $this->add_control(
                        'header_alignment',
                        [
                                'label'   => __( 'Выравнивание заголовка', 'wp-price-manager' ),
                                'type'    => Controls_Manager::CHOOSE,
                                'options' => [
                                        'left'   => [
                                                'title' => __( 'Left', 'wp-price-manager' ),
                                                'icon'  => 'eicon-text-align-left',
                                        ],
                                        'center' => [
                                                'title' => __( 'Center', 'wp-price-manager' ),
                                                'icon'  => 'eicon-text-align-center',
                                        ],
                                        'right'  => [
                                                'title' => __( 'Right', 'wp-price-manager' ),
                                                'icon'  => 'eicon-text-align-right',
                                        ],
                                ],
                                'default' => 'left',
                        ]
                );

                $this->add_control(
                        'row_alignment',
                        [
                                'label'   => __( 'Выравнивание строк', 'wp-price-manager' ),
                                'type'    => Controls_Manager::CHOOSE,
                                'options' => [
                                        'left'   => [
                                                'title' => __( 'Left', 'wp-price-manager' ),
                                                'icon'  => 'eicon-text-align-left',
                                        ],
                                        'center' => [
                                                'title' => __( 'Center', 'wp-price-manager' ),
                                                'icon'  => 'eicon-text-align-center',
                                        ],
                                        'right'  => [
                                                'title' => __( 'Right', 'wp-price-manager' ),
                                                'icon'  => 'eicon-text-align-right',
                                        ],
                                ],
                                'default' => 'left',
                        ]
                );

		$this->end_controls_section();
	}

	// Получение списка категорий из БД
	protected function get_categories_options() {
		global $wpdb;
		$table = $wpdb->prefix . 'wppm_categories';
		$results = $wpdb->get_results( "SELECT id, name FROM $table ORDER BY display_order ASC", ARRAY_A );
		$options = [ '' => __( 'Выберите категорию', 'wp-price-manager' ) ];
		if ( $results ) {
			foreach ( $results as $cat ) {
				$options[ $cat['id'] ] = $cat['name'];
			}
		}
		return $options;
	}

        protected function render() {
                $settings = $this->get_settings_for_display();
                $styles   = wppm_get_style_settings();
                $mobile   = array();
                foreach ( $styles as $k => $v ) {
                        if ( strpos( $k, '_mobile' ) !== false ) {
                                $mobile[ str_replace( '_mobile', '', $k ) ] = $v;
                        }
                }
                // Получаем услуги по выбранной категории
                global $wpdb;
                $srv_table = $wpdb->prefix . 'wppm_services';
                $cat_id = intval( $settings['selected_category'] );
                if ( $cat_id ) {
                        $services = $wpdb->get_results( $wpdb->prepare(
                                "SELECT s.*, pg.default_price, pg.name as pg_name
                                 FROM $srv_table s
                                 LEFT JOIN {$wpdb->prefix}wppm_price_groups pg ON s.price_group_id = pg.id
                                 WHERE s.category_id = %d ORDER BY s.display_order ASC",
                                 $cat_id
                        ), ARRAY_A );
                        $total_services = count( $services );
                } else {
                        $services = [];
                        $total_services = 0;
                }
                $border_css  = esc_attr( $styles['border_width'] ) . ' ' . esc_attr( $styles['border_style'] ) . ' ' . esc_attr( $styles['border_color'] );
                $table_style = 'border-collapse: collapse;border-spacing:0;width:100%;font-family:' . esc_attr( $styles['text_font'] ) . ';font-size:' . esc_attr( $styles['text_size'] ) . ';color:' . esc_attr( $styles['text_color'] ) . ';font-weight:' . esc_attr( $styles['text_weight'] ) . ';border-radius:' . esc_attr( $styles['border_radius'] ) . ';';
                $border_css_m  = esc_attr( $mobile['border_width'] ) . ' ' . esc_attr( $mobile['border_style'] ) . ' ' . esc_attr( $mobile['border_color'] );
                $table_mobile  = 'border-collapse: collapse;border-spacing:0;width:100%;font-family:' . esc_attr( $mobile['text_font'] ) . ';font-size:' . esc_attr( $mobile['text_size'] ) . ';color:' . esc_attr( $mobile['text_color'] ) . ';font-weight:' . esc_attr( $mobile['text_weight'] ) . ';border-radius:' . esc_attr( $mobile['border_radius'] ) . ';';
                $cell_border = 'border:' . $border_css . ';';
                $cell_border_m = 'border:' . $border_css_m . ';';
                switch ( $styles['border_apply'] ) {
                    case 'outer':
                        $table_style .= 'border:' . $border_css . ';';
                        $cell_border = 'border:none;';
                        break;
                    case 'inner':
                        $cell_border = 'border:' . $border_css . ';';
                        break;
                    case 'vertical':
                        $table_style .= 'border:none;';
                        $cell_border = 'border-left:' . $border_css . ';border-right:' . $border_css . ';';
                        break;
                    case 'horizontal':
                        $table_style .= 'border:none;';
                        $cell_border = 'border-top:' . $border_css . ';border-bottom:' . $border_css . ';';
                        break;
                    default:
                        $table_style .= 'border:' . $border_css . ';';
                        break;
                }
                switch ( $mobile['border_apply'] ) {
                    case 'outer':
                        $table_mobile .= 'border:' . $border_css_m . ';';
                        $cell_border_m = 'border:none;';
                        break;
                    case 'inner':
                        $cell_border_m = 'border:' . $border_css_m . ';';
                        break;
                    case 'vertical':
                        $table_mobile .= 'border:none;';
                        $cell_border_m = 'border-left:' . $border_css_m . ';border-right:' . $border_css_m . ';';
                        break;
                    case 'horizontal':
                        $table_mobile .= 'border:none;';
                        $cell_border_m = 'border-top:' . $border_css_m . ';border-bottom:' . $border_css_m . ';';
                        break;
                    default:
                        $table_mobile .= 'border:' . $border_css_m . ';';
                        break;
                }
		?>
                <div class="wppm-price-list-widget wppm-widget-<?php echo $this->get_id(); ?>" data-cat="<?php echo intval( $cat_id ); ?>" data-limit="<?php echo esc_attr( $styles['show_limit'] ); ?>" data-speed="<?php echo esc_attr( $styles['show_more_speed'] ); ?>">
                        <style>
                        .wppm-widget-<?php echo $this->get_id(); ?> {
                            width: <?php echo esc_attr( $settings['table_width'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> {
                            <?php echo $table_style; ?>
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> thead {
                            background: <?php echo esc_attr( $styles['header_bg_color'] ); ?>;
                            color: <?php echo esc_attr( $styles['header_text_color'] ); ?>;
                            height: <?php echo esc_attr( $styles['header_height'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> tbody tr {
                            height: <?php echo esc_attr( $styles['row_height'] ); ?>;
                            text-align: <?php echo esc_attr( $styles['row_alignment'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> tbody tr:nth-child(odd){background: <?php echo esc_attr( $styles['even_row_bg_color'] ); ?>;}
                        .wppm-table-<?php echo $this->get_id(); ?> tbody tr:nth-child(even){background: <?php echo esc_attr( $styles['odd_row_bg_color'] ); ?>;}
                        .wppm-table-<?php echo $this->get_id(); ?> th,
                        .wppm-table-<?php echo $this->get_id(); ?> td{
                            <?php echo $cell_border; ?>
                            padding: <?php echo esc_attr( $styles['text_padding'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> th{
                            text-align: <?php echo esc_attr( $styles['header_alignment'] ); ?>;
                            font-size: <?php echo esc_attr( $styles['header_text_size'] ); ?>;
                            font-weight: <?php echo esc_attr( $styles['header_text_weight'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> a {
                            color: <?php echo esc_attr( $styles['link_color'] ); ?>;
                            transition: color <?php echo esc_attr( $styles['link_hover_speed'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> a:hover {
                            color: <?php echo esc_attr( $styles['link_hover_color'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> tbody tr:hover {
                            background: <?php echo esc_attr( $styles['row_hover_bg_color'] ); ?>;
                            transition: background <?php echo esc_attr( $styles['row_hover_speed'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> .wppm-info-icon {
                            background: <?php echo esc_attr( $styles['icon_bg_color'] ); ?>;
                            color: <?php echo esc_attr( $styles['icon_color'] ); ?>;
                            font-size: <?php echo esc_attr( $styles['icon_size'] ); ?>;
                            margin-left: <?php echo esc_attr( $styles['icon_offset_x'] ); ?>;
                            position: relative;
                            top: <?php echo esc_attr( $styles['icon_offset_y'] ); ?>;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> th {
                            position: relative;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> .wppm-header-icon {
                            position: absolute;
                            top: <?php echo esc_attr( $styles['icon_offset_y'] ); ?>;
                            right: <?php echo esc_attr( $styles['icon_offset_x'] ); ?>;
                            margin-left: 0;
                        }
                        .wppm-table-<?php echo $this->get_id(); ?> .wppm-tooltip {
                            background: <?php echo wppm_hex_to_rgba( $styles['tooltip_bg_color'], $styles['tooltip_opacity'] ); ?>;
                            color: <?php echo esc_attr( $styles['tooltip_text_color'] ); ?>;
                            border-radius: <?php echo esc_attr( $styles['tooltip_border_radius'] ); ?>;
                            box-shadow: <?php echo esc_attr( $styles['tooltip_shadow'] ); ?>;
                        }
                        .wppm-widget-<?php echo $this->get_id(); ?> .wppm-show-more-wrapper {
                            text-align: <?php echo esc_attr( $styles['show_more_align'] ); ?>;
                        }
                        .wppm-widget-<?php echo $this->get_id(); ?> .wppm-show-more {
                            background: <?php echo esc_attr( $styles['show_more_bg'] ); ?>;
                            color: <?php echo esc_attr( $styles['show_more_color'] ); ?>;
                            padding: <?php echo esc_attr( $styles['show_more_padding'] ); ?>;
                            border-radius: <?php echo esc_attr( $styles['show_more_radius'] ); ?>;
                            font-size: <?php echo esc_attr( $styles['show_more_font_size'] ); ?>;
                            width: <?php echo esc_attr( $styles['show_more_width'] ); ?>;
                            height: <?php echo esc_attr( $styles['show_more_height'] ); ?>;
                            font-family: <?php echo esc_attr( $styles['show_more_font_family'] ); ?>;
                            font-weight: <?php echo esc_attr( $styles['show_more_font_weight'] ); ?>;
                            margin-top:10px;
                        }
                        @media(max-width:768px){
                            .wppm-table-<?php echo $this->get_id(); ?> th {position:relative;}
                            .wppm-table-<?php echo $this->get_id(); ?> .wppm-header-icon {
                                position:absolute;
                                top: <?php echo esc_attr( $mobile['icon_offset_y'] ); ?>;
                                right: <?php echo esc_attr( $mobile['icon_offset_x'] ); ?>;
                                margin-left:0;
                            }
                            .wppm-table-<?php echo $this->get_id(); ?> .wppm-tooltip {
                                background: <?php echo esc_attr( $mobile['tooltip_bg_color'] ); ?>;
                                color: <?php echo esc_attr( $mobile['tooltip_text_color'] ); ?>;
                                border-radius: <?php echo esc_attr( $mobile['tooltip_border_radius'] ); ?>;
                                box-shadow: <?php echo esc_attr( $mobile['tooltip_shadow'] ); ?>;
                                background: <?php echo wppm_hex_to_rgba( $mobile['tooltip_bg_color'], $mobile['tooltip_opacity'] ); ?>;
                            }
                            .wppm-table-<?php echo $this->get_id(); ?> {
                                <?php echo $table_mobile; ?>
                            }
                            .wppm-table-<?php echo $this->get_id(); ?> thead {
                                background: <?php echo esc_attr( $mobile['header_bg_color'] ); ?>;
                                color: <?php echo esc_attr( $mobile['header_text_color'] ); ?>;
                                height: <?php echo esc_attr( $mobile['header_height'] ); ?>;
                            }
                            .wppm-table-<?php echo $this->get_id(); ?> tbody tr {
                                height: <?php echo esc_attr( $mobile['row_height'] ); ?>;
                                text-align: <?php echo esc_attr( $mobile['row_alignment'] ); ?>;
                            }
                            .wppm-table-<?php echo $this->get_id(); ?> tbody tr:nth-child(odd){background: <?php echo esc_attr( $mobile['even_row_bg_color'] ); ?>;}
                            .wppm-table-<?php echo $this->get_id(); ?> tbody tr:nth-child(even){background: <?php echo esc_attr( $mobile['odd_row_bg_color'] ); ?>;}
                            .wppm-table-<?php echo $this->get_id(); ?> tbody tr:hover{background: <?php echo esc_attr( $mobile['row_hover_bg_color'] ); ?>;transition: background <?php echo esc_attr( $mobile['row_hover_speed'] ); ?>;}
                            .wppm-table-<?php echo $this->get_id(); ?> a {transition: color <?php echo esc_attr( $mobile['link_hover_speed'] ); ?>;}
                            .wppm-table-<?php echo $this->get_id(); ?> a:hover{color: <?php echo esc_attr( $mobile['link_hover_color'] ); ?>;}
                            .wppm-table-<?php echo $this->get_id(); ?> th, .wppm-table-<?php echo $this->get_id(); ?> td{
                                <?php echo $cell_border_m; ?>
                                padding: <?php echo esc_attr( $mobile['text_padding'] ); ?>;
                            }
                            .wppm-table-<?php echo $this->get_id(); ?> th{
                                text-align: <?php echo esc_attr( $mobile['header_alignment'] ); ?>;
                                font-size: <?php echo esc_attr( $mobile['header_text_size'] ); ?>;
                                font-weight: <?php echo esc_attr( $mobile['header_text_weight'] ); ?>;
                            }
                        }
                        </style>
                        <?php
                        // styles for table output already calculated above
                        ?>
                        <table class="wppm-table wppm-table-<?php echo $this->get_id(); ?>">
                                <thead>
                                        <tr>
                                                <?php
                                                $cat_info = $wpdb->get_row( $wpdb->prepare( "SELECT custom_table,column_count,column_titles FROM {$wpdb->prefix}wppm_categories WHERE id = %d", $cat_id ), ARRAY_A );
                                                $headers = array( __( 'Услуга', 'wp-price-manager' ), __( 'Цена', 'wp-price-manager' ) );
                                                $header_descs = array( '', '' );
                                                $column_count = 2;
                                                $custom = false;
                                                if ( $cat_info && $cat_info['custom_table'] ) {
                                                    $decoded = json_decode( $cat_info['column_titles'], true );
                                                    if ( is_array( $decoded ) ) {
                                                        ksort( $decoded );
                                                        $decoded = array_values( $decoded );
                                                        $custom = true;
                                                        $headers = array();
                                                        $header_descs = array();
                                                        foreach ( $decoded as $item ) {
                                                            if ( is_array( $item ) ) {
                                                                $headers[] = $item['title'] ?? '';
                                                                $header_descs[] = $item['desc'] ?? '';
                                                            } else {
                                                                $headers[] = $item;
                                                                $header_descs[] = '';
                                                            }
                                                        }
                                                        $column_count = count( $headers );
                                                    }
                                                }
                                                $is_fa = strpos( $styles['icon_char'], 'fa' ) === 0;
                                                $icon_content = $is_fa ? '<i class="' . esc_attr( $styles['icon_char'] ) . '"></i>' : esc_html( $styles['icon_char'] );
                                                for ( $i = 0; $i < $column_count; $i++ ) {
                                                    $title = $headers[$i] ?? '';
                                                    echo '<th>'.esc_html($title);
                                                    if ( ! empty( $header_descs[$i] ) ) {
                                                        echo ' <span class="wppm-info-icon wppm-header-icon" data-description="' . esc_attr( $header_descs[$i] ) . '">' . $icon_content . '</span>';
                                                    }
                                                    echo '</th>';
                                                }
                                                ?>
                                        </tr>
                                </thead>
                                <tbody>
                                        <?php if ( ! empty( $services ) ) : ?>
                                        <?php foreach ( $services as $index => $service ) : ?>
                                                <?php
                                                $display_price = ( $service['manual_price'] ? $service['price'] : ( $service['default_price'] ? $service['default_price'] : $service['price'] ) );
                                                $extras_data = json_decode( $service['extras'], true );
                                                $extras = is_array( $extras_data ) ? array_values( $extras_data ) : [];
                                                $row_class = $index >= intval( $styles['show_limit'] ) ? ' class="wppm-hidden-row"' : '';
                                                $is_fa = strpos( $styles['icon_char'], 'fa' ) === 0;
                                                $icon_content = $is_fa ? '<i class="' . esc_attr( $styles['icon_char'] ) . '"></i>' : esc_html( $styles['icon_char'] );
                                                ?>
                                                        <tr<?php echo $row_class; ?>>
                                                            <?php
                                                for ( $c = 0; $c < $column_count; $c++ ) {
                                                    echo '<td>';

                                                    if ( $c === 0 ) {
                                                        if ( $custom ) {
                                                            $val = $extras[0] ?? '';
                                                            echo esc_html( $val );
                                                        } else {
                                                            echo '<a href="' . esc_url( $service['link'] ) . '" target="_blank">' . esc_html( $service['name'] ) . '</a>';
                                                        }
                                                        if ( ! empty( $service['description'] ) ) {
                                                            echo ' <span class="wppm-info-icon" data-description="' . esc_attr( $service['description'] ) . '">' . $icon_content . '</span>';
                                                        }
                                                    } elseif ( ! $custom && $c === 1 ) {
                                                        echo esc_html( $display_price );
                                                    } else {
                                                        $idx = $custom ? $c : $c - 2;
                                                        $val = $extras[ $idx ] ?? '';
                                                        echo esc_html( $val );
                                                    }
                                                    echo '</td>';
                                                }
                                                            ?>
                                                        </tr>
						<?php endforeach; ?>
					<?php else : ?>
                                                <tr>
                                                        <td colspan="<?php echo intval( $column_count ); ?>"><?php _e( 'Нет услуг для отображения', 'wp-price-manager' ); ?></td>
                                                </tr>
					<?php endif; ?>
				</tbody>
                        </table>
                        <?php if ( $total_services > intval( $styles['show_limit'] ) ) : ?>
                            <div class="wppm-show-more-wrapper">
                                <button type="button" class="wppm-show-more" data-more="<?php echo esc_attr( $styles['show_more_text'] ); ?>" data-less="<?php echo esc_attr( $styles['show_less_text'] ); ?>">
                                    <?php echo esc_html( $styles['show_more_text'] ); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                </div>
                <?php
	}
}

add_action( 'elementor/widgets/widgets_registered', function( $widgets_manager ) {
	$widgets_manager->register_widget_type( new Elementor_Price_List_Widget() );
} );
