<?php
include 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Organisateur") {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de Bord Organisateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .dashboard-container {
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 40px;
            font-size: 24px;
        }
        .menu-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .menu-link {
            color: #2c3e50;
            text-decoration: none;
            font-size: 18px;
            padding: 10px;
            transition: all 0.3s;
            border-radius: 4px;
        }
        .menu-link:hover {
            background-color: #f0f0f0;
            color: #e74c3c;
        }
        .welcome-msg {
            margin-bottom: 30px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Tableau de Bord Organisateur</h1>
        <div class="welcome-msg">Bonjour <?= $_SESSION["prenom"] ?></div>
        
        <div class="menu-links">
            <a href="profile.php" class="menu-link">Modifier Mon Profil</a>
            <a href="creer_tournoi.php" class="menu-link">Créer un Tournoi</a>
            <a href="mes_tournois.php" class="menu-link">Mes Tournois Organisés</a>
        </div>
    </div>
</body>
</html>