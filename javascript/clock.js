const search        = document.getElementById("search_form")
const searchBox     = document.getElementById("search_box")
const select        = document.getElementById("work_type")
const whoisdiv      = document.getElementById("who_is")
const whoisForm     = document.getElementById("whois_form")
const whoisBox      = document.getElementById("emp_id")
const clocked       = document.getElementById("clocked_into")
const whois         = localStorage.getItem("whois")
if(whois){
    whoisdiv.innerHTML  = `<h1>Logged In As: ${whois}</h1>`
    const logout        = document.createElement("button")
    logout.innerText    = "Log Out"
    logout.addEventListener("click", ()=>{
        localStorage.removeItem("whois")
        location.reload()
    })
    whoisdiv.appendChild(logout)
    popClocks()
}

function updateTime(key, i, jobs){
    const date      = new Date()
    const first     = new Date(jobs[key][i].timeStart)
    let diff        = date - first
    diff            /= 1000
    const seconds   = Math.round(diff)
    const hours     = Math.round(((seconds / 60) / 60) * 100) / 100
    return hours
}

function popClocks(){
    while(clocked.firstChild){
        clocked.removeChild(clocked.lastChild)
    }
    const jobs = JSON.parse(localStorage.getItem("track-time"))
    if(jobs){
        for(const key of Object.keys(jobs)){
            const jobDiv = document.createElement("div")
            jobDiv.classList.add("Job")
            const jobHeader = document.createElement("h3")
            jobHeader.innerText = key
            jobDiv.appendChild(jobHeader)
            for(const i of Object.keys(jobs[key])){
                if(whois == i){
                    const time              = document.createElement("p")
                    time.innerText          = `Hours: ${updateTime(key, i, jobs)}`
                    jobDiv.appendChild(time)
                    setInterval(()=>{
                        time.innerText      = `Hours: ${updateTime(key, i, jobs)}`
                    }, 36000);
                    const notesLabel        = document.createElement("p")
                    notesLabel.innerText    = "Notes:"
                    jobDiv.appendChild(notesLabel)
                    const notesInput        = document.createElement("input")
                    notesInput.type         = "text"
                    workType            = jobs[key][i].workType
                    if(workType){
                        notesInput.value    = workType
                    }
                    jobDiv.appendChild(notesInput)
                    const timeLabel             = document.createElement("p")
                    timeLabel.innerText         = "Manual Time:"
                    jobDiv.appendChild(timeLabel)
                    const manualTime        = document.createElement("input")
                    manualTime.type         = "number"
                    jobDiv.appendChild(manualTime)
                    const clockOutBtn       = document.createElement("button")
                    clockOutBtn.innerText   = "Clock Out"
                    clockOutBtn.addEventListener("click", ()=>clockOut(key, manualTime.value, notes.value))
                    const cancelBtn         = document.createElement("button")
                    cancelBtn.innerText     = "Cancel"
                    cancelBtn.addEventListener("click", ()=>deleteClock(key))
                    jobDiv.appendChild(clockOutBtn)
                    jobDiv.appendChild(cancelBtn)
                    clocked.appendChild(jobDiv)
                }
            }
        }
    }
}

whoisForm.addEventListener("submit", e=>{
    e.preventDefault()
    localStorage.setItem("whois", whoisBox.value)
    location.reload()
})

search.addEventListener("submit", e=>{
    e.preventDefault()
    clockIn(searchBox.value, select.value)
})

function deleteClock(inNumber){
    const jobs = JSON.parse(localStorage.getItem("track-time"))
    delete jobs[inNumber][whois]
    localStorage.setItem("track-time", JSON.stringify(jobs))
    popClocks()
}

function clockIn(inNumber, workType){
    let tracker     = JSON.parse(localStorage.getItem("track-time"))
    const date      = new Date()
    if(tracker == null){
        tracker             = {}
        tracker[inNumber]   = {}
        tracker[inNumber][whois] = {
            timeStart: date,
            workType,
        }
        localStorage.setItem("track-time", JSON.stringify(tracker))
    }else{
        if(!tracker[inNumber]){
            tracker[inNumber] = {}
            tracker[inNumber][whois] = {
                timeStart: date,
                workType,
            }
        }else{
            tracker[inNumber][whois] = {
                timeStart: date,
                workType,
            }
        }
        localStorage.setItem("track-time", JSON.stringify(tracker))
    }
    popClocks()
}

function clockOut(invoice, manTime, notes){
    const jobs = JSON.parse(localStorage.getItem("track-time"))

    const timeStart     = new Date(jobs[invoice][whois].timeStart)
    const start         = Math.floor(timeStart.getTime() / 1000)

    const date          = new Date()
    let diff            = date - timeStart
    diff                /= 1000
    const seconds       = Math.round(diff)
    const time          = Math.round(((seconds / 60) / 60) * 100) / 100

    fetch(`${wpVars.restURL}track-time/v1/invoice`,{
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": wpVars.wpNonce,
        },
        body: JSON.stringify({
            time,
            manTime,
            start,
            invoice,
            notes,
            whois
        }),
	})
	.then(res=>res.json())
	.then(obj=>{
        console.log(obj)
        deleteClock(invoice)
    })
}
