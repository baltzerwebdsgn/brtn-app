// Removed the input's error border upon changing field info
document.querySelectorAll('.input-error').forEach(function (field){
    field.addEventListener('input', function () {
        field.classList.remove('input-error');
    });
});

// Displays corresponding section based off the frequency chosen
var daySection = document.getElementById('day-of-week-section');
var weekSection = document.getElementById('week-of-month-section');

if (daySection && weekSection) {
    function updateFrequencyVisibility() {
        var checkedFrequency = document.querySelector('input[name="frequency"]:checked');
        var value = checkedFrequency ? checkedFrequency.value : null;

        daySection.style.display = (value === 'weekly') ? 'block' : 'none';
        weekSection.style.display = (value === 'monthly') ? 'block' : 'none';
    }

    document.querySelectorAll('input[name="frequency"]').forEach(function (radio) {
        radio.addEventListener('change', updateFrequencyVisibility);
    });

    updateFrequencyVisibility();
}
