import DataTable from "datatables.net"
import $ from 'jquery';
import 'datatables.net-plugins/api/sum().mjs';
import JSzip from 'jszip';
import pdfMake from 'pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts'
import 'datatables.net-buttons-dt';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import 'datatables.net-select-dt';
import 'datatables.net-staterestore-dt';
import { Chart } from "chart.js/auto";
DataTable.Buttons.jszip(JSzip)
DataTable.Buttons.pdfMake(pdfMake)

pdfMake.vfs = pdfFonts.pdfMake.vfs;


function getMonthlyJobTotalsTable(year){
    if ($.fn.DataTable.isDataTable( '#monthlyJobs' )){
        $('#monthlyJobs').dataTable().fnDestroy()
    }

    const Jobstable = new DataTable('#monthlyJobs', {
        serverSide: true,
        ajax: {url: '/reports/load/yearlyJobs', data: {
                'year': year
            }},
        info: false,
        paging: false,
        searching: false,
        orderMulti: false,
        dom: 'frtip<"my-5 text-center "B>',
        buttons: [
            {extend: 'copy', className: 'btn btn-primary text-white'},
            {extend: 'csv', className: 'btn btn-primary text-white'},
            {extend: 'excel', className: 'btn btn-primary text-white'},
            {extend: 'pdfHtml5', className: 'btn btn-primary text-white'},
            {extend: 'print', className: 'btn btn-primary text-white'},
             ],
        drawCallback: function () {
            var api = this.api()
                $( api.column(1).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column(1, {page:'current'} ).data().sum())
                );

                $( api.column(2).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column(2, {page:'current'} ).data().sum())
                );
                $( api.column(3).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column(3, {page:'current'} ).data().sum())
                );
        },
        columns: [
            {data: row => `
            <div class="d-flex flex-">
            <button type="submit" class="btn btn-white list-jobs-btn text-decoration-underline tooltip-test" title="jobs list" data-id="${ row.month_name }">
            ${ row.month_name }
            </button>
            </div>
            `},
            {data: row => function () { 
                return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.bill)
                }
            },
            {data: row => function () { 
                return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.paid)
                }
            },
            {data: row => function () { 
                return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(+row.bill - +row.paid)
                }
            },
            
        ]
    })
}

function listOfJobs(year, month, modal){
    const jobs = new DataTable ('#listOfJobs', {
            serverSide: true,
            ajax: {url: '/reports/load/jobs_by_month', data: {
                'month': month,
                'year': year,
                }
            },
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            dom: 'frtip<"my-5 text-center "B>',
            buttons: [
                {extend: 'copy', className: 'btn btn-primary text-white'},
                {extend: 'csv', className: 'btn btn-primary text-white'},
                {extend: 'excel', className: 'btn btn-primary text-white'},
                {extend: 'pdf', className: 'btn btn-primary text-white'},
                {extend: 'print', className: 'btn btn-primary text-white'},
             ],
            drawCallback: function () {
                var api = this.api()

                $( api.column(3).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 3, {page:'current'} ).data().sum()));

                $( api.column(4).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column( 4, {page:'current'} ).data().sum()));

                $( api.column(5).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column( 5, {page:'current'} ).data().sum()));
            },
            columns: [
            {sortable: false,
                data: 'date'},
            {sortable: false,
                data: 'client'},
            {sortable: false,
                data: 'jobType'},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.bill)},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.paid)},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.bill - row.paid)},
            {sortable: false,
                data: 'jobStatus'}
        ]
    })
        modal._element.querySelector('.table-label').innerText = month

        modal.show()
        modal._element.addEventListener('hidden.bs.modal', function () {
            jobs.destroy()
        })
}

function getMonthlyJobTotalsChart(chart, data, year, reportBtn){

    const MonthlyChart = new Chart(chart, {
    type: 'line',
    data: {
        labels: data.data.map(row => row.month_name),
        datasets: [
            {
        label: `Months & Bills Chart for ${year}`,
        data: data.data.map(row => row.bill),
        borderWidth: 4,
        backgroundColor: 
            ["rgba(37, 254, 192, 0.52)"]
        
        },
            {
        label: `Months & Payments Chart ${year}`,
        data: data.data.map(row => row.paid),
        borderWidth: 4,
        backgroundColor: 
        ["rgba(200, 043, 122, 0.76)"]
        },
            {
        label: `Months & Outstandings Chart ${year}`,
        data: data.data.map(row => row.bill - row.paid),
        borderWidth: 4,
        backgroundColor: 
        ["rgba(96, 243, 22, 0.76)"]
    }
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
            document.querySelector('[name="job-year"]').value !== '' ? MonthlyChart.destroy() : ''
        }
    })
}

function getMonthlyExpenseTotalsTable(year){
    if ($.fn.DataTable.isDataTable( '#monthlyExpenses' )){
        $('#monthlyExpenses').dataTable().fnDestroy()
    }

    const Expensestable = new DataTable('#monthlyExpenses', {
        serverSide: true,
        ajax: {url: '/reports/load/yearlyExpenses', data: {
                'year': year
            }},
        info: false,
        paging: false,
        searching: false,
        orderMulti: false,
        dom: 'frtip<"my-5 text-center"B>',
        buttons: [
            {extend: 'copy', className: 'btn btn-primary text-white'},
            {extend: 'csv', className: 'btn btn-primary text-white'},
            {extend: 'excel', className: 'btn btn-primary text-white'},
            {extend: 'pdf', className: 'btn btn-primary text-white'},
            {extend: 'print', className: 'btn btn-primary text-white'},
             ],
        drawCallback: function () {
            var api = this.api()
                $( api.column(1).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column(1, {page:'current'} ).data().sum())
                );
        },
        columns: [
            {data: row => `
            <div class="d-flex flex-">
            <button type="submit" class="btn btn-white list-expenses-btn text-decoration-underline tooltip-test" title="Expnses list" data-id="${ row.month_name }">
            ${ row.month_name }
            </button>
            </div>
            `},
            {data: row => function () { 
                return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.amount)
                }
            },            
        ]
    })
}

