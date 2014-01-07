<?php
if ( !is_admin() ) { die( 'Access Denied.' ); }

?>

<style>
.profile_item {
	display: block;
	color: #252525;
	line-height: 2;
	font-size: medium;
	height: 25px;
}

.profile_text {
	display: block;
	float: left;
	line-height: 26px;
	//margin-right: 8px;
}
.profile_divide {
	border-right: 1px solid #ebebeb;
	display: block;
	float: right;
	width: 1px;
	height: 100%;
}
</style>


<div style="width: 300px; float: left;">
	<div class="profile_item">
		<span class="profile_text">Full</span>
		<span class="profile_divide"></span>
	</div>
</div>



<div style="float: left;">
	stuff
</div>