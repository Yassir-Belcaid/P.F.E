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

$organisateur_id = $_GET['id'];

$sql = "SELECT id_utilisateur, prenom, nom, type, photo, genre FROM Utilisateur WHERE id_utilisateur = ? AND type = 'Organisateur'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organisateur_id);
$stmt->execute();
$result = $stmt->get_result();
$organisateur = $result->fetch_assoc();

if (!$organisateur) {
    header("Location: find_tournoi.php");
    exit();
}

$tournois_sql = "SELECT * FROM Tournoi WHERE id_organisateur = ? ORDER BY date_heure DESC";
$tournois_stmt = $conn->prepare($tournois_sql);
$tournois_stmt->bind_param("i", $organisateur_id);
$tournois_stmt->execute();
$tournois_result = $tournois_stmt->get_result();
$tournois = $tournois_result->fetch_all(MYSQLI_ASSOC);

$total_tournois = count($tournois);
$upcoming_tournois = 0;
$past_tournois = 0;

foreach ($tournois as $tournoi) {
    if (strtotime($tournoi['date_heure']) > time()) {
        $upcoming_tournois++;
    } else {
        $past_tournois++;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil de l'Organisateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
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
            border: 4px solid #3498db;
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
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }
        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
        }
        .tournois-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .tournoi-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .tournoi-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .tournoi-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .tournoi-info {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .info-item {
            background: #e8f4fd;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-upcoming {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-past {
            background: #ffebee;
            color: #c62828;
        }
        .no-tournois {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="find_tournoi.php" class="back-link">← Retour aux tournois</a>
        
        <div class="profile-header">
            <img src="<?= $organisateur['photo'] ? $organisateur['photo'] : 'uploads/default-avatar.png' ?>" 
                 alt="Photo de profil" class="profile-photo">
            <div class="profile-info">
                <h1><?= htmlspecialchars($organisateur['prenom']) ?> <?= htmlspecialchars($organisateur['nom']) ?></h1>
                <p><strong>Type:</strong> <?= $organisateur['type'] ?></p>
                <?php if ($organisateur['genre']): ?>
                    <p><strong>Genre:</strong> <?= $organisateur['genre'] === 'Masculin' ? 'Masculin' : 'Féminin' ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total_tournois ?></div>
                <div class="stat-label">Total Tournois</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $upcoming_tournois ?></div>
                <div class="stat-label">Tournois à venir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $past_tournois ?></div>
                <div class="stat-label">Tournois passés</div>
            </div>
        </div>
        
        <div class="tournois-section">
            <h2>Tournois organisés</h2>
            
            <?php if (empty($tournois)): ?>
                <div class="no-tournois">
                    <p>Aucun tournoi organisé pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tournois as $tournoi): ?>
                    <div class="tournoi-card">
                        <div class="tournoi-title"><?= htmlspecialchars($tournoi['nom']) ?></div>
                        <div class="tournoi-info">
                            <span class="info-item">Lieu: <?= htmlspecialchars($tournoi['lieu']) ?></span>
                            <span class="info-item">Date: <?= date('d/m/Y', strtotime($tournoi['date_heure'])) ?></span>
                            <span class="info-item">Heure: <?= date('H:i', strtotime($tournoi['date_heure'])) ?></span>
                            <span class="info-item">Équipes: <?= $tournoi['nombre_equipes'] ?></span>
                            <span class="info-item">Joueurs: <?= $tournoi['nombre_joueurs_par_equipe'] ?></span>
                            <span class="info-item">Genre: <?= $tournoi['genre_participants'] === 'Masculin' ? 'Masculin' : 'Féminin' ?></span>
                            <span class="info-item">Frais: <?= $tournoi['participation_fee'] ?> DH</span>
                            <?php if (strtotime($tournoi['date_heure']) > time()): ?>
                                <span class="status-badge status-upcoming">À venir</span>
                            <?php else: ?>
                                <span class="status-badge status-past">Terminé</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 