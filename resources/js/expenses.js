import { Modal }          from "bootstrap"
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import { clearValues, getPaymentDetails, getJobDetails, getPayStatus} from "./helpers"

window.addEventListener('DOMContentLoaded', function () {
    const newExpenseModal        = new Modal(document.getElementById('newExpenseModal'))
    const editExpenseModal       = new Modal(document.getElementById('editExpenseModal'))

    const recordExpenseBtn       = document.querySelector('.record-expense-btn')
    const saveExpenseBtn         = document.querySelector('.save-expense-btn')

    const table = new DataTable('#expenseTable', {
        serverSide: true,
        ajax: '/expenses/load',
        orderMulti: false,
        columns: [
            {data: "date"},
            {data: row => function () {
                if (row.flag === 1) {
                    return `<span class="text-danger fw-bold">${ row.sponsor }</span>`
                } 
                return row.sponsor
            }},
            {data: row => function () {
                if (row.flag === 1) {
                    return `<span class="text-danger fw-bold">${ row.category }</span>`
                } 
                return row.category
            }},
            {data: row => function () {
                if (row.flag === 1) {
                    return `<span class="text-danger fw-bold">${ row.description }</span>`
                }
                return row.description
            }},
            {data: row => function () {
                if (row.flag === 1) {
                    return `<span class="text-danger fw-bold">${ new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.amount) }</span>`
                }
                return row.amount
            }},
            {data: "createdAt"},
            {
                sortable: false,
                data: row => function () {
                    if (row.activeUser === 'Admin') {
                        return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-expense-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-expense-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>` }
                    else if (row.activeUser === 'Editor') {
                    return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-expense-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-expense-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `
                    }
                    else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-expense-btn invisible" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-expense-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
                }
            }
        ]
    });

    document.querySelector('#expenseTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-expense-btn')
        const deleteBtn = event.target.closest('.delete-expense-btn')

        if (editBtn) {
            const expenseId = editBtn.getAttribute('data-id')

            get(`/expenses/${ expenseId }`)
                .then(response => response.json())
                .then(response => openEditExpenseModal(editExpenseModal, response))
        } 
        else {
            const expenseId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this Expense?')) {
                del(`/expenses/${ expenseId }`).then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    newExpenseModal._element.addEventListener('show.bs.modal', function () {
        let date = new Date().toISOString().split('T')[0]
        newExpenseModal._element.querySelector('[name="date"]').setAttribute('max', date)

    })

    recordExpenseBtn.addEventListener('click', function (event) {
        recordExpenseBtn.setAttribute('disabled', 'disabled')
        post(`/expenses`, getExpenseFormData(newExpenseModal), newExpenseModal._element)
            .then(response => {
                recordExpenseBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    newExpenseModal.hide()
                }
            })
    })

    saveExpenseBtn.addEventListener('click', function (event) {
        const expenseId = event.currentTarget.getAttribute('data-id')
        saveExpenseBtn.setAttribute('disabled', 'disabled')

        post(`/expenses/${ expenseId }`, getExpenseFormData(editExpenseModal), editExpenseModal._element)
            .then(response => {
                saveExpenseBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    editExpenseModal.hide()
                    clearValues(newExpenseModal)
                }
            })
    })

    newExpenseModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(newExpenseModal._element)
    })

    editExpenseModal._element.addEventListener('hidden.bs.modal', function () {
        clearValidationErrors(editExpenseModal._element)
    })

    document.querySelector('#categoryBtn').addEventListener('click', function () {
        window.location = '/category'
    })

    document.querySelector('#sponsorBtn').addEventListener('click', function () {
        window.location = '/sponsor'
    })

})

function getExpenseFormData(modal) {
    let data     = {}
    const fields = [
        ...modal._element.getElementsByTagName('input'),
        ...modal._element.getElementsByTagName('select')
    ]

    fields.forEach(select => {
        data[select.name] = select.value
    })
    
    return data
}

function openEditExpenseModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }

    let date = new Date().toISOString().split('T')[0]
        modal._element.querySelector('.save-expense-btn').setAttribute('data-id', id)
        modal._element.querySelector('[name="date"]').setAttribute('max', date)

    modal.show()
}
