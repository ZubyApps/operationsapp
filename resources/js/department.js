import { Modal }          from "bootstrap";
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import { clearValues } from "./helpers";

window.addEventListener('DOMContentLoaded', function () {
    const newDepartmentModal    = new Modal(document.getElementById('newDepartmentModal'))
    const editDepartmentModal   = new Modal(document.getElementById('editDepartmentModal'))
    const openModal             = document.getElementById('openModal')

    const createDepartmentBtn   = document.querySelector('.create-department-btn')
    const saveDepartmentBtn   = document.querySelector('.save-department-btn')

    const table = new DataTable('#departmentTable', {
        serverSide: true,
        ajax: '/settings/department/load',
        orderMulti: false,
        columns: [
            {data: "name"},
            {data: "description"},
            {data: "head"},
            {data: 'count'},
            {data: "createdAt"},
            {
                sortable: false,
                data: row => function () {
                    if (row.activeUser === 'Admin') {
                    if (row.count < 1) {
                        return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-department-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-department-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>` } else {return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-department-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-department-btn invisible" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>`}}
                    if (row.activeUser === 'Editor') {
                    return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-department-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-department-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `
                }
                    else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-department-btn invisible" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-department-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
                }
            }
        ]
    });

    document.querySelector('#departmentTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-department-btn')
        const deleteBtn = event.target.closest('.delete-department-btn')

        if (editBtn) {
            const DepartmentId = editBtn.getAttribute('data-id')

            get(`/settings/department/${ DepartmentId }`)
                .then(response => response.json())
                .then(response => openEditDepartmentModal(editDepartmentModal, response))
        } else {
            const DepartmentId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this Department?')) {
                del(`/settings/department/${ DepartmentId }`)
                .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    createDepartmentBtn.addEventListener('click', function (event) {
        createDepartmentBtn.setAttribute('disabled', 'disabled')
        post(`/settings/department`, getDepartmentFormData(newDepartmentModal), newDepartmentModal._element)
        .then(response => {
            createDepartmentBtn.removeAttribute('disabled')
            if (response.ok) {
                    table.draw()
                    newDepartmentModal.hide()
                    clearValues(newDepartmentModal)
                }
            })
    })

    saveDepartmentBtn.addEventListener('click', function (event) {
        const DepartmentId = event.currentTarget.getAttribute('data-id')
        saveDepartmentBtn.setAttribute('disabled', 'disabled')
        post(`/settings/department/${ DepartmentId }`, getDepartmentFormData(editDepartmentModal), editDepartmentModal._element).then(response => {
            saveDepartmentBtn.removeAttribute('disabled')
            if (response.ok) {
                    table.draw()
                    editDepartmentModal.hide()
                }
            })
    })

    newDepartmentModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(newDepartmentModal._element)
    })

    editDepartmentModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(editDepartmentModal._element)
    })

})

function getDepartmentFormData(modal) {
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

function openEditDepartmentModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }

    modal._element.querySelector('.save-department-btn').setAttribute('data-id', id)

    modal.show()
}