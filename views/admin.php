<?php
    if(!isset($_SESSION['loggedin'])){
        header('Location: /phpmotors/accounts/');
    }

    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
    }
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/phpmotors/css/style.css">
    <title>Document</title>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/phpmotors/snippets/header.php'?>
    <nav>
        <?php // require_once $_SERVER['DOCUMENT_ROOT'] . '/phpmotors/snippets/navigation.php';
        echo $navList 
        ?>
    </nav>
    <main>
        <h1><?php echo $_SESSION['clientData']['clientFirstname'].' '.$_SESSION['clientData']['clientLastname']; ?></h1>
        <p>You are logged in.</p>
        <?php
            if(isset($message)){
                echo $message;
            }
        ?>
        <ul>
            <li>First name: <?php echo $_SESSION['clientData']['clientFirstname']; ?></li>
            <li>Last name: <?php echo $_SESSION['clientData']['clientLastname']; ?></li>
            <li>Email: <?php echo $_SESSION['clientData']['clientEmail']; ?></li>
        </ul>
        <section>
            <h2>Account Management</h2>
            <p>Use this link to update account information</p>
            <p><a href="../accounts/?action=accountUpdate">Update Account Information</a></p>
        </section>
        <section>
        <?php
            $clientLevel = $_SESSION['clientData']['clientLevel'];
            if($clientLevel > 1){
                echo "<h2>Inventory Management</h2>
                <p>Use this link to manage the inventory.</p>
                <p><a href='../vehicles/'>Vehicle Management</a></p>";
            }
        ?>

<h2>Manage Your Product Reviews</h2>
            <?php  
            if (isset($reviewsDisplay)) {
                echo $reviewsDisplay;
               } ?>
        </section>
        
            
        
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/phpmotors/snippets/footer.php' ?>
    <script src="/phpmotors/js/motors.js"></script>
</body>
</html>
<?php unset($_SESSION['message']); ?>