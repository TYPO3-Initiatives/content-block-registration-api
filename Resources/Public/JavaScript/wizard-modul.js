function nextStep(numberNext) {
    document.querySelectorAll('.step-' + numberNext)[0].classList.add('open');
}
function setupField(fieldType){
    document.querySelectorAll('.step-4')[0].classList.add(fieldType);
}