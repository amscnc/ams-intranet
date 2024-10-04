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
			$invoice = $req->get_param("invoice");
			$current = $wpdb->get_results("SELECT time FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
			$array = json_decode($current[0][0], true);
			return [
				"invoice"	=> $array,
				"number"	=> $invoice,
			];
		}
	]);
	register_rest_route("track-time/v1", "/employee", [
		"methods"	=> "GET",
		"callback"	=> function(WP_REST_Request $req){
			global $wpdb;
			// $invoice	= $req->get_param("invoice");
			// $date		= $req->get_param("date");
			$date		= "2024-10-04";
			$whois		= $req->get_param("whois");
			$result 	= $wpdb->get_results("SELECT * FROM wp_track_time WHERE employee = {$whois} and DATE(date) = '{$date}'", ARRAY_N);
			// $array = json_decode($current[0][0], true);
			return [
				// "invoice"	=> $array,
				// "number"	=> $invoice,
				// "plain"		=> $current,
				"response"	=> $result,
			];
		}
	]);
	register_rest_route("track-time/v1", "/invoice", [
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
				<form id="invoice_lookup_form">
					<label for="Invoice Lookup">Invoice Lookup</label>
					<input type="text" name="Invoice Lookup" id="invoice_number">
					<button type="submit">Get</button>
				</form>
				<div id="time_list">
				</div>
				<script>
					const invoiceForm = document.getElementById("invoice_lookup_form")
					const timeList = document.getElementById("time_list")
					const search = document.getElementById("invoice_number")
					invoiceForm.addEventListener('submit', (e)=>{
						e.preventDefault()

						fetch(`<?=get_rest_url()?>track-time/v1/invoice?invoice=${search.value}`, {
							method: "GET",
						})
						.then(res=>res.json())
						.then(obj=>popRecord(obj.response))
					})

					function popRecord(data){
						while(timeList.firstChild){
							timeList.removeChild(timeList.lastChild)
						}

						let employees 		= [...new Set(data.map(entries => entries[3]))];
						const employeeList 	= document.createElement("ul")
						for(const employee of employees){
							console.log("employee:", employee)
							const employeeItem			= document.createElement("li")
							const employeeHeader		= document.createElement("h3")
							employeeHeader.innerText	= `Employee: ${employee}`
							employeeItem.appendChild(employeeHeader)
							const entryList 			= document.createElement("ul")
							for(const entry of data){
								if(entry[3] == employee){
									const entryData = JSON.parse(entry[2])
									const entryItem = document.createElement("li")
									const date = new Date(entry[4])
									const dateElement = document.createElement("p")
									dateElement.innerText = date.toLocaleString()
									entryItem.appendChild(dateElement)
									const time = document.createElement("p")
									time.innerText = `Time: ${entryData.time}`
									entryItem.appendChild(time)
									if(entryData.notes){
										const notes = document.createElement("p")
										notes.innerText = entryData.notes
										entryItem.appendChild(notes)
									}
									
									const deleteBtn = document.createElement("button")
									deleteBtn.innerText = "Delete"
									deleteBtn.addEventListener("click", ()=>deleteRecord(i, x))
									entryItem.appendChild(deleteBtn)
									entryList.appendChild(entryItem)
								}
							}
							employeeItem.appendChild(entryList)
							employeeList.appendChild(employeeItem)
						}
						timeList.appendChild(employeeList)
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

	add_submenu_page(
		"time-tracking",
		"Employee Lookup",
		"Employee Lookup",
		"administrator",
		"time-tracking-employee",
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
				<form id="invoice_lookup_form">
					<label for="Invoice Lookup">Invoice Lookup</label>
					<input type="text" name="Invoice Lookup" id="invoice_number">
					<button type="submit">Get</button>
				</form>
				<div id="time_list">
				</div>
				<script>
					const invoiceForm = document.getElementById("invoice_lookup_form")
					const timeList = document.getElementById("time_list")
					const search = document.getElementById("invoice_number")
					invoiceForm.addEventListener('submit', (e)=>{
						e.preventDefault()

						fetch(`<?=get_rest_url()?>track-time/v1/employee?whois=${search.value}`, {
							method: "GET",
						})
						.then(res=>res.json())
						.then(obj=>popRecord(obj.response))
					})

					function popRecord(data){
						console.log(data)
						while(timeList.firstChild){
							timeList.removeChild(timeList.lastChild)
						}

						let employees 		= [...new Set(data.map(entries => entries[3]))];
						const employeeList 	= document.createElement("ul")
						for(const employee of employees){
							console.log("employee:", employee)
							const employeeItem			= document.createElement("li")
							const employeeHeader		= document.createElement("h3")
							employeeHeader.innerText	= `Employee: ${employee}`
							employeeItem.appendChild(employeeHeader)
							const entryList 			= document.createElement("ul")
							for(const entry of data){
								if(entry[3] == employee){
									const entryData = JSON.parse(entry[2])
									const entryItem = document.createElement("li")
									const date = new Date(entry[4])
									const dateElement = document.createElement("p")
									dateElement.innerText = date.toLocaleString()
									entryItem.appendChild(dateElement)
									const time = document.createElement("p")
									time.innerText = `Time: ${entryData.time}`
									entryItem.appendChild(time)
									if(entryData.notes){
										const notes = document.createElement("p")
										notes.innerText = entryData.notes
										entryItem.appendChild(notes)
									}
									
									const deleteBtn = document.createElement("button")
									deleteBtn.innerText = "Delete"
									deleteBtn.addEventListener("click", ()=>deleteRecord(i, x))
									entryItem.appendChild(deleteBtn)
									entryList.appendChild(entryItem)
								}
							}
							employeeItem.appendChild(entryList)
							employeeList.appendChild(employeeItem)
						}
						timeList.appendChild(employeeList)
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
		}
	);
});