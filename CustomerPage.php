<?php
    session_start();

    if(!isset($_SESSION["userid"]))
    {
       header("Location: login.php");
       die("Redirecting to login.php");
    }

    $userid = $_SESSION["userid"];
    $name = $_SESSION["name"];

    require __DIR__ . '/dbConnection.php';
    $conn = newDBConn();
    //Bewertung des Users wird in die Datenbank geschrieben
    if(isset($_POST["postid"]))
    {
        if (isset($_POST["rating"]) && $_POST["rating"] == "geholfen")
        {
            $status = "erledigt";
        }
        else
        {
            $status = "unerledigt";
        }
        
        $postid = $_POST["postid"];
        $rating = $_POST["rating"];

        $stmt = $conn->prepare("UPDATE posts SET rating = :rating, status = :status WHERE id = :postID ;");
        $stmt->execute([':rating' => $rating, ':status' => $status, ':postID' => $postid]);
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customerpage</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/CustomerPage.css" rel="stylesheet">
    <meta charset="UTF-8">
</head>

<body>
<div class="header">
    <h1>Fragen System</h1>
</div>


<h1>Hallo Nutzer "<?php print $name ?>" (UserID=<?php print $userid ?>)</h1>

<div class="newproblem">
    <form method="POST" action="postQuestion.php">
        <table border="1">
            <tr class="tableheader">
                <th>Titel</th>
                <th>Beschreibung</th>
                <th>Kategorie</th>
            </tr>
            <tr>
                <td>
                    <label for="title">Titel des Problems eingeben</label>
                    <input type="text" name="title">
                </td>
                <td>
                    <label for="question">Bitte beschreiben Sie ihr Problem</label>
                    <input type="text" name="question">
                </td>
                <td>
                    <label for="category">Wählen Sie ihre Kategorie</label>
                    <select name="category">
                        <?php
                            //Filter für Kategorien
                            $stmt = $conn->prepare("SELECT category FROM categorytable;");
                            $stmt->execute();
                            $categories  = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($categories as $category)
                            {
                                echo "<option value='$category'>$category</option>";
                            }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <input name="upload" type="submit" value="upload" class="beautofulbutton">
    </form>
</div>

<form name="filterTable" action="#" method="get" class="filter">
    <select name="viewcategory">
        <option value="all" selected="selected">all</option>
        <?php
           //Dropdown zum auswählen der Kategorie wird gebaut
            $stmt = $conn->prepare("SELECT category FROM categorytable;");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($categories as $category)
            {
                echo "<option value='$category'>$category</option>";
            }

        ?>
    </select>
    <input name="search" type="submit" value="Search"/>
</form>

<div class="problemtable">
    <table border="1">
        <?php
            //Prüfung, ob category gesetzt ist
            if(isset($_GET["viewcategory"]))
            {
                $category = $_GET["viewcategory"];
            }
            else 
            {
                $category = "all";
            }
            //Wenn Kategorie gesetzt ist, filtere SQL nach Kategorie, ansonsten hole alle Posts

            if($category == "all")
            {
                $stmt = $conn->prepare("SELECT * FROM posts WHERE status = :status ;");
                $stmt->execute([ ":status" => "erledigt" ]);
            }
            else
            {
                $stmt = $conn->prepare("SELECT * FROM posts WHERE status = :status AND category = :category ;");
                $stmt->execute([":status" => "erledigt", ":category" => $category]);
            }
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        ?>

        <!--Header der Tabelle (Überschriften)-->
        <tr class="tableheader">
            <th>Category</th>
            <th>Problemtitel</th>
            <th>Problembeschreibung</th>
            <th>Antwort</th>
            <th>Status</th>
            <th>Bewertung</th>
        </tr>

        <!--Ausgabe aller werte der SQL Query-->
        <?php foreach ($records as $record) { ?>
        <tr>
            <!-- Ausgaben von allen Datensätzen, welche erledigt oder richtig sind -->
            <td><?php print $record["category"];  ?></td>
            <td><?php print $record["title"]      ?></td>
            <td><?php print $record["question"]   ?></td>
            <td><?php print $record["answer"]     ?></td>
            <td><?php print $record["status"];    ?></td>
            <td><?php print $record["rating"];    ?></td>
        </tr>
        <?php  } ?>
    </table>
</div>

<!-- Tabelle der eigenen Fragen kann gesehen werden und Antworten können bewertet werden -->
<div class="myproblmtable">
    <h1>Ihre beantworteten Fragen</h1>
    <table border="1">    
        <tr class="tableheader">
            <th>PostID</th>
            <th>KundenID</th>
            <th>Category</th>
            <th>Problemtitel</th>
            <th>Problembeschreibung</th>
            <th>Antwort</th>
            <th>Status</th>
            <th>Beitrag bewerten</th>
            <th>Bewertung senden</th>
        </tr>

        <?php
            $userid = $_SESSION["userid"];
            $stmt = $conn->prepare("SELECT * FROM posts WHERE status = :status AND submitter = :submitter ;");
            $stmt->execute([":status" => "vorgeschlagen", ":submitter" => $userid ]);
            $myproblemresult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <!--Ausgabe der Beiträge, welche bewertet werden können!-->
        <?php foreach ($myproblemresult as $record) { //Datensätze als Tabelle ausgeben ?> 
        <tr>
        <form method='post' action="CustomerPage.php">
            <!-- Ausgabe der eigene gestellten Probleme -->
            <input type="hidden" name="postid" value="<?php echo $record ["id"]; ?>">
            <td><?php print $record["id"];        ?></td>
            <td><?php print $record["submitter"]; ?></td>
            <td><?php print $record["category"];  ?></td>
            <td><?php print $record["title"];     ?></td>
            <td><?php print $record["question"];  ?></td>
            <td><?php print $record["answer"];    ?></td>
            <td><?php print $record["status"];    ?></td>
            <td>
                <!--Bewertung der User-->
                <select name='rating'>
                    <option name='rating' value='empty'>bitte wählen Sie eine Bewertung</option>
                    <option name='rating' value='geholfen'>hat geholfen</option>
                    <option name='rating' value='nichtgeholfen'>hat nicht geholfen</option>
                </select>
            </td>
            <td><input type='submit' value='senden' name='sendevaluation'/></td>
        </form>
        </tr>
        <?php  } ?>
    </table>
</div>
</body>
</html>