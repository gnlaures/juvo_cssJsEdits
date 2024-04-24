function goBack() {
    window.history.back();
}
function readMoreTextExpand(tipName, tipNum) {
  if (tipName == "response") {
    document.querySelector('#text_' + tipName + 'Flow_' + tipNum).style.display = 'table-cell';
    document.querySelector('.text_' + tipName + '_' + tipNum).style.display = 'none';
  } else {
    document.querySelector('#text_' + tipName + 'Flow_'+ tipNum).style.display = 'block';
    document.querySelector('.text_' + tipName + '_' + tipNum).style.display = 'none';
  }
}
jQuery(function ($) {
    $('#hfe-frontend-js-js').remove();
    $('#owce-custom-js').remove();
    $('#owce-custom-css').remove();
    var observerOWL = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                $('button.owl-prev').attr({'title': 'Previous Slide', 'aria-label': 'Previous Slide'});
                $('button.owl-next').attr({'title': 'Next Slide', 'aria-label': 'Next Slide'});
                $('button.owl-dot').attr({'title': 'Next Slide', 'aria-label': 'Next Slide'});
            }
        });
    });
    observerOWL.observe(document.body, { childList: true, subtree: true });
    var observerTab = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes') {
                $('.elementor-tab-title.elementor-active').removeAttr('aria-selected');
                $('.elementor-tab-title').removeAttr('aria-selected');
            }
            if (mutation.addedNodes.length) {
                $('.elementor-tab-title.elementor-active').removeAttr('aria-selected');
                $('.elementor-tab-title').removeAttr('aria-selected');
            }
        });
    });
    observerTab.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['aria-selected'] });
    const slideCalenderDivs = document.querySelectorAll('.swiper-slide-calender');
    slideCalenderDivs.forEach(div => {
        div.classList.add('swiper-slide');
    });
    const swiperCalendarAvailabilityProperty = new Swiper('.availabilityCalenderSwiper', {
      loop: false,
      slidesPerView: 'auto',
      spaceBetween: 0,
      navigation: {
        nextEl: '.availabilityCalenderSwiper .swiper-button-next',
        prevEl: '.availabilityCalenderSwiper .swiper-button-prev',
      },
    });
    const slideAttractionsDivs = document.querySelectorAll('.swiper-slide-attractions');
    slideAttractionsDivs.forEach(div => {
      div.classList.add('swiper-slide');
    });
    const swiperAttractions = new Swiper('.attractionsSwiper', {
      loop: false,
      slidesPerView: 'auto',
      spaceBetween: 0,
      navigation: {
        nextEl: '.attractionsSwiper .swiper-button-next',
        prevEl: '.attractionsSwiper .swiper-button-prev',
      },
      breakpoints:{
        0:{
            slidesPerView: 2,
            spaceBetween: 20
        },
        550:{
            slidesPerView: 'auto',
            spaceBetween: 0
        }
      },
    });
    const slideSimilarHomesDivs = document.querySelectorAll('.swiper-slide-similarhomes');
    slideSimilarHomesDivs.forEach(div => {
        div.classList.add('swiper-slide');
    });
    const swiperSimilarHomesProperty = new Swiper('.similarHomesSwiper', {
        loop: false,
        slidesPerView: 'auto',
        spaceBetween: 0,
        navigation: {
            nextEl: '.similarHomesSwiper .swiper-button-next',
            prevEl: '.similarHomesSwiper .swiper-button-prev',
        },
    });
    document.addEventListener("DOMContentLoaded", function () {
        function initializeOrUpdateSwiper() {
            const slideSimilarHomesDivs = document.querySelectorAll('.swiper-slide-similarhomes');
            if (slideSimilarHomesDivs.length > 0) {
                slideSimilarHomesDivs.forEach(div => {
                    if (!div.classList.contains('swiper-slide')) {
                        div.classList.add('swiper-slide');
                    }
                });
                if (!swiperSimilarHomesProperty) {
                    swiperSimilarHomesProperty = new Swiper('.similarHomesSwiper', {
                        loop: false,
                        slidesPerView: 'auto',
                        spaceBetween: 0,
                        navigation: {
                            nextEl: '.similarHomesSwiper .swiper-button-next',
                            prevEl: '.similarHomesSwiper .swiper-button-prev',
                        },
                    });
                } else {
                    swiperSimilarHomesProperty.update();
                }
            }
        }
        let swiperSimilarHomesProperty;
        initializeOrUpdateSwiper();
        const observer = new MutationObserver(() => {
            initializeOrUpdateSwiper();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
    const slideFeaturedRecentlyDivs = document.querySelectorAll('.swiper-slide-featured-recentlyviewed');
    slideFeaturedRecentlyDivs.forEach(div => {
        div.classList.add('swiper-slide');
    });
    const swiperFeaturedRecentlyProperty = new Swiper('.featured-container .featuredRecentlyViewedSwiper', {
        loop: false,
        slidesPerView: 'auto',
        spaceBetween: 0,
        navigation: {
            nextEl: '.featured-container .swiper-button-next',
            prevEl: '.featured-container .swiper-button-prev',
        },
        pagination: {
            el: '.featured-container .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            320: {
                navigation: false,
                pagination: {
                    el: '.featured-container .swiper-pagination',
                    clickable: true,
                },
            },
            768: {
                navigation: {
                    nextEl: '.featured-container .swiper-button-next',
                    prevEl: '.featured-container .swiper-button-prev',
                },
                pagination: false, 
            },
        }
    });
    document.addEventListener("DOMContentLoaded", function () {
        function initializeFeaturedOrUpdateFeaturedSwiper() {
            const slideFeaturedRecentlyDivs = document.querySelectorAll('.swiper-slide-featured-recentlyviewed');
            if (slideFeaturedRecentlyDivs.length > 0) {
                slideFeaturedRecentlyDivs.forEach(div => {
                    if (!div.classList.contains('swiper-slide')) {
                        div.classList.add('swiper-slide');
                    }
                });
                if (!swiperFeaturedRecentlyProperty) {
                    swiperFeaturedRecentlyProperty = new Swiper('.featured-container .featuredRecentlyViewedSwiper', {
                        loop: false,
                        slidesPerView: 'auto',
                        spaceBetween: 0,
                        navigation: {
                            nextEl: '.featured-container .swiper-button-next',
                            prevEl: '.featured-container .swiper-button-prev',
                        },
                        pagination: {
                            el: '.featured-container .swiper-pagination',
                            clickable: true,
                        },
                        breakpoints: {
                            320: {
                                navigation: false,
                                pagination: {
                                    el: '.featured-container .swiper-pagination',
                                    clickable: true,
                                },
                            },
                            768: {
                                navigation: {
                                    nextEl: '.featured-container .swiper-button-next',
                                    prevEl: '.featured-container .swiper-button-prev',
                                },
                                pagination: false, 
                            },
                        }
                    });
                } else {
                    swiperFeaturedRecentlyProperty.update();
                }
            }
        }
        initializeFeaturedOrUpdateFeaturedSwiper();
        const observerF = new MutationObserver((mutations, obs) => {
            initializeFeaturedOrUpdateFeaturedSwiper();
        });
        observerF.observe(document.body, { childList: true, subtree: true });
    });
    document.addEventListener("DOMContentLoaded", function () {
        const slideRecentlyVDivs = document.querySelectorAll('.swiper-slide-recentlyviewed');
        slideRecentlyVDivs.forEach(div => {
            div.classList.add('swiper-slide');
        });
        const swiperRecentlyVProperty = new Swiper('.recentlyViewedSwiper', {
            loop: false,
            slidesPerView: 'auto',
            spaceBetween: 0,
            navigation: {
                nextEl: '.recentlyviewed-container .swiper-button-next',
                prevEl: '.recentlyviewed-container .swiper-button-prev',
            },
            pagination: {
                el: '.recentlyviewed-container .swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                320: {
                    navigation: false,
                    pagination: {
                        el: '.recentlyviewed-container .swiper-pagination',
                        clickable: true,
                    },
                },
                768: {
                    navigation: {
                        nextEl: '.recentlyviewed-container .swiper-button-next',
                        prevEl: '.recentlyviewed-container .swiper-button-prev',
                    },
                    pagination: false, 
                },
            }
        });
        function initializeUpdateRecentlySwiper() {
            const slideRecentlyVDivs = document.querySelectorAll('.swiper-slide-recentlyviewed');
            if (slideRecentlyVDivs.length > 0) {
                slideRecentlyVDivs.forEach(div => {
                    if (!div.classList.contains('swiper-slide')) {
                        div.classList.add('swiper-slide');
                    }
                });
                if (!window.swiperRecentlyVProperty) {
                    window.swiperRecentlyVProperty = new Swiper('.recentlyViewedSwiper', {
                        loop: false,
                        slidesPerView: 'auto',
                        spaceBetween: 0,
                        navigation: {
                            nextEl: '.recentlyviewed-container .swiper-button-next',
                            prevEl: '.recentlyviewed-container .swiper-button-prev',
                        },
                        pagination: {
                            el: '.recentlyviewed-container .swiper-pagination',
                            clickable: true,
                        },
                        breakpoints: {
                            320: {
                                navigation: false,
                                pagination: {
                                    el: '.recentlyviewed-container .swiper-pagination',
                                    clickable: true,
                                },
                            },
                            768: {
                                navigation: {
                                    nextEl: '.recentlyviewed-container .swiper-button-next',
                                    prevEl: '.recentlyviewed-container .swiper-button-prev',
                                },
                                pagination: false, 
                            },
                        }
                    });
                } else {
                    window.swiperRecentlyVProperty.update();
                }
            }
        }
        initializeUpdateRecentlySwiper();
        const observerR = new MutationObserver((mutations, obs) => {
            initializeUpdateRecentlySwiper();
        });
        observerR.observe(document.body, { childList: true, subtree: true });
    });
    var topElement = $('#container-content-slider');
    var boxSection = $('.box-section.sticky-sidebar-container');
    var beginScroll = $('#photo_container');
    var navigationBar = $('#navigational-bar-prop');
    var navigationBarHeight = navigationBar.height();
    var topOffset = beginScroll.length ? (beginScroll.offset().top + beginScroll.outerHeight() - navigationBarHeight) : 0;
    var stopSticky = $('.stop-sticky');
    if (topOffset != 0) {
        $(window).on('scroll', function() {
            var scrollTop = $(window).scrollTop();
            var boxSectionOffset = boxSection.offset().top;
            var boxSectionBottom = boxSectionOffset + boxSection.height();
            var bottomSticky = (navigationBarHeight + 60) * 2;
            var stopStickyTop = stopSticky.offset().top - bottomSticky;
            if (scrollTop >= topOffset && scrollTop <= stopStickyTop) {
                topElement.removeClass('top static').addClass('sticky');
            } else if (scrollTop >= stopStickyTop) {
                topElement.removeClass('sticky top').addClass('static');
            } else {
                topElement.removeClass('sticky static').addClass('top');
            }
        });
    }
    document.addEventListener("DOMContentLoaded", function () {
        var scrollDurationLeft = 300;
        var scrollDurationRight = 300;
        $('.features-swipe-wrapper').each(function() {
            var $wrapper = $(this);
            var leftPaddle = $wrapper.find('.left-features-paddle');
            var rightPaddle = $wrapper.find('.right-features-paddle');
            var $menu = $wrapper.find('.features-swipe');
            var itemsLength = $menu.find('.features-swipe-item').length;
            var itemSize = $menu.find('.features-swipe-item').outerWidth(true);
            var paddleMargin = 20;
            var menuWrapperSize = $wrapper.outerWidth();
            $(window).on('resize', function() {
                menuWrapperSize = $wrapper.outerWidth();
            });
            var menuVisibleSize = menuWrapperSize;
            var menuSize = itemsLength * itemSize;
            var menuInvisibleSize = menuSize - menuWrapperSize;
            $menu.on('scroll', function() {
                var menuPosition = $menu.scrollLeft();
                var tolerance = 5;
                if (menuPosition <= 0) {
                    leftPaddle.addClass('hidden');
                    rightPaddle.removeClass('hidden');
                } else if (menuPosition + menuWrapperSize + tolerance >= menuSize) {
                    leftPaddle.removeClass('hidden');
                    rightPaddle.addClass('hidden');
                } else {
                    leftPaddle.removeClass('hidden');
                    rightPaddle.removeClass('hidden');
                }
            });
            function smoothScroll(element, targetScrollLeft, duration) {
                var start = element.scrollLeft,
                    change = targetScrollLeft - start,
                    currentTime = 0,
                    increment = 16.7;
                var animateScroll = function() {
                    if (!isScrolling) {
                        return;
                    }
                    currentTime += increment;
                    var val = Math.easeInOutQuad(currentTime, start, change, duration);
                    element.scrollLeft = val;
                    if (currentTime < duration) {
                        requestAnimationFrame(animateScroll);
                    }
                };
                animateScroll();
            }
            Math.easeInOutQuad = function (t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            };
            var isScrolling = false;
            if (screen.width > 768) {
                rightPaddle.on('mousedown touchstart', function(e) {
                    e.preventDefault(); 
                    isScrolling = true;
                    smoothScroll($menu[0], menuSize, scrollDurationRight);
                });
                leftPaddle.on('mousedown touchstart', function(e) {
                    e.preventDefault(); 
                    isScrolling = true;
                    smoothScroll($menu[0], 0, scrollDurationLeft);
                });
            } else {
                rightPaddle.on('mousedown touchstart', function(e) {
                    e.preventDefault(); 
                    isScrolling = true;
                    smoothScroll($menu[0], menuSize, scrollDurationRight);
                });
                leftPaddle.on('mousedown touchstart', function(e) {
                    e.preventDefault(); 
                    isScrolling = true;
                    smoothScroll($menu[0], 0, scrollDurationLeft);
                });
                $(document).on('touchmove', function(e) {
                    if (isScrolling) {
                        e.preventDefault();
                    }
                });
                $(document).on('mouseup', function(e) {
                    if (isScrolling) {
                        e.preventDefault(); 
                        isScrolling = false;
                    }
                });
            }
            var scrollDurationRight = 2000;
            var scrollDurationLeft = 2000;
        });
    });
    $(".accordion-accommodation-item").click(function () {
        $(this).toggleClass("active-accordion-accommodation");
        var panel = $(this).next();
        panel.slideToggle();
    });
    $('#linkToAccordionReviews').click(function () {
        event.preventDefault();
        var accordionButton = $('#reviewsAnchor'); 
        accordionButton.toggleClass("active-accordion-accommodation");
        var panel = accordionButton.next('.accordion-accommodation-content');
        panel.slideToggle();
        $('html, body').animate({
            scrollTop: $("#reviewsAnchor").offset().top - navigationBarHeight
        }, 1000);
    });
    $('#accordion_map').click(function() {
        $(this).toggleClass("accordion_map_active");
        $('.panel').toggleClass('panel-active');
    });
    $('#icon-grid').click(function() {
        $('#icon-list').removeClass('selected');
        $('#map-maker').removeClass('selected');
        $(this).addClass('selected');
        $('#prop-view').removeClass('prop-list-view');
    });
    $('#icon-list').click(function() {
        $('#icon-grid').removeClass('selected');
        $('#map-maker').removeClass('selected');
        $(this).addClass('selected');
        $('#prop-view').addClass('prop-list-view');
    });
    $('#map-maker').click(function() {
        $('#icon-grid').removeClass('selected');
        $('#icon-list').removeClass('selected');
        $(this).addClass('selected');
        $('#prop-view').removeClass('prop-list-view');
    });
    $('#icon_left_calendar').click(function() {
        $('#month-first-half').removeClass('hidden-calendar');
        $('#month-second-half').addClass('hidden-calendar');
        $('#icon_right_calendar').removeClass('disabled');
        $(this).addClass('disabled');
    });
    $('#icon_right_calendar').click(function() {
        $('#month-first-half').addClass('hidden-calendar');
        $('#month-second-half').removeClass('hidden-calendar');
        $('#icon_left_calendar').removeClass('disabled');
        $(this).addClass('disabled');
    });
    $('.readmore-button-wrapper').click(function() {
        $('#readmore-container').addClass('read-hidden');
        if ($('#descriptionText').length) {
            $('#descriptionText').removeClass('shrinked');
        }
        if ($('#search-description').length) {
            $('#search-description').removeClass('shrinked');
        }
        $('#readless-container').removeClass('read-hidden');
    });
    $('.readless-button-wrapper').click(function() {
        $('#readless-container').addClass('read-hidden');
        if ($('#descriptionText').length) {
            $('#descriptionText').addClass('shrinked');
        }
        if ($('#search-description').length) {
            $('#search-description').addClass('shrinked');
        }
        $('#readmore-container').removeClass('read-hidden');
        if ($('#scroll_page').length) {
            var propertyMap = $('.accordion-accommodation').offset().top - navigationBarHeight;
            $('html, body').animate({
                scrollTop: propertyMap
            }, 500);
        }
        if ($('#search-description').length) {
            var propertyMap = $('.propertyMap').offset().top - navigationBarHeight;
            $('html, body').animate({
                scrollTop: propertyMap
            }, 500);
        }
    });
    $('.loadmore-offers-button-wrapper').click(function() {
        $('#loadmore-offers-container').addClass('hidden-offer');
        $('.customerOffers-shrinked').removeClass('hidden-offer');
        $('#loadless-offers-container').removeClass('hidden-offer');
    });
    $('.loadless-offers-button-wrapper').click(function() {
        $('#loadless-offers-container').addClass('hidden-offer');
        $('.customerOffers-shrinked').addClass('hidden-offer');
        if ($('#scroll_page').length) {
            var offerTop = $('#mainSpecialOffers').offset().top - navigationBarHeight;
            $('html, body').animate({
                scrollTop: offerTop
            }, 500);
        }
        $('#loadmore-offers-container').removeClass('hidden-offer');
    });
    let visibleReviews = 1;
    let isFirstLoadMoreClick = true;
    $('.loadmore-reviews-button-wrapper').click(function() {
        if (isFirstLoadMoreClick) {
            visibleReviews += 4;
            isFirstLoadMoreClick = false;
        } else {
            visibleReviews += 6;
        }
        $('.customerReviews-shrinked').each(function(index) {
            if (index < visibleReviews) {
                $(this).removeClass('hidden-review');
            }
        });
        if ($('.customerReviews-shrinked').length <= visibleReviews) {
            $('#loadmore-reviews-container').hide();
        }
    });
    $('.loadless-reviews-button-wrapper').click(function() {
        visibleReviews = 1;
        isFirstLoadMoreClick = true;
        $('.customerReviews-shrinked').addClass('hidden-review').slice(0, 1).removeClass('hidden-review');
        $('#loadmore-reviews-container').show();
        if ($('#scroll_page').length) {
            var reviewTop = $('.customerReviews-container').offset().top - navigationBarHeight;
            $('html, body').animate({
                scrollTop: reviewTop
            }, 500);
        }
        $('#loadmore-reviews-container').removeClass('hidden-review');
    });
    $('.moreReviews').click(function(){
        $('.expandable-readmore').removeClass('disabled');
        $(this).addClass('disabled');
        $('.lessReviews').removeClass('disabled');
    });
    $('.lessReviews').click(function(){
        $('.expandable-readmore').addClass('disabled');
        $(this).addClass('disabled');
        $('.moreReviews').removeClass('disabled');
    });
    $('#cancellationPolicyButtonInfo').click(function(){
        $('.cancellation-conditions').parent().append("<div class='pop-up-overlay'></div>");
        $('.pop-up-info').removeClass('hidden-info');
        $('.pop-up-info').addClass('show-info');
        $(".pop-up-overlay").click(function() {
            $('body').css({'overflow-y': 'auto', 'padding-right': '0'});
            $('#scroll_page').css('padding-right', '0');
            $(this).fadeOut();
            $(this).remove();
            $('.pop-up-info').addClass('hidden-info');
            $('.pop-up-info').removeClass('show-info');
        });
        $('.icon-cancel').click(function() {
            $('body').css({'overflow-y': 'auto', 'padding-right': '0'});
            $('#scroll_page').css('padding-right', '0');
            $('.cancellation-conditions').siblings(".pop-up-overlay").fadeOut();
            $('.cancellation-conditions').siblings(".pop-up-overlay").remove();
            $('.pop-up-info').addClass('hidden-info');
            $('.pop-up-info').removeClass('show-info');
        });
        $('body').css({'overflow-y': 'hidden'});
        $('#scroll_page').css('padding-right', '15px');
    });
    $('#scrollContainer').on("mousedown", function() {
        $(this).animate({scrollLeft: "+=200px"}, "slow");
    });
    function updateOccupancyBox() {
        var adultsNum = parseInt($('#AdultNum').val(), 10);
        var childrenNum = parseInt($('#ChildrenNum').val(), 10);
        var adultsText = adultsNum === 1 ? '1 Adult' : adultsNum + ' Adults';
        var childrenText;
        if (childrenNum === 0) {
            childrenText = '0 Children';
        } else if (childrenNum === 1) {
            childrenText = '1 Child';
        } else {
            childrenText = childrenNum + ' Children';
        }
        var occupancyValue = adultsText + ' - ' + childrenText;
		if (isNaN(childrenNum) || isNaN(adultsNum)) {
			occupancyValue = '';
        }
        $('#occupancy-box').val(occupancyValue);
    }
    function updateChildAgeSelects() {
        var childrenNum = parseInt($('#ChildrenNum').val(), 10);
        $('.child-age-selects > div').each(function (index) {
            if (index < childrenNum) {
                var child_number = index+1;
                $(this).show();
                $('#Child_'+child_number+'_Age').prop("disabled", false);
            } else {
                $(this).hide();
                var child_number = index+1;
                $('#Child_'+child_number+'_Age').val('');
                $('#Child_'+child_number+'_Age').prop("disabled", true);
            }
        });
        if (childrenNum === 0) {
            $('.child-age-selects > div').hide();
        }
    }
    $('#occupancy-box').on('click', function (e) {
        updateOccupancyBox();
    });
    updateChildAgeSelects();
    $('#plusminus-a-minus').off('click').on('click', function(event) {
        event.stopPropagation();
        var input = $(this).siblings('input.num');
        var currentValue = parseInt(input.val(), 10);
        if (isNaN(currentValue)) {
            currentValue = 0;
        }
        var minValue = parseInt(input.attr('min'), 10);
        if (currentValue > minValue) {
            input.val(currentValue - 1);
            updateOccupancyBox();
            updateChildAgeSelects();
        }
        if ($("#formReserveAccommodation").length != 0) {
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
        }
    });
    $('#plusminus-a-plus').off('click').on('click', function(event) {
        event.stopPropagation();
        var input = $(this).siblings('input.num');
        var currentValue = parseInt(input.val(), 10);
        if (isNaN(currentValue)) {
            currentValue = 0;
        }
        var maxValue = parseInt(input.attr('max'), 10);
        if (currentValue < maxValue) {
            input.val(currentValue + 1);
            updateOccupancyBox();
            updateChildAgeSelects();
        }
        if ($("#formReserveAccommodation").length != 0) {
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
        }
    });
    $('#plusminus-c-minus').off('click').on('click', function(event) {
        event.stopPropagation();
        var input = $(this).siblings('input.num');
        var currentValue = parseInt(input.val(), 10);
        if (isNaN(currentValue)) {
            currentValue = 0;
        }
        var minValue = parseInt(input.attr('min'), 10);
        if (currentValue > minValue) {
            input.val(currentValue - 1);
            updateOccupancyBox();
            updateChildAgeSelects();
        }
        if ($("#formReserveAccommodation").length != 0) {
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
        }
    });
    $('.children_age_select').on('change',function() {
        if ($("#formReserveAccommodation").length != 0) {
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
        }
    });
    $('#plusminus-c-plus').off('click').on('click', function(event) {
        event.stopPropagation();
        var input = $(this).siblings('input.num');
        var currentValue = parseInt(input.val(), 10);
        if (isNaN(currentValue)) {
            currentValue = 0;
        }
        var maxValue = parseInt(input.attr('max'), 10);
        if (currentValue < maxValue) {
            input.val(currentValue + 1);
            updateOccupancyBox();
            updateChildAgeSelects();
        }
    });
    $('#occupancy-box').click(function() {
        if ($('#occupancy-dropdown').hasClass('occupancy-hidden')) {
            event.stopPropagation();
            $('#occupancy-dropdown').removeClass('occupancy-hidden');
        }
    });
    $(document).click(function(event) {
        var $target = $(event.target);
        if(!$target.closest('#occupancy-dropdown').length && $('#occupancy-dropdown').is(":visible")) {
            $('#occupancy-dropdown').addClass('occupancy-hidden');
        }
    });
    $('#occupancy-dropdown').click(function(event) {
        event.stopPropagation();
    });
    $('.setDateSticky .priceFrom p:first').filter(function() {
        return $(this).next('h2').find('span.discounted-price').length > 0;
    }).remove();
    $('form[name="formReserveAccommodation"]').on('submit', function(e) {
        e.preventDefault();
        $('#formReserveAccommodation').addClass('blur-out');
        $('#sidebar-ajax-loader').show();
        var formData = $(this).serializeArray();
        formData.push({name: "action", value: "reserve"});
        formData.push({name: "nonce", value: propScriptData.nonce});
        $.ajax({
            type: 'POST',
            url: propScriptData.ajaxurl,
            data: formData,
            success: function(response) {
                $('#formReserveAccommodation').removeClass('blur-out');
                $('#sidebar-ajax-loader').hide();
                $('#errormessage').empty();
                if (response.success) {
                    if (response.data.success_desktop && response.data.success_mobile) {
                        $('.right-sidebar .response').html(response.data.success_desktop);
                        $('.right-sidebar-mob .response').html(response.data.success_mobile);
                    } else {
                        $('.response').html(response.data);
                    }
                    $('.priceFrom p').remove();
                    $('.priceFrom h2').html($('.aprice').html());
                    $('.priceFrom h2').nextAll('p').last().remove();
                    $('.priceFrom').next().append($('.checkinDate-box-outer').html());
					$('#bt_act').hide();
					$('.right-sidebar .sidebar-priceinfobox').insertAfter('#bt_act');
                    updateContactURL();
                } else {
                    if (response.data.error_message && response.data.price_sidebar && response.data.mobile_sidebar) {
                        $('.right-sidebar .response').html(response.data.price_sidebar);
                        $('.right-sidebar-mob .response').html(response.data.mobile_sidebar);
                        $('#errormessage').html(response.data.error_message);
                        $('#bt_act').find('button').each(function() {
                            $(this).remove();
                        });
                        $('#bt_act').find('a').each(function() {
                            $(this).remove();
                        });
                        $('#bt_act').append(response.data.button_side_bar);
                    } else {
                        $('#errormessage').html(response.data);
                    }
                    $('.priceFrom h2').html($('.aprice').html());
                    $('.priceFrom h2').nextAll('p').last().remove();
                    updateContactURL();
                }
            },
            error: function(xhr, status, error) {
                $('#formReserveAccommodation').removeClass('blur-out');
                $('#sidebar-ajax-loader').hide();
            }
        });
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
        return false;
    });
    if ($('.contactParameters').length) {
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
        updateContactURL();
    }
});
jQuery(document).ready(function($) {
    $('#aai-owl-carousel').owlCarousel({
        loop:false,
        margin:15,
        autoWidth:true,
        nav:true,
        navText: [
            "<i class='eicon-chevron-left' aria-hidden='true'></i>",
            "<i class='eicon-chevron-right' aria-hidden='true'></i>"
        ],
        responsive:{
            0:{
                items:2
            },
            768:{
                items:4
            },
            1024:{
                items:8
            }
        }
    });
});
jQuery(document).ready(function($) {
    $('.owl-prev').css('display', 'none');
    setTimeout(function(){
        $('.owl-prev').css('display', 'none');
        $('.owl-next').on('click', function() {
            var $carouselContainer = $(this).closest('.js-owce-carousel-container');
            var $leftButton = $carouselContainer.find('.owl-prev');
            $leftButton.css('display', 'block');
        });
        $('button.owl-prev').attr('role', 'button');
        $('button.owl-next').attr('role', 'button');
        $('button.owl-dot').attr('role', 'button');
    }, 2000);
});
document.addEventListener("DOMContentLoaded", function () {
    const divContainingUl = document.querySelector("#breadcrumb");
    if (divContainingUl) {
        const firstLi = divContainingUl.querySelector("ul li:first-child");
        if (firstLi) {
            const anchorTag = firstLi.querySelector("a");
            if (anchorTag) {
                anchorTag.href = "javascript:void(0)";
                anchorTag.addEventListener("click", function (event) {
                    event.preventDefault();
                    window.history.back();
                });
            }
        }
    }
});