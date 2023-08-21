import "../css/dashboard.scss"
import { Modal }          from "bootstrap"
import DataTables from "datatables.net"
import { get } from "./ajax";
import { clearValues, getPaymentDetails, getPayStatus } from "./helpers"

    window.addEventListener('DOMContentLoaded', function () {
        const detailsJobModal = new Modal(document.getElementById('detailsJobModal'))

        new DataTables('#bookedJobsTable', {
            serverSide: true,
            ajax: '/jobs/load/booked',
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
            {data: row => `
            <div class="d-flex flex-">
            <button type="submit" class="btn btn-white details-job-btn text-decoration-underline" data-id="${ row.id }">
            ${ row.client }
            </button>
            </div>
            `},
            {sortable: false,
                data: 'jobType'},
            {sortable: false,
                data: row => function () {
                if (row.days <= 2) {
                    if (row.days.includes('-')) {
                        return `
                    <div class="d-flex flex-">
                    <span class="fw-bold">${row.days} day(s)</span>
                    </button>
                    </div>
                    `
                    }
                    return `
                    <div class="d-flex flex-">
                    <span class="text-danger fw-bold">${row.days} day(s)</span>
                    </button>
                    </div>
                    `} else if (row.days > 2 && row.days <= 14) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-warning fw-bold">${row.days} days(s)</span>
                    </button>
                    </div>
                    `
                } else {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-success fw-bold">${row.days} day(s)</span>
                    </button>
                    </div>
                    `
                }
                }},
            {sortable: false,
                data: row => function () {
                if (row.days <= 2) {
                    if (row.days.includes('-')) {
                        return `
                    <div class="d-flex flex-">
                    <span class="fw-bold">${row.hours} hr(s)</span>
                    </button>
                    </div>
                    `
                    }
                    return `
                    <div class="d-flex flex-">
                    <span class="text-danger fw-bold">${row.hours} hr(s)</span>
                    </button>
                    </div>
                    `} else if (row.days > 2 && row.days <= 14) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-warning fw-bold">${row.hours} hr(s)</span>
                    </button>
                    </div>
                    `
                } else {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-success fw-bold">${row.hours} hr(s)</span>
                    </button>
                    </div>
                    `
                }
                }},
        ]})

        new DataTables('#jobsInProgressTable', {
            serverSide: true,
            ajax: '/jobs/load/inprogress',
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
            {data: row => `
            <div class="d-flex flex-">
            <button type="submit" class="btn btn-white details-job-btn text-decoration-underline" data-id="${ row.id }">
            ${ row.client }
            </button>
            </div>
            `},
            {sortable: false,
                data: 'jobType'},
            {sortable: false,
                data: 'details'},
            {sortable: false,
                data: row => function () {
                if (row.days > 2) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-danger fw-bold">${row.days} day(s)</span>
                    </button>
                    </div>
                    `} else if (row.days > 1 && row.days <= 2) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-warning fw-bold">${row.days} day(s)</span>
                    </button>
                    </div>
                    `
                } else {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-success fw-bold">${row.days} day(s)</span>
                    </button>
                    </div>
                    `
                }
                }},
        ]})

        document.querySelector('#bookedJobsTable').addEventListener('click', function (event) {
        const detailsBtn = event.target.closest('.details-job-btn')
            if (detailsBtn) {
            const jobId = detailsBtn.getAttribute('data-id')

            get(`/jobs/details/${ jobId }`)
                .then(response => response.json())
                .then(response => openJobModal(detailsJobModal, response))
        }
        })

        document.querySelector('#jobsInProgressTable').addEventListener('click', function (event) {
        const detailsBtn = event.target.closest('.details-job-btn')
            if (detailsBtn) {
            const jobId = detailsBtn.getAttribute('data-id')

            get(`/jobs/details/${ jobId }`)
                .then(response => response.json())
                .then(response => openJobModal(detailsJobModal, response))
        }
        })

        new DataTables('#jobsPaystatusTable', {
            serverSide: true,
            ajax: '/payments/paystatus/load/paystatus',
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            columns: [
                {
                    sortable: false,
                    data: row => `<div class="d-flex flex-">
                    <button type="submit" class="btn btn-white details-job-btn text-decoration-underline" data-id="${ row.jobId }">${ row.client }</button></div>`},
                {
                    sortable: false,
                    data: 'job'},
                {
                    sortable: false,
                    data: row => new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.bill)},
                {
                    sortable: false,
                    data: row => new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.paid)},
                {
                    sortable: false,
                    data: row => function () {
                if (row.status < 45) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-danger fw-bold">${row.status}<i class="bi bi-percent"></i></span>
                    </button>
                    </div>
                    `
                } else if (row.status > 45 && row.status < 100) {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-warning fw-bolder" style="colour:orange;">${row.status}<i class="bi bi-percent"></i></span>
                    </button>
                    </div>
                    `
                } else {
                    return `
                    <div class="d-flex flex-">
                    <span class="text-success fw-bold">${row.status}<i class="bi bi-percent"></i></span>
                    </button>
                    </div>
                    `
                }
            }},
            ]
        })

        document.querySelector('#jobsPaystatusTable').addEventListener('click', function (event) {
        const detailsBtn = event.target.closest('.details-job-btn')
            if (detailsBtn) {
            const jobId = detailsBtn.getAttribute('data-id')

            get(`/jobs/details/${ jobId }`)
                .then(response => response.json())
                .then(response => openJobModal(detailsJobModal, response))
        }
        })
    })

function openJobModal(modal, {id, ...data}) {    
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }
    
        modal._element.querySelector('.job-details-btn').setAttribute('data-id', id)
        getPaymentDetails(id, modal)
        getPayStatus(id, modal)
        modal.show()
}