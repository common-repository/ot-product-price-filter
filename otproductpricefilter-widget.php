<?php

/**
 * OT Product Price Filter
 *
 * @description: The Facebook Reviews Widget
 * @since      : 1.0
 */

class otpf_widget extends WP_Widget {
    public $options;

    public $widget_fields = array(
        'title'              => '',
        'minValue'           => 0,
        'maxValue'           => 300,
        'minValueStart'      => 20,
        'maxValueStart'      => 250,
        'widgetclass'        => '',
    );
	function __construct() {
        parent::__construct(
            'otpf_widget', // Base ID
            'OT Product Price Filter', // Name
            array(
                'classname'   => 'ot-product-price-filter',
                'description' => otpf_e('Shows a price filter slider in a widget which lets you narrow down the list of shown products when viewing product categories.')
            )
        );
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), '0.4.2' );
        wp_register_script( 'wc-jquery-ui-touchpunch', WC()->plugin_url() . '/assets/js/jquery-ui-touch-punch/jquery-ui-touch-punch' . $suffix . '.js', array( 'jquery-ui-slider' ), WC_VERSION, true );
        wp_register_script( 'wc-price-slider', WC()->plugin_url() . '/assets/js/frontend/price-slider' . $suffix . '.js', array( 'jquery-ui-slider', 'wc-jquery-ui-touchpunch', 'accounting' ), WC_VERSION, true );
        wp_localize_script( 'wc-price-slider', 'woocommerce_price_slider_params', array(
            'min_price'                    => isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : '',
            'max_price'                    => isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : '',
            'currency_format_num_decimals' => 0,
            'currency_format_symbol'       => get_woocommerce_currency_symbol(),
            'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
            'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
            'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
        ) );
	}
	function form( $instance ) {
        global $wp_version;
        foreach ($this->widget_fields as $field => $value) {
            if (array_key_exists($field, $this->widget_fields)) {
                ${$field} = !isset($instance[$field]) ? $value : esc_attr($instance[$field]);
            }
        }
		?>
        <div id="<?php echo $this->id; ?>">
            <?php include(dirname(__FILE__) . '/otproductpricefilter-options.php'); ?>
        </div>
		<?php
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
        foreach ($this->widget_fields as $field => $value) {
            $instance[$field] = strip_tags(stripslashes($new_instance[$field]));
        }
        return $instance;
	}
	function widget( $args, $instance ) {
		extract($args);
        foreach ($this->widget_fields as $variable => $value) {
            ${$variable} = !isset($instance[$variable]) ? $this->widget_fields[$variable] : esc_attr($instance[$variable]);
        }
		global $wp, $wp_the_query;

        if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
            return;
        }

        if ( ! $wp_the_query->post_count ) {
            return;
        }

        $min_price = isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : '';
		$max_price = isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : '';

		wp_enqueue_script( 'wc-price-slider' );

		$prices = $this->get_filtered_price();
        $min    = floor( $prices->min_price );
        $max    = ceil( $prices->max_price );

        if ( $min === $max ) {
            return;
        }

        if ( '' === get_option( 'permalink_structure' ) ) {
            $form_action = remove_query_arg( array( 'page', 'paged' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
        } else {
            $form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
        }
        

		if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) {
            $tax_classes = array_merge( array( '' ), WC_Tax::get_tax_classes() );
            $class_max   = $max;

            foreach ( $tax_classes as $tax_class ) {
                if ( $tax_rates = WC_Tax::get_rates( $tax_class ) ) {
                    $class_max = $max + WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max, $tax_rates ) );
                }
            }

            $max = $class_max;
        }

        ?>

    	<div class="widget ot-product-price-filter <?php echo $widget_class ?>">
            <?php if($title){ ?><h2 class="ot-product-price-filter-title widget-title"><?php echo $title; ?></h2><?php } ?>
            <div class="clearfix">
            	<p><?php _e( 'Price Range', 'ot_swatch' ) ?>: <span id="amount"></span></p>
            	<div id="otpricefilter"></div>
            	<form method="get" action="<?php echo esc_url( $form_action ) ?>">
        			<div class="ot-price-filter-wrapper">
      					<input type="text" id="min_price" name="min_price" />
      					<input type="text" id="max_price" name="max_price" />
      					<div class="clear"></div>
      					<button type="submit" class="button"><?php echo esc_html__( 'Submit', 'otwoocommercebrands' ) ?></button>
		                   <?php wc_query_string_form_fields( null, array( 'min_price', 'max_price' ), '', true ) ?>
        			</div>
            	</form>
                <script type="text/javascript">
                    jQuery.noConflict();
                    jQuery(document).ready(function($){
                        var otpricefilter = document.getElementById('otpricefilter');

                        noUiSlider.create(otpricefilter, {
                            start: [ <?php echo $minValueStart ?>, <?php echo $maxValueStart ?> ],
                            connect: true,
                            range: {
                                'min': [  <?php echo $minValue ?> ],
                                'max': [ <?php echo $maxValue ?> ]
                            }
                        });

                        var min_price = document.getElementById('min_price');
                        var max_price = document.getElementById('max_price');
                        otpricefilter.noUiSlider.on('update', function( values, handle ) {

                            var value = values[handle];

                            if ( handle ) {
                                max_price.value = value;
                                
                            } else {
                                min_price.value = value;
                            }
                            $( "#amount" ).html( "<?php echo get_woocommerce_currency_symbol(); ?>" + min_price.value + " - <?php echo get_woocommerce_currency_symbol(); ?>" + max_price.value );
                        });
                        min_price.addEventListener('change', function(){
                            otpricefilter.noUiSlider.set([this.value, null]);
                        });
                        max_price.addEventListener('change', function(){
                            otpricefilter.noUiSlider.set([null,this.value]);
                        });
                    });
                </script>
                <?php
                
                ?>
            </div>
        </div>
        <?php
	}
	protected function get_filtered_price() {
        global $wpdb, $wp_the_query;

        $args       = $wp_the_query->query_vars;
        $tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
        $meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

        if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
            $tax_query[] = array(
                'taxonomy' => $args['taxonomy'],
                'terms'    => array( $args['term'] ),
                'field'    => 'slug',
            );
        }

        foreach ( $meta_query + $tax_query as $key => $query ) {
            if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
                unset( $meta_query[ $key ] );
            }
        }

        $meta_query = new WP_Meta_Query( $meta_query );
        $tax_query  = new WP_Tax_Query( $tax_query );

        $meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
        $tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

        $sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, max( CEILING( price_meta.meta_value ) ) as max_price FROM {$wpdb->posts} ";
        $sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
        $sql .= "   WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
                    AND {$wpdb->posts}.post_status = 'publish'
                    AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
                    AND price_meta.meta_value > '' ";
        $sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

        if ( $search = WC_Query::get_main_search_query_sql() ) {
            $sql .= ' AND ' . $search;
        }

        return $wpdb->get_row( $sql );
    }
}