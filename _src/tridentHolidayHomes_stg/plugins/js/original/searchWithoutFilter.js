jQuery(document).ready(function($) {
    $('.show-filter-popup').click(function() {
        $('.searchpopform').removeClass('filterDisplayNone');
    });
    $('.searchpopform .dialog-close-button').click(function() {
        $('.searchpopform').addClass('filterDisplayNone');
    });
    $('body').append($('.searchpopform'));
    var rangev = document.querySelector(".range-slider .progress");
    $('.searchpopform input').each(function(index, data) {
        if ($(this).is(':radio') || $(this).is(':checkbox')) {
            if (localStorage.getItem($(this).attr('name')+index) == $(this).val()) {
                $(this).prop("checked", true);
            }
        } else {
            if (localStorage.getItem($(this).attr('name')+index) != null) {
                $(this).val(localStorage.getItem($(this).attr('name')+index));
                if ($(this).attr('type') == 'range') {
                    if ($(this).attr('name') == 'thh-max-price') {
                        $('.input-max').html('&euro;'+$(this).val());
                        rangev.style.right = 100 - ($(this).val() / $(this).attr('max')) * 100 + "%";
                    } else if ($(this).attr('name') == 'thh-min-price') {
                        $('.input-min').html('&euro;'+$(this).val());
                        rangev.style.left = $(this).val() / $(this).attr('max') * 100 + "%";
                    } else {
                        $(this).next('output').html('max. '+$(this).val()+' km');
                    }
                }
            }
        }
    });
    document.getElementById("result-airport").innerHTML = 'max. '+document.getElementById("airport").value+' km';
    document.getElementById("airport").oninput = function() {
        document.getElementById("result-airport").innerHTML = 'max. '+this.value+' km';
    }
    document.getElementById("result-seaside").innerHTML = 'max. '+document.getElementById("seaside").value+' km';
    document.getElementById("seaside").oninput = function() {
        document.getElementById("result-seaside").innerHTML = 'max. '+this.value+' km';
    }
    document.getElementById("result-busstation").innerHTML = 'max. '+document.getElementById("busstation").value+' km';
    document.getElementById("busstation").oninput = function() {
        document.getElementById("result-busstation").innerHTML = 'max. '+this.value+' km';
    }
    document.getElementById("result-town").innerHTML = 'max. '+document.getElementById("town").value+' km';
    document.getElementById("town").oninput = function() {
        document.getElementById("result-town").innerHTML = 'max. '+this.value+' km';
    }
    document.getElementById("result-golf").innerHTML = 'max. '+document.getElementById("golf").value+' km';
    document.getElementById("golf").oninput = function() {
        document.getElementById("result-golf").innerHTML = 'max. '+this.value+' km';
    }
    document.getElementById("result-market").innerHTML = 'max. '+document.getElementById("supermarket").value+' km';
    document.getElementById("supermarket").oninput = function() {
        document.getElementById("result-market").innerHTML = 'max. '+this.value+' km';
    }
    document.getElementById("result-trainstation").innerHTML = 'max. '+document.getElementById("trainstation").value+' km';
    document.getElementById("trainstation").oninput = function() {
        document.getElementById("result-trainstation").innerHTML = 'max. '+this.value+' km';
    }
    const rangeInput = document.querySelectorAll(".range-input input");
    const priceInput = document.querySelectorAll(".price-input output");
    const range = document.querySelector(".range-slider .progress");
    const priceGap = 50;
    rangeInput.forEach((input) => {
        input.addEventListener("input", (e) => {
            let minVal = parseInt(rangeInput[0].value);
            let maxVal = parseInt(rangeInput[1].value);
            if (maxVal - minVal < priceGap) {
                if (e.target.className === "min-price") {
                    rangeInput[0].value = maxVal - priceGap;
                } else {
                    rangeInput[1].value = minVal + priceGap;
                }
            } else {
                priceInput[0].innerHTML = '&euro;'+minVal;
                priceInput[1].innerHTML = '&euro;'+maxVal;
                range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
                range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
            }
        });
    });
    document.getElementById("selectfilters").onclick = function(e) {
        e.preventDefault();
        $('.searchpopform input').each(function(index, data) {
            if ($(this).is(':radio') || $(this).is(':checkbox')) {
                if ($(this).is(':checked')) {
                    localStorage.setItem($(this).attr('name')+index, $(this).val());
                } else {
                    localStorage.setItem($(this).attr('name')+index, '');
                }
            } else {
                localStorage.setItem($(this).attr('name')+index, $(this).val());
            }
        });
        $('.dialog-close-button').trigger('click');
        $('#filter-submit-button').trigger('click');
    }
});
function clearfilter() {
    localStorage.clear();
    var formDiv = document.querySelector('.search_filter');
    var progressElements = document.querySelectorAll('.progress');
    var outputMin = document.querySelector('#result-min');
    var outputMax = document.querySelector('#result-max');
    var outputAirport = document.querySelector('#result-airport');
    var outputSea = document.querySelector('#result-seaside');
    var outputBus = document.querySelector('#result-busstation');
    var outputTown = document.querySelector('#result-town');
    var outputGolf = document.querySelector('#result-golf');
    var outputMarket = document.querySelector('#result-market');
    var outputTrain = document.querySelector('#result-trainstation');
    var inputs = formDiv.querySelectorAll('input:not(#max-price)');
    var inputsMinMax = formDiv.querySelectorAll('input#max-price');
    var textareas = formDiv.querySelectorAll('textarea');
    var selects = formDiv.querySelectorAll('select');
    inputs.forEach(function(input) {
        if (input.type === 'text' || input.type === 'number' || input.type === 'email') {
            input.value = '';
        } else if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false;
        } else if (input.type === 'range') {
            input.value = '0';
            if (outputMin) {
                outputMin.textContent = '€0';
            }
            if (outputAirport) {
                outputAirport.textContent = 'max. 0 km';
            }
            if (outputSea) {
                outputSea.textContent = 'max. 0 km';
            }
            if (outputBus) {
                outputBus.textContent = 'max. 0 km';
            }
            if (outputTown) {
                outputTown.textContent = 'max. 0 km';
            }
            if (outputGolf) {
                outputGolf.textContent = 'max. 0 km';
            }
            if (outputMarket) {
                outputMarket.textContent = 'max. 0 km';
            }
            if (outputTrain) {
                outputTrain.textContent = 'max. 0 km';
            }
        }
    });
    inputsMinMax.forEach(function(input) {
        if (input.type === 'range') {
            input.value = '5000';
            progressElements.forEach(function(elem) {
                elem.style.left = '0%';
                elem.style.right = '0%';
            });
            if (outputMax) {
                outputMax.textContent = '€5000';
            }
        } else if (input.type === 'range') {
            input.value = '0';
            if (outputMin) {
                outputMin.textContent = '€0';
            }
        }
    });
    textareas.forEach(function(textarea) {
        textarea.value = '';
    });
    selects.forEach(function(select) {
        select.selectedIndex = 0;
    });
}