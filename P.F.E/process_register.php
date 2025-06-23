<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["mot_de_passe"] !== $_POST["confirm_password"]) {
        die("Les mots de passe ne correspondent pas!");
    }

    $photoPath = null;
    if (!empty($_FILES["photo"]["name"])) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $photoPath = $targetDir . uniqid() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath);
    }

    $hashedPassword = password_hash($_POST["mot_de_passe"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO Utilisateur (prenom, nom, email, mot_de_passe, photo, type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", 
        $_POST["prenom"],
        $_POST["nom"],
        $_POST["email"],
        $hashedPassword,
        $photoPath,
        $_POST["type"]
    );
    
    if ($stmt->execute()) {
        header("Location: login.php?registered=1");
    } else {
        echo "Erreur: " . $conn->error;
    }
}
?>