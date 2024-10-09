<?php

add_menu_page(
		"Time Tracking",
		"Invoice Lookup",
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

				</script>
				<?php require get_template_directory()."/popRecord.php";?>
			<?php
		},
		"dashicons-admin-tools",
		1
	);