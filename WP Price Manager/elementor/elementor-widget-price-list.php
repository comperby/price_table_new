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
			<table style="
				border-collapse: collapse;
				width: 100%;
				font-family: <?php echo esc_attr( $settings['typography']['family'] ); ?>;
				font-size: <?php echo esc_attr( $settings['typography']['size'] ); ?>px;
				font-weight: <?php echo esc_attr( $settings['typography']['weight'] ); ?>;
				border: <?php echo esc_attr( $settings['border_width'] ); ?> solid <?php echo esc_attr( $settings['border_color'] ); ?>;
				border-radius: <?php echo esc_attr( $settings['border_radius'] ); ?>;
			">
				<thead style="background: <?php echo esc_attr( $settings['header_bg_color'] ); ?>; color: <?php echo esc_attr( $settings['header_text_color'] ); ?>; text-align: <?php echo esc_attr( $settings['header_alignment'] ); ?>;">
					<tr>
						<th><?php _e( 'Услуга', 'wp-price-manager' ); ?></th>
						<th><?php _e( 'Цена', 'wp-price-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $services ) ) : ?>
						<?php foreach ( $services as $index => $service ) : ?>
							<?php
							$display_price = ( $service['manual_price'] ? $service['price'] : ( $service['default_price'] ? $service['default_price'] : $service['price'] ) );
							?>
							<tr style="background: <?php echo $index % 2 === 0 ? esc_attr( $settings['even_row_bg_color'] ) : esc_attr( $settings['odd_row_bg_color'] ); ?>; height: <?php echo esc_attr( $settings['row_height'] ); ?>;">
								<td>
									<a href="<?php echo esc_url( $service['link'] ); ?>" target="_blank">
										<?php echo esc_html( $service['name'] ); ?>
									</a>
									<span class="wppm-info-icon" data-description="<?php echo esc_attr( $service['description'] ); ?>">
										&#x2753;
									</span>
								</td>
								<td><?php echo esc_html( $display_price ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="2"><?php _e( 'Нет услуг для отображения', 'wp-price-manager' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}

add_action( 'elementor/widgets/widgets_registered', function( $widgets_manager ) {
	$widgets_manager->register_widget_type( new Elementor_Price_List_Widget() );
} );
