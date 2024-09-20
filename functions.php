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
	register_rest_route("track-time/v1", "/invoice/(?P<invoice>\d+)", [
		"methods"	=> "GET",
		"callback"	=> function(WP_REST_Request $req){
			global $wpdb;
			$current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '{$req['invoice']}'", ARRAY_N);
			$array = json_decode($current[0][0], true);
			return [
				"invoice" => $array,
			];
		}
	]);
	register_rest_route("track-time/v1", "/invoice", [
		[
			'methods'	=> "POST",
			'callback'	=> function(WP_REST_Request $req){
				global $wpdb;
				$invoice = $req->get_param('invoice');
				$current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
				$array = json_decode($current[0][0], true);

				$whois = $req->get_param('whois');
				$manTime = $req->get_param('manTime');
				$start = $req->get_param('start');
				$array[$whois][$start]["time"] += $manTime ? $manTime : $req->get_param('time');
				$array[$whois][$start]["notes"] = $req->get_param('notes');
				$update = json_encode($array);

				if($current){
					$query = $wpdb->prepare("UPDATE wp_track_time SET time = '{$update}' WHERE invoice = '{$invoice}'");
					$wpdb->query($query);
				}else{
					$query = $wpdb->prepare("INSERT INTO wp_track_time (invoice, time) VALUES ('{$invoice}', '{$update}')");
					$wpdb->query($query);
				}

				$res = $manTime ? 'true' : 'false';
				return [
					'success'		=> false,
					'invoice'		=> $req->get_param('invoice'),
					'start'			=> $req->get_param('start'),
					'time'			=> $req->get_param('time'),
					'manTime'		=> $req->get_param('manTime'),
					'whois'			=> $req->get_param('whois'),
					'data'			=> $update,
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
					const search = document.getElementById('invoice_number')
					emailForm.addEventListener('submit', (e)=>{
						e.preventDefault()
						while(timeList.firstChild){
							timeList.removeChild(timeList.lastChild)
						}
						fetch(`<?=get_rest_url()?>track-time/v1/invoice/${search.value}`, {
							method: 'GET',
						})
						.then(res=>res.json())
						.then(data=>{
							console.log(data)
							const list = document.createElement('ul')
							for(const i of Object.keys(data.invoice)){
								for(const x of Object.keys(data.invoice[i])){
									console.log(i, ':', data.invoice[i][x])
									const date = new Date(x)
									const item = document.createElement('li')
									const dateElement = document.createElement('h3')
									dateElement.innerText = date.toLocaleString()
									item.appendChild(dateElement)
									const emp = document.createElement('p')
									emp.innerText = `Employee: ${i}`
									item.appendChild(emp)
									const time = document.createElement('p')
									time.innerText = `Time: ${data.invoice[i][x].time}`
									const notesData = data.invoice[i][x].notes
									if(notesData){
										const notes = document.createElement('p')
										notes.innerText = notesData
										item.appendChild(notes)
									}
									item.appendChild(time)
									list.appendChild(item)
								}
							}
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