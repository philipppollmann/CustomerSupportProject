<?php
    session_start();

    if(!isset($_SESSION["userid"]))
    {
       header("Location: login.php");
       die("Redirecting to login.php");
    }

    $userid = $_SESSION["userid"];
    $name = $_SESSION["name"];

    //Es wird grprüft, ob es sich um einen Mitarbeiter handelt wenn er keiner ist wird er rausgeschmissen
    $isemployee = $_SESSION["isemployee"];    
    if (!$isemployee) 
    {
        header("Location: CustomerPage.php");
        die("None of your business here, dude...");
    }

    //DB Connection wird aufgebaut
    require __DIR__ . '/dbConnection.php';
    $conn = newDBConn();

    //Uplopoad der Answer
    if (isset($_POST["answer"]) && isset($_POST["postid"]))
    {
        $postid = $_POST["postid"];
        $answer = $_POST["answer"];
        //Try to get the Table IDs        
        $stmt = $conn->prepare("UPDATE posts SET answer = :answer, status = :status WHERE id = :postid ;");
        $stmt->execute([':postid' => $postid, ':answer' => $answer, ':status' => 'vorgeschlagen']);

        // EMail versenden
        $stmt = $conn->prepare("SELECT u.name as username, p.question as question FROM posts p, users u where p.submitter = u.id and p.id = :postid ;");
        $stmt->execute([":postid" => $postid ]);
        $entries = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (count($entries) == 1) 
        {
            $question = $entries[0]->question;
            $username = $entries[0]->username;

            $notify_customer_email_text = htmlspecialchars('Lieber Herr '.$username.', zu Ihrem Problem "'.$question.'" schlagen wir Ihnen die Lösung "'.$answer.'" vor.');

            echo $notify_customer_email_text ;
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee page</title>
    <link href="css/CustomerPage.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <meta charset="UTF-8">
</head>
<body>
<!--Alert, dass eine Email versendert wurde-->
    <?php 
        if (isset($notify_customer_email_text))
        {
            print '<script>alert("'.$notify_customer_email_text.'")</script>';
        }
    ?>
    <div class="header">
        <h1>Fragen System</h1>
    </div>

    <h1>Hallo <?php print $name ?> Willkommen zurück! (UserID=<?php print $userid ?>)</h1>

<form name="filterTable" action="#" method="get" class="filter">
    <select name="categorydropdown">
        <option value="all" selected="selected">all</option>
        <?php
            $stmt = $conn->prepare("SELECT category FROM categorytable;");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($categories as $category)
            {
                echo "<option value='$category'>$category</option>";
            }
        ?>
    <input name="search" type="submit" value="Search"/>
</form>

<!--tabelle welche die unbeantworteten oder schlecht beantworteten Probleme der User zeigt-->
<h1>Tabelle</h1>
<div class="problemtable">
    <?php
        //Prüfung, ob category gesetzt ist
        $category = "all";
        if (isset($_GET["categorydropdown"]))
        {
            $category = $_GET["categorydropdown"];
        }
        //Wenn Kategorie gesetzt ist, filtere SQL nach Kategorie, ansonsten hole alle Posts
        if($category == "all")
        {
            $stmt = $conn->prepare("SELECT * FROM posts WHERE (status LIKE 'angefragt' OR status LIKE 'unerledigt') ;");
            $stmt->execute();
        }
        else
        {
            $stmt = $conn->prepare("SELECT * FROM posts WHERE (status LIKE 'angefragt' OR status LIKE 'unerledigt') AND category LIKE :category ;");
            $stmt->execute([":category" => $category]);
        }
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <table border="1">
        <tr class="tableheader">
            <th>PostID</th>
            <th>KundenID</th>
            <th>Category</th>
            <th>Problemtitel</th>
            <th>Problembeschreibung</th>
            <th>Antwort</th>
            <th>Status</th>
            <th>Bewertung</th>
            <th>Antworten</th>
        </tr>

        <!--Ausgabe der tatsächlichen Query werte-->
        <?php foreach ($records as $datensatz) { ?>
        <tr>
            <form method="post">
               <input type="hidden" name="postid" value="<?php print $datensatz["id"]; ?>">
               <td><?php print $datensatz["id"];        ?></td>
               <td><?php print $datensatz["submitter"]; ?></td>
               <td><?php print $datensatz["category"];  ?></td>
               <td><?php print $datensatz["title"];     ?></td>
               <td><?php print $datensatz["question"];  ?></td>
               <td><input type='text' name='answer'>      </td>
               <td><?php print $datensatz["status"];    ?></td>
               <td><?php print $datensatz["rating"];    ?></td>
               <td><input type='submit'>Submit</input></td>
            </form>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>