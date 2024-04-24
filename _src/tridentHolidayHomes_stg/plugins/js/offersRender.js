var homesdata = {}, propids = [], pageSize = 9, pageNumber = 1;
const paginateArray =  (array, pageNumber, pageSize) => {
	const page = array.slice((pageNumber - 1) * pageSize, pageNumber * pageSize);
	return page;
}; 
function unique(array) {
  return array.filter(function(el, index, arr) {
      return index == arr.indexOf(el);
  });
}
function loadOffersHomes() {
	var sList = [];
	var cnt = 0;
	if (jQuery('.filter-checkbox:checked').length) {
		jQuery('.filter-checkbox:checked').each(function () {
			if (cnt) {
				sList = sList.filter(value => eval(jQuery(this).data('filter')).includes(value));
			} else {
				sList = eval(jQuery(this).data('filter'));
			}
			cnt++;
		});
	} else {
		sList = javascript_array;
	}
	propids = paginateArray(sList, pageNumber, pageSize);
	if (propids.length < 1) {
		jQuery('#loadMoreButton').hide();
		if (pageNumber < 2) {
			jQuery('#prop-view').html('<p id="noPropertiesMessage">No matching records found.</p>').fadeIn();
		}
		return false;
	}
	jQuery('#loading').show();		
	jQuery('#loadMoreButton').hide();	
	jQuery('.filter-checkbox').prop('disabled', true);
	jQuery.ajax({
		url: offersUrl,
		contentType: "application/json",
		dataType: 'json',
		data: {display_type: 'offers', propids: propids.join('_')},
		type: 'GET',
		success: function (result) {
			jQuery('#loading').hide();
			const data = result;
			if (data.length > 0) {
				homesdata = data.homes;
				var cnth = 0;
				data.forEach(function(home) {
					jQuery('#prop-view').append(home.homes);
					cnth++;
				});
				pageNumber++;
				if (cnth < pageSize) {
					jQuery('#loadMoreButton').hide();
				} else {
					if (sList.length == jQuery('.property').length) {
						jQuery('#loadMoreButton').hide();
					} else {
						jQuery('#loadMoreButton').show();
					}
				}
			}
			jQuery('.filter-checkbox').prop('disabled', false);
			window.document.dispatchEvent(new Event("DOMContentLoaded", {
				bubbles: true,
				cancelable: true
			}));
		}
	});
}
function handleFilterChange() {
	pageNumber = 1;
	if (jQuery(this).is(':checked')) {
		jQuery(this).parent().addClass('active');
	} else {
		jQuery(this).parent().removeClass('active');	
	}
	jQuery('#prop-view').empty();
	loadOffersHomes();
}
jQuery(document).ready(function() {
	loadOffersHomes();
});
const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
filterCheckboxes.forEach(function (checkbox) {
	checkbox.addEventListener('change', handleFilterChange);
});