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
	require get_template_directory()."/clock-api-get.php";
	
	register_rest_route("track-time/v1", "/invoice", [
		[
			"methods"	=> "POST",
			"callback"	=> function(WP_REST_Request $req){
				global $wpdb;
				$invoice			= $req->get_param("invoice");
				$whois				= $req->get_param("whois");
				$manTime			= $req->get_param("manTime");
				$start				= $req->get_param("start");
				if(!is_numeric($start)){
					$start 			= strtotime($start);
				}
				$trackArr 			= [];
				$trackArr["time"]	= $manTime ? $manTime : $req->get_param("time");
				$trackArr["notes"]	= $req->get_param("notes");
				$trackObj			= json_encode($trackArr);

				$query				= $wpdb->prepare("INSERT INTO wp_track_time (invoice, time, employee, date) VALUES ('{$invoice}', '{$trackObj}', {$whois}, FROM_UNIXTIME({$start}))");
				$success 			= $wpdb->query($query);

				return [
					"success"		=> $success,
				];
			},
			"permission_callback"	=> function(){
				$nonce = sanitize_text_field($_SERVER["HTTP_X_WP_NONCE"]);
				if(wp_verify_nonce($nonce, "wp_rest")){
					return true;
				}
			}
		],
		[
			"methods"	=> "DELETE",
			"callback"	=> function(WP_REST_Request $req){
				global $wpdb;
				$invoice	= $req->get_param("invoice");
				$whois				= $req->get_param("whois");
				$manTime			= $req->get_param("manTime");
				$start				= $req->get_param("start");
				if(!is_numeric($start)){
					$start = strtotime($start);
				}
				$array 				= [];
				$array["time"]		= $manTime ? $manTime : $req->get_param("time");
				$array["notes"]		= $req->get_param("notes");
				$update				= json_encode($array);

				$query		= $wpdb->prepare("INSERT INTO wp_track_time (invoice, time, employee, date) VALUES ('{$invoice}', '{$update}', {$whois}, FROM_UNIXTIME({$start}))");
				$success 	= $wpdb->query($query);

				return [
					"success"	=> $success,
					"invoice"	=> $array,
				];
			},
			"permission_callback"	=> function(){
				$nonce = sanitize_text_field($_SERVER["HTTP_X_WP_NONCE"]);
				if(wp_verify_nonce($nonce, "wp_rest")){
					return true;
				}
			}
		],
		[
			"methods"	=> "UPDATE",
			"callback"	=> function(WP_REST_Request $req){
				global $wpdb;
				$invoice = $req->get_param("invoice");
				$current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
				$array = json_decode($current[0][0], true);

				// foreach($array as $key => $value){
				// 	if($key == $req->get_param("employee")){
				// 		$result[] = $key;
				// 		if(count($value) > 1){
				// 			foreach($value as $start => $time){
				// 				if($start == $req->get_param("start")){
				// 					$result[] = $start;
				// 					unset($array[$key][$start]);
				// 				}
				// 			}
				// 		}else{
				// 			unset($array[$key]);
				// 		}
				// 	}
				// }
				// $update = json_encode($array);
				// $query = $wpdb->prepare("UPDATE wp_track_time SET time = '{$update}' WHERE invoice = '{$invoice}'");
				// $success = $wpdb->query($query);

				return [
					// "success"	=> $success,
					// "invoice"	=> $array,
					"response"		=> "nothing right now"
				];
			},
			"permission_callback"	=> function(){
				$nonce = sanitize_text_field($_SERVER["HTTP_X_WP_NONCE"]);
				if(wp_verify_nonce($nonce, "wp_rest")){
					return true;
				}
			}
		],
	]);
});

add_action("admin_menu", function(){
	require get_template_directory()."/admin-invoice.php";
	require get_template_directory()."/admin-employee.php";
});