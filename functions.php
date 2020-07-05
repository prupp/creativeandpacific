<?php
function divi__child_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'divi__child_theme_enqueue_styles' );
 
 
// Custom functions below this line

// current year for footer copyright
add_shortcode( 'currentyear', 'get_current_year');

function get_current_year()
{
    return date('Y');
}

// show free shipping message in cart
add_action( 'woocommerce_before_cart', 'free_shipping_note' );

// show free shipping message at checkout
add_action( 'woocommerce_checkout_before_order_review', 'free_shipping_note' );

function free_shipping_note() {
           $amount_for_free_shipping = get_free_shipping_amount();
           $customer_shipping_country = WC()->customer->get_shipping_country();
           $cart = WC()->cart->subtotal;
           $remaining = $amount_for_free_shipping - $cart;
           if( $amount_for_free_shipping > $cart && $customer_shipping_country == "CH" ){
               $notice = sprintf( "Kaufe für weitere <b>%s</b> ein und wir <b>liefern kostenlos</b> zu dir nach Hause! (Nur gültig innerhalb der Schweiz)", wc_price($remaining));
               wc_print_notice( $notice , 'notice' );
           }    
}

add_shortcode( 'free_shipping_amount_ch', 'get_free_shipping_amount');

add_shortcode( 'flat_rate_shipping_amount_ch', 'get_flat_rate_shipping_amount');

add_shortcode( 'free_shipping_amount_eu', 'get_free_shipping_amount_eu');

add_shortcode( 'flat_rate_shipping_amount_eu', 'get_flat_rate_shipping_amount_eu');

function get_free_shipping_amount() {
    return get_shipping_amount('free_shipping', 'Schweiz');
}

function get_flat_rate_shipping_amount() {
    return get_shipping_amount('flat_rate', 'Schweiz');
}

function get_free_shipping_amount_eu() {
    return get_shipping_amount('free_shipping', 'EU');
}

function get_flat_rate_shipping_amount_eu() {
    return get_shipping_amount('flat_rate', 'EU');
}

/**
 * Accepts a zone name and returns its value for the given shipping method.
 *
 * @return int The value corresponding to the zone, if there is any. If there is no such zone, or no given shipping method, null will be returned.
 */
function get_shipping_amount($shipping_method, $zone_name) {
    global $woocommerce;

    if ( ! isset( $zone_name ) ) $zone_name = 'Schweiz';
  
    $result = null;
    $zone = null;
  
    $zones = WC_Shipping_Zones::get_zones();
    foreach ( $zones as $z ) {
      if ( $z['zone_name'] == $zone_name ) {
        $zone = $z;
      }
    }
  
    if ( $zone ) {
      $shipping_methods_nl = $zone['shipping_methods'];
      $shipping_methods_result = null;
      foreach ( $shipping_methods_nl as $method ) {
        if ( $method->id == $shipping_method ) {
          $shipping_methods_result = $method;
          break;
        }
      }
  
      if ( $shipping_methods_result ) {
        $result = $shipping_methods_result->min_amount;
        if (!$result) {
            $result = $shipping_methods_result->cost;
        }
      }
    }
  
    return $result;
}


add_shortcode ('cp_shopping_bag', 'cp_shopping_bag' );
/**
 * Create Shortcode for WooCommerce Cart Menu Item
 */
function cp_shopping_bag() {
	ob_start();
 
        $cart_count = WC()->cart->cart_contents_count; // Set variable for cart item count
        $cart_url = wc_get_cart_url();  // Set Cart URL
  
        ?>

		<div class="shopping-cart menu-icon"><a href="<?php echo $cart_url; ?>" title="Mein Warenkorb"><span class="eticon icon_bag"></span>
	    <?php
        if ( $cart_count > 0 ) {
       ?>
            <span class="product-count"><?php echo $cart_count; ?></span>
        <?php
        }
        ?>
        </a></div>
        <?php
	        
    return ob_get_clean();
 
}

