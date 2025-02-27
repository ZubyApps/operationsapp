import { Modal }          from "bootstrap";
import $ from 'jquery'
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import 'datatables.net-plugins/api/sum().mjs'
import { clearValues } from "./helpers";

window.addEventListener('DOMContentLoaded', function () {
    const newJobTypeModal   = new Modal(document.getElementById('newJobTypeModal'))
    const editJobTypeModal  = new Modal(document.getElementById('editJobTypeModal'))

    const createJobTypeBtn  = document.querySelector('.create-jobtype-btn')
    const saveJobTypeBtn  = document.querySelector('.save-jobtype-btn')

    const table = new DataTable('#jobTypesTable', {
        serverSide: true,
        ajax: '/settings/jobtype/load',
        orderMulti: false,
        drawCallback: function () {
            var api = this.api()

            if (api.data()[0]['activeUser'] === 'Admin') {
                
                $( api.column(2).footer() ).html(api.column( 2, {page:'current'} ).data().sum());

                $( api.column(3).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                    api.column( 3, {page:'current'} ).data().sum())
                );
            }
        },
        columns: [
            {data: "name"},
            {data: "description"},
            {data: "count"},
            {data: row => function () { 
                    if (row.activeUser === 'Admin') {
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.total)
                    }
                    return ''
                }
            },
            {data: "createdAt"},
            {sortable: false,
                data: row => function () {
                    if (row.count < 1) {
                        return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-jobtype-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-jobtype-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                `
                    } else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-jobtype-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-jobtype-btn invisible" data-id="${ row.id }"><i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
            } 
            }
        ]
    });

    document.querySelector('#jobTypesTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-jobtype-btn')
        const deleteBtn = event.target.closest('.delete-jobtype-btn')

        if (editBtn) {
            const jobTypeId = editBtn.getAttribute('data-id')

            get(`/settings/jobtype/${ jobTypeId }`)
                .then(response => response.json())
                .then(response => openEditJobTypeModal(editJobTypeModal, response))
        } else {
            const jobTypeId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this Job Type?')) {
                del(`/settings/jobtype/${ jobTypeId }`)
                .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    createJobTypeBtn.addEventListener('click', function (event) {
        createJobTypeBtn.setAttribute('disabled', 'disabled')
        post(`/settings/jobtype`, getJobTypeFormData(newJobTypeModal), newJobTypeModal._element)
            .then(response => {
                createJobTypeBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    newJobTypeModal.hide()
                    clearValues(newJobTypeModal)
                }
            })
    })

    saveJobTypeBtn.addEventListener('click', function (event) {
        const jobtypeId = event.currentTarget.getAttribute('data-id')
        saveJobTypeBtn.setAttribute('disabled', 'disabled')

        post(`/settings/jobtype/${ jobtypeId }`, getJobTypeFormData(editJobTypeModal), editJobTypeModal._element)
            .then(response => {
                saveJobTypeBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    editJobTypeModal.hide()
                }
            })
    })

    newJobTypeModal._element.addEventListener('hidden.bs.modal', function (){
        clearValues(newJobTypeModal)
        clearValidationErrors(newJobTypeModal._element)
    })

    editJobTypeModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(editJobTypeModal._element)
    })

})

function getJobTypeFormData(modal) {
    let data     = {}
    const fields = [
        ...modal._element.getElementsByTagName('input')
    ]

    fields.forEach(select => {
        data[select.name] = select.value
    })

    return data
}

function openEditJobTypeModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
    }

    modal._element.querySelector('.save-jobtype-btn').setAttribute('data-id', id)

    modal.show()
}