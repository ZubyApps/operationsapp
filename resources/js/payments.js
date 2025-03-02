import { del } from "./ajax"
import $ from 'jquery'
import DataTable          from "datatables.net"
import 'datatables.net-plugins/api/sum().mjs'

window.addEventListener('DOMContentLoaded', function () {

    const table = new DataTable('#paymentTable', {
        serverSide: true,
        ajax: '/payments/paydetails/load',
        orderMulti: false,
        drawCallback: function () {
            var api = this.api()
            if (api.data()[0]['activeUser'] === 'Admin') {
            $( api.column(4).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                api.column( 4, {page:'current'} ).data().sum())
            );
            }
        },
        columns: [
            {data: "createdAt"},
            {data: "client"},
            {data: "jobtype"},
            {data: "date"},
            {
                sortable: false,
                data: row => new Intl.NumberFormat(
                    'en-US',
                    {
                        currencySign: 'accounting'
                    }
                ).format(row.paid)
            },
            {
                sortable: false,
                data: row => new Intl.NumberFormat(
                    'en-US',
                    {
                        currencySign: 'accounting'
                    }
                ).format(row.balance)
            },
            {data: "paymethod"},
            {data: "staff"},
            {
                sortable: false,
                data: row => function () {
                    if (row.activeUser === 'Admin' || row.activeUser === 'Editor') {
                        return `
                    <div class="d-flex flex-">
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-payment-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>` 
                } else {return ``}
                }
            }
        ]
    });

    document.querySelector('#paymentTable').addEventListener('click', function (event) {
        const deleteBtn = event.target.closest('.delete-payment-btn')

        if (deleteBtn) {
            const paymentId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this payment?')) {
                del(`/payments/paydetails/${ paymentId }`)
                .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    document.querySelector('#payDetails').addEventListener('click', function () {
        window.location = "/payments/paystatus"
    })
})