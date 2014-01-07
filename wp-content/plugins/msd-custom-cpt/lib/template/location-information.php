<?php 
$fields = array(
);
$i = 0; 
$states = array('AL'=>"Alabama",
		'AK'=>"Alaska",
		'AZ'=>"Arizona",
		'AR'=>"Arkansas",
		'CA'=>"California",
		'CO'=>"Colorado",
		'CT'=>"Connecticut",
		'DE'=>"Delaware",
		'DC'=>"District Of Columbia",
		'FL'=>"Florida",
		'GA'=>"Georgia",
		'HI'=>"Hawaii",
		'ID'=>"Idaho",
		'IL'=>"Illinois",
		'IN'=>"Indiana",
		'IA'=>"Iowa",
		'KS'=>"Kansas",
		'KY'=>"Kentucky",
		'LA'=>"Louisiana",
		'ME'=>"Maine",
		'MD'=>"Maryland",
		'MA'=>"Massachusetts",
		'MI'=>"Michigan",
		'MN'=>"Minnesota",
		'MS'=>"Mississippi",
		'MO'=>"Missouri",
		'MT'=>"Montana",
		'NE'=>"Nebraska",
		'NV'=>"Nevada",
		'NH'=>"New Hampshire",
		'NJ'=>"New Jersey",
		'NM'=>"New Mexico",
		'NY'=>"New York",
		'NC'=>"North Carolina",
		'ND'=>"North Dakota",
		'OH'=>"Ohio",
		'OK'=>"Oklahoma",
		'OR'=>"Oregon",
		'PA'=>"Pennsylvania",
		'RI'=>"Rhode Island",
		'SC'=>"South Carolina",
		'SD'=>"South Dakota",
		'TN'=>"Tennessee",
		'TX'=>"Texas",
		'UT'=>"Utah",
		'VT'=>"Vermont",
		'VA'=>"Virginia",
		'WA'=>"Washington",
		'WV'=>"West Virginia",
		'WI'=>"Wisconsin",
		'WY'=>"Wyoming");


?>
<ul class="location_meta_control">
<?php while($mb->have_fields('address',1)): ?>
	<li>
    <?php $metabox->the_field('street'); ?>
	<label id="<?php $metabox->the_name(); ?>_label" for="<?php $metabox->the_name(); ?>">Street Address</label>
	<div class="ginput_container"><input type="text" tabindex="1" value="<?php $metabox->the_value(); ?>" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>"></div>
	</li>
	<li>
    <?php $metabox->the_field('street2'); ?>
	<label id="<?php $metabox->the_name(); ?>_label" for="<?php $metabox->the_name(); ?>">Address Line 2</label>
	<div class="ginput_container"><input type="text" tabindex="2" value="<?php $metabox->the_value(); ?>" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>"></div>
	</li>
	<li>
    <?php $metabox->the_field('city'); ?>
	<label id="<?php $metabox->the_name(); ?>_label" for="<?php $metabox->the_name(); ?>">City</label>
	<div class="ginput_container"><input type="text" tabindex="3" value="<?php $metabox->the_value(); ?>" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>"></div>
	</li>
	<li>
    <?php $metabox->the_field('state'); ?>
	<label id="<?php $metabox->the_name(); ?>_label" for="<?php $metabox->the_name(); ?>">State</label>
	<div class="ginput_container">
	<select tabindex="4" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>">
		<option value="">--SELECT--</option>
		<?php foreach($states AS $k =>$v){ ?>
			<option value="<?php print $v; ?>"<?php print $metabox->get_the_value()==$v?' SELECTED':''?>><?php print $v; ?></option>
		<?php } ?>
	</select>
	</div>
	</li>
	<li>
    <?php $metabox->the_field('zip'); ?>
	<label id="<?php $metabox->the_name(); ?>_label" for="<?php $metabox->the_name(); ?>">Zip Code</label>
	<div class="ginput_container"><input type="text" tabindex="5" value="<?php $metabox->the_value(); ?>" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>"></div>
	</li>
<?php endwhile; ?>

<?php
foreach($fields AS $k=>$v){
?>
	<?php $mb->the_field('_location_'.$k); ?>
	<li class="gfield even" id="field_<?php $mb->the_name(); ?>"><label for="<?php $mb->the_name(); ?>"
		class="gfield_label"><?php print $v; ?></label>
	<div class="ginput_container last-child even">
			<textarea name="<?php print $mb->get_the_name(); ?>" id="<?php print $mb->get_the_name(); ?>"><?php print $mb->get_the_value(); ?></textarea>
			<?php // wp_editor($mb->get_the_value(),$mb->get_the_name(),array()); ?>
		</div>
	</li>
<?php 
$i++;
} ?>
</ul>