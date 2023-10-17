<!DOCTYPE html>
<html>
<head>
    <link href="css/style.css" rel="stylesheet">
    <title>GSUS - Help Desk | Login</title>
</head>
<body>
<!-- Header -->
    <div class="header">
        <h1>GSUS - Help Desk | Login</h1>
    </div>

    <!-- Fenster, wo Kunde die Daten einträgt -->
    <div class="fillcontent">
        <form method="post" action="#">
            <h2>Login</h2>
            <div class="textinput">
                <h3>Email:</h3>
                <input type="text" name="email">
                <h3>Password:</h3>
                <input type="password" name="password">
            </div>
            <input type="submit" value="Login" class="button"></br>
        </form>
    
        <a href="register.php" class="btn">Neuen Account anlegen: Registrieren</a>
    </div>
</body>
</html>

<?php
    if (isset($_POST["email"]) && isset($_POST["password"])) 
    {
        //neue Datenbank Connection wird aufgebaut
        require __DIR__ . '/dbConnection.php';
        $conn = newDBConn();

        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);
        session_start();

        //SQL Statement wird gebaut
        $stmt = $conn->prepare("SELECT id, name, isemployee FROM Users WHERE email LIKE :email AND password LIKE :password");
        //Werte werden in Statement eingesetzt
        $stmt->execute([
            ':email' => $email, 
            ':password' => $password
        ]);
        $entries = $stmt->fetchAll(PDO::FETCH_OBJ);
        //es werden sich die Nutzerdaten aus der Datenbank geholt
        if (count($entries) == 1) 
        {
            $_SESSION["userid"] = $entries[0]->id;
            $_SESSION["name"] = $entries[0]->name;
            $_SESSION["email"] = $entries[0]->email;
            $_SESSION["isemployee"] = $entries[0]->isemployee;
            //Wenn der Employee boolean nicht gesetzt ist wird auf Customerpage geleitet sonst auf die Employee Seite
            if ($entries[0]->isemployee == 0)
            {
                header("Location: CustomerPage.php");
            }
            else 
            {
                header("Location: EmployeePage.php");
            }
        }
    }
?>