add_filter( 'woocommerce_add_to_cart_fragments', 'cp_shopping_bag_count' );
/**
 * Add AJAX Shortcode when cart contents update
 */
function cp_shopping_bag_count( $fragments ) {
 
    ob_start();
    
    $cart_count = WC()->cart->cart_contents_count;
    $cart_url = wc_get_cart_url();
    
    ?>
    <div class="shopping-cart menu-icon"><a href="<?php echo $cart_url; ?>" title="Mein Warenkorb"><span class="eticon icon_bag"></span>
	<?php
    if ( $cart_count > 0 ) {
        ?>
        <span class="product-count"><?php echo $cart_count; ?></span>
        <?php            
    }
        ?></a></div>
    <?php
 
    $fragments['div.shopping-cart'] = ob_get_clean();
     
    return $fragments;
}

add_shortcode ('cp_wishlist', 'cp_wishlist' );
/**
 * Create Shortcode for Wishlist Menu Item
 */
function cp_wishlist() {
	ob_start();
 
        $wishlist_count = TInvWL_Public_WishlistCounter::instance()->counter(); // Set variable for wishlist item count
        $wishlist_url = tinv_url_wishlist_default();  // Set Wishlist URL
  
        ?>

		<div class="wishlist menu-icon"><a href="<?php echo $wishlist_url; ?>" title="Meine Wunschliste"><span class="eticon icon_heart"></span>
	    <?php
        if ( $wishlist_count > 0 ) {
       ?>
            <span class="product-count"><?php echo $wishlist_count; ?></span>
        <?php
        }
        ?>
        </a></div>
        <?php
	        
    return ob_get_clean();
 
}

add_filter( 'woocommerce_add_to_cart_fragments', 'cp_wishlist_count' );
/**
 * Add AJAX Shortcode when wishlist contents update
 */
function cp_wishlist_count( $fragments ) {
 
    ob_start();
    
    $wishlist_count = TInvWL_Public_WishlistCounter::instance()->counter(); // Set variable for wishlist item count
    $wishlist_url = tinv_url_wishlist_default();  // Set Wishlist URL
    
    ?>
    <div class="wishlist menu-icon"><a href="<?php echo $wishlist_url; ?>" title="Meine Wunschliste"><span class="eticon icon_heart"></span>
	<?php
    if ( $wishlist_count > 0 ) {
        ?>
        <span class="product-count"><?php echo $wishlist_count; ?></span>
        <?php            
    }
        ?></a></div>
    <?php
 
    $fragments['div.wishlist'] = ob_get_clean();
     
    return $fragments;
}

// Add "Add to Cart" buttons in Divi shop pages
add_action( 'woocommerce_after_shop_loop_item', function() {
    global $product;

    woocommerce_template_loop_add_to_cart( array(
        'class'      => implode(
            ' ',
            array_filter(
                array(
                    'cp_add_to_cart_button',
                    'product_type_' . $product->get_type(),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
                )
            )
        ),
    ) );
} );

// Conditional function: Checking cart items stock
function is_out_of_stock_item_in_cart(){
    $out_of_stock = false; // initializing

    // Loop through cart items
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        // Get an instance of the WC_Product Object
        $product = $cart_item['data'];
        // Get stock quantity
        $stock_qty = $product->get_stock_quantity();
        // get cart item quantity
        $item_qty  = $cart_item['quantity'];

        if( ( $stock_qty - $item_qty ) < 0 ){
            $out_of_stock = true; // not enough stock
        }
    }
    // return true if at least one product is out of stock
    return $out_of_stock;
}

// Display a custom delivery notice
add_action( 'woocommerce_after_cart_table', 'display_delivery_notification' );
add_action( 'woocommerce_review_order_before_payment', 'display_delivery_notification' );

