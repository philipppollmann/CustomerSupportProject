<?php  
    require __DIR__ . '/dbConnection.php';

    createTable("CREATE TABLE Users (
        id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        password varchar(100) NOT NULL,
        isemployee BOOLEAN
    )");

    createTable("CREATE TABLE categorytable (
        id        int NOT NULL PRIMARY KEY AUTO_INCREMENT,
        category varchar(100)
    )");

    createTable("CREATE TABLE Posts(
        id        int NOT NULL PRIMARY KEY AUTO_INCREMENT,
        submitter varchar(100),
        category  varchar(100),
        title     varchar(100),
        question  varchar(100),
        answer    varchar(100),
        status    varchar(100),
        rating    varchar(100)
    );");
?>