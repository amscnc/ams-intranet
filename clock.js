const root          = document.getElementById('root')
const search        = document.getElementById('search_form')
const searchBox     = document.getElementById('search_box')
const whoisdiv      = document.getElementById('who_is')
const whoisForm     = document.getElementById('whois_form')
const whoisBox      = document.getElementById('emp_id')
const clocked       = document.getElementById('clocked_into')
const whois         = localStorage.getItem('whois')
let clocksPopped    = false
if(whois){
    // checkOldJobs()
    whoisdiv.innerHTML = `<h1>Logged In As: ${whois}</h1>`
    const logout = document.createElement('button')
    logout.innerText = "Log Out"
    logout.addEventListener('click', ()=>{
        localStorage.removeItem('whois')
        location.reload()
    })
    whoisdiv.appendChild(logout)
    popClocks()
}
setInterval(()=>{
    if(clocksPopped){
        popClocks()
    }
}, 36000)

function popClocks(){
    while(clocked.firstChild){
        clocked.removeChild(clocked.lastChild)
    }
    const jobs = JSON.parse(localStorage.getItem('track-time'))
    // console.log(jobs)
    if(jobs){
        const obj = jobs[Object.keys(jobs)[0]]
        const date = new Date()
        for(const key of Object.keys(jobs)){
            const div = document.createElement('div')
            const job = document.createElement('h3')
            job.innerText = key
            div.appendChild(job)
            for(const i of Object.keys(jobs[key])){
                if(whois == i){
                    const first = new Date(jobs[key][i].timeStart)
                    let diff = date - first
                    diff /= 1000
                    const seconds = Math.round(diff)
                    const hours = Math.round(((seconds / 60) / 60) * 100) / 100
                    const time = document.createElement('p')
                    time.innerText = `Hours: ${hours}`
                    div.appendChild(time)
                    const notesL = document.createElement('p')
                    notesL.innerText = "Notes:"
                    div.appendChild(notesL)
                    const notes = document.createElement('input')
                    notes.type = "text"
                    div.appendChild(notes)
                    const timeL = document.createElement('p')
                    timeL.innerText = "Manual Time:"
                    div.appendChild(timeL)
                    const manualTime = document.createElement('input')
                    manualTime.type = "number"
                    div.appendChild(manualTime)
                    const clockOutBtn = document.createElement('button')
                    clockOutBtn.innerText = "Clock Out"
                    clockOutBtn.addEventListener('click', ()=>clockOut(key, manualTime.value, notes.value))
                    const cancelBtn = document.createElement('button')
                    cancelBtn.innerText = "Cancel"
                    cancelBtn.addEventListener('click', ()=>deleteClock(key))
                    div.appendChild(clockOutBtn)
                    div.appendChild(cancelBtn)
                    clocked.appendChild(div)
                }
            }
        }
    }
    clocksPopped = true
}

whoisForm.addEventListener('submit', e=>{
    e.preventDefault()
    localStorage.setItem('whois', whoisBox.value)
    location.reload()
})

search.addEventListener('submit', e=>{
    e.preventDefault()
    clockIn(searchBox.value)
})

function deleteClock(inNumber){
    const jobs = JSON.parse(localStorage.getItem('track-time'))
    // delete jobs[inNumber][stepNumber]
    if(Object.keys(jobs[inNumber]).keys.length == 0){
        delete jobs[inNumber]
    }
    localStorage.setItem('track-time', JSON.stringify(jobs))
    popClocks()
}

function clockIn(inNumber){
    let tracker = JSON.parse(localStorage.getItem('track-time'))
    const date = new Date()
    if(tracker == null){
        tracker = {}
        tracker[inNumber] = {}
        tracker[inNumber][whois] = {
            timeStart: date,
        }
        localStorage.setItem('track-time', JSON.stringify(tracker))
    }else{
        if(!tracker[inNumber]){
            tracker[inNumber] = {}
            tracker[inNumber][whois] = {
                timeStart: date,
            }
        }else{
            tracker[inNumber][whois] = {
                timeStart: date,
            }
        }
        localStorage.setItem('track-time', JSON.stringify(tracker))
    }
    popClocks()
}

function clockOut(inNumber, manTime, notes){
    const jobs = JSON.parse(localStorage.getItem('track-time'))
    console.log(manTime)

    const first = new Date(jobs[inNumber][whois].timeStart)
    const date = new Date()
    let diff = date - first
    diff /= 1000
    const seconds = Math.round(diff)
    const hours = (seconds / 60) / 60
    console.log(hours)

    fetch(`${wpVars.restURL}track-time/v1/invoice`,{
            method: 'POST',
            headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpVars.wpNonce,
        },
        body: JSON.stringify({
            time: hours,
            manTime,
            start: first,
            invoice: inNumber,
            notes,
            whois
        }),
	})
	.then(res=>res.json())
	.then(obj=>{
        console.log(obj)
        deleteClock(inNumber)
    })
}
