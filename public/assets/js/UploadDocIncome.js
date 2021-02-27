// Upload document year
$("#DocYear").on('show.bs.modal', function (e) { 
    const yearCode = $(e.relatedTarget).data('year-code');
    // console.log(yearCode);
    const id = $(e.relatedTarget).data('id');
    // console.log(id);
    $('#formDocYear').attr('action', `/mes-revenues/${id}/${yearCode}/upload`);
    // console.log($('#formDocYear').attr('action'));
})

// Upload document Income
$("#DocIncome").on('show.bs.modal', function (e) {
    const income = $(e.relatedTarget).data('income-id');
    // console.log(income);
    const user = $(e.relatedTarget).data('user-id');
    // console.log(user);
    $('#formDocIncome').attr('action', `/mes-revenues/${user}/upload/${income}`);
    // console.log($('#formDocIncome').attr('action'));
})
