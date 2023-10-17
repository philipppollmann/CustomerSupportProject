<!DOCTYPE html>
<html>
<head>
    <link href="css/style.css" rel="stylesheet">
    <title>GSUS - Help Desk | Registrieren</title>
</head>
<body>
<div class="header">
    <h1>GSUS - Help Desk | Registrieren</h1>
</div>
<!--Daten zum registrieren werden eingefragen-->
<div class="fillcontent">
    <form method="post" action="#" >
        <h2>Registrieren</h2>
        <div class="textinput">
            <h3>Name:</h3>
            <input type="text" name="name">
            <h3>Email:</h3>
            <input type="text" name="email">
            <h3>Password:</h3>
            <input type="password" name="password">
            <h3>Mitarbeiter?</h3>
            <input type="checkbox" name="isemployee">
        </div>
        <input type="submit" value="Registrieren" class="button">
        <a href="login.php" class="btn">Bereits einen Account? : Zur Login Seite</a>
    </form>
</div>
</body>
</html>

<?php
    // wird nur ausgeführt, wenn die Daten gesetzt sind
    if(isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["password"]))
    {
        //Verbindung zur Datenbank wird aufgebaut
        require __DIR__ . '/dbConnection.php';
        $conn = newDBConn();

        //Registerdaten werden aus HTML geholt
        $name = htmlspecialchars($_POST["name"]);
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);
        //Es wird geprüft, ob es sich um einen Mitarbeiter handelt oder nicht
        if (isset($_POST["isemployee"])) {
            $isemployee = 1;
        }
        else
        {
            $isemployee = 0;
        }

        //Es wird geprüft, ob der User bereits in der Datenbank vorhanden ist oder nicht
        $stmt = $conn->prepare("SELECT id FROM Users WHERE email LIKE :email");
        $stmt->execute([':email' => $email]);
        $entries = $stmt->fetchAll(PDO::FETCH_OBJ);
        // wenn mehr als 1 user zurück kommt existiert der User bereits
        if(count($entries) > 0) 
        {
            header("Location: user_exists.html");
        }
        else
        //ansonten wird der User in die DB geschrieben
        {
            //Wenn nicht vorhanden wird der User neu angelegt
            $stmt = $conn->prepare("INSERT INTO Users (name, email, password, isemployee) VALUES (:name, :email, :password, :isemployee)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $password,
                ':isemployee' => $isemployee
            ]);
            //User wird weitergeleitet auf die Login Seite, wenn der User erfolgreich gespeichert wurde
            header("Location: login.php");
        }
    }
?>