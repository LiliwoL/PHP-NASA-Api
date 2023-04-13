<?php

include_once ('includes.php');

// ###########################################

/**
 * En cas de requête POST
 *
 * TODO: vérification correcte de CSRF
 */
if ( isset($_POST['csrf-token']) && $_POST['csrf-token'] == 'ici_on_met_un_jeton_csrf_de_malade' )
{
    // Date
    $date_user = htmlspecialchars($_POST['date']);

    // Message
    $message = htmlspecialchars($_POST['message']);

    // Nom destinataire
    $destName = htmlspecialchars($_POST['name']);

    // Mail destinataire
    $destEmail = htmlspecialchars($_POST['email']);

    // Construction du endPoint avec la date a la clé API
    $today_api_endpoint = $api_endpoint . $date_user . "?api_key=" . $api_key;

    // Requete + Download
    // @todo: en cas de réponse vide
    $parsing = parse_and_download_data ( get_day_data( $today_api_endpoint ) );

    if ($parsing)
    {
        // Création d'un GIF à partir des images d'un dossier
        $gif_filename = makeAGif( "./images/" . $date_user, $date_user );

        // Envoi d'un mail avec le GIF en PJ
        try {
            sendMail($destEmail, "Pour mon amour, La terre le " . $date_user, $message, $gif_filename);
        }
        catch( Exception $exception ){
            echo "Erreur";
        }
    }

}else{
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
}