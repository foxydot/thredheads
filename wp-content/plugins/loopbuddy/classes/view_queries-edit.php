<?php
include_once( $this->_pluginPath . '/classes/queryitems.php' );

$lb_query_items = new loopbuddy_queryitems( $this->_parent );

wp_enqueue_script( 'thickbox', 'dashboard' );
wp_print_scripts( array( 'thickbox', 'dashboard' ) );
wp_print_styles( 'thickbox' );

/*
wp_enqueue_script( 'jquery-ui-sortable' );
wp_print_scripts( 'jquery-ui-sortable' );
*/

// For date & time picker
//wp_print_scripts( 'jquery-ui-core' );
wp_enqueue_script( 'ithemes-custom-ui-js', $this->_pluginURL . '/js/jquery.custom-ui.js' );
wp_print_scripts( 'ithemes-custom-ui-js' );
wp_enqueue_script( 'ithemes-timepicker-js', $this->_pluginURL . '/js/timepicker.js' );
wp_print_scripts( 'ithemes-timepicker-js' );
echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/ui-smoothness/jquery-ui-1.8.2.custom.css" type="text/css" media="all" />';

?>

<style type="text/css">
.pb_ruleset {
	border: 1px solid #BCBCBC;
	padding: 10px;
	padding-bottom: 0px; /* internal elements have padding to handle below spacing */
	position: relative;
	top: -10px;
	margin-top: 25px;
	margin-bottom: -10px;
	vertical-align: top;
}
.pb_ruleset ul {
	list-style-type: disc;
	vertical-align: top;
}
.pb_ruleset ul li {
	margin-left: 18px;
	margin-bottom: 15px;
	vertical-align: top;
	padding-left: 5px;
}
.pb_ruleset_del {
	position: absolute;
	right: 0px;
	background: #F9F9F9;
	//padding: 0px;
	padding-left: 5px;
	padding-bottom: 5px;
	z-index: 15;
}
.pb_ruleset_del a {
	cursor: pointer;
}
.pb_ruleset_outerdiv {
	position: relative;
}
.pb_rule_add_outerdiv {
	//margin-top: -10px;
	margin-left: 22px;
	margin-bottom: 15px;
}
optgroup option {
	margin-left: 10px;
}
.pb_rule_delete {
	cursor: pointer;
	//margin-left: 10px;
}
.pb_slot_settings {
	display: none;
}
.ui-datepicker {
	font-size: 80%;
}
.pb_ajax_assist {
	cursor: pointer;
	font-size: 80%;
	text-decoration: none;
}
.form-table th {
	width: 400px;
}
</style>
<div class='wrap'>
<?php 
$lb_query_items->output_html();
?>
</div><!--/.wrap-->