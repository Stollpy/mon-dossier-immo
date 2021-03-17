const URL_DIRECTORY_UPLOAD = '/assets/images/upload/'
var display = document.getElementById('Display');
var id = display.getAttribute('data-id');

fetch('/api/ads/'+id)
.then(function(response){
    if(response.status !== 200){
        display.innerHTML += '<div class="text-center">'
            +'<h2>4😭4 Aucune image</h2>'
        +'</div>'
    }else{
        response.json().then(function(data){
            
            for(var i = 0; i < data.adsPictures.length; i++){
                display.innerHTML += '<div class="col-sm-12 col-md-6 col-lg-3 d-flex justify-content-end mt-3" id="Col'+ data.adsPictures[i]['id'] +'">'
                    +'<button class="btn btn-outline-danger mx-1 my-1 buttonDelete" id="Delete'+ data.adsPictures[i]['id'] +'" data-id="'+ data.adsPictures[i]['id'] +'"><i class="far fa-times-circle"></i></button>'
                    +'<img src="'+ URL_DIRECTORY_UPLOAD + data.adsPictures[i]['data'] +'" class="w-100 shadow" alt="'+ data.title +'">'
                +'</div>'
            }

            var btnDelete = document.querySelectorAll('.buttonDelete');
            for(const btn of btnDelete){
                    
                    btn.addEventListener('click', function(e){

                    var idPictures = btn.getAttribute('data-id');

                    fetch('/api/ads_pictures/'+idPictures, { method: 'DELETE' })
                    .then(function(res){
                        if(res.status !== 204){
                            console.log('Error status: '+ res.status);
                        }else{
                            document.getElementById('Col'+idPictures).remove();
                        }
                    })
                    .catch(function(err){
                        console.log('Error fetch', err)
                    })
                
                })
            }
        })
    }
})