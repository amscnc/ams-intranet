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