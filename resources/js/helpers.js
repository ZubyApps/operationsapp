import DataTable          from "datatables.net";
import { del } from "./ajax";

let setTimeIn
function clearValues(modal) {
        const tagName = modal._element.querySelectorAll('input, select, textarea, #clientOption')

            tagName.forEach(tag => {
                tag.value = ''
            });        
    }

function clearClientsList(element){
    element.remove()
}

function getPaymentDetails(id, modal){
    const paymentsTable = new DataTable('#paymentDetailsTable', {
            serverSide: true,
            ajax: {url: '/payments/paydetails/load/details', data: {
                'id': id,
                'modal': modal._element.id
            }},
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
            {sortable: false,
                data: 'date'},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.amount)},
            {sortable: false,
                data: 'paymethod'},
            {sortable: false,
                data: 'staff'},
            {sortable: false,
            data: row => function() {if (row.activeUser === 'Admin') {return `<button type="submit" class="ms-1 btn btn-outline-primary delete-payment-details-btn" job-id="${id}" data-id="${ row.id }"><i class="bi bi-trash3-fill"></i></button>`} else {return ''}}
            }]})
        
    modal.show()
    
    document.querySelector('#paymentDetailsTable').addEventListener('click', function(event){
            const deletePaymentDetailsBtn = event.target.closest('.delete-payment-details-btn')

            const paymentId = deletePaymentDetailsBtn.getAttribute('data-id')
            const jobId = deletePaymentDetailsBtn.getAttribute('job-id')
            if (deletePaymentDetailsBtn) {
                if (confirm('Are you sure you want to delete this payment?')) {
                        del(`/payments/paydetails/${ paymentId }`, {job: jobId}).then(response => {
                        if (response.ok) {
                        paymentsTable.draw()
                        }
                    })
                }
            }
        }) 

        modal._element.addEventListener('hidden.bs.modal', function () {
            paymentsTable.destroy()
        })
}

function getJobDetails(id, modal){
    const jobsTable = new DataTable('#jobDetailsTable', {
            serverSide: true,
            ajax: {url: '/jobs/load/details', data: {
                'id': id,
                'modal': modal._element.id
            }},
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
            {sortable: false,
                data: 'dueDate'},
            {sortable: false,
                data: 'jobType'},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.bill)},
            {sortable: false,
                data: 'jobStatus'},
            {sortable: false,
                data: 'staff'},
        ]})

        modal._element.addEventListener('hidden.bs.modal', function () {
            jobsTable.destroy()
        })
}

