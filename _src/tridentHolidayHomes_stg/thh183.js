document.addEventListener("DOMContentLoaded", function() {
    var toggleMenu = document.querySelector(".js-toggleOccupancyMenu");
    if (toggleMenu !== null) {
        toggleMenu.querySelector("span").addEventListener("click", function() {
            document.querySelector(".occupancy-dropdown").classList.add("occupancy-hidden");
        });
    }
});
