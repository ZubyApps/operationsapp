import { get } from "./ajax";
import { Modal } from "bootstrap";
import { getMonthlyJobTotalsTable, getMonthlyJobTotalsChart, listOfJobs, getMonthlyExpenseTotalsTable, getMonthlyExpenseTotalsChart, listOfExpenses, getMonthlyIncomeTotalsTable, getMonthlyIncomeTotalsChart} from "./yearly_reports_helper";

window.addEventListener('DOMContentLoaded', function () {

    const getJobReportBtn       = document.querySelector('.search-job-year-btn')
    const getExpenseReportBtn   = document.querySelector('.search-expense-year-btn')
    const getIncomeReportBtn    = document.querySelector('.search-income-year-btn')

    const listJobsModal     = new Modal(document.getElementById('listJobsModal'))
    const listExpensesModal = new Modal(document.getElementById('listExpensesModal'))

    const jobChart      = document.querySelector('#myJobChart')
    const expenseChart  = document.querySelector('#myExpenseChart')
    const incomeChart   = document.querySelector('#myIncomeChart')

    const jobYear       = document.querySelector('[name="job-year"]')
    const expenseYear   = document.querySelector('[name="expense-year"]')
    const incomeYear    = document.querySelector('[name="summary-year"]')

    const jobTab = document.querySelector('#nav-jobs-tab')
    const expensesTab = document.querySelector('#nav-expenses-tab')
    const summaryTab = document.querySelector('#nav-summary-tab')

    this.document.title = "Yealy Jobs"

    jobTab.addEventListener('click', function(){
        document.title = "Yealy Jobs"
    })

    expensesTab.addEventListener('click', function(){
        document.title = "Yearly Expenses"
    })

    summaryTab.addEventListener('click', function(){
        document.title = "Combined Summary"
    })

    
    getMonthlyJobTotalsTable(jobYear.value)

    get('/reports/load/yearlyJobs', {'year' : jobYear.value})
                .then(response => response.json())
                .then(response => getMonthlyJobTotalsChart(jobChart, response, jobYear.value, getJobReportBtn))

    getMonthlyExpenseTotalsTable(expenseYear.value)

    get('/reports/load/yearlyExpenses', {'year' : expenseYear.value})
                .then(response => response.json())
                .then(response => getMonthlyExpenseTotalsChart(expenseChart, response, expenseYear.value, getExpenseReportBtn))
    
    getMonthlyIncomeTotalsTable(incomeYear.value)

    get('/reports/load/yearlyIncome', {'year' : incomeYear.value})
                .then(response => response.json())
                .then(response => getMonthlyIncomeTotalsChart(incomeChart, response, incomeYear.value, getIncomeReportBtn))


    getJobReportBtn.addEventListener('click', function () {
        
        if (jobYear.value !== '') {

            getMonthlyJobTotalsTable(jobYear.value)

            get('/reports/load/yearlyJobs', {'year' : jobYear.value})
                .then(response => response.json())
                .then(response => getMonthlyJobTotalsChart(jobChart, response, jobYear.value, getJobReportBtn))
        }else {

            alert('Please pick a year')

        }
    })

    getExpenseReportBtn.addEventListener('click', function () {
        
        if (expenseYear.value !== '') {

            getMonthlyExpenseTotalsTable(expenseYear.value)

            get('/reports/load/yearlyExpenses', {'year' : expenseYear.value})
                .then(response => response.json())
                .then(response => getMonthlyExpenseTotalsChart(expenseChart, response, expenseYear.value, getExpenseReportBtn))
        }else {

            alert('Please pick a year')

        }
    })

    getIncomeReportBtn.addEventListener('click', function () {
        
        if (incomeYear.value !== '') {

            getMonthlyIncomeTotalsTable(incomeYear.value)

            get('/reports/load/yearlyIncome', {'year' : incomeYear.value})
                .then(response => response.json())
                .then(response => getMonthlyIncomeTotalsChart(incomeChart, response, incomeYear.value, getIncomeReportBtn))

        }else {

            alert('Please pick a year')

        }
    })

    document.querySelector('#monthlyJobs').addEventListener('click', function (event) {
        const listJobsBtn = event.target.closest('.list-jobs-btn')

        if (listJobsBtn) {
            const month = listJobsBtn.getAttribute('data-id')

            jobYear.value !== '' ? 
            listOfJobs(jobYear.value, month, listJobsModal) 
            : alert('Reinsert year')
        }
    })

    document.querySelector('#monthlyExpenses').addEventListener('click', function (event) {
        const listExpensesBtn = event.target.closest('.list-expenses-btn')

        if (listExpensesBtn) {
            const month = listExpensesBtn.getAttribute('data-id')

            expenseYear.value !== ''  ? 
            listOfExpenses(expenseYear.value, month, listExpensesModal) 
            : alert('Reinsert year')
        }
    })

})
