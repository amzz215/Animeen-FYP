<?php
session_start();
require_once __DIR__ . "/AnimeenDbConn.php";

if (!isset($_SESSION["uid"])) {
    $_SESSION["interaction_error"] = "You must be logged in to interact with anime.";
    http_response_code(401);
    echo json_encode(["ok" => false]);
    exit;
}

$uid = (int) $_SESSION["uid"];
$animeId = isset($_POST["anime_id"]) ? (int)$_POST["anime_id"] : 0;
$action = $_POST["action"] ?? "";

if ($animeId <= 0 || !in_array($action, ["watchlist","like","dislike"])) {
    $_SESSION["interaction_error"] = "Invalid interaction request.";
    echo json_encode(["ok" => false]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT liked, disliked, watchlisted
        FROM interactions
        WHERE uid=? AND anime_id=?
        LIMIT 1
    ");
    $stmt->execute([$uid,$animeId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $liked = $row["liked"] ?? 0;
    $disliked = $row["disliked"] ?? 0;
    $watchlisted = $row["watchlisted"] ?? 0;

    if ($action === "watchlist") {
        $watchlisted = $watchlisted ? 0 : 1;
    }

    if ($action === "like") {
        $liked = $liked ? 0 : 1;
        if ($liked) $disliked = 0;
    }

    if ($action === "dislike") {
        $disliked = $disliked ? 0 : 1;
        if ($disliked) $liked = 0;
    }

    $stmt = $pdo->prepare("
        INSERT INTO interactions (uid,anime_id,liked,disliked,watchlisted)
        VALUES (?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
        liked=VALUES(liked),
        disliked=VALUES(disliked),
        watchlisted=VALUES(watchlisted)
    ");

    $stmt->execute([$uid,$animeId,$liked,$disliked,$watchlisted]);

    $_SESSION["interaction_success"] = "Interaction saved.";

    echo json_encode([
        "ok"=>true,
        "liked"=>$liked,
        "disliked"=>$disliked,
        "watchlisted"=>$watchlisted
    ]);

} catch(PDOException $e){

    $_SESSION["interaction_error"] = "Something went wrong. Please try again.";
    echo json_encode(["ok"=>false]);
}