function getPayStatus(id, modal){
    const paystatusTable = new DataTable('#paystatusTable', {
            serverSide: true,
            ajax: {url: '/payments/paystatus/load/details', data: {
                'id': id,
                'modal': modal._element.id
            }},
            lengthChange: false,
            info: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.bill)},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.paid)},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.balance)},
            {sortable: false,
                data: row => function () {
                if (row.status < 45) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-danger fw-bold">${row.status}%</span>
                    </button>
                    </div>
                    `
                } else if (row.status > 45 && row.status < 100) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-warning fw-bolder" style="colour:orange;">${row.status}%</span>
                    </button>
                    </div>
                    `
                } else {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-success fw-bold">${row.status}%</i></span>
                    </button>
                    </div>
                    `
                }
            }}
        ]})

        modal._element.addEventListener('hidden.bs.modal', function () {
            paystatusTable.destroy()
        })
}

function getReceiptPaymentDetails(id, modal){
    const paymentsTable = new DataTable('#paymentReceiptDetailsTable', {
            serverSide: true,
            ajax: {url: '/payments/paydetails/load/details', data: {
                'id': id,
                'modal': modal._element.id
            }},
            lengthChange: false,
            info: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
            {sortable: false,
                data: 'date'},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.amount)},
            {sortable: false,
                data: 'paymethod'},
            {sortable: false,
                data: 'staff'}
        ]})

        modal._element.addEventListener('hidden.bs.modal', function () {
            paymentsTable.destroy()
        })
}

function getReceiptJobDetails(id, modal){
    const jobsTable = new DataTable('#jobDetailsTable', {
            serverSide: true,
            ajax: {url: '/jobs/load/details', data: {
                'id': id,
                'modal': modal._element.id
            }},
            lengthChange: false,
            info: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
            {sortable: false,
                data: 'client'},
            {sortable: false,
                data: 'clientNumber'},
            {sortable: false,
                data: 'jobType'},
            {sortable: false,
                data: 'jobStatus'},
        ]})

        modal._element.addEventListener('hidden.bs.modal', function () {
            jobsTable.destroy()
        })
}

// function getMinsDiff(today, futureDate) {
//     const minsCoverter = 1000 * 60;
//     return (Math.floor(futureDate.getTime() - today.getTime())/minsCoverter).toFixed(1);
// }

function getHoursDiff(today, futureDate) {
    const hoursConverter = 1000 * 60 * 60; // Convert milliseconds to hours
    return (Math.floor(futureDate.getTime() - today.getTime()) / hoursConverter).toFixed(1);
}

const getTimeToDeadline = (args) => {
    const deadline = args[0]
    const elementId = args[1]
    const taskStatus = args[2]
    console.log(elementId)
    const timeLeftToDeadline = getHoursDiff(new Date(), new Date(deadline))
    let setInt
    const spanEl = document.getElementById(elementId)
    console.log(spanEl)

    if (spanEl){
        if (timeLeftToDeadline <= 300 && taskStatus !== 'Finished'){
            setInt = setInterval(function () {
                
                const timeLeftToDeadlineNow = new Date(deadline).getTime() - new Date().getTime()
                let hourValue = Math.floor(timeLeftToDeadlineNow / (1000 * 60 * 60))
                let hours = hourValue < 0 ? hourValue + 1 : hourValue
                let mins = Math.floor((timeLeftToDeadlineNow % (1000 * 60 * 60)) / (1000 * 60))
                let secs = Math.floor((timeLeftToDeadlineNow % (1000 * 60)) / 1000)
    
                if (timeLeftToDeadlineNow > 0){
                    spanEl.innerHTML = hours + ' hrs ' + mins + ' mins ' + secs + ' secs' + ' left';
                } else {
                    spanEl.innerHTML = hours + ' hrs ' + mins + ' mins ' + secs + ' secs' + ' past';
                }
    
            }, 1000)
        } else {
            clearInterval(setInt)
        }
    } else {
        clearInterval(setInt)
    }
    return ''

}

const debounce = (func, wait) => {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
};

function getTasksTable(id, filter, inputId) {
    let count = 1
    const table = new DataTable(id, {
        serverSide: true,
        ajax: {url: '/tasks/load', data: {
            'filter': filter,
            'inputId': inputId,
        }},
        orderMulti: false,
        columns: [
            {data: "createdAt"},
            {data: "client"},
            {data: "details"},
            {data: "taskComment"},
            {
                data: row => {
                    const tCount = count++
                    setTimeIn = setTimeout(() => {
                        getTimeToDeadline([row.deadline, 'task' + tCount, row.status]);
                    }, 4000);
                    return `<span id="task${tCount}"></span>`;
                }

            },
            {data: row => function () {
                if (row.status === 'Pending') {
                    return `
                <div class="dropdown">
                    <button class="btn text-primary dropdown-toggle status-btn" data-id="" type="button" data-bs-toggle="dropdown" aria-expanded="false">${row.status}</button>
                    <ul class="dropdown-menu p-0">
                    <li class="dropdown-item ongoing-task-btn" data-id="${ row.id }">Ongoing</li>
                    <li class="dropdown-item finished-task-btn" data-id="${ row.id }">Finished</li>
                    </ul>
                </div>
                `
                } else if (row.status === 'Ongoing') {
                    return `
                <div class="dropdown">
                    <button class="btn text-info dropdown-toggle status-btn" data-id="" type="button" data-bs-toggle="dropdown" aria-expanded="false">${row.status}</button>
                    <ul class="dropdown-menu p-0">
                    <li class="dropdown-item pending-task-btn" data-id="${ row.id }">Pending</li>
                    <li class="dropdown-item finished-task-btn" data-id="${ row.id }">Finished</li>
                    </ul>
                </div>
                `
                } else {
                    return `
                <div class="dropdown">
                    <button class="btn text-success dropdown-toggle status-btn" data-id="" type="button" data-bs-toggle="dropdown" aria-expanded="false">${row.status}</button>
                    <ul class="dropdown-menu p-0">
                    <li class="dropdown-item pending-task-btn" data-id="${ row.id }">Pending</li>
                    <li class="dropdown-item ongoing-task-btn" data-id="${ row.id }">Ongoing</li>
                    </ul>
                </div>`
                }
                
            }
        },
            {data: "assignedTo"},
            {
                sortable: false,
                data: row => function () {
                    if (row.activeUser === 'Admin' || row.activeUser === 'Reception') {
                        return `
                    <div class="d-flex flex-">
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-task-btn ${row.status == 'Finished' ? 'd-none' : ''}" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>` 
                } else {return ``}
                }
            }
        ]
    });

    return table
}

export {clearValues, getPaymentDetails, getJobDetails, getPayStatus, getReceiptPaymentDetails, getReceiptJobDetails, clearClientsList, getTimeToDeadline, debounce, getTasksTable}
