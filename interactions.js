function showInteractionMessage(message, type = "success") {
    const wrap = document.getElementById("interactionMessageWrap");
    const box = document.getElementById("interactionMessageBox");

    if (!wrap || !box) return;

    box.textContent = message;
    wrap.style.display = "flex";

    if (type === "success") {
        box.style.backgroundColor = "green";
        box.style.border = "1px solid green";
    } else {
        box.style.backgroundColor = "#c0392b";
        box.style.border = "1px solid #c0392b";
    }

    clearTimeout(window.interactionMessageTimeout);

    window.interactionMessageTimeout = setTimeout(() => {
        wrap.style.display = "none";
        box.textContent = "";
    }, 3000);
}

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

function updateAccountRow(row, state, action) {
    const watchedBtn = row.querySelector(".watched-btn");

    if (watchedBtn) {
        watchedBtn.classList.toggle("watched-active", state.watched == 1);
    }

    if (
        action === "remove_watchlist" ||
        action === "remove_like" ||
        action === "remove_dislike"
    ) {
        row.remove();
    }
}

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

    if (recButton) {
        card = recButton.closest("[data-id]");
        if (!card) return;

        animeId = card.dataset.id;
        source = "recommendations";

        if (recButton.classList.contains("btn-watchlist")) action = "watchlist";
        if (recButton.classList.contains("btn-like")) action = "like";
        if (recButton.classList.contains("btn-dislike")) action = "dislike";
    }

    if (accountWatchedBtn) {
        row = accountWatchedBtn.closest(".anime-row");
        if (!row) return;

        animeId = row.dataset.id;
        action = "toggle_watched";
        source = "account";
    }

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
        const response = await fetch("interactions.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

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

        if (source === "recommendations" && card) {
            updateButtons(card, data);
        }

        if (source === "account" && row) {
            updateAccountRow(row, data, action);
        }

        showInteractionMessage(data.message || "Interaction saved.", "success");

    } catch (error) {
        showInteractionMessage("Unable to save interaction right now.", "error");
    }
});