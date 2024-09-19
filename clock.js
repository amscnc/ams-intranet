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
    checkOldJobs()
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

// Really just for fixing my own mistakes lol
function checkOldJobs(){
    const jobs = JSON.parse(localStorage.getItem('track-time'))
    for(const check in jobs){
        if(Object.keys(jobs[check]).length == 0){
            delete jobs[check]
        }
    }
    localStorage.setItem('track-time', JSON.stringify(jobs))
}

function popClocks(){
    while(clocked.firstChild){
        clocked.removeChild(clocked.lastChild)
    }
    const jobs = JSON.parse(localStorage.getItem('track-time'))
    console.log(jobs)
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
                    // const step = document.createElement('h3')
                    // step.innerText = i
                    // div.appendChild(step)
                    const time = document.createElement('p')
                    time.innerText = `Hours: ${hours}`
                    div.appendChild(time)
                    const clockOutBtn = document.createElement('button')
                    clockOutBtn.innerText = "Clock Out"
                    clockOutBtn.addEventListener('click', ()=>clockOut(key, i))
                    const cancelBtn = document.createElement('button')
                    cancelBtn.innerText = "Cancel"
                    cancelBtn.addEventListener('click', ()=>deleteClock(key, i))
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
    clockIn(50456)
    
    // while(root.firstChild){
    //     root.removeChild(root.lastChild)
    // }
    // retrieve(searchBox.value)
})

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

function deleteClock(jobNumber, stepNumber){
    const jobs = JSON.parse(localStorage.getItem('track-time'))
    delete jobs[jobNumber][stepNumber]
    if(Object.keys(jobs[jobNumber]).keys.length == 0){
        delete jobs[jobNumber]
    }
    localStorage.setItem('track-time', JSON.stringify(jobs))
    popClocks()
}

function clockOut(inNumber){
    const jobs = JSON.parse(localStorage.getItem('track-time'))
    // const opRes = await fetch(`https://api-jb2.integrations.ecimanufacturing.com:443/api/v1/operation-codes/${jobs[jobNumber][stepNumber].opCode}`, {
    //     method: "GET",
    //     headers: {
    //         "Authorization": `Bearer ${accessToken.token}`,
    //         "Content-Type": "application/json;odata=verbose"
    //     },
    // })
    // const opObj = await opRes.json()
    // const opNum = await opObj.Data.operationNumber
    // const centersRes = await fetch(`https://api-jb2.integrations.ecimanufacturing.com:443/api/v1/work-centers/`, {
    //     method: "GET",
    //     headers: {
    //         "Authorization": `Bearer ${accessToken.token}`,
    //         "Content-Type": "application/json;odata=verbose"
    //     },
    // })
    // const centersObj = await centersRes.json()
    // const centers = await centersObj
    // let centerNumber
    // centers.Data.forEach(center=>{
    //     if(center.shortName == jobs[jobNumber][stepNumber].workCenter){
    //         centerNumber = center.workCenter
    //     }
    // });
    // const first = new Date(jobs[inNumber][whois].timeStart)
    // const date = new Date()
    // let diff = date - first
    // diff /= 1000
    // const seconds = Math.round(diff)
    // const hours = (seconds / 60) / 60
    // jobs[inNumber][whois].timeEnd = date
    // jobs[inNumber][whois].elapsed = hours
    // console.log(hours)
    let times = []
    for(const i of Object.keys(jobs)){
        for(const x of Object.keys(jobs[i])){
            if(x == whois){
                const first = new Date(jobs[i][whois].timeStart)
                const date = new Date()
                let diff = date - first
                diff /= 1000
                const seconds = Math.round(diff)
                const hours = (seconds / 60) / 60
                // jobs[inNumber][whois].timeEnd = date
                // jobs[inNumber][whois].elapsed = hours
                console.log(hours)
                times.push(hours)
            }
        }
    }
    let total = 0
    for(const i in times){
        console.log(times[i])
        total += times[i]
    }
    console.log('average', total / times.length)

    fetch(`${wpVars.restURL}track-time/v1/invoice`,{
            method: 'POST',
            headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpVars.wpNonce,
        },
        body: JSON.stringify({time: total / times.length}),
	})
	.then(res=>res.json())
	.then(obj=>console.log(obj))

    // let pieces = jobs[jobNumber][stepNumber].pieces
    // const done = confirm(`Are you done? Pieces to finish: ${pieces}`)
    // if(!done)pieces = 0
    // fetch(`https://api-jb2.integrations.ecimanufacturing.com:443/api/v1/time-tickets`, {
    //     method: "POST",
    //     headers: {
    //         "Authorization": `Bearer ${accessToken.token}`,
    //         "Content-Type": "application/json;odata=verbose"
    //     },
    //     body: JSON.stringify({
    //         "timeTicketDetails": [{
    //             "jobNumber": jobNumber,
    //             "stepNumber": Number(stepNumber),
    //             "workCenter": centerNumber,
    //             "piecesFinished": pieces,
    //             "piecesScrapped": 0,
    //             "setupTime": .25,
    //             "cycleTime": hours,
    //             "machinesRun": 1,
    //             "operationNumber": opNum,
    //         }],
    //         "allowClosedJobs": true,
    //         "employeeCode": whois,
    //         "ticketDate": date.toISOString().split('T')[0]
    //     })
    // })
    // .then(res=>res.json())
    // .then(obj=>{
    //     console.log(obj)
    //     deleteClock(jobNumber, stepNumber)
    //     popClocks()
    //     // checkFinished(jobNumber)
    // })
}

// function checkFinished(jobNumber){
//     split = jobNumber.split('-')
//     if(split.length > 2)orderNumber = `${split[0]}-${split[1]}`
//     else orderNumber = split[0]
//     fetch(`https://api-jb2.integrations.ecimanufacturing.com:443/api/v1/order-routings?orderNumber=${orderNumber}`, {
//         headers: {
//             "Authorization": `Bearer ${accessToken.token}`
//         }
//     })
//     .then(res=>res.json())
//     .then(obj=>{
//         let done = 0
//         for(const route of obj.Data){
//             if(route.status == "finished")done += 1
//         }
//     })
// }