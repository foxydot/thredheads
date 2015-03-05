<?php
/*
Plugin Name: MSD Site Settings
Description: Provides settings panel for several social/address options and widgets/shortcodes/functions for display.
Version: 0.3
Author: Catherine M OBrien Sandrick (CMOS)
Author URI: http://msdlab.com/biological-assets/catherine-obrien-sandrick/
License: GPL v2
*/

class MSDSocial{
	private $the_path;
	private $the_url;
	public $icon_size;
	function MSDSocial(){$this->__construct();}
    function __construct(){
		$this->the_path = plugin_dir_path(__FILE__);
		$this->the_url = plugin_dir_url(__FILE__);
		$this->icon_size = get_option('msdsocial_icon_size')?get_option('msdsocial_icon_size'):'0';
		/*
		 * Pull in some stuff from other files
		 */
		$this->requireDir($this->the_path . '/inc');
        if(!is_admin()){
    		wp_enqueue_style('msd-social-style',$this->the_url.'css/style.css');
    		wp_enqueue_style('msd-social-style-'.$this->icon_size,$this->the_url.'css/style'.$this->icon_size.'.css');
            wp_enqueue_style('font-awesome-style','//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css');
        }
        add_action('admin_enqueue_scripts', array(&$this,'add_admin_scripts') );
        add_action('admin_enqueue_scripts', array(&$this,'add_admin_styles') );
        
		add_shortcode('msd-address',array(&$this,'get_address'));
		add_shortcode('msd-bizname',array(&$this,'get_bizname'));
		add_shortcode('msd-copyright',array(&$this,'get_copyright'));
		add_shortcode('msd-digits',array(&$this,'get_digits'));
        add_shortcode('msd-social',array(&$this,'social_media'));
        add_shortcode('msd-hours',array(&$this,'get_hours'));
	}

        function add_admin_scripts() {
            global $current_screen;
            if($current_screen->id == 'settings_page_msdsocial-options'){
                wp_enqueue_script('bootstrap-jquery','//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js',array('jquery'));
                wp_enqueue_script('timepicker-jquery',$this->the_url.'js/jquery.timepicker.min.js',array('jquery'));
            }
        }
        
