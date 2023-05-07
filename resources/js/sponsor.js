import { Modal }          from "bootstrap";
import $ from "jquery"
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import 'datatables.net-plugins/api/sum().mjs'
import { clearValues } from "./helpers";

window.addEventListener('DOMContentLoaded', function () {
    const newSponsorModal   = new Modal(document.getElementById('newSponsorModal'))
    const editSponsorModal  = new Modal(document.getElementById('editSponsorModal'))

    const createSponsorBtn  = document.querySelector('.create-sponsor-btn')
    const saveSponsorBtn  = document.querySelector('.save-sponsor-btn')

    const table = new DataTable('#sponsorsTable', {
        serverSide: true,
        ajax: '/sponsor/load',
        orderMulti: false,
        drawCallback: function () {
            var api = this.api()
            console.log(api.data()[0]['activeUser'] === 'Admin')
            if (api.data()[0]['activeUser'] === 'Admin') {
            $( api.column(4).footer() ).html( new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(
                api.column( 4, {page:'current'} ).data().sum())
            );
            }
        },
        columns: [
            {data: "name"},
            {data: "description"},
            {data: "flag"},
            {data: "count"},
            {data: row => function () { 
                    if (row.activeUser === 'Admin') {
                    return new Intl.NumberFormat('en-US', {currencySign: 'accounting'}).format(row.total)
                    }
                    return ''
                }},
            {data: "createdAt"},
            {sortable: false,
                data: row => function () {
                    if (row.count < 1) {
                        return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-sponsor-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-sponsor-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                `
                    } else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-sponsor-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-sponsor-btn invisible" data-id="${ row.id }"><i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
                } 
            }
        ]
    });

    document.querySelector('#sponsorsTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-sponsor-btn')
        const deleteBtn = event.target.closest('.delete-sponsor-btn')

        if (editBtn) {
            const sponsorId = editBtn.getAttribute('data-id')

            get(`/sponsor/${ sponsorId }`)
                .then(response => response.json())
                .then(response => openEditSponsorModal(editSponsorModal, response))
        } else {
            const sponsorId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this Sponsor?')) {
                del(`/sponsor/${ sponsorId }`)
                .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    createSponsorBtn.addEventListener('click', function (event) {
        createSponsorBtn.setAttribute('disabled', 'disabled')
        post(`/sponsor`, getSponsorFormData(newSponsorModal), newSponsorModal._element)
            .then(response => {
                createSponsorBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    newSponsorModal.hide()
                    clearValues(newSponsorModal)
                }
            })
    })

    saveSponsorBtn.addEventListener('click', function (event) {
        const sponsorId = event.currentTarget.getAttribute('data-id')
        saveSponsorBtn.setAttribute('disabled', 'disabled')

        post(`/sponsor/${ sponsorId }`, getSponsorFormData(editSponsorModal), editSponsorModal._element)
            .then(response => {
                saveSponsorBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    editSponsorModal.hide()
                }
            })
    })

    newSponsorModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(newSponsorModal._element)
    })

    editSponsorModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(editSponsorModal._element)
    })

    document.querySelector('#expensesBtn').addEventListener('click', function () {
        window.location = '/expenses'
    })

})

function getSponsorFormData(modal) {
    let data     = {}
    const fields = [
        ...modal._element.getElementsByTagName('input')
    ]

    fields.forEach(select => {
        if (select.name === 'flag'){
            if (select.checked){
                data[select.name] = select.value = 1
            } else if (!select.checked) {
                data[select.name] = select.value = 0
            }
        }

        data[select.name] = select.value
    })

    return data
}

function openEditSponsorModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]
        data[name] === 1 & nameInput.name === 'flag' ? nameInput.checked = true: nameInput.checked = false
    }

    modal._element.querySelector('.save-sponsor-btn').setAttribute('data-id', id)

    modal.show()
}

