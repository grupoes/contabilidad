const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

flatpickr(document.querySelector('#rango-fecha-movimientos'),{
    mode: "range",
    dateFormat: 'd-m-Y',
    defaultDate: getDefaultDate(),
    allowInput: true,
    /*onClose: function (selectedDates, dateStr, instance) {
        const selectedDate = instance.selectedDates[0];
        console.log("Fecha seleccionada:", selectedDate);
        renderVentas();
    }*/
});

function getDefaultDate() {
    const today = new Date();
    const startDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30);
    const endDate = today;
    return [startDate, endDate];
}