import { Modal }          from "bootstrap"
import $ from 'jquery';
import { get, post, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import 'datatables.net-plugins/api/sum().mjs'
import html2pdf  from "html2pdf.js"
import { clearValues, getPaymentDetails, getReceiptJobDetails,getPayStatus, getReceiptPaymentDetails} from "./helpers"

window.addEventListener('DOMContentLoaded', function () {
    const payJobModal           = new Modal(document.getElementById('payJobModal'))
    const detailsPaystatusModal = new Modal(document.getElementById('detailsPaystatusModal'))
    const receiptModal          = new Modal(document.getElementById('receiptModal'))

    const paidJobBtn        = document.querySelector('.paid-job-btn')
    const printReceiptBtn   = document.querySelector('.print-receipt-btn')
    const receiptModalBody  = document.querySelector('.receipt')

    const table = new DataTable('#payStatusTable', {
        serverSide: true,
        ajax: '/payments/paystatus/load',
        orderMulti: false,
        language: {
            searchPlaceholder:"Date? eg '2023-12-31'"
        },
        drawCallback: function () {
            var api = this.api()
            if (api.data()[0]['activeUser'] === 'Admin') {
                $( api.column(4).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column( 4, {page:'current'} ).data().sum())
                );
        
                $( api.column(5).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column( 5, {page:'current'} ).data().sum())
                );

                $( api.column(6).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column( 6, {page:'current'} ).data().sum())
                );
            }
        },
        columns: [
            //{data: "createdAt"},
            {data: row => `
            <div class="d-flex flex-">
            <button type="submit" class="btn btn-white receipt-btn text-decoration-underline tooltip-test" title="Generate Receipt" data-id="${ row.jobId }" value="${ row.client }">
            ${ row.client }
            </button>
            </div>
            `},
            {data: "number"},
            {data: "jobtype"},
            {data: "jobstatus"},
            {data: row => new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.bill)},
            {data: row => new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.paid)},
            {data: row => new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.balance)},
            {data: row => function () {
                if (row.status < 45) {
                    return `
                    <div class="d-flex flex-">
                    <button type="submit" class="btn btn-white details-payment-btn text-decoration-underline tooltip-test" title="Pay Details" data-id="${ row.jobId }">
                    <span class="text-danger fw-bold">${row.status}<i class="bi bi-percent"></i></span>
                    </button>
                    </div>
                    `
                } else if (row.status > 45 && row.status < 100) {
                    return `
                    <div class="d-flex flex-">
                    <button type="submit" class="btn btn-white details-payment-btn text-decoration-underline tooltip-test" title="Pay Details" data-id="${ row.jobId }">
                    <span class="text-warning fw-bolder" style="colour:orange;">${row.status}<i class="bi bi-percent"></i></span>
                    </button>
                    </div>
                    `
                } else {
                    return `
                    <div class="d-flex flex-">
                    <button type="submit" class="btn btn-white details-payment-btn text-decoration-underline tooltip-test" title="Pay Details" data-id="${ row.jobId }">
                    <span class="text-success fw-bold">${row.status}<i class="bi bi-percent"></i></span>
                    </button>
                    </div>
                    `
                }
            }},
            {
                sortable: false,
                data: row => function () {
                    if (row.bill > row.paid){
                        return `
                    <div class="d-flex flex-">
                    <button type="submit" class="btn btn-outline-primary pay-job-btn" data-id="${ row.jobId }">
                            <i class="bi bi-plus-square"></i>
                    </button>
                    </div>
                    `
                    } else{
                        return ''
                    }
                }},
        ]
    });

    document.querySelector('#payStatusTable').addEventListener('click', function (event) {
        const payJobBtn   = event.target.closest('.pay-job-btn')
        const detailsPaymentBtn   = event.target.closest('.details-payment-btn')
        const receiptBtn   = event.target.closest('.receipt-btn')

        if (payJobBtn) {
            const jobId = payJobBtn.getAttribute('data-id')

            get(`/payments/paystatus/${ jobId }`)
                .then(response => response.json())
                .then(response => openPayModal(payJobModal, response))
        } else if (detailsPaymentBtn) {
            const jobId = detailsPaymentBtn.getAttribute('data-id')

            getPaymentDetails(jobId, detailsPaystatusModal)

        } else if (receiptBtn){
            const jobId = receiptBtn.getAttribute('data-id')
            printReceiptBtn.setAttribute('client', receiptBtn.getAttribute('value'))
            
            getReceiptJobDetails(jobId, receiptModal)
            getReceiptPaymentDetails(jobId, receiptModal)
            getPayStatus(jobId, receiptModal)
            receiptModal.show()
        }
    })

    paidJobBtn.addEventListener('click', function (event) {
        paidJobBtn.setAttribute('disabled', 'disabled')

        post('/payments/paydetails', {
            job: event.currentTarget.getAttribute('data-id'),
            paid: payJobModal._element.querySelector('[name="paid"]').value,
            date: payJobModal._element.querySelector('[name="date"]').value,
            paymethod: payJobModal._element.querySelector('[name="paymethod"]').value,
        }, payJobModal._element)
        .then(response => {
            paidJobBtn.removeAttribute('disabled')
            if (response.ok) {
                table.draw()
                payJobModal.hide()
                clearValues(payJobModal)
            }
        })
        })

    
        printReceiptBtn.addEventListener('click', function () {
            const clientName = printReceiptBtn.getAttribute('client')

            var opt = {
            margin:       0.5,
            filename:     clientName + ' receipt.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 3 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(receiptModalBody).save()
    })



    payJobModal._element.addEventListener('hidden.bs.modal', function () {
        clearValidationErrors(payJobModal._element)
    })

    detailsPaystatusModal._element.addEventListener('hidden.bs.modal', function() {
        table.draw()
    })

    document.querySelector('#payDetails').addEventListener('click', function () {
        window.location = "/payments/paydetails"
    })

})


function openPayModal(modal, {id, jobId, dueDate, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]

        if (dueDate === 'N/A' || dueDate === '') {
            modal._element.querySelector(`[name="dueDate"]`).removeAttribute('type')
            modal._element.querySelector(`[name="dueDate"]`).value = dueDate
        } else if (dueDate !== 'N/A' || dueDate === '') {
            modal._element.querySelector(`[name="dueDate"]`).setAttribute('type', 'datetime-local' )
            modal._element.querySelector(`[name="dueDate"]`).value = dueDate
            
        }
    }

        let date = new Date().toISOString().split('T')[0]
            modal._element.querySelector('.paid-job-btn').setAttribute('data-id', jobId)
            modal._element.querySelector('[name="date"]').setAttribute('max', date)

    modal.show()
}


