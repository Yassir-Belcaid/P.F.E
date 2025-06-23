<?php
include 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Participant") {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de Bord Joueur</title>
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
        .welcome-msg {
            margin-bottom: 30px;
            color: #7f8c8d;
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
            padding: 12px;
            transition: all 0.3s;
            border-radius: 4px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .menu-link:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Tableau de Bord Joueur</h1>
        <div class="welcome-msg">Bienvenue <?= $_SESSION["prenom"] ?></div>
        
        <div class="menu-links">
            <a href="find_tournoi.php" class="menu-link">Trouver un Tournoi</a>
            <a href="profile.php" class="menu-link">Modifier Mon Profil</a>
        </div>
    </div>
</body>
</html>