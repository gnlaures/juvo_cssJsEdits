document.addEventListener("DOMContentLoaded", function () {
    let favorites = getCookie("favorites");
    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
        const cookieAttributes = `expires=${expires}; path=/; SameSite=None; Secure`;
        document.cookie = `${name}=${value}; ${cookieAttributes}`;
    }
    function getCookie(name) {
        const cookies = document.cookie.split(";").map(cookie => cookie.trim());
        for (const cookie of cookies) {
            if (cookie.startsWith(name + "=")) {
                return JSON.parse(cookie.substring(name.length + 1));
            }
        }
        return [0];
    }
    const propSea = document.getElementById('propSea');
    if (propSea) {
        const propSectionsMain = propSea.querySelectorAll('li[data-propid]');
        propSectionsMain.forEach((section, index) => {
            const propid = section.getAttribute("data-propid");
            const iconClass = favorites.includes(propid) ? "fas fa-heart" : "far fa-heart";
            section.querySelector(".favouritesProp i").className = iconClass + " fa-lg";
            section.querySelector(".favouritesProp i").addEventListener("click", (e) => {
                const propid = e.target.parentElement.parentElement.parentElement.parentElement.getAttribute("data-propid");
                var findex = favorites.indexOf(propid);
                if (findex > -1) {
                    favorites.splice(findex, 1);
                } else {
                    favorites.push(propid);
                }
                const iconClass = favorites.includes(propid) ? "fas fa-heart" : "far fa-heart";
                e.target.className = iconClass + " fa-lg";
                setCookie("favorites", JSON.stringify(favorites), 7);
            });
            let carousel, dotsContainer;
            const carouselElements = propSea.querySelectorAll(".carousel");
            const dotsElements = propSea.querySelectorAll(".carousel-dots");
            const differentCarouselElements = propSea.querySelectorAll(".search-carousel");
            const differentDotsElements = propSea.querySelectorAll(".search-carousel-dots");
            if (carouselElements.length > 0 && dotsElements.length > 0) {
                carousel = section.querySelector(".carousel");
                dotsContainer = section.querySelector(".carousel-dots");
            } else if (differentCarouselElements.length > 0 && differentDotsElements.length > 0) {
                carousel = section.querySelector(".search-carousel");
                dotsContainer = section.querySelector(".search-carousel-dots");
            } else {
                return;
            }
            if (carousel && !dotsContainer.childNodes.length) {
                const slides = Array.from(carousel.querySelectorAll("li"));
                let currentIndex = 0;
                let autoRotateInterval;
                carousel.setAttribute('data-initialized', 'true');
                let dots = [];
                dotsContainer.innerHTML = '';
                slides.forEach((slide, index) => {
                    const dot = document.createElement("span");
                    dot.className = "dot";
                    dotsContainer.appendChild(dot);
                    dots.push(dot);
                    dot.addEventListener("click", () => {
                        currentIndex = index;
                        updateCarousel();
                    });
                });
                function updateCarousel() {
                    carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
                    slides.forEach((slide, i) => {
                        slide.classList.toggle("active", i === currentIndex);
                    });
                    dots.forEach((dot, i) => {
                        dot.classList.toggle("active", i === currentIndex);
                    });
                }
                function startAutoSlide() {
                    if (autoRotateInterval) {
                        clearInterval(autoRotateInterval);
                    }
                    autoRotateInterval = setInterval(() => {
                        currentIndex = (currentIndex + 1) % slides.length;
                        updateCarousel();
                    }, 3000);
                }
                function stopAutoSlide() {
                    clearInterval(autoRotateInterval);
                }
                carousel.addEventListener('touchstart', stopAutoSlide);
                carousel.addEventListener('mousedown', stopAutoSlide);
                dots.forEach(dot => dot.addEventListener('click', stopAutoSlide));
                carousel.addEventListener('mouseenter', stopAutoSlide);
                carousel.addEventListener('mouseleave', startAutoSlide);
                startAutoSlide();
                dots.forEach((dot, i) => {
                    dot.addEventListener("click", () => {
                        currentIndex = i;
                        updateCarousel();
                    });
                });
                let startX, currentX, isDragging = false;
                const handleTouchStart = (e) => {
                    startX = e.touches[0].clientX;
                };
                const handleTouchMove = (e) => {
                    currentX = e.touches[0].clientX;
                };
                const handleTouchEnd = () => {
                    const deltaX = startX - currentX;
                    if (Math.abs(deltaX) > 50) {
                        if (deltaX > 0 && currentIndex < slides.length - 1) {
                            currentIndex++;
                        } else if (deltaX < 0 && currentIndex > 0) {
                            currentIndex--;
                        }
                        updateCarousel();
                    }
                };
                carousel.addEventListener('touchstart', handleTouchStart);
                carousel.addEventListener('touchmove', handleTouchMove);
                carousel.addEventListener('touchend', handleTouchEnd);
                const handleDragStart = (e) => {
                    e.preventDefault();
                    startX = e.clientX;
                    isDragging = true;
                    carousel.classList.add("dragging");
                    carousel.style.transition = 'none';
                };
                const handleDragMove = (e) => {
                    if (!isDragging) return;
                    currentX = e.clientX;
                    const deltaX = startX - currentX;
                    const movePercentage = deltaX / carousel.offsetWidth * 100;
                    carousel.style.transform = `translateX(-${currentIndex * 100 + movePercentage}%)`;
                };
                const handleDragEnd = () => {
                    if (!isDragging) return;
                    isDragging = false;
                    carousel.classList.remove("dragging");
                    carousel.style.transition = '';
                    const deltaX = startX - currentX;
                    if (Math.abs(deltaX) > 50) {
                        if (deltaX > 0 && currentIndex < slides.length - 1) {
                            currentIndex++;
                        } else if (deltaX < 0 && currentIndex > 0) {
                            currentIndex--;
                        }
                    }
                    updateCarousel();
                };
                carousel.addEventListener('mousedown', handleDragStart);
                carousel.addEventListener('mousemove', handleDragMove);
                document.addEventListener('mouseup', handleDragEnd);
                updateCarousel();
            }
        });
    }
    const sHomesObserver = new MutationObserver((mutations, obs) => {
        const sHomes = document.getElementById('sHomes');
        if (sHomes) {
            const propSectionsOther = sHomes.querySelectorAll('li[data-propid]');
            propSectionsOther.forEach((section, index) => {
                const propid = section.getAttribute("data-propid");
                const iconClass = favorites.includes(propid) ? "fas fa-heart" : "far fa-heart";
                section.querySelector(".favouritesProp i").className = iconClass + " fa-lg";
                section.querySelector(".favouritesProp i").addEventListener("click", (e) => {
                    const propid = e.target.parentElement.parentElement.parentElement.parentElement.getAttribute("data-propid");
                    var findex = favorites.indexOf(propid);
                    if (findex > -1) {
                        favorites.splice(findex, 1);
                    }else{
                        favorites.push(propid);
                    }
                    const iconClass = favorites.includes(propid) ? "fas fa-heart" : "far fa-heart";
                    e.target.className = iconClass + " fa-lg";
                    setCookie("favorites", JSON.stringify(favorites), 7);
                });
                let carousel, dotsContainer;
                const carouselElements = sHomes.querySelectorAll(".carousel");
                const dotsElements = sHomes.querySelectorAll(".carousel-dots");
                const differentCarouselElements = sHomes.querySelectorAll(".search-carousel");
                const differentDotsElements = sHomes.querySelectorAll(".search-carousel-dots");
                if (carouselElements.length > 0 && dotsElements.length > 0) {
                    carousel = section.querySelector(".carousel");
                    dotsContainer = section.querySelector(".carousel-dots");
                } else if (differentCarouselElements.length > 0 && differentDotsElements.length > 0) {
                    carousel = section.querySelector(".search-carousel");
                    dotsContainer = section.querySelector(".search-carousel-dots");
                } else {
                    return;
                }
                if (carousel && !dotsContainer.childNodes.length) {
                    const slides = Array.from(carousel.querySelectorAll("li"));
                    let currentIndex = 0;
                    let autoRotateInterval;
                    carousel.setAttribute('data-initialized', 'true');
                    let dots = [];
                    dotsContainer.innerHTML = '';
                    slides.forEach((slide, index) => {
                        const dot = document.createElement("span");
                        dot.className = "dot";
                        dotsContainer.appendChild(dot);
                        dots.push(dot);
                        dot.addEventListener("click", () => {
                            currentIndex = index;
                            updateCarousel();
                        });
                    });
                    function updateCarousel() {
                        carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
                        slides.forEach((slide, i) => {
                            slide.classList.toggle("active", i === currentIndex);
                        });
                        dots.forEach((dot, i) => {
                            dot.classList.toggle("active", i === currentIndex);
                        });
                    }
                    function startAutoSlide() {
                        if (autoRotateInterval) {
                            clearInterval(autoRotateInterval);
                        }
                        autoRotateInterval = setInterval(() => {
                            currentIndex = (currentIndex + 1) % slides.length;
                            updateCarousel();
                        }, 3000);
                    }
                    function stopAutoSlide() {
                        clearInterval(autoRotateInterval);
                    }
                    carousel.addEventListener('touchstart', stopAutoSlide);
                    carousel.addEventListener('mousedown', stopAutoSlide);
                    dots.forEach(dot => dot.addEventListener('click', stopAutoSlide));
                    carousel.addEventListener('mouseenter', stopAutoSlide);
                    carousel.addEventListener('mouseleave', startAutoSlide);
                    startAutoSlide();
                    dots.forEach((dot, i) => {
                        dot.addEventListener("click", () => {
                            currentIndex = i;
                            updateCarousel();
                        });
                    });
                    let startX, currentX, isDragging = false;
                    const handleTouchStart = (e) => {
                        startX = e.touches[0].clientX;
                    };
                    const handleTouchMove = (e) => {
                        currentX = e.touches[0].clientX;
                    };
                    const handleTouchEnd = () => {
                        const deltaX = startX - currentX;
                        if (Math.abs(deltaX) > 50) {
                            if (deltaX > 0 && currentIndex < slides.length - 1) {
                                currentIndex++;
                            } else if (deltaX < 0 && currentIndex > 0) {
                                currentIndex--;
                            }
                            updateCarousel();
                        }
                    };
                    carousel.addEventListener('touchstart', handleTouchStart);
                    carousel.addEventListener('touchmove', handleTouchMove);
                    carousel.addEventListener('touchend', handleTouchEnd);
                    const handleDragStart = (e) => {
                        e.preventDefault();
                        startX = e.clientX;
                        isDragging = true;
                        carousel.classList.add("dragging");
                        carousel.style.transition = 'none';
                    };
                    const handleDragMove = (e) => {
                        if (!isDragging) return;
                        currentX = e.clientX;
                        const deltaX = startX - currentX;
                        const movePercentage = deltaX / carousel.offsetWidth * 100;
                        carousel.style.transform = `translateX(-${currentIndex * 100 + movePercentage}%)`;
                    };
                    const handleDragEnd = () => {
                        if (!isDragging) return;
                        isDragging = false;
                        carousel.classList.remove("dragging");
                        carousel.style.transition = '';
                        const deltaX = startX - currentX;
                        if (Math.abs(deltaX) > 50) {
                            if (deltaX > 0 && currentIndex < slides.length - 1) {
                                currentIndex++;
                            } else if (deltaX < 0 && currentIndex > 0) {
                                currentIndex--;
                            }
                            updateCarousel();
                        }
                    };
                    carousel.addEventListener('mousedown', handleDragStart);
                    carousel.addEventListener('mousemove', handleDragMove);
                    document.addEventListener('mouseup', handleDragEnd);
                    updateCarousel(carousel, slides, dots, currentIndex);
                }
            });
            obs.disconnect();
        }
    });
    sHomesObserver.observe(document.body, { childList: true, subtree: true });
});