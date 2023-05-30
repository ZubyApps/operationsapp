import DataTable          from "datatables.net";
import $ from 'jquery';
import { Chart } from "chart.js/auto";


function getExpenseReportTable(from, to){
    if ($.fn.DataTable.isDataTable( '#expenseReports' )){
        $('#expenseReports').dataTable().fnDestroy()
    }
        const table = new DataTable('#expenseReports', {
        serverSide: true,
        ajax: {url: '/reports/load/expenses', data: {
                'from': from,
                'to': to
            }},
        info: false,
        paging: false,
        searching: false,
        orderMulti: false,
        drawCallback: function () {
            var api = this.api()
                $( api.column(2).footer() ).html(api.column( 2, {page:'current'} ).data().sum());

                $( api.column(1).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column(1, {page:'current'} ).data().sum())
                );
        },
        columns: [
            {data: row => `
            <div class="d-flex flex-">
            <button type="submit" class="btn btn-white list-expenses-btn text-decoration-underline tooltip-test" title="jobs list" data-id="${ row.category }">
            ${ row.category }
            </button>
            </div>
            `},
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalAmount)
                }
            },
            {data: "count"},
        ]
    })

    return table
    }

function getListofExpenses(from, to, category, modal){
    const expensesTable = new DataTable('#listOfExpenses', {
            serverSide: true,
            ajax: {url: '/reports/load/listexpenses', data: {
                'from': from,
                'to': to,
                'category': category
            }},
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            drawCallback: function () {
                var api = this.api()

                $( api.column(2).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 2, {page:'current'} ).data().sum()));

            },
            columns: [
            {sortable: false,
                data: 'createdAt'},
            {sortable: false,
                data: 'sponsor'},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.amount)},
            {sortable: false,
                data: 'description'},
            {sortable: false,
                data: 'dateSpent'}
        ]})
        
        modal._element.querySelector('.table-label').innerText = category

        modal.show()
        modal._element.addEventListener('hidden.bs.modal', function () {
            expensesTable.destroy()
        })
}

function getExpensesTotalsChart(chart, data, from, to, reportBtn){

    const expensesChart = new Chart(chart, {
    type: 'doughnut',
    data: {
        labels: data.data.map(row => row.category),
        datasets: [
            {
        label: `Expenses & Amounts Chart (${convertDate(from)} to ${convertDate(to)})`,
        data: data.data.map(row => row.totalAmount),
        borderWidth: 4,
        backgroundColor: 
            ["rgba(37, 254, 192, 0.52)","rgba(254, 243, 37, 0.52)", "rgba(254, 37, 167, 0.52)", "rgba(37, 59, 254, 0.52)", "rgba(254, 37, 37, 0.52)", "rgba(11, 226, 254, 0.65)", "rgba(58, 32, 254, 0.54)", "rgba(127, 1, 1, 0.53)", "rgba(127, 1, 104, 0.55)", "rgba(1, 95, 127, 0.55)", "rgba(127, 62, 1, 0.36)", "rgba(84, 11, 254, 0.64)", "rgba(254, 6, 6, 0.65)", ]
        
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
        if (!chart.classList.contains('d-none')){
            document.querySelector('[name="from"]').value !== '' && document.querySelector('[name="to"]').value !== '' ? expensesChart.destroy(): ''
        }
    })

}

function getExpensesCountsChart(chart, data, from, to, reportBtn){

    const expensesChart = new Chart(chart, {
    type: 'pie',
    data: {
        labels: data.data.map(row => row.category),
        datasets: [
            {
        label: `No. of Jobs by JobTypes ${convertDate(from)} to ${convertDate(to)}`,
        data: data.data.map(row => row.count),
        borderWidth: 4,
        backgroundColor: ["rgba(37, 254, 192, 0.52)", "rgba(254, 243, 37, 0.52)", "rgba(254, 37, 167, 0.52)", "rgba(37, 59, 254, 0.52)", "rgba(254, 37, 37, 0.52)", "rgba(11, 226, 254, 0.65)", "rgba(58, 32, 254, 0.54)", "rgba(127, 1, 1, 0.53)", "rgba(127, 1, 104, 0.55)", "rgba(1, 95, 127, 0.55)", "rgba(127, 62, 1, 0.36)", "rgba(84, 11, 254, 0.64)", "rgba(254, 6, 6, 0.65)"]
        }
    ]
    },
    options: {
        scales: {
        y: {
            beginAtZero: true
            }
        }
    }
    });

    reportBtn.addEventListener('click', function() {
        if (!chart.classList.contains('d-none')){
            document.querySelector('[name="from"]').value !== '' && document.querySelector('[name="to"]').value !== '' ? expensesChart.destroy(): ''
        }
    })
}

function convertDate(date){
    var d_arr = date.split("-");
    var newdate = d_arr[2] + '-' + d_arr[1] + '-' + d_arr[0];
    return newdate;
    }

export {getExpenseReportTable, getListofExpenses, getExpensesTotalsChart, getExpensesCountsChart}
