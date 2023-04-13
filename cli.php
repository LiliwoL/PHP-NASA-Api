<?php

include_once ('includes.php');

/**
 * Interaction avec l'utilisateur
 *
 * Toute la partie suivante pourrait se faire via un client navigateur plutôt qu'en ligne de commande
 */

// On demande à l'utilisateur de saisir une date
$date_user = readline("Saisir une date au format YYYY-MM-DD:\n");

// Au cas où rien n'est tapé, on saisi une valeur par défaut
$date_user = ( $date_user == '' ) ? '2018-06-12' : $date_user;
// @todo Vérifier la saisie de l'utilisateur

// Construction du endPoint avec la date a la clé API
$today_api_endpoint = $api_endpoint . $date_user . "?api_key=" . $api_key;

// Affichage d'un texte avec de la couleur et un espace en bas
// cf. fonction displayMessage
displayMessage( "Recherche de " . $today_api_endpoint, 32, ['bottom'] );


// Requete + Download
// @todo: en cas de réponse vide
$parsing = parse_and_download_data ( get_day_data( $today_api_endpoint ) );

if ($parsing)
{
    // Création d'un GIF à partir des images d'un dossier
    $gif_filename = makeAGif( "./images/" . $date_user, $date_user );

    // Envoi d'un mail avec le GIF en PJ
    sendMail ( "marie@marie.com", "La terre le " . $date_user , "La terre vue du ciel le " . $date_user, $gif_filename );
}