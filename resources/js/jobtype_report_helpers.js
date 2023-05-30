import DataTable          from "datatables.net";
import $ from 'jquery';
import { Chart } from "chart.js/auto";


function getJobTypesReportTable(from, to){
    if ($.fn.DataTable.isDataTable( '#jobTypeReports' )){
        $('#jobTypeReports').dataTable().fnDestroy()
    }
        const table = new DataTable('#jobTypeReports', {
        serverSide: true,
        ajax: {url: '/reports/load/jobtypes', data: {
                'from': from,
                'to': to
            }},
        info: false,
        paging: false,
        searching: false,
        orderMulti: false,
        drawCallback: function () {
            var api = this.api()
                $( api.column(4).footer() ).html(api.column( 4, {page:'current'} ).data().sum());

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
            <button type="submit" class="btn btn-white list-jobs-btn text-decoration-underline tooltip-test" title="jobs list" data-id="${ row.jobType }">
            ${ row.jobType }
            </button>
            </div>
            `},
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalBill)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.totalPaid)
                }
            },
            {data: row => function () { 
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(+row.totalBill - +row.totalPaid)
                }
            },
            {data: "count"},
        ]
    })

    return table
    }

function getListofJobs(from, to, jobType, modal){
    const jobsTable = new DataTable('#listOfJobs', {
            serverSide: true,
            ajax: {url: '/reports/load/listjobtypes', data: {
                'from': from,
                'to': to,
                'jobType': jobType
            }},
            lengthChange: false,
            paging: false,
            searching: false,
            orderMulti: false,
            drawCallback: function () {
                var api = this.api()

                $( api.column(2).footer() ).html(new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(api.column( 2, {page:'current'} ).data().sum()));

                $( api.column(3).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column( 3, {page:'current'} ).data().sum()));
            },
            columns: [
            {sortable: false,
                data: 'date'},
            {sortable: false,
                data: 'client'},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.bill)},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US',{currencySign: 'accounting'}).format(row.paid)},
            {sortable: false,
                data: 'jobStatus'}
        ]})
        
        modal._element.querySelector('.table-label').innerText = jobType

        modal.show()
        modal._element.addEventListener('hidden.bs.modal', function () {
            jobsTable.destroy()
        })
}

function getJobTypesTotalsChart(chart, data, from, to, reportBtn){

    const jobTypesChart = new Chart(chart, {
    type: 'bar',
    data: {
        labels: data.data.map(row => row.jobType),
        datasets: [
            {
        label: `Jobtypes & Bills Chart (${convertDate(from)} to ${convertDate(to)})`,
        data: data.data.map(row => row.totalBill),
        borderWidth: 4,
        backgroundColor: 
            ["rgba(37, 254, 192, 0.52)","rgba(254, 243, 37, 0.52)", "rgba(254, 37, 167, 0.52)", "rgba(37, 59, 254, 0.52)", "rgba(254, 37, 37, 0.52)", "rgba(11, 226, 254, 0.65)", "rgba(58, 32, 254, 0.54)", "rgba(127, 1, 1, 0.53)", "rgba(127, 1, 104, 0.55)", "rgba(1, 95, 127, 0.55)", "rgba(127, 62, 1, 0.36)", "rgba(84, 11, 254, 0.64)", "rgba(254, 6, 6, 0.65)", ]
        
        },
            {
        label: `Jobtypes & Payments Chart (${convertDate(from)} to ${convertDate(to)})`,
        data: data.data.map(row => row.totalPaid),
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
            document.querySelector('[name="from"]').value !== '' && document.querySelector('[name="to"]').value !== '' ? jobTypesChart.destroy() : ''
        }
    })

}

function getJobTypesCountsChart(chart, data, from, to, reportBtn){

    const jobTypesChart = new Chart(chart, {
    type: 'doughnut',
    data: {
        labels: data.data.map(row => row.jobType),
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
            document.querySelector('[name="from"]').value !== '' && document.querySelector('[name="to"]').value !== '' ?jobTypesChart.destroy() : ''
        }
    })
}

function convertDate(date){
    var d_arr = date.split("-");
    var newdate = d_arr[2] + '-' + d_arr[1] + '-' + d_arr[0];
    return newdate;
    }

export {getJobTypesReportTable, getListofJobs, getJobTypesTotalsChart, getJobTypesCountsChart, convertDate}