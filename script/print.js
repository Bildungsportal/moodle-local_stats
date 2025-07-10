// Prepare printing of charts

var elements = document.querySelectorAll('.chart-table-data');
elements.forEach(function(element) {
    element.style.display = 'block';
    element.setAttribute('aria-expanded', true);
});
var elements = document.querySelectorAll('.chart-table-expand');
elements.forEach(function(element) {
    element.remove();
});

