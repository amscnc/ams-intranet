<?php

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
						width: auto;
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
				<form id="employee_lookup_form">
					<label for="Employee Lookup">Employee Lookup</label>
					<input type="text" name="Employee Lookup" id="employee_number">
					<input required type="date" name="date" id="date">
					<button type="submit">Get</button>
				</form>
				<div id="time_list">
				</div>
				<script>
					const employeeForm = document.getElementById("employee_lookup_form")
					const timeList = document.getElementById("time_list")
					const search = document.getElementById("employee_number")
					const when	= document.getElementById("date")
					employeeForm.addEventListener("submit", e=>{
						e.preventDefault()
						fetch(`<?=get_rest_url()?>track-time/v1/employee?whois=${search.value}&date=${date.value}`, {
							method: "GET",
						})
						.then(res=>res.json())
						.then(obj=>popRecord(obj.response, date.value))
					})
				</script>
				<?php require get_template_directory()."/popRecord.php";?>
			<?php
		}
	);