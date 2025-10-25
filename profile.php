<?php
session_start();

// 📚 Chargement des classes nécessaires
require_once 'classes/Database.php';
require_once 'classes/User.php';

// 📚 CONCEPT : Vérification de connexion
// Si l'utilisateur n'est pas connecté, on le redirige vers login.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 📚 CONCEPT : Affichage d'un message de succès (Flash Message)
// Ce message s'affiche une seule fois puis disparaît
$messageSucces = '';
if (isset($_SESSION['message_succes'])) {
    $messageSucces = $_SESSION['message_succes'];
    unset($_SESSION['message_succes']); // suppression après affichage
}

// 📚 Chargement des questionnaires depuis la BDD
$pdo = Database::getConnexion();

// (exemple : si tu veux afficher les quiz disponibles)
$stmt = $pdo->query("SELECT * FROM questionnaires ORDER BY id ASC");
$questionnaires = $stmt->fetchAll();

// 📚 Récupération de l'utilisateur connecté
// On peut recréer un objet User à partir de son ID stocké en session
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$ligne = $stmt->fetch();

$user = new User(
    $ligne['id'],
    $ligne['pseudo'],
    $ligne['email']
);

// 📚 Récupération de l'historique de l'utilisateur
$historique = $user->getHistorique();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?= htmlspecialchars($user->getPseudo()) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php if ($messageSucces): ?>
        <p class="message-succes"><?= htmlspecialchars($messageSucces) ?></p>
    <?php endif; ?>

    <h1>👤 Profil de <?= htmlspecialchars($user->getPseudo()) ?></h1>

    <section>
        <h2>Informations personnelles</h2>
        <ul>
            <li><strong>Pseudo :</strong> <?php echo htmlspecialchars($user->getPseudo()) ?></li>
            <li><strong>Email :</strong> <?php echo htmlspecialchars($user->getEmail()) ?></li>
            <li><strong>created_ad :</strong> <?php echo htmlspecialchars($user->getSignInDate()) ?></li>
        </ul>
    </section>

    <section>
        <h2>🏆 données de jeu</h2>
        <ul>
            <li><strong>Nb parties jouées :</strong> <?php echo htmlspecialchars(count($historique)) ?></li>
        </ul>
    </section>

    <section>
        <h2>🧠 changer pseudo</h2>
        <ul>
            <?php
                $themes=[];
                foreach ($historique as $quiz){
                    if(!isset($themes[$quiz["title"]])){
                        $themes[$quiz["title"]]=1;
                    }else{
                        $themes[$quiz["title"]]+=1;
                    }
                }
                $themeKey="";
                foreach ($themes as $index=>$value){
                    if($themeKey==="" || $themes[$index]>=$themes[$themeKey]){
                        $themeKey=$index;
                    }
                }
                echo $themeKey;
            ?>
        </ul>
    </section>

    <p><a href="logout.php">Se déconnecter</a></p>
</body>
</html>
