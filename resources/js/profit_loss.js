import DataTable          from "datatables.net";
import { get } from "./ajax";
import $ from 'jquery';
import 'datatables.net-plugins/api/sum().mjs';
import { Chart } from "chart.js";
import { convertDate } from "./jobtype_report_helpers";

window.addEventListener('DOMContentLoaded', function () {

    const getReportBtn  = document.querySelector('.search-date-btn')
    const table = document.querySelector('#profitLossReports')
    const chart = document.querySelector('#myChart')

    const from = document.querySelector('[name="from"]')
    const to = document.querySelector('[name="to"]')

    getReportBtn.addEventListener('click', function () {
        
        if (from.value !== '' && to.value !== '') {

            getProfitLossReportTable(from.value, to.value)

            table.classList.contains('d-none') ? table.classList.remove('d-none') : ''

            get('/reports/load/profit_loss', {'from' : from.value, 'to' : to.value})
                .then(response => response.json())
                .then(response => getProfitLossChart(chart, response, from.value, to.value, getReportBtn))
            
                chart.classList.contains('d-none') ? chart.classList.remove('d-none') : ''

        } else {

            alert('Please pick dates')

        }

    })
})

function getProfitLossReportTable(from, to){
    if ($.fn.DataTable.isDataTable( '#profitLossReports' )){
        $('#profitLossReports').dataTable().fnDestroy()
    }
        const table = new DataTable('#profitLossReports', {
        serverSide: true,
        ajax: {url: '/reports/load/profit_loss', data: {
                'from': from,
                'to': to
            }},
        info: false,
        paging: false,
        searching: false,
        orderMulti: false,
        columns: [
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalBills)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalPayments)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalExpenses)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalBills-row.totalExpenses)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalPayments-row.totalExpenses)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format((row.totalBills-row.totalExpenses) - (row.totalPayments-row.totalExpenses))
                }
            }
        ]
    })

    return table
    }

function getProfitLossChart(chart, data, from, to, reportBtn){
    const profitLossChart = new Chart(chart, {
    type: 'bar',
    data: {
        labels: ['Total Bills', 'Total Payments', 'Total Expenses'],
        datasets: [
            {
        label: `Bills, Payments and Expenses Chart (${convertDate(from)} to ${convertDate(to)})`,
        data: [data.data[0].totalBills, data.data[0].totalPayments, data.data[0].totalExpenses],
        borderWidth: 4,
        backgroundColor: 
            ["rgba(37, 59, 254, 0.52)", "rgba(96, 243, 22, 0.76)", "rgba(254, 37, 37, 0.52)"]
        },
    ]
    },
    options: {
        scales: {
        y: {
            beginAtZero: true,
            grid: {
                offset: true
            }
            }
        }
    }
    });

    reportBtn.addEventListener('click', function() {
        if (!chart.classList.contains('d-none')) {
            document.querySelector('[name="from"]').value !== '' && document.querySelector('[name="to"]').value !== '' ? profitLossChart.destroy() : ''
        }
    })
}
