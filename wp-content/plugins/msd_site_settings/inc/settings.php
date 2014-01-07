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
		'phone',
		'fax',
		'email',
		'mailing_street',
		'mailing_street2',
		'mailing_city',
		'mailing_state',
		'mailing_zip',
		'linkedin_link',
		'twitter_user',
		'google_link',
		'facebook_link',
		'flickr_link',
		'youtube_link',
		'landing_link',
		'sharethis_link',
		'contact_link',
		'show_feed');
		
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
<div class="wrap">
	<h2>Site Settings</h2>
	
<form method="post" action="">
	<fieldset style="border:1px solid #ddd; padding-bottom:20px; margin-top:20px;">
	<legend style="margin-left:5px; padding:0 5px; color:#2481C6;text-transform:uppercase;"><strong>Location Information</strong></legend>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="biz_name">Business Name:</label></th>
			<td>
				<input name="biz_name" type="text" id="biz_name" value="<?php echo get_option('msdsocial_biz_name'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="street">Street Address:</label></th>
			<td>
				<input name="street" type="text" id="street" value="<?php echo get_option('msdsocial_street'); ?>" class="regular-text" /><br />
				<input name="street2" type="text" id="street2" value="<?php echo get_option('msdsocial_street2'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="city">City:</label></th>
			<td>
				<input name="city" type="text" id="city" value="<?php echo get_option('msdsocial_city'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="state">State:</label></th>
			<td>
				<select name="state" id="state" class="regular-text" />
					<option>Select</option>
					<?php foreach($states AS $state => $st){ ?>
						<option value="<?php print $st; ?>"<?php print get_option('msdsocial_state')==$st?' SELECTED':'';?>><?php print ucwords(strtolower($state)); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="zip">Zip:</label></th>
			<td>
				<input name="zip" type="text" id="zip" value="<?php echo get_option('msdsocial_zip'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="phone">Phone:</label></th>
			<td>
				<input name="phone" type="text" id="phone" value="<?php echo get_option('msdsocial_phone'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="fax">Fax:</label></th>
			<td>
				<input name="fax" type="text" id="fax" value="<?php echo get_option('msdsocial_fax'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="email">Email:</label></th>
			<td>
				<input name="email" type="text" id="email" value="<?php echo get_option('msdsocial_email'); ?>" class="regular-text" />
			</td>
		</tr>
        </table>
        </fieldset>
        <fieldset style="border:1px solid #ddd; padding-bottom:20px; margin-top:20px;">
	<legend style="margin-left:5px; padding:0 5px; color:#2481C6;text-transform:uppercase;"><strong>Mailing Information</strong></legend>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="mailing_street">Street Address:</label></th>
			<td>
				<input name="mailing_street" type="text" id="mailing_street" value="<?php echo get_option('msdsocial_mailing_street'); ?>" class="regular-text" /><br />
				<input name="mailing_street2" type="text" id="mailing_street2" value="<?php echo get_option('msdsocial_mailing_street2'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="mailing_city">City:</label></th>
			<td>
				<input name="mailing_city" type="text" id="mailing_city" value="<?php echo get_option('msdsocial_mailing_city'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="mailing_state">State:</label></th>
			<td>
				<select name="mailing_state" id="mailing_state" class="regular-text" />
					<option>Select</option>
					<?php foreach($states AS $state => $st){ ?>
						<option value="<?php print $st; ?>"<?php print get_option('msdsocial_mailing_state')==$st?' SELECTED':'';?>><?php print ucwords(strtolower($state)); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="mailing_zip">Zip:</label></th>
			<td>
				<input name="mailing_zip" type="text" id="zip" value="<?php echo get_option('msdsocial_mailing_zip'); ?>" class="regular-text" />
			</td>
		</tr>
        </table>
        </fieldset>
	<fieldset style="border:1px solid #ddd; padding-bottom:20px; margin-top:20px;">
	<legend style="margin-left:5px; padding:0 5px; color:#2481C6;text-transform:uppercase;"><strong>Social Links</strong></legend>
		<table class="form-table">

        <tr valign="top">
			<th scope="row"><label for="google_link">Google+ link</label></th>
			<td>
				<input name="google_link" type="text" id="google_link" value="<?php echo get_option('msdsocial_google_link'); ?>" class="regular-text" />
			</td>
		</tr>

        <tr valign="top">
			<th scope="row"><label for="facebook_link">Facebook link</label></th>
			<td>
				<input name="facebook_link" type="text" id="facebook_link" value="<?php echo get_option('msdsocial_facebook_link'); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="twitter_user">Twitter Username</label></th>
			<td>
				<input name="twitter_user" type="text" id="twitter_user" value="<?php echo get_option('msdsocial_twitter_user'); ?>" class="regular-text" />
			</td>
		</tr>
        <tr valign="top">
			<th scope="row"><label for="linkedin_link">LinkedIn link</label></th>
			<td>
				<input name="linkedin_link" type="text" id="linkedin_link" value="<?php echo get_option('msdsocial_linkedin_link'); ?>" class="regular-text" />
			</td>
		</tr>        <tr valign="top">
			<th scope="row"><label for="flickr_link">Flickr link</label></th>
			<td>
				<input name="flickr_link" type="text" id="flickr_link" value="<?php echo get_option('msdsocial_flickr_link'); ?>" class="regular-text" />
			</td>
		</tr>
        <tr valign="top">
			<th scope="row"><label for="youtube_link">YouTube link</label></th>
			<td>
				<input name="youtube_link" type="text" id="youtube_link" value="<?php echo get_option('msdsocial_youtube_link'); ?>" class="regular-text" />
			</td>
		</tr>
        <tr valign="top">
			<th scope="row"><label for="sharethis_link">ShareThis link</label></th>
			<td>
				<input name="sharethis_link" type="text" id="sharethis_link" value="<?php echo get_option('msdsocial_sharethis_link'); ?>" class="regular-text" />
			</td>
		</tr>
        <tr valign="top">
			<th scope="row"><label for="contact_link">Contact link</label></th>
			<td>
				<input name="contact_link" type="text" id="contact_link" value="<?php echo get_option('msdsocial_contact_link'); ?>" class="regular-text" />
			</td>
		</tr>
        <tr valign="top">
			<th scope="row"><label for="show_feed">Show Feed?</label></th>
			<td>
				<input name="show_feed" type="checkbox" id="show_feed" value="true" <?php print get_option('msdsocial_show_feed')==true?'CHECKED':''; ?>> yes
			</td>
		</tr>
        <tr valign="top">
			<th scope="row"><label for="icon_size">Icon Size</label></th>
			<td>
				<input name="icon_size" type="checkbox" id="icon_size" value="16" <?php print get_option('msdsocial_icon_size')==16?'CHECKED':''; ?>> 16 &nbsp;|&nbsp;
				<input name="icon_size" type="checkbox" id="icon_size" value="24" <?php print get_option('msdsocial_icon_size')==24?'CHECKED':''; ?>> 24 &nbsp;|&nbsp;
				<input name="icon_size" type="checkbox" id="icon_size" value="32" <?php print get_option('msdsocial_icon_size')==32?'CHECKED':''; ?>> 32
			</td>
		</tr>
        </table>
        </fieldset>
		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
		<input type="hidden" name="msdsocial_settings" value="save" style="display:none;" />
		</p>
</form>
</div>
<?php }