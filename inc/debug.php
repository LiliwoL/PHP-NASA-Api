<?php

/**
 * Fonction de callback d'affichage de l'avancée des cUrl
 *
 * @param [type] $source
 * @param [type] $downsize
 * @param [type] $down
 * @param [type] $upsize
 * @param [type] $up
 * @return void
 */
function Progress($source, $downsize, $down, $upsize, $up): void
{
    static $prev = 0;
    if ($downsize == 0) {
        $progress = 0;
        $prev = 0; // On réinitialise $prev à chaque appel
    } else {
        $progress = round($down / $downsize * 100);
        if ($progress > $prev) {
            $prev = $progress; // Pour éviter de répéter

            displayMessage( "$progress % ", 31, ['none']);

            /*$fopen = fopen('prog.txt', 'w+');
            fputs($fopen, "$progress\n");
            fclose($fopen);*/
        }
    }

} #Progress()

/**
 * Une fonction qui renverrait une chaîne de caractère contenant des variables à remplacer
 * Cette chaîne serait insérée dans un fichier lu via XHR poiur afficher une progression sur le client
 *
 * @param string $progressType
 * @return string
 */
function getProgressString( string $progressType ): string
{
    $output = '';

    switch ($progressType){
        case 'parse_and_download_data':
            $output = '<label for="file">Téléchargements:</label>
                        <progress id="file" max="%d" value="%d"></progress>';
            break;
        case 'reduceFileSize':
            $output = '<label for="reduc">Réduction des images:</label>
                        <progress id="reduc" max="%d" value="%d"></progress>';
            break;
        case 'convertGif':
            $output = '<label for="reduc">Création de l\'animation:</label>
                        <progress id="anim" max="%d" value="%d"></progress>';
            break;
        default:
            $output = '';
            break;
    }

    return $output;
}

function writeProgressToFile( string $content ): bool
{
    $progressFile = "./tmp/progress.json";

    $fp = file_put_contents(
        $progressFile,
        $content,
        LOCK_EX
    );

    return is_int($fp);
}

/**
 * Pour afficher un message avec une couleur particulière
 *
 * Info: https://i.stack.imgur.com/HFSl1.png
 *
 * @param string $msg
 * @param int $color
 * 	Un entier correspond à une couleur
 * 	39: couleur par défaut
 * 	30: noir
 * 	31: rouge
 * 	32: vert
 * @param array $T_spaces
 * @return void
 */
function displayMessage ( string $msg, int $color, array $T_spaces) : void
{
    // Top space
    if (in_array( 'top', $T_spaces) )
        echo "\n\n";

    // Tableau des couleurs
    $T_colors = [
        39 => "\033[39m", // Défaut
        30 => "\033[30m", // Black
        31 => "\033[31m", // Red
        32 => "\033[32m", // Green
    ];

    // Message (et on remet la couleur par défaut à la fin)
    $output = $T_colors[$color] . $msg . $T_colors[39];
    echo $output;

    // Par défaut, on met un espace à la fin, sauf si on a spécifi none dans le tableau
    if (!in_array( 'none', $T_spaces) )
        echo "\n";

    // Bottom space
    if (in_array( 'bottom', $T_spaces) )
        echo "\n\n";

} #displayMessage ($msg, $color, $spaces)
