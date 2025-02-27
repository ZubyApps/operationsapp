import { Modal }          from "bootstrap"
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import { clearValues } from "./helpers";

window.addEventListener('DOMContentLoaded', function () {
    const newUserRoleModal  = new Modal(document.getElementById('newUserRoleModal'))
    const editUserModal     = new Modal(document.getElementById('editUserModal'))

    const saveRoleBtn       = document.querySelector('.save-role-btn')
    const saveUserBtn       = document.querySelector('.save-user-btn')
    const changePasswordDiv = document.querySelector('.change-password-div')
    const passwordDiv       = document.querySelector('.password-div')
    var activeUserRole
    
    const table = new DataTable('#usersTable', {
        serverSide: true,
        ajax: '/settings/users/load',
        orderMulti: false,
        columns: [
            {data: "firstname"},
            {data: "phonenumber"},
            {data: row => function () { 
                    if (row.activeUser === 'Admin'){
                        return row.jobCount
                    }
                    return ''
                }
                },
            {data: row => function () { 
                    if (row.activeUser === 'Admin'){
                        return row.payCount
                    }
                    return ''
                }
                },
            {data: row => function () { 
                    if (row.activeUser === 'Admin'){
                        return row.role
                    }
                    return ''
                }
                },
            {data: "department"},
            {data: "head"},
            {data: "createdAt"},
            {
                sortable: false,
                data: row => function () { 
                    activeUserRole = row.activeUser
                    if (row.activeUser === 'Admin'){
                        if (row.jobCount > 0 || row.payCount > 0) {return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-user-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    </div>`
                    } else {return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-user-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-user-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>`}
                    } else {
                    return ` `
                    }
                }
            }
        ]
    });

    document.querySelector('#usersTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-user-btn')
        const deleteBtn = event.target.closest('.delete-user-btn')

        if (editBtn) {
            const userId = editBtn.getAttribute('data-id')

            get(`/settings/users/${ userId }`)
                .then(response => response.json())
                .then(response => openEditUserModal(editUserModal, response))
        } else {
            const userId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this User?')) {
                del(`/settings/users/${ userId }`).then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    saveRoleBtn.addEventListener('click', function (event) {
        saveRoleBtn.setAttribute('disabled', 'disabled')
        post(`/settings/users`, getUserFormData(newUserRoleModal), newUserRoleModal._element)
        .then(response => {
            saveRoleBtn.removeAttribute('disabled')
            if (response.ok) {
                    table.draw()
                    newUserRoleModal.hide()
                    clearValues(newUserRoleModal)
                }
            })
    })

    saveUserBtn.addEventListener('click', function (event) {
        const userId = event.currentTarget.getAttribute('data-id')
        saveUserBtn.setAttribute('disabled', 'disabled')

        post(`/settings/users/${ userId }`, getUserFormData(editUserModal), editUserModal._element)
            .then(response => {
                saveUserBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    editUserModal.hide()
                }
            })
    })

    changePasswordDiv.addEventListener('click', function () {
        const radioBtn = changePasswordDiv.querySelector('#radio-btn')

        if (radioBtn.checked){
            passwordDiv.classList.remove('d-none')
        } else if (!radioBtn.checked){
            passwordDiv.classList.add('d-none')
        }

    })

    newUserRoleModal._element.addEventListener('hidden.bs.modal', function () {
        clearValidationErrors(newUserRoleModal._element)
    })

    editUserModal._element.addEventListener('hidden.bs.modal', function () {
        clearValidationErrors(editUserModal._element)
    })

    document.querySelector('#registerUser').addEventListener('click', function () {
        if (activeUserRole === 'Admin' || activeUserRole === 'Editor'){
                window.location = '/register'
        } else {
            alert('Please, you are not authorized to register a new staff')
        }
    })

})

function getUserFormData(modal) {
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

function openEditUserModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }

    modal._element.querySelector('.save-user-btn').setAttribute('data-id', id)

    modal.show()
}


