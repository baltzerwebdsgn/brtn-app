// Removed the input's error border upon changing field info
document.querySelectorAll('.input-error').forEach(function (field){
    field.addEventListener('input', function () {
        field.classList.remove('input-error');
    });
});