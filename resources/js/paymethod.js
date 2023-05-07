import { Modal }          from "bootstrap";
import $ from 'jquery'
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import 'datatables.net-plugins/api/sum().mjs'
import { clearValues } from "./helpers";

window.addEventListener('DOMContentLoaded', function () {
    const newPayMethodModal     = new Modal(document.getElementById('newPayMethodModal'))
    const editPayMethodModal    = new Modal(document.getElementById('editPayMethodModal'))

    const createPayMethodBtn    = document.querySelector('.create-paymethod-btn')
    const savePayMethodBtn    = document.querySelector('.save-paymethod-btn')

    const table = new DataTable('#payMethodTable', {
        serverSide: true,
        ajax: '/settings/paymethod/load',
        orderMulti: false,
        drawCallback: function () {
            var api = this.api()
            console.log(api.data()[0]['activeUser'] === 'Admin')
            if (api.data()[0]['activeUser'] === 'Admin') {
            $( api.column(3).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                api.column( 3, {page:'current'} ).data().sum())
            );
            }
        },
        columns: [
            {data: "name"},
            {data: "description"},
            {data: 'count'},
            {data: row => function () { 
                    if (row.activeUser === 'Admin') {
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.total)
                    }
                    return ''
                }
            },
            {data: "createdAt"},
            {
                sortable: false,
                data: row => function () {
                    if (row.count < 1) {
                        return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-paymethod-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-paymethod-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                `
                    } else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-paymethod-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-paymethod-btn invisible" data-id="${ row.id }"><i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
                } 
            }
        ]
    });

    document.querySelector('#payMethodTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-paymethod-btn')
        const deleteBtn = event.target.closest('.delete-paymethod-btn')

        if (editBtn) {
            const payMethodId = editBtn.getAttribute('data-id')

            get(`/settings/paymethod/${ payMethodId }`)
                .then(response => response.json())
                .then(response => openEditPayMethodModal(editPayMethodModal, response))
        } else {
            const payMethodId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this Payment Method?')) {
                del(`/settings/paymethod/${ payMethodId }`)
                .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    createPayMethodBtn.addEventListener('click', function (event) {
        createPayMethodBtn.setAttribute('disabled', 'disabled')
        post(`/settings/paymethod`, getPayMethodFormData(newPayMethodModal), newPayMethodModal._element)
            .then(response => {
                createPayMethodBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    newPayMethodModal.hide()
                    clearValues(newPayMethodModal)
                }
            })
    })

    savePayMethodBtn.addEventListener('click', function (event) {
        const payMethodId = event.currentTarget.getAttribute('data-id')
        savePayMethodBtn.setAttribute('disabled', 'disabled')

        post(`/settings/paymethod/${ payMethodId }`, getPayMethodFormData(editPayMethodModal), editPayMethodModal._element)
            .then(response => {
                savePayMethodBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    editPayMethodModal.hide()
                }
            })
    })

    newPayMethodModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(newPayMethodModal._element)
    })

    editPayMethodModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(editPayMethodModal._element)
    })

})

function getPayMethodFormData(modal) {
    let data     = {}
    const fields = [
        ...modal._element.getElementsByTagName('input')
    ]

    fields.forEach(select => {
        data[select.name] = select.value
    })

    return data
}

function openEditPayMethodModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }

    modal._element.querySelector('.save-paymethod-btn').setAttribute('data-id', id)

    modal.show()
}