        function add_admin_styles() {
            global $current_screen;
            if($current_screen->id == 'settings_page_msdsocial-options'){
                wp_register_style('bootstrap-style','//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css');
                wp_register_style('font-awesome-style','//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css',array('bootstrap-style'));
                wp_register_style('timepicker-style',$this->the_url.'css/jquery.timepicker.css');
                wp_enqueue_style('font-awesome-style');
                wp_enqueue_style('timepicker-style');
            }
        }  


//contact information
function get_bizname(){
	$ret .= (get_option('msdsocial_biz_name')!='')?get_option('msdsocial_biz_name'):get_bloginfo('name');
	return $ret;
}
function get_address(){
	if((get_option('msdsocial_street')!='') || (get_option('msdsocial_city')!='') || (get_option('msdsocial_state')!='') || (get_option('msdsocial_zip')!='')) {
		$ret = '<address itemscope itemtype="http://schema.org/LocalBusiness">';
			$ret .= (get_option('msdsocial_street')!='')?'<span itemprop="streetAddress" class="msdsocial_street">'.get_option('msdsocial_street').'</span> ':'';
			$ret .= (get_option('msdsocial_street2')!='')?'<span itemprop="streetAddress" class="msdsocial_street_2">'.get_option('msdsocial_street2').'</span> ':'';
			$ret .= (get_option('msdsocial_city')!='')?'<span itemprop="addressLocality" class="msdsocial_city">'.get_option('msdsocial_city').'</span>, ':'';
			$ret .= (get_option('msdsocial_state')!='')?'<span itemprop="addressRegion" class="msdsocial_state">'.get_option('msdsocial_state').'</span> ':'';
			$ret .= (get_option('msdsocial_zip')!='')?'<span itemprop="postalCode" class="msdsocial_zip">'.get_option('msdsocial_zip').'</span> ':'';
		$ret .= '</address>';
		  return $ret;
		} else {
			return false;
		} 
}

function get_digits($dowrap = TRUE,$sep = " | "){
        $sepsize = count($sep);
		if((get_option('msdsocial_phone')!='') || (get_option('msdsocial_tollfree')!='') || (get_option('msdsocial_fax')!='')) {
		    if((get_option('msdsocial_tracking_phone')!='')){
		        if(wp_is_mobile()){
		          $phone .= 'Phone: <a href="tel:+1'.get_option('msdsocial_tracking_phone').'">'.get_option('msdsocial_tracking_phone').'</a> ';
		        } else {
		          $phone .= 'Phone: <span>'.get_option('msdsocial_tracking_phone').'</span> ';
		        }
		      $phone .= '<span itemprop="telephone" style="display: none;">'.get_option('msdsocial_phone').'</span> ';
		    } else {
		        if(wp_is_mobile()){
		          $phone .= (get_option('msdsocial_phone')!='')?'Phone: <a href="tel:+1'.get_option('msdsocial_phone').'" itemprop="telephone">'.get_option('msdsocial_phone').'</a> ':'';
		        } else {
                  $phone .= (get_option('msdsocial_phone')!='')?'Phone: <span itemprop="telephone">'.get_option('msdsocial_phone').'</span> ':'';
		        }
		    }
            if((get_option('msdsocial_tracking_tollfree')!='')){
                if(wp_is_mobile()){
                  $tollfree .= 'Phone: <a href="tel:+1'.get_option('msdsocial_tracking_tollfree').'">'.get_option('msdsocial_tracking_tollfree').'</a> ';
                } else {
                  $tollfree .= 'Phone: <span>'.get_option('msdsocial_tracking_tollfree').'</span> ';
                }
              $tollfree .= '<span itemprop="telephone" style="display: none;">'.get_option('msdsocial_tollfree').'</span> ';
            } else {
                if(wp_is_mobile()){
                  $tollfree .= (get_option('msdsocial_tollfree')!='')?'Phone: <a href="tel:+1'.get_option('msdsocial_tollfree').'" itemprop="telephone">'.get_option('msdsocial_tollfree').'</a> ':'';
                } else {
                  $tollfree .= (get_option('msdsocial_tollfree')!='')?'Phone: <span itemprop="telephone">'.get_option('msdsocial_tollfree').'</span> ':'';
                }
            }
            $fax = (get_option('msdsocial_fax')!='')?'Fax: <span itemprop="faxNumber">'.get_option('msdsocial_fax').'</span> ':'';
            $ret = $phone;
            $ret .= ($phone!='' && $tollfree!='')?$sep:'';
            $ret .= $tollfree;
            $ret .= (!strpos($ret,$sep,$sepsize))?$sep:'';
            $ret .= $fax;
 		  if($dowrap){$ret = '<address itemscope itemtype="http://schema.org/LocalBusiness">'.$ret.'</address>';}
		return $ret;
		} else {
			return false;
		} 
}

function get_phone($dowrap = TRUE){
        if((get_option('msdsocial_phone')!='')) {
            if((get_option('msdsocial_tracking_phone')!='')){
                if(wp_is_mobile()){
                  $ret .= '<a href="tel:+1'.get_option('msdsocial_tracking_phone').'">'.get_option('msdsocial_tracking_phone').'</a> ';
                } else {
                  $ret .= '<span>'.get_option('msdsocial_tracking_phone').'</span> ';
                }
              $ret .= '<span itemprop="telephone" style="display: none;">'.get_option('msdsocial_phone').'</span> ';
            } else {
                if(wp_is_mobile()){
                  $ret .= (get_option('msdsocial_phone')!='')?'<a href="tel:+1'.get_option('msdsocial_phone').'" itemprop="telephone">'.get_option('msdsocial_phone').'</a> ':'';
                } else {
                  $ret .= (get_option('msdsocial_phone')!='')?'<span itemprop="telephone">'.get_option('msdsocial_phone').'</span> ':'';
                }
            }
          if($dowrap){$ret = '<address itemscope itemtype="http://schema.org/LocalBusiness">'.$ret.'</address>';}
        return $ret;
        } else {
            return false;
        } 
}

function get_hours($atts = array()){
    extract( shortcode_atts( array(
                'sep' => ' | ',
                'additup' => TRUE
            ), $atts ) );
    $days = array(
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
            );
            foreach ($days as $day) {
                $open = get_option('msdsocial_hours_'.strtolower($day).'_open');
                $close = get_option('msdsocial_hours_'.strtolower($day).'_close');
                $hours[$day]['open'] = $open;
                $hours[$day]['close'] = $close;
            }
    if($additup){
        foreach($hours as $k => $v){
            if($v['open']==''||$v['close']==''){
                $closed[] = $k;
                if($v != $prev){
                    $ds = $ns;
                    $de = $prek;
                }
            } else {
                if($v == $prev){
                    $ds = $ns;
                } else {
                    $ns = $k;
                    $de = $prek;
                    if($prev){
                        if($ds == $de){
                            $str[] = '<span class="day">'.$ds.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
                        } elseif($de == 'Sunday'){
                            $str[] = '<span class="day">'.$de.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
                        } else {
                            $str[] = '<span class="day">'.$ds.'-'.$de.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
                        }
                        $ds = $ns;
                    }
                }
            $prek = $k;
            $prev = $v;
            }
        }
        $de = $prek;
        if($ds == $de){
            $str[] = '<span class="day">'.$ds.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
        } else {
            $str[] ='<span class="day">'. $ds.'-'.$de.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
        }
    } else {
        foreach($hours as $k => $v){
            if($v['open']==''||$v['close']==''){
                $str[] = '<span class="day">'.$k.':</span> <span class="hours">Closed</span>';
            } else {
                $str[] = '<span class="day">'.$k.':</span> <span class="hours">'.$v['open'].'-'.$v['close'].'</span>';
            }
        }
    }
    return implode($sep,$str);
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


function social_media($atts = array()){
    extract( shortcode_atts( array(
            ), $atts ) );
    
    $ret = '<div id="social-media" class="social-media">';
    if(get_option('msdsocial_facebook_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_facebook_link').'" class="fa fa-facebook" title="Join Us on Facebook!" target="_blank"></a>';
    }    
    if(get_option('msdsocial_twitter_user')!=""){
        $ret .= '<a href="http://www.twitter.com/'.get_option('msdsocial_twitter_user').'" class="fa fa-twitter" title="Follow Us on Twitter!" target="_blank"></a>';
    }    
    if(get_option('msdsocial_pinterest_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_pinterest_link').'" class="fa fa-pinterest" title="Pinterest" target="_blank"></a>';
    }    
    if(get_option('msdsocial_google_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_google_link').'" class="fa fa-google-plus" title="Google+" target="_blank"></a>';
    }    
    if(get_option('msdsocial_linkedin_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_linkedin_link').'" class="fa fa-linkedin" title="LinkedIn" target="_blank"></a>';
    }    
    if(get_option('msdsocial_instagram_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_instagram_link').'" class="fa fa-instagram" title="Instagram" target="_blank"></a>';
    }    
    if(get_option('msdsocial_tumblr_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_tumblr_link').'" class="fa fa-tumblr" title="Tumblr" target="_blank"></a>';
    }    
    if(get_option('msdsocial_reddit_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_reddit_link').'" class="fa fa-reddit" title="Reddit" target="_blank"></a>';
    }    
    if(get_option('msdsocial_flickr_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_flickr_link').'" class="fa fa-flickr" title="Flickr" target="_blank"></a>';
    }    
    if(get_option('msdsocial_youtube_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_youtube_link').'" class="fa fa-youtube" title="YouTube" target="_blank"></a>';
    }    
    if(get_option('msdsocial_vimeo_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_vimeo_link').'" class="fa fa-vimeo-square" title="Vimeo" target="_blank"></a>';
    }    
    if(get_option('msdsocial_vine_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_vine_link').'" class="fa fa-vine" title="Vine" target="_blank"></a>';
    }    
    if(get_option('msdsocial_sharethis_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_sharethis_link').'" class="fa fa-share-alt" title="ShareThis" target="_blank"></a>';
    }    
    if(get_option('msdsocial_contact_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_contact_link').'" class="fa fa-envelope" title="Contact Us" target="_blank"></a>';
    }    
    if(get_option('msdsocial_show_feed')!=""){
        $ret .= '<a href="'.get_bloginfo('rss2_url').'" class="fa fa-rss" title="RSS Feed" target="_blank"></a>';
    }
    $ret .= '</div>';
    return $ret;
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