window.addEventListener("scroll", () => {
  const nav = document.querySelector(".static-control-bar");
  if (!nav) return;

  nav.classList.toggle("scrolled", window.scrollY > 0);
});

const openBtn = document.getElementById("openFilters");
const closeBtn = document.getElementById("closeFilters");
const drawer = document.getElementById("filterDrawer");
const overlay = document.getElementById("drawerOverlay");

const startYearEl = document.getElementById("startYear");
const endYearEl = document.getElementById("endYear");

const clearBtn = document.getElementById("clearFilters");
const applyBtn = document.getElementById("applyFilters");

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

function fillYearSelects() {
  if (!startYearEl || !endYearEl) return;

  const currentYear = new Date().getFullYear();
  const minYear = 1960;

  startYearEl.innerHTML = `<option value="">Start</option>`;
  endYearEl.innerHTML = `<option value="">End</option>`;

  for (let y = currentYear; y >= minYear; y--) {
    startYearEl.insertAdjacentHTML("beforeend", `<option value="${y}">${y}</option>`);
    endYearEl.insertAdjacentHTML("beforeend", `<option value="${y}">${y}</option>`);
  }
}

function clampYearRange() {
  if (!startYearEl || !endYearEl) return;

  const start = parseInt(startYearEl.value, 10);
  const end = parseInt(endYearEl.value, 10);

  if (!isNaN(start) && !isNaN(end) && end < start) {
    endYearEl.value = String(start);
  }
}

startYearEl?.addEventListener("change", clampYearRange);
endYearEl?.addEventListener("change", clampYearRange);

clearBtn?.addEventListener("click", () => {
  document.querySelectorAll(".genre-pill input").forEach(cb => cb.checked = false);
  document.querySelectorAll('input[name="filterType"]').forEach(r => {
    r.checked = (r.value === "");
  });

  if (startYearEl) startYearEl.value = "";
  if (endYearEl) endYearEl.value = "";
});

applyBtn?.addEventListener("click", () => {
  const genres = Array.from(document.querySelectorAll(".genre-pill input:checked"))
    .map(input => input.value);

  const type = document.querySelector('input[name="filterType"]:checked')?.value || "";
  const startYear = startYearEl?.value || "";
  const endYear = endYearEl?.value || "";

  const params = new URLSearchParams();

  if (type) params.set("type", type);
  if (startYear) params.set("startYear", startYear);
  if (endYear) params.set("endYear", endYear);

  let targetUrl = "RankingPage.php";

  if (genres.length > 0) {
    targetUrl = "TopGenreAnime.php";
    params.set("genre", genres[0]);
  }

  window.location.href = `${targetUrl}?${params.toString()}`;
});

fillYearSelects();