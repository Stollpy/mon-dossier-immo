var btnsIncome = document.querySelectorAll('.btns-modal-income');
console.log(btnsIncome);
for(const btnIncome of btnsIncome){
    btnIncome.addEventListener('click', function(event){
        const incomeId = btnIncome.getAttribute('data-income-id');
        const userId = btnIncome.getAttribute('data-user-id');
        document.querySelector('#formDocIncome').setAttribute('action', `/mes-revenues/${userId}/upload/${incomeId}`);
    })
}


var btnsYear = document.querySelectorAll(".btns-modal-year");
for(const btnYear of btnsYear){
    btnYear.addEventListener('click', function(e){
        const yearCode = btnYear.getAttribute('data-year-code');
        const id = btnYear.getAttribute('data-id');
        document.getElementById('formDocYear').setAttribute('action', `/mes-revenues/${id}/${yearCode}/upload`);
    })
}
