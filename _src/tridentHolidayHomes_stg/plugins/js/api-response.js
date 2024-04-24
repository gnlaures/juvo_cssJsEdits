document.getElementById('ChildrenNum').addEventListener('change', function () {
    var selectedValue = this.value;
    for (var i = 1; i <= 6; i++) {
        var childAgeDiv = document.querySelector('.child' + i);
        if (i <= selectedValue) {
            childAgeDiv.style.display = 'block';
        } else {
            childAgeDiv.style.display = 'none';
        }
    }
});
function submitForm() {
    // Get the form and other data
    var form = document.getElementById("formReservaPropiedad");
    var accommodationId = document.getElementById("idPropiedad").value;
    var language = document.getElementById("Idioma").value;

    // Construct the action URL using the fetched data
    var actionURL = getDescriptionFeedsURL(accommodationId, language);
    
    // Update the form's action attribute
    form.action = actionURL;

    // Submit the form
    form.submit();
}

jQuery(document).ready(function($) {
    $('input[name="daterange"]').daterangepicker({
        "autoApply": true,
        "locale": {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Apply",
            "cancelLabel": "Cancel",
            "fromLabel": "From",
            "toLabel": "To",
            "customRangeLabel": "Custom",
            "weekLabel": "W",
            "daysOfWeek": [
                "Mo",
                "Tu",
                "We",
                "Th",
                "Fr",
                "Sa",
                "Su"
            ],
            "monthNames": [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December"
            ],
        },
        "linkedCalendars": false,
        "showCustomRangeLabel": false,
        "startDate": "<?php echo $getFirstDate; ?>",
        "endDate": "<?php echo $getLastDate; ?>",
        opens: 'left',
        isInvalidDate: function(date) {
            // Check if the date is within any of the available ranges
            for (var i = 0; i < availableRanges.length; i++) {
                var start = moment(availableRanges[i].start);
                var end = moment(availableRanges[i].end);
                if (date.isBetween(start, end, null, '[]')) {
                    // Find the calendar cell for the start and end dates
                    var calendarCells = $('input[name="daterange"]').data('daterangepicker').container.find('.calendar tbody td');
                    // Get the month and year of the selected date
                    var selectedMonth = date.month();
                    var selectedYear = date.year();
                    calendarCells.each(function() {
                        var cellDate = moment($(this).attr('data-date'));
                        var cellMonth = cellDate.month();
                        var cellYear = cellDate.year();

                        // Check if the cell's date is within the selected month and year
                        if (cellDate.isSame(start, 'day') && selectedMonth === cellMonth && selectedYear === cellYear) {
                            $(this).addClass('start-available');
                        }
                        if (cellDate.isSame(end, 'day') && selectedMonth === cellMonth && selectedYear === cellYear) {
                            $(this).addClass('end-available');
                        }
                        // Hide days not part of the current month
                        if (cellMonth !== selectedMonth || cellYear !== selectedYear) {
                            $(this).addClass('hiddendates');
                        } else {
                            $(this).removeClass('hiddendates');
                        }
                    });
                    return false;
                }
            }
            // Return true to disable unavailable dates
            return true;
        },
        isOutsideRange: function(date) {
            var currentMonth = moment().month();
            var selectedMonth = date.month();
            return selectedMonth !== currentMonth;
        },
    }, function(start, end, label) {
        $('#dateFrom').val(start.format('YYYY-MM-DD'));
        $('#dateTo').val(end.format('YYYY-MM-DD'));
    });
});