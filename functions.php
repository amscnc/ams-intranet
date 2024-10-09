<?php

add_action("wp_enqueue_scripts", function(){
	wp_enqueue_style("main-stylesheet", get_stylesheet_uri());
	wp_style_add_data("main-stylesheet", "rtl", "replace");
	wp_register_script("clock", get_template_directory_uri() . "/clock.js", [], "1.0", true);
	wp_localize_script("clock", "wpVars", [
		"restURL" => get_rest_url(),
		"wpNonce"	=> wp_create_nonce("wp_rest"),
	]);
	wp_enqueue_script("clock");
});

add_action("rest_api_init", function(){
	require get_template_directory()."/clock-api.php";
	
	
});

add_action("admin_menu", function(){
	require get_template_directory()."/admin-invoice.php";
	require get_template_directory()."/admin-employee.php";
});