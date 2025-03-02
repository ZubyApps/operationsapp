import { del, post } from "./ajax"
import $ from 'jquery'
import DataTable          from "datatables.net"
import 'datatables.net-plugins/api/sum().mjs'
import { getTasksTable } from "./helpers"

window.addEventListener('DOMContentLoaded', function () {

    let TaskTable = getTasksTable('#taskTable', 'Unfinished', null)

    document.querySelector('#taskTable').addEventListener('click', function (event) {
        const pendingTaskBtn      = event.target.closest('.pending-task-btn')
        const ongoingTaskBtn  = event.target.closest('.ongoing-task-btn')
        const finishedTaskBtn   = event.target.closest('.finished-task-btn')
        const deleteBtn = event.target.closest('.delete-task-btn')


        if (pendingTaskBtn) {
            const taskId = pendingTaskBtn.getAttribute('data-id')

            post(`/tasks/status/${ taskId }`, {taskStatus: 'Pending'})
            .then(response => {
                    if (response.ok) {
                        TaskTable.draw(false)
                    }
                })

        }
        
        if (ongoingTaskBtn) {
            const taskId = ongoingTaskBtn.getAttribute('data-id')

            post(`/tasks/status/${ taskId }`, {taskStatus: 'Ongoing'})
            .then(response => {
                    if (response.ok) {
                        TaskTable.draw(false)
                    }
                })
        }
        
        if (finishedTaskBtn) {
            const taskId = finishedTaskBtn.getAttribute('data-id')

            post(`/tasks/status/${ taskId }`, {taskStatus: 'Finished'})
            .then(response => {
                    if (response.ok) {
                        TaskTable.draw(false)
                    }
                })
        }

        if (deleteBtn) {
            const taskId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure you want to delete this task?')) {
                del(`/tasks/${ taskId }`)
                .then(response => {
                    if (response.ok) {
                        TaskTable.draw()
                    }
                })
            }
        }
    })

    document.querySelectorAll('#allTasks, #myTasks').forEach(filterInput => {
        filterInput.addEventListener('change', function () {
            if (filterInput.id == 'allTasks'){
                TaskTable ?TaskTable.destroy() : ''
                TaskTable = getTasksTable('#taskTable', filterInput.value, filterInput.id)
            }
            if (filterInput.id == 'myTasks'){
                TaskTable ?TaskTable.destroy() : ''
                TaskTable = getTasksTable('#taskTable', filterInput.value, filterInput.id)
            }
        })
})
})