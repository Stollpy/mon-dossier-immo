import Chart from 'chart.js';

var id = document.querySelector('#myChartIdentity').getAttribute("data-id"); 

fetch(`/api/individual_datas?individual=${id}`)
    .then(function(responce){
        if(responce.status !== 200){
            return 'ERROR status: '+responce.status;
        }
        responce.json().then(function(datas){
            document.querySelector('#spinner-dom').classList.add('d-none');
            var data = datas['hydra:member']
            var countDom = 0;
            for(var i = 0; i < data.length; i++){
                if(data[i]['profilModelData']['individualDataCategory']['code'] == 'domiciliation'){
                    countDom++;
                }
               
            }

            var finish = '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check-circle" class="svg-inline--fa fa-check-circle fa-w-16 w-25" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#2AF25C" d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"></path></svg>';

            var canvasDomiciliation = document.querySelector('#myChartDomiciliation');

            var domiciliationLength = 5 - countDom;


            if(domiciliationLength !== 0){
                new Chart(canvasDomiciliation, {
                    type: 'doughnut',
                    data: {
                        labels: ['Accomplit', 'Non accomplie'],
                        datasets: [
                            {
                                label: "Accomplissement",
                                data: [ countDom, domiciliationLength],
                                backgroundColor: ['#40CAFF', '#C1C1C1']
                            }
                        ]
                    },
                    options: {
                        responsive: false
                    }
                })
            }
            else{
                canvasDomiciliation.className += 'd-none';
                document.getElementById('colDomiciliation').innerHTML = finish; 
            }
            
        })
    })
    .catch(function(err){
        return 'Err fetch' + err.message;
    });
