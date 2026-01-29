document.addEventListener("click", (e) => {
  const card = e.target.closest(".anime-card");
  if (!card) return;

  if (e.target.closest(".btn-watchlist")) {
    const current = card.getAttribute("data-watchlist") === "1";
    card.setAttribute("data-watchlist", current ? "0" : "1");
    e.target.textContent = current ? "+ Watchlist" : "✓ Watchlisted";
  }

  if (e.target.closest(".btn-like")) {
    const liked = card.getAttribute("data-liked") === "1";
    card.setAttribute("data-liked", liked ? "0" : "1");
    card.setAttribute("data-disliked", "0");
  }

  if (e.target.closest(".btn-dislike")) {
    const disliked = card.getAttribute("data-disliked") === "1";
    card.setAttribute("data-disliked", disliked ? "0" : "1");
    card.setAttribute("data-liked", "0");
  }
});
