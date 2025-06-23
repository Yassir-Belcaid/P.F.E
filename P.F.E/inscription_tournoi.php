<?php
include 'config.php';

$error = null;

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Participant") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET['id'])) {
    header("Location: find_tournoi.php");
    exit();
}
$tournoi_id = $_GET['id'];

if (isset($_GET['action']) && $_GET['action'] == 'get_players') {
    if (!isset($_GET['equipe_id'])) {
        echo json_encode(['error' => 'Team ID not provided']);
        exit();
    }
    $equipe_id_ajax = $_GET['equipe_id'];
    
    $sql_players = "SELECT u.id_utilisateur, u.prenom, u.nom FROM Participation p
                    JOIN Utilisateur u ON p.id_utilisateur = u.id_utilisateur
                    WHERE p.id_equipe = ?";
    $stmt_players = $conn->prepare($sql_players);
    $stmt_players->bind_param("i", $equipe_id_ajax);
    $stmt_players->execute();
    $players_result = $stmt_players->get_result();
    $players = $players_result->fetch_all(MYSQLI_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($players);
    exit();
}

$sql_tournoi = "SELECT t.*, u.prenom as org_prenom, u.nom as org_nom
                FROM Tournoi t
                JOIN Utilisateur u ON t.id_organisateur = u.id_utilisateur
                WHERE t.id_tournoi = ?";
$stmt_tournoi = $conn->prepare($sql_tournoi);
$stmt_tournoi->bind_param("i", $tournoi_id);
$stmt_tournoi->execute();
$tournoi = $stmt_tournoi->get_result()->fetch_assoc();

if (!$tournoi) {
    header("Location: find_tournoi.php");
    exit();
}

$userGenderSql = "SELECT genre FROM Utilisateur WHERE id_utilisateur = ?";
$userGenderStmt = $conn->prepare($userGenderSql);
$userGenderStmt->bind_param("i", $user_id);
$userGenderStmt->execute();
$userGender = $userGenderStmt->get_result()->fetch_assoc()['genre'];

if ($tournoi['genre_participants'] !== $userGender) {
    header("Location: find_tournoi.php?error=gender_mismatch");
    exit();
}

$sql_equipes = "SELECT e.id_equipe, e.nom, COUNT(p.id_participation) as nb_joueurs
                FROM Equipe e
                LEFT JOIN Participation p ON e.id_equipe = p.id_equipe
                WHERE e.id_tournoi = ?
                GROUP BY e.id_equipe";
$stmt_equipes = $conn->prepare($sql_equipes);
$stmt_equipes->bind_param("i", $tournoi_id);
$stmt_equipes->execute();
$equipes = $stmt_equipes->get_result()->fetch_all(MYSQLI_ASSOC);

$sql_check = "SELECT p.id_participation FROM Participation p
              JOIN Equipe e ON p.id_equipe = e.id_equipe
              WHERE p.id_utilisateur = ? AND e.id_tournoi = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $tournoi_id);
