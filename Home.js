// Adds a scroll effect to the navbar so it changes style once the user scrolls down
window.addEventListener("scroll", () => {
  const nav = document.querySelector(".static-control-bar");
  if (!nav) return;
  nav.classList.toggle("scrolled", window.scrollY > 0);
});


// Select key UI elements used for the filter drawer functionality
const openBtn = document.getElementById("openFilters");
const closeBtn = document.getElementById("closeFilters");
const drawer = document.getElementById("filterDrawer");
const overlay = document.getElementById("drawerOverlay");
const clearBtn = document.getElementById("clearFilters");


// Opens the filter drawer and enables the overlay background
function openDrawer() {
  if (!drawer || !overlay) return;
  drawer.classList.add("open");
  overlay.classList.add("open");
  drawer.setAttribute("aria-hidden", "false");
}


// Closes the filter drawer and removes the overlay
function closeDrawer() {
  if (!drawer || !overlay) return;
  drawer.classList.remove("open");
  overlay.classList.remove("open");
  drawer.setAttribute("aria-hidden", "true");
}


// Event listeners to handle opening and closing the filter drawer
openBtn?.addEventListener("click", openDrawer);
closeBtn?.addEventListener("click", closeDrawer);
overlay?.addEventListener("click", closeDrawer);


// Allows user to close the drawer using the Escape key for better UX
window.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeDrawer();
});


// Clears all selected genre filters by unchecking every checkbox
clearBtn?.addEventListener("click", () => {
  document.querySelectorAll(".genre-pill input[type='checkbox']").forEach((cb) => {
    cb.checked = false;
  });
});