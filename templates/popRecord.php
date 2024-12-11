<script>
function popRecord(data, searchDate){
		while(timeList.firstChild){
			timeList.removeChild(timeList.lastChild)
		}
		let employees 		= [...new Set(data.map(entries => entries[3]))];
		const employeeList 	= document.createElement("ul")
		for(const employee of employees){
			const employeeItem			= document.createElement("li")
			const employeeHeader		= document.createElement("h3")
			employeeHeader.innerText	= `Employee: ${employee}`
			employeeItem.appendChild(employeeHeader)
			const entryList 			= document.createElement("ul")
			for(const entry of data){
				if(entry[3] == employee){
					const entryData 		= JSON.parse(entry[2])
					const entryItem 		= document.createElement("li")
					const invoiceEl			= document.createElement("p")
					invoiceEl.innerText		= `Invoice: ${entry[1]}`
					entryItem.appendChild(invoiceEl)
					const date 				= new Date(entry[4])
					const dateElement 		= document.createElement("p")
					dateElement.innerText	= date.toLocaleString()
					entryItem.appendChild(dateElement)
					const time 				= document.createElement("p")
					time.innerText 			= `Time: ${entryData.time}`
					entryItem.appendChild(time)
					if(entryData.notes){
						const notes 		= document.createElement("p")
						notes.innerText 	= entryData.notes
						entryItem.appendChild(notes)
					}
					
					const deleteBtn = document.createElement("button")
					deleteBtn.innerText = "Delete"
					deleteBtn.addEventListener("click", ()=>deleteRecord(entry[0], entry[1], searchDate, entry[3]))
					entryItem.appendChild(deleteBtn)
					entryList.appendChild(entryItem)
				}
			}
			employeeItem.appendChild(entryList)
			employeeList.appendChild(employeeItem)
		}
		timeList.appendChild(employeeList)
	}

	function deleteRecord(id, invoice, date, employee){
		const bool = confirm("Are you sure you want to delete?")
		if(bool){
			fetch("<?=get_rest_url()?>track-time/v1/invoice", {
				method: "DELETE",
				headers: {
					"Content-Type": "application/json",
					"X-WP-Nonce": "<?=wp_create_nonce("wp_rest");?>",
				},
				body: JSON.stringify({
					id,
					invoice,
					date,
					employee
				})
			})
			.then(res=>res.json())
			.then(obj=>{
				popRecord(obj.response)
			})
		}
	}
</script>
