import { Modal }          from "bootstrap";
import { get } from "./ajax";
import 'datatables.net-plugins/api/sum().mjs'
import { getExpenseReportTable, getListofExpenses, getExpensesTotalsChart, getExpensesCountsChart } from "./expense_report_helpers";

window.addEventListener('DOMContentLoaded', function () {

    const listExpenseModal = new Modal(document.getElementById('listExpensesModal'))
    const getReportBtn  = document.querySelector('.search-date-btn')
    const table = document.querySelector('#expenseReports')
    const chart = document.querySelector('#myChart')
    const chart2 = document.querySelector('#myChart2')

    const from = document.querySelector('[name="from"]')
    const to = document.querySelector('[name="to"]')

    getReportBtn.addEventListener('click', function () {
        
        if (from.value !== '' && to.value !== '') {

            getExpenseReportTable(from.value, to.value)

            table.classList.contains('d-none') ? table.classList.remove('d-none') : ''

            get('/reports/load/expenses', {'from' : from.value, 'to' : to.value})
                .then(response => response.json())
                .then(response => getExpensesTotalsChart(chart, response, from.value, to.value, getReportBtn))
            
                chart.classList.contains('d-none') ? chart.classList.remove('d-none') : ''
                
            get('/reports/load/expenses', {'from' : from.value, 'to' : to.value})
                .then(response => response.json())
                .then(response => getExpensesCountsChart(chart2, response, from.value, to.value, getReportBtn))

                chart2.classList.contains('d-none') ? chart2.classList.remove('d-none') : ''

        } else {

            alert('Please pick dates')

        }

    })

    document.querySelector('#expenseReports').addEventListener('click', function (event) {
        const listExpensesBtn = event.target.closest('.list-expenses-btn')

        if (listExpensesBtn) {
            const category = listExpensesBtn.getAttribute('data-id')

            from.value !== '' && to.value !== '' ? 
            getListofExpenses(from.value, to.value, category, listExpenseModal) 
            : alert('Reinsert dates')

            
    }
    })

})
