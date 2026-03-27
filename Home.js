window.addEventListener("scroll", () => {
  const nav = document.querySelector(".static-control-bar");
  if (!nav) return;
  nav.classList.toggle("scrolled", window.scrollY > 0);
});

const openBtn = document.getElementById("openFilters");
const closeBtn = document.getElementById("closeFilters");
const drawer = document.getElementById("filterDrawer");
const overlay = document.getElementById("drawerOverlay");
const clearBtn = document.getElementById("clearFilters");

function openDrawer() {
  if (!drawer || !overlay) return;
  drawer.classList.add("open");
  overlay.classList.add("open");
  drawer.setAttribute("aria-hidden", "false");
}

function closeDrawer() {
  if (!drawer || !overlay) return;
  drawer.classList.remove("open");
  overlay.classList.remove("open");
  drawer.setAttribute("aria-hidden", "true");
}

openBtn?.addEventListener("click", openDrawer);
closeBtn?.addEventListener("click", closeDrawer);
overlay?.addEventListener("click", closeDrawer);

window.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeDrawer();
});

clearBtn?.addEventListener("click", () => {
  document.querySelectorAll(".genre-pill input[type='checkbox']").forEach((cb) => {
    cb.checked = false;
  });
});