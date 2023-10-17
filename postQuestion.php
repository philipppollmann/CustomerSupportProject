<?php
    session_start();
    //Werte werden aus HTML geholt
    $submitter = $_SESSION["userid"];
    $category = htmlspecialchars($_POST["category"]);
    $title = htmlspecialchars($_POST["title"]);
    $question = htmlspecialchars($_POST["question"]);
    $status = "angefragt";

    require __DIR__ . '/dbConnection.php';
    $conn = newDBConn();

    //SQL Statement wird gebaut
    $stmt = $conn->prepare("
        INSERT INTO posts (submitter, category, title, question, status) 
        VALUES (:submitter, :category, :title, :question, :status);");

    //werte werden in SQL statement eingesetzt
    $stmt->execute([
        ':submitter' => $submitter,
        ':category' => $category,
        ':title' => $title,
        ':question' => $question,
        ':status' => $status
    ]);
    
    header("Location: " . $_SERVER['HTTP_REFERER']);
?>