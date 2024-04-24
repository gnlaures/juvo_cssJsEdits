var homesdata = {}, pageNumber = 1;
var favname = "favorites";
var favorites = [0];
const cookies = document.cookie.split(";").map(cookie => cookie.trim());
for (const cookie of cookies) {
	if (cookie.startsWith(favname + "=")) {
		favorites = JSON.parse(cookie.substring(favname.length + 1));
	}
}
jQuery(document).ready(function($) {
	favorites = favorites.slice(1, favorites.length);
	$("#homescount").html(favorites.length);
	loadSavedHomes();
});
const paginateArray =  (array, pageNumber, pageSize) => {
	const page = array.slice((pageNumber - 1) * pageSize, pageNumber * pageSize);
	return page;
};
function loadSavedHomes() {
	const pageSize = 9;
	const propids = paginateArray(favorites, pageNumber, pageSize);
	if (propids.length < 1) {
		jQuery('#loadMoreButton').hide();
		jQuery('#loading').hide();
		return false;
	}
	jQuery('#loading').show();
	jQuery('#loadMoreButton').hide();
	$.ajax({
		url: savedhomesUrl,
		contentType: "application/json",
		dataType: 'json',
		data: {display_type: 'favorites', propids: propids.join('_')},
		type: 'GET',
		success: function (result) {
			$('#loading').hide();
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
					jQuery('#loadMoreButton').show();	
				}
			}
			window.document.dispatchEvent(new Event("DOMContentLoaded", {
			  	bubbles: true,
			  	cancelable: true
			}));
		}
	});
}