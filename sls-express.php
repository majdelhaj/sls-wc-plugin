<?php
/*
Plugin Name:  SLS Express WooCommerce
Plugin URI:   http://sls-express.com
Description:  SLS Express WooCommerce Plugin
Version:      1.1.1
Author:       Majd Abdullatif Alhaj
Author URI:   https://www.facebook.com/majdelhaj
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  slsexpress
Domain Path:  /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    function sls_express_init() {

        if ( ! class_exists( 'SLSExpress_Shipping_Method' ) ) {
            class SLSExpress_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'sls_express';
                    $this->title       = __( 'SLS Express' );
					$this->method_title = __('SLS Express', 'SLS Express');
                    $this->method_description = __( 'SLS Express provides logistical support services for enterprises and service sectors in high-quality and competitive services through national competencies and expertise.' ); //
                    $this->enabled            = "yes"; // This can be added as an setting but for this example its forced enabled
                    $this->init();
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                /**
                 * Initialise Gateway Settings Form Fields
                 */
                function init_form_fields() {
                    $this->form_fields = array(
                        'shipping_cost_main' => array(
                            'title' => __( 'Shipping Cost (Riyadh, Jeddah, Dammam)', 'woocommerce' ),
                            'type' => 'text',
                            'description' => __( 'This controls the cost of shipping for the customers on checkout page.', 'woocommerce' ),
                            'default' => 20
                        ),
						'shipping_cost_other' => array(
                            'title' => __( 'Shipping Cost (Other Cities)', 'woocommerce' ),
                            'type' => 'text',
                            'description' => __( 'This controls the cost of shipping for the customers on checkout page.', 'woocommerce' ),
                            'default' => 25
                        ),
						'cod_cost_main' => array(
                            'title' => __( 'COD Cost (Riyadh, Jeddah, Dammam)', 'woocommerce' ),
                            'type' => 'text',
                            'description' => __( 'This controls the cost of shipping for the customers on checkout page.', 'woocommerce' ),
                            'default' => 5
                        ),
						'cod_cost_other' => array(
                            'title' => __( 'COD Cost (Other Cities)', 'woocommerce' ),
                            'type' => 'text',
                            'description' => __( 'This controls the cost of shipping for the customers on checkout page.', 'woocommerce' ),
                            'default' => 10
                        ),
                        'account_number' => array(
                            'title' => __( 'Account Number', 'woocommerce' ),
                            'type' => 'text'
                        ),
                        'sls_api_token' => array(
                            'title' => __( 'API Token', 'woocommerce' ),
                            'type' => 'text'
                        ),
                        'collection_name' => array(
                            'title' => __( 'Collection Name', 'woocommerce' ),
                            'type' => 'text'
                        ),
                        'collection_address1' => array(
                            'title' => __( 'Collection Address 1', 'woocommerce' ),
                            'type' => 'text'
                        ),
                        'collection_address2' => array(
                            'title' => __( 'Collection Address 2', 'woocommerce' ),
                            'type' => 'text'
                        ),
                        'collection_city' => array(
                            'title' => __( 'Collection City', 'woocommerce' ),
                            'type' => 'text'
                        ),
                        'collection_country' => array(
                            'title' => __( 'Collection Country', 'woocommerce' ),
                            'type' => 'text',
                            'default' => 'Saudi Arabia'
                        ),
                        'collection_phone' => array(
                            'title' => __( 'Collection Phone', 'woocommerce' ),
                            'type' => 'text'
                        ),
                        'collection_email' => array(
                            'title' => __( 'Collection Email', 'woocommerce' ),
                            'type' => 'text'
                        )

                    );
                }
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package = array() ) {
					
					$this->init_settings();
                    
					if(stripos($package["destination"]["city"], "riyad") !== false || stripos($package["destination"]["city"], "رياض") !== false || stripos($package["destination"]["city"], "jed") !== false || stripos($package["destination"]["city"], "جدة") !== false || stripos($package["destination"]["city"], "جده") !== false || stripos($package["destination"]["city"], "dammam") !== false || stripos($package["destination"]["city"], "damam") !== false || stripos($package["destination"]["city"], "دمام") !== false) {

						$rate = array(
							'id'       => $this->id .'_'. $this->instance_id,
							'label'    => $this->title,
							'cost'     => $this->settings['shipping_cost_main'],
							//'calc_tax' => 'per_item'
						);
						
						// Check COD
						if (WC()->session->get( 'chosen_payment_method' ) == "cod") {
							$rate["cost"] += $this->settings['cod_cost_main'];
						}
						
					} else {
						
						$rate = array(
							'id'       => $this->id .'_'. $this->instance_id,
							'label'    => $this->title,
							'cost'     => $this->settings['shipping_cost_other'],
							//'calc_tax' => 'per_item'
						);
						
						// Check COD
						if (WC()->session->get( 'chosen_payment_method' ) == "cod") {
							$rate["cost"] += $this->settings['cod_cost_other'];
						}
						
					}
					
				
					// Register the rate
                    $this->add_rate( $rate );

                }

            }
        }


    }
    add_action( 'woocommerce_shipping_init', 'sls_express_init' );
	//remove_filter( 'woocommerce_shipping_methods', 'add_sls_express_shipping_method' );

    function sendOrderToSLSExpress( $order_id ){

        $order = new WC_Order( $order_id );
		if ($order->get_shipping_country() == "SA") {
            $sls = new SLSExpress_Shipping_Method();

            $price_set_name = "";
            $payment_method = $order->get_payment_method();
            $cod_amount = ($payment_method == "cod")? $order->get_total() : 0;
            if (stripos($order->get_shipping_city(), 'riyad') !== false || mb_strpos($order->get_shipping_city(), 'رياض') !== false) {
                // inside
                if ($payment_method == 'cod') {
                    //$price_set_name = $sls->settings['priceset_inside_cod'];
                    $price_set_name = "inside cod";
                } else {
                    //$price_set_name = $sls->settings['priceset_inside'];
                    $price_set_name = "inside";
                }
            } else {
                // outside
                if ($payment_method == 'cod') {
                    //$price_set_name = $sls->settings['priceset_outside_cod'];
                    $price_set_name = "outside cod";
                } else {
                    //$price_set_name = $sls->settings['priceset_outside'];
                    $price_set_name = "outside";
                }
            }

            $product_details = array();
            $order_items = $order->get_items();
            foreach( $order_items as $item ) {
                $product_details[] = $item['name']." x ".$item['qty'];
            }
            $description = implode(', ', $product_details);

            $shippingData = array(
                'account_number' => $sls->settings['account_number'],
                'api_token' => $sls->settings['sls_api_token'],
                'requested_by' => 'SLS Express WC Plugin',
                'price_set_name' => $price_set_name,
                'order_id' => $order_id,

                'collection_name' => $sls->settings['collection_name'],
                'collection_contact' => $sls->settings['collection_name'],
                'collection_street1' => $sls->settings['collection_address1'],
                'collection_street2' => $sls->settings['collection_address2'],
                'collection_city' => $sls->settings['collection_city'],
                'collection_country' => $sls->settings['collection_country'],
                'collection_phone' => $sls->settings['collection_phone'],
                'collection_email' => $sls->settings['collection_email'],

                'delivery_name' => $order->get_shipping_first_name().' '.$order->get_shipping_last_name(),
                'delivery_contact' => $order->get_shipping_first_name().' '.$order->get_shipping_last_name(),
                'delivery_street1' => $order->get_shipping_address_1(),
                'delivery_street2' => $order->get_shipping_address_2(),
                'delivery_city' => $order->get_shipping_city(),
                'delivery_country' => $order->get_shipping_country(),
                'delivery_postal_code' => $order->get_shipping_postcode(),
                'delivery_phone' => $order->get_billing_phone(),
                'delivery_email' => $order->get_billing_email(),

                'quantity' => 1,
                'weight' => $order->get_item_count() * 0.5,
                'declared_value' => $order->get_subtotal(),
                'description' => $description,
                'comments' => $order->get_customer_note(),

                'cod_amount' => $cod_amount
            );

            $postdata = http_build_query($shippingData);

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
            );
            $context  = stream_context_create($opts);
            $result = file_get_contents('http://www.sls-express.com/api/custom/v1/order/create', false, $context);
            //var_dump($shippingData); var_dump($result); die();

        }
    }
    add_action( 'woocommerce_checkout_order_processed', 'sendOrderToSLSExpress');

    function add_sls_express_shipping_method( $methods ) {
        $methods[] = 'SLSExpress_Shipping_Method';
        return $methods;
    }
    add_filter( 'woocommerce_shipping_methods', 'add_sls_express_shipping_method' );
	
	
	
	function my_hide_shipping_when_free_is_available( $rates ) {
		
		if(array_key_exists('sls_express', $rates)) {
			unset($rates['sls_express']);
		}
		
		return $rates;
		
		/*
		$free = array();
		foreach ( $rates as $rate_id => $rate ) {
			if ( 'free_shipping' === $rate->method_id ) {
				$free[ $rate_id ] = $rate;
				break;
			}
		}
		return ! empty( $free ) ? $free : $rates;
		*/
	}
	//add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );
}

