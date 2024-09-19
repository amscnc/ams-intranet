<?php

add_action('wp_enqueue_scripts', function(){
	wp_enqueue_style('main-stylesheet', get_stylesheet_uri());
	wp_style_add_data('main-stylesheet', 'rtl', 'replace');
	wp_register_script('clock', get_template_directory_uri() . '/clock.js', [], '1.0', true);
	wp_localize_script('clock', 'wpVars', [
		'restURL' => get_rest_url(),
		'wpNonce'	=> wp_create_nonce('wp_rest'),
	]);
	wp_enqueue_script('clock');
});

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
		[
			'methods'	=> "POST",
			'callback'	=> function(WP_REST_Request $req){
				global $wpdb;
				$current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '50605'", ARRAY_N);
				$json = json_decode($current[0][0], true);

				// $array = ["employee"=>"Jorge", "time"=>"6"];
				// $update = json_encode($array);

				// $query = $wpdb->prepare("UPDATE wp_track_time SET time = '{$update}' WHERE invoice = '50605'");
				// $wpdb->query($query);

				return [
					'success'		=> false,
					'time'			=> $req->get_param('time'),
					'data'			=> $json['time'] + 8.2,
				];
			},
			'permission_callback'	=> function(){
				$nonce = sanitize_text_field($_SERVER['HTTP_X_WP_NONCE']);
				if(wp_verify_nonce($nonce, 'wp_rest')){
					return true;
				}
			}
		],
	]);
});

add_action('admin_menu', function(){
	add_menu_page(
		'Time Tracking',
		'Time Tracking',
		'administrator',
		'time-tracking',
		function(){
			?>
				<form id="email_porch_hosts_form" style="display:flex;flex-direction:column;padding:1rem;padding-right:2rem;">
					<label for="Invoice Lookup">Invoice Lookup</label>
					<input type="text" name="Invoice Lookup" id="invoice_number">
					<button type="submit">Get</button>
				</form>
				<div id="time_list">
				</div>
				<script>
					const emailForm = document.getElementById('email_porch_hosts_form')
					const timeList = document.getElementById('time_list')
					emailForm.addEventListener('submit', (e)=>{
						e.preventDefault()
						while(timeList.firstChild){
							timeList.removeChild(timeList.lastChild)
						}
						// const subject = document.getElementById('email_subject').value
						// const body = document.getElementById('email_body').value.replace(/\n/g, "<br />");
						fetch('<?=get_rest_url(null, '/track-time/v1/invoice')?>', {
							method: 'GET'
						})
						.then(res=>res.json())
						.then(data=>{
							const parsed = JSON.parse(data[0])
							console.log(parsed)
							const list = document.createElement('ul')
							const item = document.createElement('li')
							item.innerText = `${parsed[0].employee}: ${parsed[0].time}`
							list.appendChild(item)
							timeList.appendChild(list)
						})
					})
				</script>
			<?php
		},
		'dashicons-admin-tools
		',
		1
	);
});