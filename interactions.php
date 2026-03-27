<?php
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

header("Content-Type: application/json");

if (!isset($_SESSION["uid"])) {
    http_response_code(401);
    echo json_encode([
        "ok" => false,
        "message" => "You must be logged in to interact with anime."
    ]);
    exit;
}

$uid = (int)$_SESSION["uid"];
$animeId = isset($_POST["anime_id"]) ? (int)$_POST["anime_id"] : 0;
$action = $_POST["action"] ?? "";
$source = $_POST["source"] ?? "";

$allowedActions = [
    "watchlist",
    "like",
    "dislike",
    "toggle_watched",
    "remove_watchlist",
    "remove_like",
    "remove_dislike"
];

if ($animeId <= 0 || !in_array($action, $allowedActions, true)) {
    echo json_encode([
        "ok" => false,
        "message" => "Invalid interaction request."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT liked, disliked, watchlisted, watched
        FROM interactions
        WHERE uid = ? AND anime_id = ?
        LIMIT 1
    ");
    $stmt->execute([$uid, $animeId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $liked = (int)($row["liked"] ?? 0);
    $disliked = (int)($row["disliked"] ?? 0);
    $watchlisted = (int)($row["watchlisted"] ?? 0);
    $watched = (int)($row["watched"] ?? 0);

    if (!$row && in_array($action, ["remove_watchlist", "remove_like", "remove_dislike", "toggle_watched"], true)) {
        echo json_encode([
            "ok" => false,
            "message" => "Interaction not found."
        ]);
        exit;
    }

    $message = "Interaction saved.";

    if ($action === "watchlist") {
        $watchlisted = $watchlisted ? 0 : 1;
        $message = $watchlisted ? "Added to watchlist." : "Removed from watchlist.";
    }

    if ($action === "like") {
        $liked = $liked ? 0 : 1;
        if ($liked) {
            $disliked = 0;
            $message = "Added to liked.";
        } else {
            $message = "Removed from liked.";
        }
    }

    if ($action === "dislike") {
        $disliked = $disliked ? 0 : 1;
        if ($disliked) {
            $liked = 0;
            $message = "Added to disliked.";
        } else {
            $message = "Removed from disliked.";
        }
    }

    if ($action === "toggle_watched") {
        $watched = $watched ? 0 : 1;
        $message = $watched ? "Watched." : "Removed from watched.";
    }

    if ($action === "remove_watchlist") {
        $watchlisted = 0;
        $message = "Removed from watchlist.";
    }

    if ($action === "remove_like") {
        $liked = 0;
        $message = "Removed from liked.";
    }

    if ($action === "remove_dislike") {
        $disliked = 0;
        $message = "Removed from disliked.";
    }

    if ($liked === 0 && $disliked === 0 && $watchlisted === 0 && $watched === 0) {
        $stmt = $pdo->prepare("
            DELETE FROM interactions
            WHERE uid = ? AND anime_id = ?
        ");
        $stmt->execute([$uid, $animeId]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO interactions (uid, anime_id, liked, disliked, watchlisted, watched)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                liked = VALUES(liked),
                disliked = VALUES(disliked),
                watchlisted = VALUES(watchlisted),
                watched = VALUES(watched)
        ");
        $stmt->execute([$uid, $animeId, $liked, $disliked, $watchlisted, $watched]);
    }

    echo json_encode([
        "ok" => true,
        "message" => $message,
        "liked" => $liked,
        "disliked" => $disliked,
        "watchlisted" => $watchlisted,
        "watched" => $watched,
        "source" => $source
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "ok" => false,
        "message" => "Something went wrong. Please try again."
    ]);
}