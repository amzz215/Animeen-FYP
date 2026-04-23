// Displays a temporary success or error message at the top of the page
function showInteractionMessage(message, type = "success") {
    const wrap = document.getElementById("interactionMessageWrap");
    const box = document.getElementById("interactionMessageBox");

    if (!wrap || !box) return;

    box.textContent = message;
    wrap.style.display = "flex";

    // Changes the message styling depending on whether the interaction succeeded or failed
    if (type === "success") {
        box.style.backgroundColor = "green";
        box.style.border = "1px solid green";
    } else {
        box.style.backgroundColor = "#c0392b";
        box.style.border = "1px solid #c0392b";
    }

    // Clears any previous timeout so messages do not overlap
    clearTimeout(window.interactionMessageTimeout);

    // Hides the message automatically after a short delay
    window.interactionMessageTimeout = setTimeout(() => {
        wrap.style.display = "none";
        box.textContent = "";
    }, 3000);
}


// Updates the button states on recommendation and ranking cards after an interaction
function updateButtons(card, state) {
    const watchBtn = card.querySelector(".btn-watchlist");
    const likeBtn = card.querySelector(".btn-like");
    const dislikeBtn = card.querySelector(".btn-dislike");

    // Updates watchlist button text and style depending on current saved state
    if (watchBtn) {
        watchBtn.classList.toggle("active", state.watchlisted == 1);
        watchBtn.textContent = state.watchlisted == 1 ? "✓ Watchlisted" : "+ Watchlist";
    }

    // Updates like button styling
    if (likeBtn) {
        likeBtn.classList.toggle("active", state.liked == 1);
    }

    // Updates dislike button styling
    if (dislikeBtn) {
        dislikeBtn.classList.toggle("active", state.disliked == 1);
    }
}


// Updates anime rows on the account page after watched or remove interactions
function updateAccountRow(row, state, action) {
    const watchedBtn = row.querySelector(".watched-btn");

    // Toggles the watched button style if the anime has been marked as watched
    if (watchedBtn) {
        watchedBtn.classList.toggle("watched-active", state.watched == 1);
    }

    // Removes the row completely if the anime has been removed from that interaction category
    if (
        action === "remove_watchlist" ||
        action === "remove_like" ||
        action === "remove_dislike"
    ) {
        row.remove();
    }
}


// Handles all interaction button clicks across recommendation pages and the account page
document.addEventListener("click", async function (event) {
    const recButton = event.target.closest(".btn-watchlist, .btn-like, .btn-dislike");
    const accountWatchedBtn = event.target.closest(".watched-btn");
    const accountRemoveBtn = event.target.closest(".remove-btn");

    if (!recButton && !accountWatchedBtn && !accountRemoveBtn) return;

    let animeId = "";
    let action = "";
    let source = "";
    let row = null;
    let card = null;

    // Identifies interactions coming from recommendation or ranking cards
    if (recButton) {
        card = recButton.closest("[data-id]");
        if (!card) return;

        animeId = card.dataset.id;
        source = "recommendations";

        if (recButton.classList.contains("btn-watchlist")) action = "watchlist";
        if (recButton.classList.contains("btn-like")) action = "like";
        if (recButton.classList.contains("btn-dislike")) action = "dislike";
    }

    // Identifies watched toggle actions coming from the account page
    if (accountWatchedBtn) {
        row = accountWatchedBtn.closest(".anime-row");
        if (!row) return;

        animeId = row.dataset.id;
        action = "toggle_watched";
        source = "account";
    }

    // Identifies remove actions coming from the account page
    if (accountRemoveBtn) {
        row = accountRemoveBtn.closest(".anime-row");
        if (!row) return;

        animeId = row.dataset.id;
        action = accountRemoveBtn.dataset.action || "";
        source = "account";
    }

    if (!animeId || !action) return;

    const formData = new FormData();
    formData.append("anime_id", animeId);
    formData.append("action", action);
    formData.append("source", source);

    try {
        // Sends the interaction request to the backend without reloading the page
        const response = await fetch("interactions.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        // Handles failed interaction responses, including redirecting unauthenticated users
        if (!response.ok || !data.ok) {
            if (response.status === 401) {
                showInteractionMessage("Please log in first.", "error");
                setTimeout(() => {
                    window.location.href = "login.php";
                }, 1000);
                return;
            }

            showInteractionMessage(data.message || "Something went wrong.", "error");
            return;
        }

        // Updates recommendation or ranking buttons if interaction came from those pages
        if (source === "recommendations" && card) {
            updateButtons(card, data);
        }

        // Updates account page rows if interaction came from the account page
        if (source === "account" && row) {
            updateAccountRow(row, data, action);
        }

        // Shows success feedback after the interaction is saved
        showInteractionMessage(data.message || "Interaction saved.", "success");

    } catch (error) {
        // Displays an error message if the request cannot be completed
        showInteractionMessage("Unable to save interaction right now.", "error");
    }
});