function display_delivery_notification() {
    if( is_out_of_stock_item_in_cart() ){
        $message = __("Im Warenkorb befinden sich Produkte, welche zuerst produziert werden müssen. Daher wird die Lieferung voraussichtlich in 5-9 Werktagen bei dir eintreffen. (Gilt nur für Versand innerhalb der Schweiz)");
    } else {
        $message = __("Yay, alle Produkte im Warenkorb sind an Lager. Daher wird die Lieferung voraussichtlich in 1-3 Werktagen bei dir eintreffen. (Gilt nur für Versand innerhalb der Schweiz)");
    }
    wc_print_notice( $message, 'notice');
}

// place social login buttons in login form
add_action( 'woocommerce_login_form_end', 'the_champ_login_button' );

// custom social login buttons
function heateor_ss_custom_social_login_icons( $html, $theChampLoginOptions, $widget ) {
    if ( isset( $theChampLoginOptions['providers'] ) && is_array($theChampLoginOptions['providers'] ) && count( $theChampLoginOptions['providers'] ) > 0 ) {
        $html = the_champ_login_notifications( $theChampLoginOptions );
        if ( ! $widget ) {
            $html .= '<div class="the_champ_outer_login_container">';
            if ( isset( $theChampLoginOptions['title'] ) && $theChampLoginOptions['title'] != '' ) {
                $html .= '<div class="the_champ_social_login_title" style="margin-top:20px;margin-bottom:15px;">' . $theChampLoginOptions['title'] . '</div>';
            }
        }
        $html .= '<div class="the_champ_login_container" style="display:flex;justify-content:space-between;">';
        if ( isset( $theChampLoginOptions['providers'] ) && is_array( $theChampLoginOptions['providers'] ) && count( $theChampLoginOptions['providers'] ) > 0 ) {
            
            if ( in_array('google', $theChampLoginOptions['providers']) ) {
                $html .= '<button style="min-width:48%;" type="button" class="button" name="Login mit Google"value="Google" onclick="theChampInitiateLogin(this)" alt="Login with Google">';
                $html .= '<span><svg style="width:20px;height:20px;fill:#3baa96;margin-right:10px;vertical-align:text-top;" data-name="01_Icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M14.84 6.73H8.16v2.7H12c-.35 1.72-1.85 2.71-3.84 2.71A4.18 4.18 0 0 1 3.93 8a4.23 4.23 0 0 1 6.87-3.21l2.08-2A7.22 7.22 0 0 0 8.16 1 7.06 7.06 0 0 0 1 8a7.06 7.06 0 0 0 7.16 7A6.68 6.68 0 0 0 15 8a6.13 6.13 0 0 0-.16-1.27z"></path></svg>Google</span>';
                $html .= '</button>';
            }

            if (in_array('facebook', $theChampLoginOptions['providers'])) {
                $html .= '<button style="min-width:48%;" type="button" class="button" name="Login mit Facebook"value="Facebook" onclick="theChampInitiateLogin(this)" alt="Login with Facebook">';
                $html .= '<span><svg style="width:20px;height:20px;fill:#3baa96;margin-right:10px;vertical-align:text-top;" data-name="01_Icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M11.53 6H9V4.69s-.11-1.16.63-1.16h1.68V1H8.47a2.37 2.37 0 0 0-2.42 2.42v2.63H4.47v2h1.69v7H9.1V8h1.79l.64-2z"></path></svg>Facebook</span>';
                $html .= '</button>';
            }
            
        }
        $html .= '</div>';
        if ( ! $widget ) {
            $html .= '</div><div style="clear:both"></div>';
        }
    }
    return $html;
}

add_filter( 'the_champ_login_interface_filter', 'heateor_ss_custom_social_login_icons', 10, 3 );

/**
 * Change the default state and country on the checkout page
 */
add_filter( 'default_checkout_billing_country', 'change_default_checkout_country' );
add_filter( 'default_checkout_billing_state', 'change_default_checkout_state' );

function change_default_checkout_country() {
  return 'CH'; // country code
}

function change_default_checkout_state() {
  return 'CH'; // state code
}