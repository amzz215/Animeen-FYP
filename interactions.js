document.addEventListener("click", async function (event) {
    const button = event.target.closest(".btn-watchlist, .btn-like, .btn-dislike");
    if (!button) return;

    const card = button.closest("[data-id]");
    if (!card) return;

    const animeId = card.dataset.id;
    if (!animeId) return;

    let action = "";

    if (button.classList.contains("btn-watchlist")) action = "watchlist";
    if (button.classList.contains("btn-like")) action = "like";
    if (button.classList.contains("btn-dislike")) action = "dislike";

    if (!action) return;

    const formData = new FormData();
    formData.append("anime_id", animeId);
    formData.append("action", action);

    try {
        const response = await fetch("interactions.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (!response.ok || !data.ok) {
            if (response.status === 401) {
                alert("Please log in first.");
                window.location.href = "login.php";
                return;
            }

            alert(data.message || "Something went wrong.");
            return;
        }

        updateButtons(card, data);
    } catch (error) {
        alert("Unable to save interaction right now.");
    }
});

function updateButtons(card, state) {
    const watchBtn = card.querySelector(".btn-watchlist");
    const likeBtn = card.querySelector(".btn-like");
    const dislikeBtn = card.querySelector(".btn-dislike");

    if (watchBtn) {
        watchBtn.classList.toggle("active", state.watchlisted == 1);
        watchBtn.textContent = state.watchlisted == 1 ? "✓ Watchlisted" : "+ Watchlist";
    }

    if (likeBtn) {
        likeBtn.classList.toggle("active", state.liked == 1);
    }

    if (dislikeBtn) {
        dislikeBtn.classList.toggle("active", state.disliked == 1);
    }
}