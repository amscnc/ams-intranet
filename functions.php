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
	register_rest_route("track-time/v1", "/invoice", [
		"methods"	=> "GET",
		"callback"	=> function(WP_REST_Request $req){
			global $wpdb;
			$invoice = $req->get_param('invoice');
			$current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
			$array = json_decode($current[0][0], true);
			return [
				"invoice"	=> $array,
				"number"	=> $invoice,
			];
		}
	]);
	register_rest_route("track-time/v1", "/invoice", [
		[
			"methods"	=> "POST",
			"callback"	=> function(WP_REST_Request $req){
				global $wpdb;
				$invoice	= $req->get_param("invoice");
				$current	= $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
				$array		= json_decode($current[0][0], true);

				$whois		= $req->get_param("whois");
				$manTime	= $req->get_param("manTime");
				$start		= $req->get_param("start");
				$array[$whois][$start]["time"] += $manTime ? $manTime : $req->get_param("time");
				$array[$whois][$start]["notes"] = $req->get_param("notes");
				$update		= json_encode($array);

				if($current){
					$query = $wpdb->prepare("UPDATE wp_track_time SET time = '{$update}' WHERE invoice = '{$invoice}'");
					$success = $wpdb->query($query);
				}else{
					$query = $wpdb->prepare("INSERT INTO wp_track_time (invoice, time) VALUES ('{$invoice}', '{$update}')");
					$success = $wpdb->query($query);
				}

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

				foreach($array as $key => $value){
					if($key == $req->get_param("employee")){
						$result[] = $key;
						if(count($value) > 1){
							foreach($value as $start => $time){
								if($start == $req->get_param("start")){
									$result[] = $start;
									unset($array[$key][$start]);
								}
							}
						}else{
							unset($array[$key]);
						}
					}
				}
				$update = json_encode($array);
				$query = $wpdb->prepare("UPDATE wp_track_time SET time = '{$update}' WHERE invoice = '{$invoice}'");
				$success = $wpdb->query($query);

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
	]);
});

add_action("admin_menu", function(){
	add_menu_page(
		"Time Tracking",
		"Time Tracking",
		"administrator",
		"time-tracking",
		function(){
			?>
				<style>
					form{
						display: flex;
						gap: 10px;
						width: 50rem;
						margin-top: 1rem;
						label{
							align-self: center;
							font-size: 2rem;
						}
						input{
							width: 20rem;
							font-size: 2rem;

						}
						button{
							font-size: 2rem;
							background-color: green;
							color: white;
							padding: .5rem;
							width: 10rem;
						}
					}
				</style>
				<form id="email_porch_hosts_form">
					<label for="Invoice Lookup">Invoice Lookup</label>
					<input type="text" name="Invoice Lookup" id="invoice_number">
					<button type="submit">Get</button>
				</form>
				<div id="time_list">
				</div>
				<script>
					const emailForm = document.getElementById("email_porch_hosts_form")
					const timeList = document.getElementById("time_list")
					const search = document.getElementById("invoice_number")
					emailForm.addEventListener('submit', (e)=>{
						e.preventDefault()

						fetch(`<?=get_rest_url()?>track-time/v1/invoice?invoice=${search.value}`, {
							method: "GET",
						})
						.then(res=>res.json())
						.then(obj=>{
							console.log(obj)
							popRecord(obj)
						})
					})

					function popRecord(data){
						while(timeList.firstChild){
							timeList.removeChild(timeList.lastChild)
						}
						const list = document.createElement("ul")
						for(const i of Object.keys(data.invoice)){
							const item = document.createElement("li")
							const emp = document.createElement("h3")
							emp.innerText = `Employee: ${i}`
							item.appendChild(emp)
							const innerList = document.createElement("ul")
							for(const x of Object.keys(data.invoice[i])){
								const innerItem = document.createElement("li")
								console.log(i, ":", data.invoice[i][x])
								const date = new Date(x)
								const dateElement = document.createElement("p")
								dateElement.innerText = date.toLocaleString()
								innerItem.appendChild(dateElement)
								const time = document.createElement("p")
								time.innerText = `Time: ${data.invoice[i][x].time}`
								const notesData = data.invoice[i][x].notes
								if(notesData){
									const notes = document.createElement("p")
									notes.innerText = notesData
									innerItem.appendChild(notes)
								}
								innerItem.appendChild(time)
								const deleteBtn = document.createElement("button")
								deleteBtn.innerText = "Delete"
								deleteBtn.addEventListener("click", ()=>deleteRecord(i, x))
								innerItem.appendChild(deleteBtn)
								innerList.appendChild(innerItem)
							}
							item.appendChild(innerList)
							list.appendChild(item)
						}
						timeList.appendChild(list)
					}

					function deleteRecord(employee, start){
						const bool = confirm("Are you sure you want to delete?")
						if(bool){
							fetch("<?=get_rest_url()?>track-time/v1/invoice",{
								method: "UPDATE",
								headers: {
									"Content-Type": "application/json",
									"X-WP-Nonce": "<?=wp_create_nonce("wp_rest");?>",
								},
								body: JSON.stringify({
									invoice: search.value,
									start,
									employee,
								})
							})
							.then(res=>res.json())
							.then(obj=>{
								console.log(obj)
								popRecord(obj)
							})
						}
					}
				</script>
			<?php
		},
		"dashicons-admin-tools",
		1
	);
});