<?php
/*******************************
  THEME OPTIONS PAGE
********************************/

add_action('admin_menu', 'msdsocial_theme_page');
function msdsocial_theme_page ()
{
	if ( count($_POST) > 0 && isset($_POST['msdsocial_settings']) )
	{
		$options = array (
		'biz_name',
		'street',
		'street2',
		'city',
		'state',
		'zip',
		'lat',
		'lng',
        'phone',
        'tracking_phone',
        'tollfree',
        'tracking_tollfree',
		'fax',
		'email',
		'careof',
		'mailing_street',
		'mailing_street2',
		'mailing_city',
		'mailing_state',
		'mailing_zip',
		'facebook_link',
		'twitter_user',
		'pinterest_link',
		'google_link',
		'linkedin_link',
        'instagram_link',
        'tumblr_link',
        'reddit_link',
        'flickr_link',
        'youtube_link',
        'vimeo_link',
        'vine_link',
		'sharethis_link',
		'contact_link',
		'show_feed',
		'hours_sunday_open',
        'hours_sunday_close',
        'hours_monday_open',
        'hours_monday_close',
        'hours_tuesday_open',
        'hours_tuesday_close',
        'hours_wednesday_open',
        'hours_wednesday_close',
        'hours_thursday_open',
        'hours_thursday_close',
        'hours_friday_open',
        'hours_friday_close',
        'hours_saturday_open',
        'hours_saturday_close',
        );
		
		foreach ( $options as $opt )
		{
			delete_option ( 'msdsocial_'.$opt, $_POST[$opt] );
			add_option ( 'msdsocial_'.$opt, $_POST[$opt] );	
		}			
		 
	}
	add_submenu_page('options-general.php',__('Settings'), __('MSD Site Settings'), 'administrator', 'msdsocial-options', 'msdsocial_settings');
}
function msdsocial_settings()
{
$states = array('ALABAMA'=>"AL",
'ALASKA'=>"AK",
'AMERICAN SAMOA'=>"AS",
'ARIZONA'=>"AZ",
'ARKANSAS'=>"AR",
'CALIFORNIA'=>"CA",
'COLORADO'=>"CO",
'CONNECTICUT'=>"CT",
'DELAWARE'=>"DE",
'DISTRICT OF COLUMBIA'=>"DC",
"FEDERATED STATES OF MICRONESIA"=>"FM",
'FLORIDA'=>"FL",
'GEORGIA'=>"GA",
'GUAM' => "GU",
'HAWAII'=>"HI",
'IDAHO'=>"ID",
'ILLINOIS'=>"IL",
'INDIANA'=>"IN",
'IOWA'=>"IA",
'KANSAS'=>"KS",
'KENTUCKY'=>"KY",
'LOUISIANA'=>"LA",
'MAINE'=>"ME",
'MARSHALL ISLANDS'=>"MH",
'MARYLAND'=>"MD",
'MASSACHUSETTS'=>"MA",
'MICHIGAN'=>"MI",
'MINNESOTA'=>"MN",
'MISSISSIPPI'=>"MS",
'MISSOURI'=>"MO",
'MONTANA'=>"MT",
'NEBRASKA'=>"NE",
'NEVADA'=>"NV",
'NEW HAMPSHIRE'=>"NH",
'NEW JERSEY'=>"NJ",
'NEW MEXICO'=>"NM",
'NEW YORK'=>"NY",
'NORTH CAROLINA'=>"NC",
'NORTH DAKOTA'=>"ND",
"NORTHERN MARIANA ISLANDS"=>"MP",
'OHIO'=>"OH",
'OKLAHOMA'=>"OK",
'OREGON'=>"OR",
"PALAU"=>"PW",
'PENNSYLVANIA'=>"PA",
'RHODE ISLAND'=>"RI",
'SOUTH CAROLINA'=>"SC",
'SOUTH DAKOTA'=>"SD",
'TENNESSEE'=>"TN",
'TEXAS'=>"TX",
'UTAH'=>"UT",
'VERMONT'=>"VT",
'VIRGIN ISLANDS' => "VI",
'VIRGINIA'=>"VA",
'WASHINGTON'=>"WA",
'WEST VIRGINIA'=>"WV",
'WISCONSIN'=>"WI",
'WYOMING'=>"WY");
	?>
<style>
    span.note{
        display: block;
        font-size: 0.9em;
        font-style: italic;
        color: #999999;
    }
    body{
        background-color: transparent;
    }
    .input-table .description{display:none}
    .input-table li:after{content:".";display:block;clear:both;visibility:hidden;line-height:0;height:0}
    .input-table label{display:block;font-weight:bold;margin-right:1%;float:left;width:14%;text-align:right}
    .input-table label span{display:inline;font-weight:normal}
    .input-table span{color:#999;display:block}
    .input-table .input{width:85%;float:left}
    .input-table .input .half{width:48%;float:left}
    .input-table textarea,.input-table input[type='text'],.input-table select{display:inline;margin-bottom:3px;width:90%}
    .input-table .mceIframeContainer{background:#fff}
    .input-table h4{color:#999;font-size:1em;margin:15px 6px;text-transform:uppercase}
</style>
<div class="wrap">
	<h2>MSDLAB Site Settings</h2>
	<p>This panel provides an interface for site settings used by custom MSDLAB themes and plugins.</p>
	
<form method="post" action="">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs">
      <li class="active"><a href="#social-media" data-toggle="tab">Social Media</a></li>
      <li><a href="#location" data-toggle="tab">Location</a></li>
      <li><a href="#contact" data-toggle="tab">Contact</a></li>
      <li><a href="#hours" data-toggle="tab">Business Hours</a></li>
      <li><a href="#settings" data-toggle="tab">Plugin Settings</a></li>
    </ul>
    
    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="social-media">
          <h2>Social Media Settings</h2>
          <p>Please supply full urls except as indicated. Leave blank any items you do not wish to display.</p>
          <ul class="input-table">
              <li>
                  <label for="facebook_link">Facebook Link <i class="fa fa-facebook"></i></label>
                  <div class="input">
                    <input name="facebook_link" type="text" id="facebook_link" value="<?php echo get_option('msdsocial_facebook_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="twitter_user">Twitter (username only) <i class="fa fa-twitter"></i></label>
                  <div class="input">
                    <input name="twitter_user" type="text" id="twitter_user" value="<?php echo get_option('msdsocial_twitter_user'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="pinterest_link">Pinterest Link <i class="fa fa-pinterest"></i></label>
                  <div class="input">
                    <input name="pinterest_link" type="text" id="pinterest_link" value="<?php echo get_option('msdsocial_pinterest_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="google_link">Google+ Link <i class="fa fa-google-plus"></i></label>
                  <div class="input">
                    <input name="google_link" type="text" id="google_link" value="<?php echo get_option('msdsocial_google_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="linkedin_link">LinkedIn Link <i class="fa fa-linkedin"></i></label>
                  <div class="input">
                    <input name="linkedin_link" type="text" id="linkedin_link" value="<?php echo get_option('msdsocial_linkedin_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="instagram_link">Instagram Link <i class="fa fa-instagram"></i></label>
                  <div class="input">
                    <input name="instagram_link" type="text" id="instagram_link" value="<?php echo get_option('msdsocial_instagram_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="tumblr_link">Tumblr Link <i class="fa fa-tumblr"></i></label>
                  <div class="input">
                    <input name="tumblr_link" type="text" id="tumblr_link" value="<?php echo get_option('msdsocial_tumblr_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="reddit_link">Reddit Link <i class="fa fa-reddit"></i></label>
                  <div class="input">
                    <input name="reddit_link" type="text" id="reddit_link" value="<?php echo get_option('msdsocial_reddit_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="flickr_link">Flickr Link <i class="fa fa-flickr"></i></label>
                  <div class="input">
                    <input name="flickr_link" type="text" id="flickr_link" value="<?php echo get_option('msdsocial_flickr_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="youtube_link">YouTube Link <i class="fa fa-youtube"></i></label>
                  <div class="input">
                    <input name="youtube_link" type="text" id="youtube_link" value="<?php echo get_option('msdsocial_youtube_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="vimeo_link">Vimeo Link <i class="fa fa-vimeo-square"></i></label>
                  <div class="input">
                    <input name="vimeo_link" type="text" id="vimeo_link" value="<?php echo get_option('msdsocial_vimeo_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="vine_link">Vine Link <i class="fa fa-vine"></i></label>
                  <div class="input">
                    <input name="vine_link" type="text" id="vine_link" value="<?php echo get_option('msdsocial_vine_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="sharethis_link">Sharethis Link <i class="fa fa-share-alt"></i></label>
                  <div class="input">
                    <input name="sharethis_link" type="text" id="sharethis_link" value="<?php echo get_option('msdsocial_sharethis_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="contact_link">Contact Link <i class="fa fa-envelope"></i></label>
                  <div class="input">
                    <input name="contact_link" type="text" id="contact_link" value="<?php echo get_option('msdsocial_contact_link'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="show_feed">Show Feed? <i class="fa fa-rss"></i></label>
                  <div class="input">
                    <input name="show_feed" type="checkbox" id="show_feed" value="true" <?php print get_option('msdsocial_show_feed')==true?'CHECKED':''; ?>> yes
                  </div>
              </li>
          </ul>
      </div>
      <div class="tab-pane" id="location">
          <h2>Location Information</h2>
          <p></p>Please input information for the primary physical location. If you have a separate address for mailing, please use the Contact tab to supply a mailing address.</p>
          <ul class="input-table">
              <li>
                  <label for="biz_name">Business Name</label>
                  <div class="input">
                    <input name="biz_name" type="text" id="biz_name" value="<?php echo get_option('msdsocial_biz_name'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="street">Street Address</label>
                  <div class="input">
                <input name="street" type="text" id="street" value="<?php echo get_option('msdsocial_street'); ?>" class="regular-text" /><br />
                <input name="street2" type="text" id="street2" value="<?php echo get_option('msdsocial_street2'); ?>" class="regular-text" />
                </div>
              </li>
              <li>
                  <label for="city">City</label>
                  <div class="input">
                <input name="city" type="text" id="city" value="<?php echo get_option('msdsocial_city'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="state">State</label>
                  <div class="input">
                <select name="state" id="state" class="regular-text" />
                    <option>Select</option>
                    <?php foreach($states AS $state => $st){ ?>
                        <option value="<?php print $st; ?>"<?php print get_option('msdsocial_state')==$st?' SELECTED':'';?>><?php print ucwords(strtolower($state)); ?></option>
                    <?php } ?>
                </select>
                </div>
              </li>
              <li>
                  <label for="zip">ZIP Code</label>
                  <div class="input">
                <input name="zip" type="text" id="zip" value="<?php echo get_option('msdsocial_zip'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="lat">Latitude</label>
                  <div class="input">
                <input name="lat" type="text" id="lat" value="<?php echo get_option('msdsocial_lat'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="lng">Longitude</label>
                  <div class="input">
                <input name="lng" type="text" id="lng" value="<?php echo get_option('msdsocial_lng'); ?>" class="regular-text" />
                  </div>
              </li>
          </ul>
      </div>
      <div class="tab-pane" id="contact">
          <h2>Contact Information</h2>
          <p></p>
          <ul class="input-table">
              <li>
                  <label for="phone">Phone</label>
                  <div class="input">
                    <input name="phone" type="text" id="phone" value="<?php echo get_option('msdsocial_phone'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="tracking_phone">Tracking Phone</label>
                  <div class="input">
                    <input name="tracking_phone" type="text" id="tracking_phone" value="<?php echo get_option('msdsocial_tracking_phone'); ?>" class="regular-text" />
                  <span class="note">If you fill this in, this is what will display in the browser, and the "real" phone number will be available only in the code.</span>
                  </div>
              </li>
              <li>
                  <label for="tollfree">Toll Free</label>
                  <div class="input">
                    <input name="tollfree" type="text" id="tollfree" value="<?php echo get_option('msdsocial_tollfree'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="tracking_tollfree">Tracking Toll Free</label>
                  <div class="input">
                    <input name="tracking_tollfree" type="text" id="tracking_tollfree" value="<?php echo get_option('msdsocial_tracking_tollfree'); ?>" class="regular-text" /> 
                  <span class="note">If you fill this in, this is what will display in the browser, and the "real" phone number will be available only in the code.</span>
                  </div>
              </li>
              <li>
                  <label for="fax">Fax</label>
                  <div class="input">
                    <input name="fax" type="text" id="fax" value="<?php echo get_option('msdsocial_fax'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="email">Email</label>
                  <div class="input">
                    <input name="email" type="text" id="email" value="<?php echo get_option('msdsocial_email'); ?>" class="regular-text" />
                  <span class="note">Primary contact email. This may be displayed on the website.</span>
                  </div>
              </li>
          </ul>
          <h2>Mailing Information</h2>
          <p>If you have a separate address for mailing, such as a PO Box, please enter that here.</p>
          <ul class="input-table">
              
              <li>
                  <label for="careof">Care Of</label>
                  <div class="input">
                    <input name="careof" type="text" id="careof" value="<?php echo get_option('msdsocial_careof'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="mailing_street">Street Address</label>
                  <div class="input">
                <input name="mailing_street" type="text" id="mailing_street" value="<?php echo get_option('msdsocial_mailing_street'); ?>" class="regular-text" /><br />
                <input name="mailing_street2" type="text" id="mailing_street2" value="<?php echo get_option('msdsocial_mailing_street2'); ?>" class="regular-text" />
                </div>
              </li>
              <li>
                  <label for="mailing_city">City</label>
                  <div class="input">
                <input name="mailing_city" type="text" id="mailing_city" value="<?php echo get_option('msdsocial_mailing_city'); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="mailing_state">State</label>
                  <div class="input">
                <select name="mailing_state" id="mailing_state" class="regular-text" />
                    <option>Select</option>
                    <?php foreach($states AS $state => $st){ ?>
                        <option value="<?php print $st; ?>"<?php print get_option('msdsocial_mailing_state')==$st?' SELECTED':'';?>><?php print ucwords(strtolower($state)); ?></option>
                    <?php } ?>
                </select>
                </div>
              </li>
              <li>
                  <label for="mailing_zip">ZIP Code</label>
                  <div class="input">
                <input name="mailing_zip" type="text" id="mailing_zip" value="<?php echo get_option('msdsocial_mailing_zip'); ?>" class="regular-text" />
                  </div>
              </li>
          </ul>
      </div>
      <div class="tab-pane" id="hours">
          <h2>Hours</h2>
          <p>Set open and close time the same to be "closed" for the day.</p>
        <ul class="input-table">
              <li>
                  <label></label>
                  <div class="input">
                    <div class="col-sm-1"><strong>Closed</strong></div>
                    <div class="col-sm-8">
                        <div class="col-sm-6"><strong>Open</strong></div>
                        <div class="col-sm-6"><strong>Close</strong></div>
                    </div>
                    <div class="col-sm-1"><strong>Copy</strong></div>
                  </div>
              </li>
            <?php 
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
                $checked = $open==''||$close==''?' checked':'';
                $hide = $open==''||$close==''?' hidden':'';
                ?>
              <li>
                  <label for="hours_<?php print strtolower($day); ?>"><?php print $day; ?></label>
                  <div class="input">
                      <div class="col-sm-1">
                          <input type="checkbox" class="closed-check" <?php print $checked; ?> />
                      </div>
                      <div class="col-sm-8">
                          <div class="col-sm-5 input-append bootstrap-timepicker">
                            <input name="hours_<?php print strtolower($day); ?>_open" type="text" id="hours_<?php print strtolower($day); ?>_open" value="<?php echo get_option('msdsocial_hours_'.strtolower($day).'_open'); ?>" class="regular-text start time<?php print $hide; ?>" />
                          </div>
                          <div class="col-sm-5  input-append bootstrap-timepicker">
                            <input name="hours_<?php print strtolower($day); ?>_close" type="text" id="hours_<?php print strtolower($day); ?>_close" value="<?php echo get_option('msdsocial_hours_'.strtolower($day).'_close'); ?>" class="regular-text end time<?php print $hide; ?>" />
                          </div>
                      </div>
                      <div class="col-sm-1">
                          <?php if($day != 'Sunday'): ?>
                          <a class="btn btn-default copy">Copy Previous Day</a>
                          <?php endif; ?>
                      </div>
                  </div>
              </li>
              <?php
            }
            ?>
        </ul>
      </div>
      <div class="tab-pane" id="settings">
          <h2>Plugin Settings</h2>
          <p></p>
          <ul class="input-table">
              <li>
                  <label for="icon_size">Icon Size</label>
                  <div class="input">
                    <input name="icon_size" type="radio" id="icon_size" value="0" <?php print get_option('msdsocial_icon_size')==0?'CHECKED':''; ?>> None, use FontAwesome &nbsp;|&nbsp;
                    <input name="icon_size" type="radio" id="icon_size" value="16" <?php print get_option('msdsocial_icon_size')==16?'CHECKED':''; ?> disabled> 16 &nbsp;|&nbsp;
                    <input name="icon_size" type="radio" id="icon_size" value="24" <?php print get_option('msdsocial_icon_size')==24?'CHECKED':''; ?> disabled> 24 &nbsp;|&nbsp;
                    <input name="icon_size" type="radio" id="icon_size" value="32" <?php print get_option('msdsocial_icon_size')==32?'CHECKED':''; ?> disabled> 32
                  </div>
              </li>
          </ul></div>
    </div>
		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
		<input type="hidden" name="msdsocial_settings" value="save" style="display:none;" />
		</p>
</form>
</div>
 <script type="text/javascript">
jQuery('.time').timepicker();
jQuery(document).ready(function($) {  
    $('.closed-check').click(function(){
        var sibs = $(this).parent().siblings().find('.time');
        if($(this).is(':checked')){
            sibs.val('');
            sibs.addClass('hidden');
        } else {
            sibs.removeClass('hidden');
        }
    });
    $('.copy').click(function(){
        var cur = $(this).parent().parent();
        var prev = $(this).parent().parent().parent().prev('li').find('.input');
        cur.find('.time.start').val(prev.find('.time.start').val());
        cur.find('.time.end').val(prev.find('.time.end').val());
    });
});
</script>
<?php }