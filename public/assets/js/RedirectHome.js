var element = document.querySelector('.btn')

element.addEventListener('click', function(){

    var elementTitle = document.querySelector('.btn-title')
    var spinner = document.querySelector('.spinner-border')

    elementTitle.classList.add('d-none')
    spinner.classList.remove('d-none')
    spinner.classList.add('d-block')

    window.location.href = "https://localhost:8000/", 5000
})

