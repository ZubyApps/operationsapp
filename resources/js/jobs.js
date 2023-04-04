import { Modal }          from "bootstrap";
import { get, post, del, clearValidationErrors } from "./ajax";
import DataTable          from "datatables.net"
import { clearValues, getPaymentDetails, getPayStatus, clearClientsList } from "./helpers";


window.addEventListener('DOMContentLoaded', function () {
    const newJobModal       = new Modal(document.getElementById('newJobModal'))
    const editJobModal      = new Modal(document.getElementById('editJobModal'))
    const detailsJobModal   = new Modal(document.getElementById('detailsJobModal'))

    const createJobBtn      = document.querySelector('.create-job-btn')
    const saveJobBtn        = document.querySelector('.save-job-btn')

    const forceBooking          = newJobModal._element.querySelector('[name="confirmBooking"]')
    const forceBookingDiv       = newJobModal._element.querySelector('.form-check')
    const newjobStatusInput     = newJobModal._element.querySelector('[name="jobStatus"]')
    const editjobStatusInput    = editJobModal._element.querySelector('[name="jobStatus"]')
    const newDueDateDiv         = newJobModal._element.querySelector('.dueDate')
    const editDueDateDiv        = editJobModal._element.querySelector('.dueDate')
    const newdueDateInput       = newJobModal._element.querySelector('[name="dueDate"]')
    const editdueDateInput      = editJobModal._element.querySelector('[name="dueDate"]')

    const table = new DataTable('#jobsTable', {
        serverSide: true,
        ajax: '/jobs/load',
        orderMulti: false,
        language: {
            searchPlaceholder:"Date? eg '2023-12-31'"
        },
        columns: [
            {data: row => `
            <div class="d-flex flex-">
            <button type="submit" class="btn btn-white details-job-btn text-decoration-underline tooltip-test" title="See more" data-id="${ row.id }">
            ${ row.client }
            </button>
            </div>
            `},
            {data: "number"},
            {data: "jobType"},
            {data: "date"},
            {data: "dueDate"},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.bill)
                },
            {data: row => function () {
                if (row.jobStatus === 'Booked') {
                    return `
                <div class="dropdown">
                    <button class="btn  dropdown-toggle jobStatus-btn" data-id="" type="button" data-bs-toggle="dropdown" aria-expanded="false">${row.jobStatus}</button>
                    <ul class="dropdown-menu p-0">
                    <li class="dropdown-item inprogress-job-btn" data-id="${ row.id }">Inprogress</li>
                    <li class="dropdown-item delivered-job-btn" data-id="${ row.id }">Delivered</li>
                    </ul>
                </div>
                `
                } else if (row.jobStatus === 'Inprogress') {
                    return `
                <div class="dropdown">
                    <button class="btn text-warning dropdown-toggle jobStatus-btn" data-id="" type="button" data-bs-toggle="dropdown" aria-expanded="false">${row.jobStatus}</button>
                    <ul class="dropdown-menu p-0">
                    <li class="dropdown-item delivered-job-btn" data-id="${ row.id }">Delivered</li>
                    </ul>
                </div>
                `
                } else {
                    return `<span class="text-success fw-bold">${row.jobStatus}</span>`
                }
                
            }
        },
        {
            sortable: false,
                data: row => function () {
                    if (row.jobStatus === 'Delivered') {
                        return `
                        <div class="d-flex flex-">
                            <button class=" btn btn-outline-primary edit-job-btn" data-id="${ row.id }">
                            <i class="bi bi-pencil-fill"></i>
                        </div>
                        `
                    } else {
                    if (row.activeUser === 'Admin') {
                        if (row.count < 1) {
                        return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-job-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-job-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>` } else {return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-job-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-job-btn invisible" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>`}}
                    if (row.activeUser === 'Editor') {
                    return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-job-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-job-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `
                }
                    else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-job-btn invisible" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-job-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
                }
            }
            }
        ]
    });
    

    document.querySelector('#jobsTable').addEventListener('click', function (event) {
        const editBtn       = event.target.closest('.edit-job-btn')
        const deleteBtn     = event.target.closest('.delete-job-btn')
        const detailsBtn    = event.target.closest('.details-job-btn')

        const bookedJobBtn      = event.target.closest('.booked-job-btn')
        const inprogressJobBtn  = event.target.closest('.inprogress-job-btn')
        const deliveredJobBtn   = event.target.closest('.delivered-job-btn')

        if (editBtn) {
            const jobId = editBtn.getAttribute('data-id')

            get(`/jobs/${ jobId }`)
                .then(response => response.json())
                .then(response => openJobModal(editJobModal, response))
        }else if (detailsBtn) {
            const jobId = detailsBtn.getAttribute('data-id')

            get(`/jobs/details/${ jobId }`)
                .then(response => response.json())
                .then(response => openJobModal(detailsJobModal, response))
        } else if (bookedJobBtn) {
            const jobId = bookedJobBtn.getAttribute('data-id')

            post(`/jobs/status/${ jobId }`, {jobStatus: 'Booked'})
            .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })

        } else if (inprogressJobBtn) {
            const jobId = inprogressJobBtn.getAttribute('data-id')

            post(`/jobs/status/${ jobId }`, {jobStatus: 'Inprogress'})
            .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
        } else if (deliveredJobBtn) {
            const jobId = deliveredJobBtn.getAttribute('data-id')

            post(`/jobs/status/${ jobId }`, {jobStatus: 'Delivered'})
            .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
        }
        else if (deleteBtn) {
            const jobId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this job?')) {
                del(`/jobs/${ jobId }`).then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    newJobModal._element.addEventListener('show.bs.modal', function () {
        get('/clients/list')
                .then(response => response.json())
                .then(response => displayClientList(newJobModal, response))

        let date = new Date().toISOString().slice(0,16)
        newJobModal._element.querySelector('[name="dueDate"]').setAttribute('min', date)

    })

    newjobStatusInput.addEventListener('change', function () {
        dueDateReveal(newJobModal._element, newjobStatusInput, newDueDateDiv, newdueDateInput)
    })

    editjobStatusInput.addEventListener('change', function () {
        dueDateReveal(editJobModal._element, editjobStatusInput, editDueDateDiv, editdueDateInput)
    })

    createJobBtn.addEventListener('click', function () {
        createJobBtn.setAttribute('disabled', 'disabled')

            post('/jobs', {
            jobType:    newJobModal._element.querySelector('[name="jobType"]').value,
            details:    newJobModal._element.querySelector('[name="details"]').value,
            dueDate:    newJobModal._element.querySelector('[name="dueDate"]').value,
            bill:       newJobModal._element.querySelector('[name="bill"]').value,
            jobStatus:  newJobModal._element.querySelector('[name="jobStatus"]').value,
            forceBooking: forceBooking.checked ? 'Yes' : '',
            client:     getClientDataId(newJobModal)
        }, newJobModal._element).then(response => {
            createJobBtn.removeAttribute('disabled')
            if (response.ok) {
                table.draw()
                newJobModal.hide()
                clearValues(newJobModal)
            }
            })
        })

    saveJobBtn.addEventListener('click', function (event) {
        const jobId = event.currentTarget.getAttribute('data-id')
        saveJobBtn.setAttribute('disabled', 'disabled')

            post(`/jobs/${ jobId }`, {
            jobType:    editJobModal._element.querySelector('[name="jobType"]').value,
            details:    editJobModal._element.querySelector('[name="details"]').value,
            dueDate:    editJobModal._element.querySelector('[name="dueDate"]').value,
            bill:       editJobModal._element.querySelector('[name="bill"]').value,
            jobStatus:  editJobModal._element.querySelector('[name="jobStatus"]').value,
            client:     getClientDataId(editJobModal)
        }, editJobModal._element).then(response => { 
            saveJobBtn.removeAttribute('disabled')
            if (response.ok) {
                table.draw()
                editJobModal.hide()
                }
            })
        })
    
    newJobModal._element.addEventListener('hidden.bs.modal', function (){
        newJobModal._element.querySelectorAll('#clientOption').forEach(clientList => {
            clearClientsList(clientList)
        })
        
        clearValidationErrors(newJobModal._element)

        forceBooking.checked = false

        if (forceBookingDiv){
            forceBookingDiv.classList.add('d-none')
        }

        if (!newJobModal._element.querySelector('.dueDate').classList.contains('d-none')) {
                newJobModal._element.querySelector('.dueDate').classList.add('d-none')
            }
    })
    
    editJobModal._element.addEventListener('hidden.bs.modal', function () {
        clearClientsList(editJobModal._element.querySelector('#clientOption'))
        clearValidationErrors(editJobModal._element)
    })
})



function openJobModal(modal, {id, ...data}) {    
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }

        if (modal._element.id === 'editJobModal'){
            let date = new Date().toISOString().slice(0,16)
            modal._element.querySelector('.save-job-btn').setAttribute('data-id', id)
            modal._element.querySelector('[name="dueDate"]').setAttribute('min', date)

            if (modal._element.querySelector('[name="jobStatus"]').value === 'Booked') {
            modal._element.querySelector('.dueDate').classList.remove('d-none')
            } else {
            modal._element.querySelector('.dueDate').classList.add('d-none')
            }

            modal.show()
            get('/clients/list')
                .then(response => response.json())
                .then(response => displayClientList(modal, response))
            
        } else if (modal._element.id === 'detailsJobModal') {
            modal._element.querySelector('.job-details-btn').setAttribute('data-id', id)
            getPaymentDetails(id, modal)
            getPayStatus(id, modal)
            modal.show()
        }
    }

function displayClientList(modal, data) {
        data.forEach(line => {
            const option = document.createElement("OPTION")
            option.setAttribute('id', 'clientOption')

            if (modal._element.id === 'editJobModal')
            {
                var elementAttributes = {
                "value"     : line.name,
                "data-id"   : line.id,
                "name"      : line.name,
                }
            } else {
                var elementAttributes = {
                "value"     : line.name + ' ' + line.phoneNumber,
                "data-id"   : line.id,
                "name"      : line.name + ' ' + line.phoneNumber,
                }
            }


            Object.keys(elementAttributes).forEach(attribute => {
            option.setAttribute(attribute, elementAttributes[attribute])
            
            modal._element.querySelector('#client-id').setAttribute('list', 'clientList')
            modal._element.querySelector('datalist').setAttribute('id', 'clientList')
            modal._element.querySelector('#clientList').appendChild(option)
            });
            })
        }

function getClientDataId(modal) {
        const inputEl = modal._element.querySelector('#client-id')

        const dataListId = modal._element.querySelector('#clientList')

        const selectedOption = dataListId.options.namedItem(inputEl.value)

        if (selectedOption) {
            return selectedOption.getAttribute('data-id')
        } else {
            return ""
        }
    }

function dueDateReveal(modal, jobStatusInput, dueDatediv, dueDateInput) {

    if (jobStatusInput.value === 'Booked') {

            dueDatediv.classList.remove('d-none')

    }else if (jobStatusInput.value !== 'Booked') {

                if (modal.id === 'newJobModal'){
                    if (dueDateInput.value) {
                    dueDateInput.value = ''
                    }
                }

            dueDatediv.classList.add('d-none')
    }
}