function getMonthlyExpenseTotalsChart(chart, data, year, reportBtn){    
    const MonthlyChart = new Chart(chart, {
        type: 'line',
        data: {
            labels: data.data.map(row => row.month_name),
            datasets: [
                {
            label: `Months & Expenses Chart for ${year}`,
            data: data.data.map(row => row.amount),
            borderWidth: 4,
            backgroundColor: 
                ["rgba(37, 254, 192, 0.52)"]
            
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
                document.querySelector('[name="expense-year"]').value !== '' ? MonthlyChart.destroy() : ''
            }
        })
}

function listOfExpenses(year, month, modal){
    const jobs = new DataTable ('#listOfExpenses', {
            serverSide: true,
            ajax: {url: '/reports/load/expenses_by_month', data: {
                'month': month,
                'year': year,
                }
            },
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            dom: 'frtip<"my-5 text-center "B>',
            buttons: [
            {extend: 'copy', className: 'btn btn-primary text-white'},
            {extend: 'csv', className: 'btn btn-primary text-white'},
            {extend: 'excel', className: 'btn btn-primary text-white'},
            {extend: 'pdfHtml5', className: 'btn btn-primary text-white'},
            {extend: 'print', className: 'btn btn-primary text-white'},
             ],
            drawCallback: function () {
                var api = this.api()

                $( api.column(2).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 2, {page:'current'} ).data().sum()));
            },
            columns: [
            {sortable: false,
                data: 'sponsor'},
            {sortable: false,
                data: 'category'},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.amount)},
            {sortable: false,
                data: 'description'},
            {sortable: false,
                    data: 'date'},
        ]
    })
        modal._element.querySelector('.table-label').innerText = month

        modal.show()
        modal._element.addEventListener('hidden.bs.modal', function () {
            jobs.destroy()
        })
}

function getMonthlyIncomeTotalsTable(year){
    if ($.fn.DataTable.isDataTable( '#monthlyIncome' )){
        $('#monthlyIncome').dataTable().fnDestroy()
    }
        const table = new DataTable('#monthlyIncome', {
        serverSide: true,
        ajax: {url: '/reports/load/yearlyIncome', data: {
                'year': year, 
            }},
        info: false,
        paging: false,
        searching: false,
        orderMulti: false,
        dom: 'frtip<"my-5 text-center "B>',
        buttons: [
            {extend: 'copy', className: 'btn btn-primary text-white'},
            {extend: 'csv', className: 'btn btn-primary text-white'},
            {extend: 'excel', className: 'btn btn-primary text-white'},
            {extend: 'pdf', className: 'btn btn-primary text-white'},
            {extend: 'print', className: 'btn btn-primary text-white'},
             ],
        drawCallback: function () {
            var api = this.api()

            $( api.column(1).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 1, {page:'current'} ).data().sum()));

            $( api.column(2).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 2, {page:'current'} ).data().sum()));

            $( api.column(3).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 3, {page:'current'} ).data().sum()));

            $( api.column(4).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 4, {page:'current'} ).data().sum()));

            $( api.column(5).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 5, {page:'current'} ).data().sum()));
            $( api.column(6).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 6, {page:'current'} ).data().sum()));
        },
        columns: [
            {data: 'month_name'},
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.bill)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.paid)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.expense)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.bill-row.expense)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.paid-row.expense)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format((row.bill-row.expense) - (row.paid-row.expense))
                }
            }
        ]
    })

    return table
    }

function getMonthlyIncomeTotalsChart(chart, data, year, reportBtn){
    const profitLossChart = new Chart(chart, {
    type: 'line',
    data: {
        labels: data.data.map(row => row.month_name),
        datasets: [
            {
                label: `Expected Net Chart for ${year}`,
                data: data.data.map(row => row.bill-row.expense),
                borderWidth: 4,
                backgroundColor: 
                    ["rgba(37, 254, 192, 0.52)"]
                
                },
            {
                label: `Actual Net Chart ${year}`,
                data:data.data.map(row => row.paid-row.expense),
                borderWidth: 4,
                backgroundColor: 
                ["rgba(37, 59, 254, 0.52)"]
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
            document.querySelector('[name="income-year"]').value !== '' ? profitLossChart.destroy() : ''
        }
    })
}


export {getMonthlyJobTotalsTable, getMonthlyJobTotalsChart, listOfJobs, getMonthlyExpenseTotalsTable, getMonthlyExpenseTotalsChart, listOfExpenses, getMonthlyIncomeTotalsTable, getMonthlyIncomeTotalsChart}