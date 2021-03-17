window.onload = function(){
    var inputAll = document.querySelectorAll('.input-form');
    for(const input of inputAll){
        input.addEventListener('focusout', function(event){
            const idReq = input.getAttribute('data-ind-id');
            const value = input.value;

            fetch('/api/individual_datas/'+idReq, {
                method: 'PATCH', 
                body: JSON.stringify({
                    data: value
                }),
                headers: {
                    'Content-type': 'application/merge-patch+json; charset=UTF-8'
                }                    
            })
            .then(function(response){
                if(response.status !== 200){
                    console.log('Erreur status ' + response.status);
                }
                response.json().then(function(data){
                    console.log(data);
                })
            })
            .catch(function(error){
                console.log(error)
            })
        })
    }
}