$stmt_check->execute();
$deja_inscrit = $stmt_check->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$deja_inscrit) {
    if (!isset($_POST["equipe_id"])) {
        $error = "Veuillez sélectionner une équipe.";
    } else {
        $equipe_id = $_POST["equipe_id"];
        
        $sql_check_equipe = "SELECT COUNT(id_participation) as count FROM Participation WHERE id_equipe = ?";
        $stmt_check_equipe = $conn->prepare($sql_check_equipe);
        $stmt_check_equipe->bind_param("i", $equipe_id);
        $stmt_check_equipe->execute();
        $nb_joueurs_actuel = $stmt_check_equipe->get_result()->fetch_assoc()['count'];

        if ($nb_joueurs_actuel < $tournoi['nombre_joueurs_par_equipe']) {
            $sql_inscription = "INSERT INTO Participation (id_utilisateur, id_equipe) VALUES (?, ?)";
            $stmt_inscription = $conn->prepare($sql_inscription);
            $stmt_inscription->bind_param("ii", $user_id, $equipe_id);
            
            if ($stmt_inscription->execute()) {
                header("Location: inscription_tournoi.php?id=$tournoi_id&join_success=1");
                exit();
            } else {
                $error = "Erreur lors de l'inscription: " . $conn->error;
            }
        } else {
            $error = "Cette équipe est déjà complète!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription au tournoi: <?= htmlspecialchars($tournoi['nom']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #1a1a1a;
            color: #fff;
            margin: 0;
            overflow-x: hidden;
        }

        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.2;
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .back-link {
            color: #3498db;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }
        
        .error, .success {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin: 0 auto 20px auto;
            max-width: 600px;
        }
        .error { color: #fff; background-color: #e74c3c; }
        .success { color: #fff; background-color: #2ecc71; }

        #teams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            transition: opacity 0.5s;
        }

        .team-card {
            background-color: rgba(44, 62, 80, 0.8);
            border: 1px solid #34495e;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .team-card:not(.full):hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }

        .team-card.full {
            background-color: rgba(127, 140, 141, 0.5);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .team-card h3 {
            margin: 0;
            font-size: 1.8em;
            text-transform: uppercase;
        }

        .team-card .players-count {
            font-size: 2em;
            font-weight: bold;
            margin-top: 10px;
        }

        .team-card .full-text {
            font-size: 2em;
            font-weight: bold;
            color: #e74c3c;
        }

        #player-list-view {
            display: none;
            padding: 30px;
            background-color: rgba(44, 62, 80, 0.9);
            border-radius: 15px;
            text-align: center;
        }
        
        #player-list-view h2 {
            font-size: 2em;
            margin-bottom: 20px;
        }

        #player-list-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .player-slot {
            background-color: #1a1a1a;
            border-left: 5px solid #3498db;
            padding: 15px;
            border-radius: 5px;
            font-size: 1.1em;
            text-align: left;
        }

        .player-slot.empty {
            border-left-color: #2ecc71;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .player-slot.empty:hover {
            background-color: #27ae60;
        }

        .plus-icon {
            font-size: 2em;
            font-weight: bold;
        }
        
        #back-to-teams {
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #e74c3c;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <img src="uploads\6851c38a9ce86_78b73ac6-6dd2-49e0-acc5-35748806a600.jpg" class="background-image" alt="Football stadium">

    <div class="container">
        <a href="find_tournoi.php" class="back-link">← Retour aux tournois</a>
        <h1><?= htmlspecialchars($tournoi['nom']) ?></h1>

        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        
        <?php if ($deja_inscrit) echo "<div class='success'>Vous êtes déjà inscrit dans une équipe pour ce tournoi.</div>"; ?>

        <?php if (!empty($equipes)): ?>
            <div id="teams-grid">
                <?php foreach ($equipes as $equipe): 
                    $is_full = $equipe['nb_joueurs'] >= $tournoi['nombre_joueurs_par_equipe'];
                ?>
                    <div class="team-card <?= $is_full ? 'full' : '' ?>" 
                         data-equipe-id="<?= $equipe['id_equipe'] ?>"
                         data-equipe-nom="<?= htmlspecialchars($equipe['nom']) ?>"
                         data-max-joueurs="<?= $tournoi['nombre_joueurs_par_equipe'] ?>">
                        
                        <h3><?= htmlspecialchars($equipe['nom']) ?></h3>
                        <?php if ($is_full): ?>
                            <div class="full-text">FULL</div>
                        <?php else: ?>
                            <div class="players-count"><?= $equipe['nb_joueurs'] ?>/<?= $tournoi['nombre_joueurs_par_equipe'] ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="player-list-view">
                <h2 id="player-list-title"></h2>
                <div id="player-list-container"></div>
                <button id="back-to-teams">Retour aux équipes</button>
            </div>
            
            <form id="join-team-form" method="POST" action="inscription_tournoi.php?id=<?= $tournoi_id ?>" style="display: none;">
                <input type="hidden" name="equipe_id" id="equipe-id-input">
            </form>
        <?php else: ?>
            <p style="text-align: center;">Aucune équipe n'a été créée pour ce tournoi pour le moment.</p>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const teamsGrid = document.getElementById('teams-grid');
        const playerListView = document.getElementById('player-list-view');
        const playerListTitle = document.getElementById('player-list-title');
        const playerListContainer = document.getElementById('player-list-container');
        const backToTeamsBtn = document.getElementById('back-to-teams');
        const joinTeamForm = document.getElementById('join-team-form');
        const equipeIdInput = document.getElementById('equipe-id-input');

        const isAlreadyRegistered = <?= $deja_inscrit ? 'true' : 'false' ?>;

        teamsGrid.addEventListener('click', function(e) {
            const card = e.target.closest('.team-card');
            
            if (card) {
                const equipeId = card.dataset.equipeId;
                const equipeNom = card.dataset.equipeNom;
                const maxJoueurs = parseInt(card.dataset.maxJoueurs);
                
                playerListTitle.textContent = equipeNom;
                
                fetch(`inscription_tournoi.php?action=get_players&id=<?= $tournoi_id ?>&equipe_id=${equipeId}`)
                    .then(response => response.json())
                    .then(players => {
                        playerListContainer.innerHTML = '';
                        
                        players.forEach(player => {
                            const playerDiv = document.createElement('div');
                            playerDiv.className = 'player-slot';
                            
                            const playerLink = document.createElement('a');
                            playerLink.href = `profile_joueur.php?id=${player.id_utilisateur}`;
                            playerLink.textContent = `${player.prenom} ${player.nom}`;
                            playerLink.style.textDecoration = 'none';
                            playerLink.style.color = '#fff';

                            playerDiv.appendChild(playerLink);
                            playerListContainer.appendChild(playerDiv);
                        });
                        
                        const emptySlots = maxJoueurs - players.length;
                        if (!card.classList.contains('full') && !isAlreadyRegistered) {
                            for (let i = 0; i < emptySlots; i++) {
                                const emptyDiv = document.createElement('div');
                                emptyDiv.className = 'player-slot empty';
                                emptyDiv.innerHTML = '<span class="plus-icon">+</span>';
                                emptyDiv.onclick = function() {
                                    if (confirm(`Voulez-vous vraiment rejoindre l'équipe ${equipeNom}?`)) {
                                        equipeIdInput.value = equipeId;
                                        joinTeamForm.submit();
                                    }
                                };
                                playerListContainer.appendChild(emptyDiv);
                            }
                        }

                        teamsGrid.style.display = 'none';
                        playerListView.style.display = 'block';
                    })
                    .catch(error => console.error('Error fetching players:', error));
            }
        });

        backToTeamsBtn.addEventListener('click', function() {
            playerListView.style.display = 'none';
            teamsGrid.style.display = 'grid';
        });
    });
    </script>
</body>
</html>