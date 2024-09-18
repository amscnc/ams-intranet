<?php

add_action("rest_api_init", function(){
	register_rest_route("track-time/v1", "/invoice", [
		[
			"methods"	=> "GET",
			// "callback"	=> function(WP_REST_Request $request){
			"callback"	=> function(){
				global $wpdb;

				// $invoice_number = sanitize_text_field($request->get_param('invoice_number'));
				// $time_data	= $request->get_param('time');

				// if(empty($invoice_number) || !is_array($time_data)){
				// 	return ['error'=>'invalid paramaters'];
				// }

				$current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '50605'", ARRAY_N);

				// $query = $wpdb->prepare(
				// 	"UPDATE wp_track_time
				// 	SET count = count + 1
				// 	WHERE id = 1
				// ");
				// $wpdb->query($query);

				// return ['success'=>'this is the api eh'];
				return $current;
			}
		],
	]);
});
