<?php
/*
Plugin Name: MSD Site Settings
Description: Provides settings panel for several social/address options and widgets/shortcodes/functions for display.
Version: 0.1
Author: Catherine M OBrien Sandrick (CMOS)
Author URI: http://msdlab.com/biological-assets/catherine-obrien-sandrick/
License: GPL v2
*/
define('MSD_ALT_API','http://msdlab.com/plugin-api/');

class MSDSocial{
	private $the_path;
	private $the_url;
	public $icon_size;
	
	function MSDSocial(){
		$this->the_path = plugin_dir_path(__FILE__);
		$this->the_url = plugin_dir_url(__FILE__);
		$this->icon_size = get_option('msdsocial_icon_size')?get_option('msdsocial_icon_size'):'24';
		/*
		 * Pull in some stuff from other files
		 */
		$this->requireDir($this->the_path . '/inc');
		wp_enqueue_style('msd-social-style',$this->the_url.'css/style.css');
		wp_enqueue_style('msd-social-style-'.$this->icon_size,$this->the_url.'css/style'.$this->icon_size.'.css');
		add_shortcode('msd-address',array(&$this,'get_address'));
		add_shortcode('msd-bizname',array(&$this,'get_bizname'));
		add_shortcode('msd-copyright',array(&$this,'get_copyright'));
		add_shortcode('msd-digits',array(&$this,'get_digits'));
		add_shortcode('msd-social',array(&$this,'social_media'));
	}

//contact information
function get_bizname(){
	$ret .= (get_option('msdsocial_biz_name')!='')?get_option('msdsocial_biz_name'):get_bloginfo('name');
	return $ret;
}
function get_address(){
	if((get_option('msdsocial_street')!='') || (get_option('msdsocial_city')!='') || (get_option('msdsocial_state')!='') || (get_option('msdsocial_zip')!='')) {
		$ret = '<address>';
			$ret .= (get_option('msdsocial_street')!='')?get_option('msdsocial_street').' ':'';
			$ret .= (get_option('msdsocial_street2')!='')?get_option('msdsocial_street2').' ':'';
			$ret .= (get_option('msdsocial_city')!='')?get_option('msdsocial_city').', ':'';
			$ret .= (get_option('msdsocial_state')!='')?get_option('msdsocial_state').' ':'';
			$ret .= (get_option('msdsocial_zip')!='')?get_option('msdsocial_zip').' ':'';
		$ret .= '</address>';
		return $ret;
		} else {
			return false;
		} 
}
function get_digits(){
		if((get_option('msdsocial_phone')!='') || (get_option('msdsocial_fax')!='')) {
		$ret .= '<address>';
			$ret .= (get_option('msdsocial_phone')!='')?'Phone: '.get_option('msdsocial_phone').' ':'';
			$ret .= (get_option('msdsocial_phone')!='') && (get_option('msdsocial_fax')!='')?' | ':'';
			$ret .= (get_option('msdsocial_fax')!='')?'Fax: '.get_option('msdsocial_fax').' ':'';
		$ret .= '</address>';
		return $ret;
		} else {
			return false;
		} 
}
//create copyright message
function copyright($address = TRUE){
	if($address){
		$ret .= $this->msdsocial_get_address();
		$ret .= $this->msdsocial_get_digits();
	}
	$ret .= 'Copyright &copy;'.date('Y').' ';
	$ret .= $this->msdsocial_get_bizname();
	print $ret;
}


function social_media($attr){
	?>
	<div id="social-media" class="social-media">
			<?php if(get_option('msdsocial_linkedin_link')!=""){ ?>
			<a href="<?php echo get_option('msdsocial_linkedin_link'); ?>" class="li" title="LinkedIn" target="_blank">LinkedIn</a>
			<?php }?>
			<?php if(get_option('msdsocial_twitter_user')!=""){ ?>
			<a href="http://www.twitter.com/<?php echo get_option('msdsocial_twitter_user'); ?>" class="tw" title="Follow Us on Twitter!" target="_blank">Twitter</a>
			<?php }?>
			<?php if(get_option('msdsocial_google_link')!=""){ ?>
			<a href="<?php echo get_option('msdsocial_google_link'); ?>" class="gl" title="Google+" target="_blank">Google+</a>
			<?php }?>
			<?php if(get_option('msdsocial_facebook_link')!=""){ ?>
			<a href="<?php echo get_option('msdsocial_facebook_link'); ?>" class="fb" title="Join Us on Facebook!" target="_blank">Facebook</a>
			<?php }?>
			<?php if(get_option('msdsocial_flickr_link')!=""){ ?>
			<a href="<?php echo get_option('msdsocial_flickr_link'); ?>" class="fl" title="Flickr" target="_blank">Flickr</a>
			<?php }?>
			<?php if(get_option('msdsocial_youtube_link')!=""){ ?>
			<a href="<?php echo get_option('msdsocial_youtube_link'); ?>" class="yt" title="YouTube" target="_blank">YouTube</a>
			<?php }?>
			<?php if(get_option('msdsocial_sharethis_link')!=""){ ?>
			<a href="<?php echo get_option('msdsocial_sharethis_link'); ?>" class="st" title="ShareThis" target="_blank">ShareThis</a>
			<?php }?>
			<?php if(get_option('msdsocial_pinterest_link')!=""){ ?>
			<a href="<?php echo get_option('msdsocial_pinterest_link'); ?>" class="pin" title="Pinterest" target="_blank">Pinterest</a>
			<?php }?>
			<?php if(get_option('msdsocial_show_feed')!=""){ ?>
			<a href="<?php bloginfo('rss2_url'); ?>" class="rss" title="RSS Feed" target="_blank">RSS Feed</a>
			<?php }?>
		</div>
		<?php 
}

function requireDir($dir){
	$dh = @opendir($dir);

	if (!$dh) {
		throw new Exception("Cannot open directory $dir");
	} else {
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' && $file != '..') {
				$requiredFile = $dir . DIRECTORY_SEPARATOR . $file;
				if ('.php' === substr($file, strlen($file) - 4)) {
					require_once $requiredFile;
				} elseif (is_dir($requiredFile)) {
					requireDir($requiredFile);
				}
			}
		}
	closedir($dh);
	}
	unset($dh, $dir, $file, $requiredFile);
}
	//end of class
}
$msd_social = new MSDSocial();