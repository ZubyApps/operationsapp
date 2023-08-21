import { Modal }          from "bootstrap";
import { get } from "./ajax";
import 'datatables.net-plugins/api/sum().mjs'
import { getJobTypesReportTable, getListofJobs, getJobTypesTotalsChart, getJobTypesCountsChart } from "./jobtype_report_helpers";

window.addEventListener('DOMContentLoaded', function () {

    const listJobsModal = new Modal(document.getElementById('listJobsModal'))
    const getReportBtn  = document.querySelector('.search-date-btn')
    const table = document.querySelector('#jobTypeReports')
    const chart = document.querySelector('#myChart')
    const chart2 = document.querySelector('#myChart2')

    const from = document.querySelector('[name="from"]')
    const to = document.querySelector('[name="to"]')

    getReportBtn.addEventListener('click', function () {
        
        if (from.value !== '' && to.value !== '') {

            getJobTypesReportTable(from.value, to.value)

            table.classList.contains('d-none') ? table.classList.remove('d-none') : ''

            get('/reports/load/job_reports', {'from' : from.value, 'to' : to.value})
                .then(response => response.json())
                .then(response => getJobTypesTotalsChart(chart, response, from.value, to.value, getReportBtn))
            
                chart.classList.contains('d-none') ? chart.classList.remove('d-none') : ''
                
            get('/reports/load/job_reports', {'from' : from.value, 'to' : to.value})
                .then(response => response.json())
                .then(response => getJobTypesCountsChart(chart2, response, from.value, to.value, getReportBtn))

                chart2.classList.contains('d-none') ? chart2.classList.remove('d-none') : ''

        } else {

            alert('Please pick dates')

        }

    })

    document.querySelector('#jobTypeReports').addEventListener('click', function (event) {
        const listJobsBtn = event.target.closest('.list-jobs-btn')

        if (listJobsBtn) {
            const jobType = listJobsBtn.getAttribute('data-id')

            from.value !== '' && to.value !== '' ? 
            getListofJobs(from.value, to.value, jobType, listJobsModal) 
            : alert('Reinsert dates')

            
    }
    })

})
