<?php
session_start();
include('../config/dbcon.php');

if (!isset($_SESSION['auth_user']['account_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

$account_id = $_SESSION['auth_user']['account_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'apply_as_artist') {
        // Do NOT update the database yet
        echo json_encode([
            "success" => true,
            "message" => "Redirecting to subscription plan...",
            "redirect" => "plan_selection.php"
        ]);
        exit;
    }
}

    if ($_POST['action'] === 'change_password') {
        $oldPass = $_POST['oldPass'] ?? '';
        $newPass = $_POST['newPass'] ?? '';

        $stmt = $con->prepare("SELECT password FROM accounts WHERE account_id=?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!password_verify($oldPass, $result['password'])) {
            echo json_encode(["success" => false, "message" => "Old password incorrect."]);
            exit;
        }

        $hashed = password_hash($newPass, PASSWORD_BCRYPT);
        $update = $con->prepare("UPDATE accounts SET password=? WHERE account_id=?");
        $update->bind_param("si", $hashed, $account_id);
        if ($update->execute()) {
            echo json_encode(["success" => true, "message" => "Password updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update password."]);
        }
        exit;
    }
