function getSCookie(name) {
	const cookies = document.cookie.split(";").map(cookie => cookie.trim());
	for (const cookie of cookies) {
		if (cookie.startsWith(name + "=")) {
			return JSON.parse(cookie.substring(name.length + 1));
		}
	}
	return [0];
}
function setCookieS(name, value, days) {
	const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
	const cookieAttributes = `expires=${expires}; path=/; SameSite=None; Secure`;
	document.cookie = `${name}=${value}; ${cookieAttributes}`;
}
jQuery(document).ready(function($) {
	var mfav = getSCookie("favorites");
	if (mfav.includes(accommodationIdToAdd)) {
		$(".favouritesPropS i").removeClass("far");
		$(".favouritesPropS i").addClass("fas");
	} else {
		$(".favouritesPropS i").removeClass("fas");
		$(".favouritesPropS i").addClass("far");
	}
	$(".favouritesPropS").click(function() {
		var findex = mfav.indexOf(accommodationIdToAdd);
		if (findex > -1) {
			mfav.splice(findex, 1);
		} else {
			mfav.push(accommodationIdToAdd);
		}
		setCookieS("favorites", JSON.stringify(mfav), 7);
		if ($(this).find("i").hasClass("far")) {
			$(".favouritesPropS i").removeClass("far fa-heart");
			$(".favouritesPropS i").addClass("fas fa-heart");
		} else {
			$(".favouritesPropS i").removeClass("fas fa-heart");
			$(".favouritesPropS i").addClass("far fa-heart");
		}
	});
	$('#mobilebookingpop').html($('.mobilebookingpop').html());
	if($('.mobilebookingpop').html() == 'Book'){
		$('#mobilebookingpop').addClass('button-book-search button-book')
	}
	$('.priceFrom h2').html($('.aprice').html());
	$('.priceFrom h2').nextAll('p').last().remove();
	$('.priceFrom').next().append($('.checkinDate-box-outer').html());
	$("#mobilebookingpop").on("click", function(ep) {
		ep.preventDefault();
		$("html, body").animate({
			scrollTop: $("#container-content-slider").offset().top - 80
		}, 1500);                    
		$(".sticky-sidebar-container").addClass("reservation_popup");
		$("#formReserveAccommodation").css("display", "block");
		$(".sticky-sidebar-container").append("<div class='overlayBg'></div>");
		$("body").addClass("bodyFixed");
	});
	$(document).on("click", ".mobilebookingpop", function(ep) {
		ep.preventDefault();
		$("html, body").animate({
			scrollTop: $("#container-content-slider").offset().top - 80
		}, 1500);                    
		$(".sticky-sidebar-container").addClass("reservation_popup");
		$("#formReserveAccommodation").css("display", "block");
		$(".sticky-sidebar-container").append("<div class='overlayBg'></div>");
		$("body").addClass("bodyFixed");
	});
	$("a.closerpop").on("click", function(e) {
		e.preventDefault();
		$(".sticky-sidebar-container").removeClass("reservation_popup");
		$("#formReserveAccommodation").hide();
		$(".overlayBg").remove();
		$("body").removeClass("bodyFixed");
	});
	moment.updateLocale('en', {
	  week: { dow: 1 }
	});
	$(document).on("click", "#reserve-edit-dates", function(ep) {
		ep.preventDefault();
		$('input[name="daterange"]').click();
	});
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
				"SUN",
				"MON",
				"TUE",
				"WED",
				"THU",
				"FRI",
				"SAT"
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
		"startDate": accommodationStartDate,
		"endDate": accommodationEndDate,
		"minDate": accommodationMinDate,
		"maxDate": moment().add(24, 'months'),
		opens: 'left',
		isInvalidDate: function(date) {
			for (var i = 0; i < availableRanges.length; i++) {
				var start = moment(availableRanges[i].start);
				var end = moment(availableRanges[i].end);
				if (date.isBetween(start, end, null, '[]')) {
					var calendarCells = $('input[name="daterange"]').data('daterangepicker').container.find('.calendar tbody td');
					var selectedMonth = date.month();
					var selectedYear = date.year();
					calendarCells.each(function() {
						var cellDate = moment($(this).attr('data-date'));
						var cellMonth = cellDate.month();
						var cellYear = cellDate.year();
						if (cellDate.isSame(start, 'day') && selectedMonth === cellMonth && selectedYear === cellYear) {
							$(this).addClass('start-available');
						}
						if (cellDate.isSame(end, 'day') && selectedMonth === cellMonth && selectedYear === cellYear) {
							$(this).addClass('end-available');
						}
						if (cellMonth !== selectedMonth || cellYear !== selectedYear) {
							$(this).addClass('hiddendates');
						} else {
							$(this).removeClass('hiddendates');
						}
					});
					return false;
				}
			}
			return true;
		},
		isOutsideRange: function(date) {
			return date < moment() || date > moment().add(24, 'months');
		},
	}, function(start, end, label) {
		$('#dateFrom').val(start.format('YYYY-MM-DD'));
		$('#dateTo').val(end.format('YYYY-MM-DD'));
		$('#bt_act').show();
		$('#bt_act').find('button').each(function() {
			$(this).remove();
		});
		$('#bt_act').find('a').each(function() {
			$(this).remove();
		});
		$('#reserve-contact-us').hide();
		$('.right-sidebar .sidebar-priceinfobox').remove();
		if ($('#reserve-submit-button').length === 0) {
			$('#bt_act').append('<button class="button-book-search" type="input" name="reserve-submit-button" id="reserve-submit-button" aria-label="Check Availability">Check Availability</button>');
		}
		$('#reserve-submit-button').click();
		addLeadingZero();
		hidePrevButtonIfCurrentMonth();
		checkRowsAndHide();
		updateHtmlCalendar();
	});
	function addLeadingZero() {
		var dayNumbers = $('.calendar-table td');
		dayNumbers.each(function() {
			var dayNumber = $(this).text().trim();
			if (dayNumber.length === 1) {
				$(this).text('0' + dayNumber);
			}
		});
	}
	function hidePrevButtonIfCurrentMonth() {
		var currentMonthYear = moment().format('MMMM YYYY');
		var displayedMonthYear = $('.daterangepicker .left .calendar-table th.month').text().trim();
		if (currentMonthYear === displayedMonthYear) {
			$('.prev').addClass('unclickable');
			$('.prev span').addClass('hidden-calendar');
		} else {
			$('.prev').removeClass('unclickable');
			$('.prev span').removeClass('hidden-calendar');
		}
	}
	function setupMutationObserver() {
		var targetNode = $('.daterangepicker')[0];
		var config = { attributes: false, childList: true, subtree: true };
		var callback = function(mutationsList, observer) {
			for (var mutation of mutationsList) {
				if (mutation.type === 'childList') {
					addLeadingZero();
					hidePrevButtonIfCurrentMonth();
					checkRowsAndHide();
				}
			}
		};
		var observer = new MutationObserver(callback);
		if (targetNode) {
			observer.observe(targetNode, config);
		}
	}
	setupMutationObserver();
	function checkRowsAndHide() {
		$('.calendar-table tbody tr').each(function() {
			var allOff = true;
			$(this).find('td').each(function() {
				if (!$(this).hasClass('off') || !$(this).hasClass('ends')) {
					allOff = false;
					return false;
				}
			});
			if (allOff) {
				$(this).addClass('displaynone');
			}
		});
	}
	$('input[name="daterange"]').on('show.daterangepicker', function() {
		if (!$('.daterangepicker').data('wrapped')) {
			$('.daterangepicker').prepend('<p class="availability-cont"><span class="available-dot"></span> Available <span class="notavailable-dot"></span> Unavailable <span class="availablesel-dot"></span> Selected</p>');
			$('.daterangepicker').children().not('.availability-cont').wrapAll('<div class="inner-daterangepicker"></div>');
			$('.daterangepicker').data('wrapped', true);
		}
		addLeadingZero();
		hidePrevButtonIfCurrentMonth();
		checkRowsAndHide();
	});
	let isStartDateSelected = false;
	function updateDateRangePicker(startDate, endDate) {
	    const start = new Date(startDate);
	    const end = new Date(endDate);
	    function formatDate(date) {
	        if (!isNaN(date.getTime())) {
	            const day = ("0" + date.getDate()).slice(-2);
	            const month = ("0" + (date.getMonth() + 1)).slice(-2);
	            const year = date.getFullYear();
	            return `${day}/${month}/${year}`;
	        } else {
	            return null;
	        }
	    }
	    const formattedStartDate = formatDate(start);
	    const formattedEndDate = formatDate(end);
	    if (formattedStartDate) {
	        $('input[name="daterange"]').data('daterangepicker').setStartDate(formattedStartDate);
	    }
	    if (formattedEndDate) {
	        $('input[name="daterange"]').data('daterangepicker').setEndDate(formattedEndDate);
	    }
	}
	function updateHtmlCalendar() {
		const startDate = new Date($('#dateFrom').val());
		const endDate = new Date($('#dateTo').val());
		$('.calendar-date-number.calendar-date-number-available').each(function() {
			const dayDate = new Date($(this).data('date'));
			$(this).removeClass('selected-cal-range');
			if (dayDate >= startDate && dayDate <= endDate) {
				$(this).addClass('selected-cal-range');
			}
		});
	}
	$('.calendar-date-number.calendar-date-number-available').on('click', function() {
		const selectedDate = $(this).data('date');
		if (!isStartDateSelected) {
            $('#dateFrom').val(selectedDate);
            $('#dateTo').val(selectedDate);
            $(this).addClass('selected-cal-range');
            isStartDateSelected = true;
            updateDateRangePicker(selectedDate, selectedDate);
            updateHtmlCalendar();
        } else {
            isStartDateSelected = false;
            if (selectedDate < $('#dateFrom').val()) {
            	$('#dateTo').val($('#dateFrom').val());
            	updateDateRangePicker($('#dateFrom').val(), $('#dateFrom').val());
            } else {
            	$('#dateTo').val(selectedDate);
            	updateDateRangePicker($('#dateFrom').val(), selectedDate);
            }
	        updateHtmlCalendar();
	        updateSidebarInformation();
	        const isMobile = window.matchMedia("only screen and (max-width: 1199px)").matches;
	        if (isMobile) {
	        	performMobileBookingPopActions();
	        }
        }
	});
	function performMobileBookingPopActions() {
	    $("html, body").animate({
	        scrollTop: $("#container-content-slider").offset().top - 80
	    }, 1500);
	    $(".sticky-sidebar-container").addClass("reservation_popup");
	    $("#formReserveAccommodation").css("display", "block");
	    $(".sticky-sidebar-container").append("<div class='overlayBg'></div>");
	    $("body").addClass("bodyFixed");
	}
    function updateSidebarInformation() {
	    $('#formReserveAccommodation').addClass('blur-out');
	    $('#sidebar-ajax-loader').show();
	    let formData = $('form[name="formReserveAccommodation"]').serializeArray();
	    formData.push({ name: "action", value: "reserve" });
	    formData.push({ name: "nonce", value: propScriptData.nonce });
	    $.ajax({
	        type: 'POST',
	        url: propScriptData.ajaxurl,
	        data: formData,
	        success: function(response) {
	            $('#formReserveAccommodation').removeClass('blur-out');
	            $('#sidebar-ajax-loader').hide();
	            $('#errormessage').empty();
	            if (response.success) {
	                updateSidebarContent(response.data, true);
	                updateContactURL();
	            } else {
	                updateSidebarContent(response.data, false);
	            }
	        },
	        error: function(xhr, status, error) {
	            $('#formReserveAccommodation').removeClass('blur-out');
	            $('#sidebar-ajax-loader').hide();
	        }
	    });
	}
	function updateSidebarContent(data, isSuccess) {
		console.log('in update side bar');
	    if (isSuccess) {
	    	if (data.success_desktop && data.success_mobile) {
                $('.right-sidebar .response').html(data.success_desktop);
                $('.right-sidebar-mob .response').html(data.success_mobile);
            } else {
                $('.response').html(data);
            }
            $('.priceFrom p').remove();
            $('.priceFrom h2').html($('.aprice').html());
			$('#mobilebookingpop').html($('.mobilebookingpop').html());
			if($('.mobilebookingpop').html() == 'Book'){
					$('#mobilebookingpop').addClass('button-book-search button-book')
			}
            $('.priceFrom h2').nextAll('p').last().remove();
			$('.priceFrom').next().append($('.checkinDate-box-outer').html());
			$('#bt_act').hide();
			if ($('.right-sidebar .response .sidebar-priceinfobox').length) {
				$('.right-sidebar .sidebar-priceinfobox').not('.right-sidebar .response .sidebar-priceinfobox').remove();
				$('.right-sidebar .sidebar-priceinfobox').insertAfter('#bt_act');
				$('.right-sidebar .response .sidebar-priceinfobox').empty();
			}
			const formattedEntrada = formatDate($('#dateFrom').val());
		    const formattedSalida = formatDate($('#dateTo').val());
		    updateBookingButtonParameters(formattedEntrada, formattedSalida);
	    } else {
	        if (data.error_message && data.price_sidebar && data.mobile_sidebar) {
                $('.right-sidebar .response').html(data.price_sidebar);
                $('.right-sidebar-mob .response').html(data.mobile_sidebar);
                $('#errormessage').html(data.error_message);
                $('#bt_act').find('button').each(function() {
                    $(this).remove();
                });
                $('#bt_act').find('a').each(function() {
                    $(this).remove();
                });
                $('#bt_act').append(data.button_side_bar);
            } else {
                $('#errormessage').html(data);
            }
            $('.priceFrom h2').html($('.aprice').html());
            $('.priceFrom h2').nextAll('p').last().remove();
			$('#mobilebookingpop').html($('.mobilebookingpop').html());
			if($('.mobilebookingpop').html() == 'Book'){
					$('#mobilebookingpop').addClass('button-book-search button-book')
			}
			$('.priceFrom').next().append($('.checkinDate-box-outer').html());
            $('.right-sidebar .sidebar-priceinfobox').remove();
            $('#bt_act').show();
	    }
	}
    function updateContactURL() {
        var currentContactHref = $('.contactParameters').attr('href');
        var baseContactUrl = currentContactHref.includes('?') ? currentContactHref.split('?')[0] : currentContactHref;
        var dateFromC = $('#dateFrom').val();
        var dateToC = $('#dateTo').val();
        var adultsNumberC = $('#AdultNum').val();
        var childrenNumberC = $('#ChildrenNum').val();
        dateFromC = formatDate(dateFromC);
        dateToC = formatDate(dateToC);
        var contact_url_queries = '?FRMEntrada=' + dateFromC + '&FRMSalida=' + dateToC + '&FRMAdultos=' + parseInt(adultsNumberC);
        if (childrenNumberC) {
            contact_url_queries += '&FRMNinyos=' + parseInt(childrenNumberC);
            var childAges = [];
            for (var i = 1; i <= childrenNumberC; i++) {
                var childAge = $('#Child_' + i + '_Age').val();
                if (childAge) {
                    childAges.push(parseInt(childAge));
                }
            }
            if (childAges.length > 0) {
                contact_url_queries += '&EdadesNinyos=' + childAges.join(';');
            }
        }
        $('.contactParameters').attr('href', baseContactUrl + contact_url_queries);
    }
    function updateBookingButtonParameters(entrada, salida) {
	    const bookingButton = $('.sidebar-priceinfobox .bookingb a.button-book');
	    let href = bookingButton.attr('href');
	    let url = new URL(href, window.location.origin);
	    url.searchParams.set('FRMEntrada', entrada);
	    url.searchParams.set('FRMSalida', salida);
	    bookingButton.attr('href', url.href);
	}
    function formatDate(date) {
        var d = new Date(date),
            day = '' + d.getDate(),
            month = '' + (d.getMonth() + 1),
            year = d.getFullYear();
        if (day.length < 2) {
            day = '0' + day;
        }
        if (month.length < 2) {
            month = '0' + month;
        }
        return [day, month, year].join('/');
    }
	$('#dateFrom, #dateTo').on('change', function() {
	    updateDateRangePicker($('#dateFrom').val(), $('#dateTo').val());
	    updateHtmlCalendar();
	});
	updateHtmlCalendar();
});
function addToRecentlyViewed(propertyId, propertyName, propertyUrl) {
	let viewedProperties = JSON.parse(localStorage.getItem('recentlyViewedProperties')) || [];
	let property = { id: propertyId, name: propertyName, url: propertyUrl };
	viewedProperties = viewedProperties.filter(p => p.id !== propertyId);
	viewedProperties.unshift(property);
	const maxProperties = 5;
	viewedProperties = viewedProperties.slice(0, maxProperties);
	localStorage.setItem('recentlyViewedProperties', JSON.stringify(viewedProperties));
}
addToRecentlyViewed(accommodationIdToAdd, accommodationNameToAdd, accommodationURLToAdd);
