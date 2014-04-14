<?php
/*
Plugin Name: Storeya Products Feed for WP e-Commerce
Plugin URI: http://wordpress.org/plugins/wpec-facebook-shop/
Description: StoreYa's plugin automatically imports your WP-Commerce web stores onto your Facebook fan page having it fully customized to fit both the Facebook arena and the original brand's look & feel.
Version: 1.0
Author: StoreYa
Author URI: http://www.storeya.com/

=== VERSION HISTORY ===
01.10.13 - v1.0 - The first version

=== LEGAL INFORMATION ===
Copyright © 2012 StoreYa Feed LTD - http://www.storeya.com/

License: GPLv2 
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

$plugurldir = get_option('siteurl') . '/' . PLUGINDIR . '/storeya-products-feed-e-c/';
$spf_domain = 'StoreyaProductsFeed';
load_plugin_textdomain($spf_domain, 'wp-content/plugins/storeya-products-feed-e-c');
add_action('init', 'spf_init');
//add_action('wp_footer', 'spf_insert');
add_action('init', 's_wpsc_feed_publisher');
add_action('admin_notices', 'spf_admin_notice');
add_filter('plugin_action_links', 'spf_plugin_actions', 10, 2);

function spf_init()
{
    if (function_exists('current_user_can') && current_user_can('manage_options'))
        add_action('admin_menu', 'spf_add_settings_page');
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $options = get_option('scpDisable');
}
function spf_settings()
{
    register_setting('storeya-products-feed-e-c-group', 'spfID');
    register_setting('storeya-products-feed-e-c-group', 'scpDisable');
    add_settings_section('storeya-products-feed-e-c', "Storeya Products Feed for WP e-Comerce", "", 'storeya-products-feed-e-c-group');

}
function plugin_get_version_e_c()
{
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__)));
    $plugin_file   = basename((__FILE__));
    return $plugin_folder[$plugin_file]['Version'];
}

function spf_admin_notice()
{
    if (get_option('spfID'))
        echo ('<div class="error"><p><strong>' . sprintf(__('Storeya Products Feed for WP e-Comerce is disabled. Please go to the <a href="%s">plugin page</a> and enable it.'), admin_url('options-general.php?page=storeya-products-feed-e-c')) . '</strong></p></div>');
}
function spf_plugin_actions($links, $file)
{
    static $this_plugin;
    if (!$this_plugin)
        $this_plugin = plugin_basename(__FILE__);
    if ($file == $this_plugin && function_exists('admin_url')) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=storeya-products-feed-e-c') . '">' . __('Settings', $spf_domain) . '</a>';
        array_unshift($links, $settings_link);
    }
    return ($links);
}

    function spf_add_settings_page()
    {
        function spf_settings_page()
        {
            global $spf_domain, $plugurldir, $storeya_options;
?>
      <div class="wrap">
        <?php
            screen_icon();
?>
        <h2><?php
            _e('Storeya Products Feed for WP e-Comerce ', $spf_domain);
?> <small><?
            echo plugin_get_version_e_c();
?></small></h2>
        <div class="metabox-holder meta-box-sortables ui-sortable pointer">
          <div class="postbox" style="float:left;width:30em;margin-right:20px">
            <h3 class="hndle"><span><?php
            _e('Storeya Products Feed for WP e-Comerce - Settings', $spf_domain);
?></span></h3>
            <div class="inside" style="padding: 0 10px">
              <p style="text-align:center"><a href="http://www.storeya.com/" title="<?php
            _e('Convert your visitors to paying customers with StoreYa!', $spf_domain);
?>"><img src="<?php
            echo ($plugurldir);
?>logo.gif" height="50" width="50" alt="<?php
            _e('StoreYa Logo', $spf_domain);
?>" /></a></p>
              <form method="post" action="options.php">
                <?php
            settings_fields('storeya-products-feed-e-c-group');
?>
                <p><label for="spfID"><strong>Congratulations!</strong></label></p>
                <p>You have successfully generated a Products Feed for your store!</p>
                <ol>
                 <li>Please go to <a href="http://www.storeya.com/" target=_blank >www.StoreYa.com</a>.</li>
                  <li>If you are not logged in, please click on the "Get started now - Connect with Facebook" button, and choose WP as your store's solution.</li>
                   <li>Type in your Store's URL and click on the "Continue" button and then on the "Activate" button.</li>
                    <li>Connect your store to your Facebook fan page.</li>
                     <li>Once you are happy with your Facebook store's customization, have it published!</li>
                </ol>
<strong>Settings</strong>
<br/>
                  <p><input type="checkbox" name="spfID" <?php 
		  if (get_option('spfID')) {		 
				 echo 'checked="checked"'; 	 
			} 				  
	
?> value = "true"> Disable feed</p>
                    <p class="submit">
                      <input type="submit" class="button-primary" value="<?php
            _e('Save Changes');
?>" /> </p>
                  </form>
</p>
                  <p style="font-size:smaller;color:#999239;background-color:#ffffe0;padding:0.4em 0.6em !important;border:1px solid #e6db55;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px">
                  
                  Sell more products with a store tab on your Facebook page!
                  <br/>
                  <?php
            printf(__('%1$sKeep your visitors engaged with you in all social networks you are active on!%2$sImport Your Store to Facebook! %3$s', $spf_domain), '<a href="http://www.storeya.com/" target=_blank title="', '">', '</a>');
?>

</p>
                  </div>
                </div>




                </div>
              </div>
              <?php
        }
        add_action('admin_init', 'spf_settings');
        add_submenu_page('options-general.php', __('Storeya Products Feed for WP e-Comerce', $spf_domain), __('Storeya Products Feed for WP e-Comerce', $spf_domain), 'manage_options', 'storeya-products-feed-e-c', 'spf_settings_page');
    }
	
	function s_wpsc_feed_publisher() {

	// If the user wants a product feed, then hook-in the product feed function
	if ( isset($_GET["rss"]) && ($_GET["rss"] == "true") &&
	     ($_GET["action"] == "wp_e_commerce_storeya") ) {

 if (!get_option('spfID'))
    		add_action( 'wp', 's_wpsc_generate_product_feed' );

  	}

}




function s_wpsc_generate_product_feed() {

	global $wpdb, $wp_query, $post;

    set_time_limit(0);

    $xmlformat = '';
    if ( isset( $_GET['xmlformat'] ) ) {
    	$xmlformat = $_GET['xmlformat'];
    }

	// Don't build up a huge posts cache for the whole store - http://code.google.com/p/wp-e-commerce/issues/detail?id=885
	// WP 3.3+ only
	if ( function_exists ( 'wp_suspend_cache_addition' ) ) {
		wp_suspend_cache_addition(true);
	}

    $chunk_size = apply_filters ( 'wpsc_productfeed_chunk_size', 50 );

    // Don't cache feed under WP Super-Cache
    define( 'DONOTCACHEPAGE',TRUE );

	$selected_category = '';
	$selected_product = '';

	$args = array(
			'post_type'     => 'wpsc-product',
			'numberposts'   => $chunk_size,
			'offset'        => 0,
			'cache_results' => false,
		);

	$args = apply_filters( 'wpsc_productfeed_query_args', $args );

	$self = home_url( "/index.php?rss=true&amp;action=product_list$selected_category$selected_product" );

	header("Content-Type: application/xml; charset=UTF-8");
	header('Content-Disposition: inline; filename="E-Commerce_Product_List.rss"');

	echo "<?xml version='1.0' encoding='UTF-8' ?>\n\r";
	echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'";

	$google_checkout_note = false;

	if ( $xmlformat == 'google' ) {
		echo ' xmlns:g="http://base.google.com/ns/1.0"';
		// Is Google Checkout available as a payment gateway
        	$selected_gateways = get_option('custom_gateway_options');
		if (in_array('google',$selected_gateways)) {
			$google_checkout_note = true;
		}
	} else {
		echo ' xmlns:product="http://www.buy.com/rss/module/productV2/"';
	}

	echo ">\n\r";
	echo "  <channel>\n\r";
	echo "    <title><![CDATA[" . sprintf( _x( '%s Products', 'XML Feed Title', 'wpsc' ), get_option( 'blogname' ) ) . "]]></title>\n\r";
	echo "    <link>" . admin_url( 'admin.php?page=' . WPSC_DIR_NAME . '/display-log.php' ) . "</link>\n\r";
	echo "    <description>" . _x( 'This is the WP e-Commerce Product List RSS feed', 'XML Feed Description', 'wpsc' ) . "</description>\n\r";
	echo "    <generator>" . _x( 'WP e-Commerce Plugin', 'XML Feed Generator', 'wpsc' ) . "</generator>\n\r";
	echo "    <atom:link href='$self' rel='self' type='application/rss+xml' />\n\r";

	$products = get_posts( $args );

	while ( count ( $products ) ) {

		foreach ($products as $post) {

			setup_postdata($post);

			$purchase_link = wpsc_product_url($post->ID);

			echo "    <item>\n\r";
			if ($google_checkout_note) {
				echo "      <g:payment_notes>" . _x( 'Google Wallet', 'Google Checkout Payment Notes in XML Feed', 'wpsc' ) . "</g:payment_notes>\n\r";
			}
			echo "      <title><![CDATA[".get_the_title()."]]></title>\n\r";
			echo "      <link>$purchase_link</link>\n\r";
			echo "      <description><![CDATA[".apply_filters ('the_content', get_the_content())."]]></description>\n\r";
			echo "      <guid>$purchase_link</guid>\n\r";

			$image_link = wpsc_the_product_thumbnail() ;

			if ($image_link !== FALSE) {

				if ( $xmlformat == 'google' ) {
					echo "      <g:image_link><![CDATA[$image_link]]></g:image_link>\n\r";
				} else {
					echo "      <g:image_link><![CDATA[". esc_url( $image_link ) ."]]></g:image_link>\n\r";   
				}

			}

			$price = wpsc_calculate_price($post->ID);
			$currargs = array(
				'display_currency_symbol' => false,
				'display_decimal_point'   => true,
				'display_currency_code'   => false,
				'display_as_html'         => false
			);
			$price = wpsc_currency_display($price, $currargs);

			$children = get_children(array('post_parent'=> $post->ID,
						                   'post_type'=>'wpsc-product'));

			foreach ($children as $child) {
				$child_price = wpsc_calculate_price($child->ID);

				if (($price == 0) && ($child_price > 0)) {
					$price = $child_price;
				} else if ( ($child_price > 0) && ($child_price < $price) ) {
					$price = $child_price;
				}
			}

			if ( $xmlformat == 'google' ) {

				echo "      <g:price>".$price."</g:price>\n\r";

				$google_elements = Array ();

				$product_meta = get_post_custom ( $post->ID );

                if ( is_array ( $product_meta ) ) {
				    foreach ( $product_meta as $meta_key => $meta_value ) {
					    if ( stripos($meta_key,'g:') === 0 )
						    $google_elements[$meta_key] = $meta_value;
				    }
                }

				$google_elements = apply_filters( 'wpsc_google_elements', array ( 'product_id' => $post->ID, 'elements' => $google_elements ) );
				$google_elements = $google_elements['elements'];

	            $done_condition = FALSE;
	            $done_availability = FALSE;
	            $done_weight = FALSE;

	            if ( count ( $google_elements ) ) {

					foreach ( $google_elements as $element_name => $element_values ) {

						foreach ( $element_values as $element_value ) {

							echo "      <".$element_name.">";
							echo "<![CDATA[".$element_value."]]>";
							echo "</".$element_name.">\n\r";

						}

						if ($element_name == 'g:shipping_weight')
							$done_weight = TRUE;

						if ($element_name == 'g:condition')
							$done_condition = TRUE;

	                    if ($element_name == 'g:availability')
	                        $done_availability = true;
					}

				}

	            if (!$done_condition)
					echo "      <g:condition>new</g:condition>\n\r";

	            if (!$done_availability) {

	                if(wpsc_product_has_stock()) :
	                    $product_availability = "in stock";
	                else :
	                    $product_availability = "out of stock";
	                endif ;

	                echo " <g:availability>$product_availability</g:availability>";

	            }

				if ( ! $done_weight ) {
					$wpsc_product_meta = get_product_meta( $post->ID, 'product_metadata',true );
					$weight = apply_filters ( 'wpsc_google_shipping_weight', $wpsc_product_meta['weight'], $post->ID );
					if ( $weight && is_numeric ( $weight ) && $weight > 0 ) {
						echo "<g:shipping_weight>$weight pounds</g:shipping_weight>";
					}
				}

			} else {
				
				echo "      <g:price>".$price."</g:price>\n\r";

			}

			echo "    </item>\n\r";

		}

		$args['offset'] += $chunk_size;
		$products = get_posts ( $args );

	}

	echo "  </channel>\n\r";
	echo "</rss>";
	exit();
}

?>