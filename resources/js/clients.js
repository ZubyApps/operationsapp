import { Modal }          from "bootstrap"
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import { clearValues, getPaymentDetails, getJobDetails, getPayStatus} from "./helpers"

window.addEventListener('DOMContentLoaded', function () {
    const newClientModal        = new Modal(document.getElementById('newClientModal'))
    const editClientModal       = new Modal(document.getElementById('editClientModal'))
    const detailsClientModal    = new Modal(document.getElementById('detailsClientModal'))

    const createClientBtn       = document.querySelector('.create-client-btn')
    const saveClientBtn         = document.querySelector('.save-client-btn')

    const table = new DataTable('#clientsTable', {
        serverSide: true,
        ajax: '/clients/load',
        orderMulti: false,
        columns: [
            {data: row => `
                    <div class="d-flex flex-">
                    <button type="submit" class="btn btn-white details-client-btn text-decoration-underline" data-id="${ row.id }">
                            ${ row.name }
                    </button>
                    </div>
                    `},
            {data: "number"},
            {data: "email"},
            {data: "city"},
            {data: "count"},
            {sortable: false,
                data: row => new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.paid)
                },
            {data: "createdAt"},
            {
                sortable: false,
                data: row => function () {
                    if (row.activeUser === 'Admin') {
                    if (row.count < 1) {
                        return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-client-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-client-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>` } else {return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-client-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-client-btn invisible" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>`}}
                    if (row.activeUser === 'Editor') {
                    return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-client-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-client-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `
                }
                    else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-client-btn invisible" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-client-btn invisible" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
                }
            }
        ]
    });

    document.querySelector('#clientsTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-client-btn')
        const deleteBtn = event.target.closest('.delete-client-btn')
        const detailsBtn = event.target.closest('.details-client-btn')

        if (editBtn) {
            const clientId = editBtn.getAttribute('data-id')

            get(`/clients/${ clientId }`)
                .then(response => response.json())
                .then(response => openEditClientModal(editClientModal, response))
        } else if (detailsBtn) {
            const clientId = detailsBtn.getAttribute('data-id')

            get(`/clients/details/${ clientId }`)
                .then(response => response.json())
                .then(response => openEditClientModal(detailsClientModal, response))
        }
        else {
            const clientId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this Client?')) {
                del(`/clients/${ clientId }`).then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    createClientBtn.addEventListener('click', function (event) {
        createClientBtn.setAttribute('disabled', 'disabled')
        post(`/clients`, getClientFormData(newClientModal), newClientModal._element)
            .then(response => {
                createClientBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    newClientModal.hide()
                }
            })
    })

    saveClientBtn.addEventListener('click', function (event) {
        const clientId = event.currentTarget.getAttribute('data-id')
        saveClientBtn.setAttribute('disabled', 'disabled')

        post(`/clients/${ clientId }`, getClientFormData(editClientModal), editClientModal._element)
            .then(response => {
                saveClientBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    editClientModal.hide()
                    clearValues(newClientModal)
                }
            })
    })

    newClientModal._element.addEventListener('hidden.bs.modal', function (){
        //clearValues(newClientModal)
        clearValidationErrors(newClientModal._element)
    })

    editClientModal._element.addEventListener('hidden.bs.modal', function () {
        clearValidationErrors(editClientModal._element)
    })

})

function getClientFormData(modal) {
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

function openEditClientModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }

        if (modal._element.id === 'detailsClientModal'){
            modal._element.querySelector('.client-details-btn').setAttribute('data-id', id)
            getPaymentDetails(id, modal)
            getJobDetails(id, modal)
            getPayStatus(id, modal)
            modal.show()
        } else{
            modal._element.querySelector('.save-client-btn').setAttribute('data-id', id)
        }

    modal.show()
}
