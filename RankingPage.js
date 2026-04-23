// Event listener used to handle all click interactions on anime cards
document.addEventListener("click", (e) => {

  // Identify the anime card that was clicked
  const card = e.target.closest(".anime-card");
  if (!card) return;

  // Toggle watchlist state when watchlist button is clicked
  if (e.target.closest(".btn-watchlist")) {
    const current = card.getAttribute("data-watchlist") === "1";

    // Switch between watchlisted and not watchlisted
    card.setAttribute("data-watchlist", current ? "0" : "1");

    // Update button text to reflect current state
    e.target.textContent = current ? "+ Watchlist" : "✓ Watchlisted";
  }

  // Toggle like state and ensure dislike is removed (mutually exclusive behaviour)
  if (e.target.closest(".btn-like")) {
    const liked = card.getAttribute("data-liked") === "1";

    // Toggle like status
    card.setAttribute("data-liked", liked ? "0" : "1");

    // Reset dislike if like is activated
    card.setAttribute("data-disliked", "0");
  }

  // Toggle dislike state and ensure like is removed (mutually exclusive behaviour)
  if (e.target.closest(".btn-dislike")) {
    const disliked = card.getAttribute("data-disliked") === "1";

    // Toggle dislike status
    card.setAttribute("data-disliked", disliked ? "0" : "1");

    // Reset like if dislike is activated
    card.setAttribute("data-liked", "0");
  }
});
