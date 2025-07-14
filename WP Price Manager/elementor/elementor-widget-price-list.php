<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
                // Получаем услуги по выбранной категории
		global $wpdb;
		$srv_table = $wpdb->prefix . 'wppm_services';
		$cat_id = intval( $settings['selected_category'] );
		if ( $cat_id ) {
			$services = $wpdb->get_results( $wpdb->prepare(
				"SELECT s.*, pg.default_price, pg.name as pg_name 
				 FROM $srv_table s 
				 LEFT JOIN {$wpdb->prefix}wppm_price_groups pg ON s.price_group_id = pg.id 
				 WHERE s.category_id = %d ORDER BY s.display_order ASC", $cat_id
			), ARRAY_A );
		} else {
			$services = [];
		}
		?>
                <div class="wppm-price-list-widget" style="width: <?php echo esc_attr( $settings['table_width'] ); ?>">
                        <style>
                        #wppm-table-<?php echo $this->get_id(); ?> .wppm-info-icon {
                            background: <?php echo esc_attr( $styles['icon_bg_color'] ); ?>;
                            color: <?php echo esc_attr( $styles['icon_color'] ); ?>;
                        }
                        #wppm-table-<?php echo $this->get_id(); ?> .wppm-tooltip {
                            background: <?php echo esc_attr( $styles['tooltip_bg_color'] ); ?>;
                            color: <?php echo esc_attr( $styles['tooltip_text_color'] ); ?>;
                            border-radius: <?php echo esc_attr( $styles['tooltip_border_radius'] ); ?>;
                        }
                        </style>
                        <table id="wppm-table-<?php echo $this->get_id(); ?>" style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                width: 100%;
                                font-family: <?php echo esc_attr( $styles['text_font'] ); ?>;
                                font-size: <?php echo esc_attr( $styles['text_size'] ); ?>;
                                color: <?php echo esc_attr( $styles['text_color'] ); ?>;
                                font-weight: <?php echo esc_attr( $styles['text_weight'] ); ?>;
                                border: <?php echo esc_attr( $styles['border_width'] ); ?> solid <?php echo esc_attr( $styles['border_color'] ); ?>;
                                border-radius: <?php echo esc_attr( $styles['border_radius'] ); ?>;
                        ">
                                <thead style="background: <?php echo esc_attr( $styles['header_bg_color'] ); ?>; color: <?php echo esc_attr( $styles['header_text_color'] ); ?>; height: <?php echo esc_attr( $styles['header_height'] ); ?>;">
                                        <tr>
                                                <?php
                                                $cat_info = $wpdb->get_row( $wpdb->prepare( "SELECT custom_table,column_count,column_titles FROM {$wpdb->prefix}wppm_categories WHERE id = %d", $cat_id ), ARRAY_A );
                                                $headers = array( __( 'Услуга', 'wp-price-manager' ), __( 'Цена', 'wp-price-manager' ) );
                                                $column_count = 2;
                                                $custom = false;
                                                if ( $cat_info && $cat_info['custom_table'] ) {
                                                    $decoded = json_decode( $cat_info['column_titles'], true );
                                                    if ( is_array( $decoded ) ) {
                                                        $custom = true;
                                                        $headers = array_values( $decoded );
                                                        $column_count = count( $headers );
                                                    }
                                                }
                                                for ( $i = 0; $i < $column_count; $i++ ) {
                                                    $title = $headers[$i] ?? '';
                                                    echo '<th style="text-align: '.esc_attr($styles['header_alignment']).'; border: '.esc_attr($styles['border_width']).' solid '.esc_attr($styles['border_color']).'; padding: '.esc_attr($styles['text_padding']).'; font-size: '.esc_attr($styles['header_text_size']).'; font-weight: '.esc_attr($styles['header_text_weight']).';">'.esc_html($title).'</th>';
                                                }
                                                ?>
                                        </tr>
                                </thead>
                                <tbody>
                                        <?php if ( ! empty( $services ) ) : ?>
                                        <?php foreach ( $services as $index => $service ) : ?>
                                                <?php
                                                $display_price = ( $service['manual_price'] ? $service['price'] : ( $service['default_price'] ? $service['default_price'] : $service['price'] ) );
                                                $extras = array_values( json_decode( $service['extras'], true ) );
                                                $row_class = $index >= intval( $styles['show_limit'] ) ? ' class="wppm-hidden-row" style="display:none;"' : '';
                                                ?>
                                                        <tr<?php echo $row_class; ?> style="background: <?php echo $index % 2 === 0 ? esc_attr( $styles['even_row_bg_color'] ) : esc_attr( $styles['odd_row_bg_color'] ); ?>; height: <?php echo esc_attr( $styles['row_height'] ); ?>; text-align: <?php echo esc_attr( $styles['row_alignment'] ); ?>;">
                                                            <?php
                                                for ( $c = 0; $c < $column_count; $c++ ) {
                                                    echo '<td style="border:' . esc_attr( $styles['border_width'] ) . ' solid ' . esc_attr( $styles['border_color'] ) . ';padding:' . esc_attr( $styles['text_padding'] ) . ';">';

                                                    if ( $c === 0 ) {
                                                        if ( $custom ) {
                                                            $val = $extras[0] ?? '';
                                                            echo esc_html( $val ) . ' <span class="wppm-info-icon" data-description="' . esc_attr( $service['description'] ) . '">' . esc_html( $styles['icon_char'] ) . '</span>';
                                                        } else {
                                                            echo '<a href="' . esc_url( $service['link'] ) . '" target="_blank" style="color:' . esc_attr( $styles['link_color'] ) . ';">' . esc_html( $service['name'] ) . '</a> <span class="wppm-info-icon" data-description="' . esc_attr( $service['description'] ) . '">' . esc_html( $styles['icon_char'] ) . '</span>';
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
                        <?php if ( count( $services ) > intval( $styles['show_limit'] ) ) : ?>
                            <button type="button" class="wppm-show-more" style="background: <?php echo esc_attr( $styles['show_more_bg'] ); ?>; color: <?php echo esc_attr( $styles['show_more_color'] ); ?>; padding: <?php echo esc_attr( $styles['show_more_padding'] ); ?>; border-radius: <?php echo esc_attr( $styles['show_more_radius'] ); ?>; font-size: <?php echo esc_attr( $styles['show_more_font_size'] ); ?>; margin-top:10px;">
                                <?php echo esc_html( $styles['show_more_text'] ); ?>
                            </button>
                        <?php endif; ?>
                </div>
                <?php
	}
}

add_action( 'elementor/widgets/widgets_registered', function( $widgets_manager ) {
	$widgets_manager->register_widget_type( new Elementor_Price_List_Widget() );
} );
