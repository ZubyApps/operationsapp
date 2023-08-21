window.addEventListener('DOMContentLoaded', function () {
    const    jobTypeBtn = document.getElementById('jobtype-btn')
    const    departmentBtn = document.getElementById('department-btn')
    const    paymethodBtn = document.getElementById('paymethod-btn')
    const    staffBtn = document.getElementById('staff-btn')


    if (jobTypeBtn) {
    jobTypeBtn.addEventListener('click', function () {
        window.location = "/settings/jobtype"
    })
    }

    if (departmentBtn) {
    departmentBtn.addEventListener('click', function () {
        window.location = "/settings/department"
    })
    }

    if (paymethodBtn) {
    paymethodBtn.addEventListener('click', function () {
        window.location = "/settings/paymethod"
    })
    }

    if (staffBtn) {
    staffBtn.addEventListener('click', function() {
        window.location = "settings/users"
    })
    }    
})
