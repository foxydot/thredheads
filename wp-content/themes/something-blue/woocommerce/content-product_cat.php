<?php
/**
 * The template for displaying product category thumbnails within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product_cat.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 1 );

// Increase loop count
$woocommerce_loop['loop']++;
?>


<li class="row product-category product<?php
    if ( ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] == 0 || $woocommerce_loop['columns'] == 1 )
        echo ' first';
	if ( $woocommerce_loop['loop'] % $woocommerce_loop['columns'] == 0 )
		echo ' last';
	?>">
<div class="col-md-4"><?php do_action( 'woocommerce_before_subcategory', $category ); ?>
    <a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>">
        <?php
            /**
             * woocommerce_before_subcategory_title hook
             *
             * @hooked woocommerce_subcategory_thumbnail - 10
             */
            do_action( 'woocommerce_before_subcategory_title', $category );
        ?>
    </a>
</div>
<div class="col-md-8">
        <h3> <?php echo $category->name; ?> </h3>
		<?php
			/**
			 * woocommerce_after_subcategory_title hook
			 */
			do_action( 'woocommerce_after_subcategory_title', $category );
            remove_filter('the_content', 'st_add_widget');
		?>
		<div><?php print apply_filters('the_content',$category->description); ?></div>
    <div class="button-wrapper">
         <a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>" class="button" title="<?php echo $category->name; ?>">Take a Closer Look</a>
     </div>
	<?php do_action( 'woocommerce_after_subcategory', $category ); ?>
	</div>

</li>