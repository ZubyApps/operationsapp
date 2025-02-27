import DataTable          from "datatables.net";
import { del } from "./ajax";

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

export {clearValues, getPaymentDetails, getJobDetails, getPayStatus, getReceiptPaymentDetails, getReceiptJobDetails, clearClientsList}
