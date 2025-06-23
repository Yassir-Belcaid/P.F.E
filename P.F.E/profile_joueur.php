<?php
include 'config.php';


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: find_tournoi.php"); 
    exit();
}

$joueur_id = $_GET['id'];


$sql = "SELECT id_utilisateur, prenom, nom, type, photo, genre FROM Utilisateur WHERE id_utilisateur = ? AND type = 'Participant'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $joueur_id);
$stmt->execute();
$result = $stmt->get_result();
$joueur = $result->fetch_assoc();

if (!$joueur) {
    echo "Joueur non trouvé.";
    exit();
}


$sql_wins = "SELECT COUNT(t.id_tournoi) as wins 
             FROM Tournoi t
             JOIN Participation p ON t.id_equipe_gagnante = p.id_equipe
             WHERE p.id_utilisateur = ? AND t.id_equipe_gagnante IS NOT NULL";
$stmt_wins = $conn->prepare($sql_wins);
$stmt_wins->bind_param("i", $joueur_id);
$stmt_wins->execute();
$wins_result = $stmt_wins->get_result()->fetch_assoc();
$win_count = $wins_result['wins'] ?? 0;


$final_count = "N/A"; 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil de <?= htmlspecialchars($joueur['prenom']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #2ecc71;
            margin-right: 20px;
        }
        .profile-info h1 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .profile-info p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: center;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            max-width: 200px;
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #2ecc71;
        }
        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-link">← Retour</a>
        
        <div class="profile-header">
            <img src="<?= $joueur['photo'] ? $joueur['photo'] : 'uploads/default-avatar.png' ?>" 
                 alt="Photo de profil" class="profile-photo">
            <div class="profile-info">
                <h1><?= htmlspecialchars($joueur['prenom']) ?> <?= htmlspecialchars($joueur['nom']) ?></h1>
                <p><strong>Type:</strong> <?= $joueur['type'] ?></p>
                <p><strong>Genre:</strong> <?= $joueur['genre'] === 'Masculin' ? 'Masculin' : 'Féminin' ?></p>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $win_count ?></div>
                <div class="stat-label">Victoires</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $final_count ?></div>
                <div class="stat-label">Finales Atteintes</div>
            </div>
        </div>

    </div>
</body>
</html> 