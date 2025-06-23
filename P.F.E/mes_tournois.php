<?php
include 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Organisateur") {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM Tournoi WHERE id_organisateur = ? ORDER BY date_heure DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$tournois = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mes tournois organisés</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .tournoi-card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px; }
        .tournoi-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .tournoi-info { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
        .info-item { background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-size: 14px; }
        .no-tournois { text-align: center; color: #777; padding: 20px; }
        .btn { background: #3498db; color: white; padding: 8px 12px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn-danger { background: #e74c3c; }
        .actions { margin-top: 10px; display: flex; gap: 10px; }
        .back-link { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mes tournois organisés</h1>
        
        <?php if (isset($_GET["success"])): ?>
            <div style="color: green; text-align: center; margin-bottom: 15px;">
                Tournoi créé avec succès!
            </div>
        <?php endif; ?>
        
        <?php if ($tournois->num_rows > 0): ?>
            <?php while($tournoi = $tournois->fetch_assoc()): ?>
                <div class="tournoi-card">
                    <div class="tournoi-title"><?= $tournoi["nom"] ?></div>
                    <div class="tournoi-info">
                        <span class="info-item">Lieu: <?= $tournoi["lieu"] ?></span>
                        <span class="info-item">Date: <?= date("d/m/Y", strtotime($tournoi["date_heure"])) ?></span>
                        <span class="info-item">Heure: <?= date("H:i", strtotime($tournoi["date_heure"])) ?></span>
                        <span class="info-item">Équipes: <?= $tournoi["nombre_equipes"] ?></span>
                        <span class="info-item">Joueurs/équipe: <?= $tournoi["nombre_joueurs_par_equipe"] ?></span>
                        <span class="info-item">Genre: <?= $tournoi["genre_participants"] ?></span>
                        <span class="info-item">Frais: <?= $tournoi["participation_fee"] ?> DH</span>
                    </div>
                    <div class="actions">
                        <a href="#" class="btn">Modifier</a>
                        <a href="#" class="btn btn-danger">Supprimer</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-tournois">
                Vous n'avez pas encore créé de tournois.
                <br>
                <a href="creer_tournoi.php" class="btn" style="margin-top: 10px;">Créer un tournoi</a>
            </div>
        <?php endif; ?>
        
        <a href="dashboard_org.php" class="back-link">← Retour au tableau de bord</a>
    </div>
</body>
</html>