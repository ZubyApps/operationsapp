import { Modal }          from "bootstrap";
import $ from 'jquery'
import { get, post, del, clearValidationErrors } from "./ajax"
import DataTable          from "datatables.net"
import 'datatables.net-plugins/api/sum().mjs'
import { clearValues } from "./helpers";

window.addEventListener('DOMContentLoaded', function () {
    const newCategoryModal   = new Modal(document.getElementById('newCategoryModal'))
    const editCategoryModal  = new Modal(document.getElementById('editCategoryModal'))

    const createCategoryBtn  = document.querySelector('.create-category-btn')
    const saveCategoryBtn  = document.querySelector('.save-category-btn')

    const table = new DataTable('#categoriesTable', {
        serverSide: true,
        ajax: '/category/load',
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
                    <button class=" btn btn-outline-primary edit-category-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-category-btn" data-id="${ row.id }">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                `
                    } else { return `
                    <div class="d-flex flex-">
                    <button class=" btn btn-outline-primary edit-category-btn" data-id="${ row.id }">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="submit" class="ms-1 btn btn-outline-primary delete-category-btn invisible" data-id="${ row.id }"><i class="bi bi-trash3-fill"></i>
                    </button>
                    </div>
                    `}
                } 
            }
        ]
    });

    document.querySelector('#categoriesTable').addEventListener('click', function (event) {
        const editBtn   = event.target.closest('.edit-category-btn')
        const deleteBtn = event.target.closest('.delete-category-btn')

        if (editBtn) {
            const categoryId = editBtn.getAttribute('data-id')

            get(`/category/${ categoryId }`)
                .then(response => response.json())
                .then(response => openEditCategoryModal(editCategoryModal, response))
        } else {
            const categoryId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this Category?')) {
                del(`/category/${ categoryId }`)
                .then(response => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    createCategoryBtn.addEventListener('click', function (event) {
        createCategoryBtn.setAttribute('disabled', 'disabled')
        post(`/category`, getCategoryFormData(newCategoryModal), newCategoryModal._element)
            .then(response => {
                createCategoryBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    newCategoryModal.hide()
                    clearValues(newCategoryModal)
                }
            })
    })

    saveCategoryBtn.addEventListener('click', function (event) {
        const categoryId = event.currentTarget.getAttribute('data-id')
        saveCategoryBtn.setAttribute('disabled', 'disabled')

        post(`/category/${ categoryId }`, getCategoryFormData(editCategoryModal), editCategoryModal._element)
            .then(response => {
                saveCategoryBtn.removeAttribute('disabled')
                if (response.ok) {
                    table.draw()
                    editCategoryModal.hide()
                }
            })
    })

    newCategoryModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(newCategoryModal._element)
    })

    editCategoryModal._element.addEventListener('hidden.bs.modal', function (){
        clearValidationErrors(editCategoryModal._element)
    })

    document.querySelector('#expensesBtn').addEventListener('click', function () {
        window.location = '/expenses'
    })

})

function getCategoryFormData(modal) {
    let data     = {}
    const fields = [
        ...modal._element.getElementsByTagName('input')
    ]

    fields.forEach(select => {
        data[select.name] = select.value
    })

    return data
}

function openEditCategoryModal(modal, {id, ...data}) {
    for (let name in data) {
        const nameInput = modal._element.querySelector(`[name="${ name }"]`)

        nameInput.value = data[name]

    }

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)

    modal.show()
}

