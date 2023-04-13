<?php

/**
 * Méthode système de réduction de la taille des images
 *
 * @param $dir
 * @return void
 */
function reduceFileSize ( $dir ): void
{
    // Choix de la nouvelle taille
    // https://imagemagick.org/script/command-line-processing.php#geometry
    $resolution = "20%";

    // Vérification si le dossier existe, sinon erreur
    if ( is_dir( $dir ) )
    {
        // https://imagemagick.org/script/mogrify.php
        $command = 'find ' . $dir . ' -type f -exec mogrify -resize ' . $resolution . ' {} \;';
        // mogrify -path ' . $dir . '/reduce -resize 1920x1080 {}"

        // Affichage
        displayMessage ( "Réduction des images du dossier", 32, ['top', 'bottom'] );
        displayMessage ( $command, 31, ['bottom'] );

        // On échappe la commande pour éviter les caractères non prévus
        //$escaped_command = escapeshellcmd($command);
        // exec — Exécute un programme externe
        // system — Exécute un programme externe et affiche le résultat
        // shell_exec — Exécute une commande via le Shell et retourne le résultat sous forme de chaîne
        exec( $command );
    }

} #reduceFileSize ( $dir )


/**
 * À partir d'un dossier $folder, les images sont compilées en un fichier GIF avec le nom $filename
 *
 * @param $folder
 * @param $filename
 * @return string
 */
function makeAGif ( $folder, $filename ): string
{
    // Vérification si le dossier existe, sinon, on le crée
    if (!is_dir( "./gif/" . $filename ))
    {
        mkdir( "./gif/" . $filename );
    }

    // Reduce pics
    // ******************

    // Progression
    $progressOutput = getProgressString('reduceFileSize');
    writeProgressToFile( sprintf($progressOutput, 100, 0) );
    // Log
    error_log("Réduction des images\n", 3, LOG_FILE);
    // #Progression

    displayMessage("Réduction de la taille des images", 32, ['top', 'bottom']);
    reduceFileSize( $folder );
    displayMessage("--> OK", 32, ['none']);

    // Progression
    writeProgressToFile( sprintf($progressOutput, 100, 100) );
    // Log
    error_log("Réduction des images OK\n", 3, LOG_FILE);
    // #Progression

    /*
        Les images doivent être en png
    */
    $command = "convert -delay 100 -loop 0 " . $folder . "/*.png ./gif/" . $filename . "/" . $filename . ".gif";
    //echo $command . "\n";

    displayMessage("Conversion en GIF", 32, ['top', 'bottom']);
    // Progression
    $progressOutput = getProgressString('convertGif');
    writeProgressToFile( sprintf($progressOutput, 100, 0) );
    // Log
    error_log("Conversion en GIF\n", 3, LOG_FILE);
    // #Progression

    // On échappe la commande pour éviter les caractères non prévus
    $escaped_command = escapeshellcmd($command);
    // exec — Exécute un programme externe
    // system — Exécute un programme externe et affiche le résultat
    // shell_exec — Exécute une commande via le Shell et retourne le résultat sous forme de chaîne
    exec($escaped_command);

    displayMessage("--> OK   / Nom du fichier: " . getcwd() . "/gif/" . $filename . "/" . $filename . ".gif" , 32, ['none']);
    // Progression
    writeProgressToFile( sprintf($progressOutput, 100, 100) );
    // Log
    error_log("Conversion en GIF -- K\n", 3, LOG_FILE);
    // #Progression

    return ( getcwd() . "/gif/" . $filename . "/" . $filename . ".gif" );
} #makeAGif ( $folder, $filename )