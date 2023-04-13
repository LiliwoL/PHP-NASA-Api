<?php

include_once ('inc/init.php');

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
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>POV - Earth, one day</title>

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!--import bootstrap function-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

        <!--import google font-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@100&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="assets/style.css" />
    </head>
    <body>

        <!--title of the page-->
        <h1 id="title">
            <b>
                POV, Earth, One Day
            </b>
        </h1>

        <!--a small description of the survey form-->
        <p id="description">
            <i>
                an simple survey form about programming languages
            </i>
        </p>

        <!--starting survey form group-->
        <form id="survey-form" action="index.php" method="POST">

            <!--Request Name-->
            <div class="form-space">
                <label id="name-label" for="name">
                    - Nom du / de la destinataire:
                </label>

                <!--
                id              identify the input
                type            the type of input, in this case, text
                placeholder     the text before we insert the answer
                class           modify with css class
                -->
                <input id="name" name="name" type="text" placeholder="Qui va recevoir cette délicate et romantique attention?" class="form-input-css" />
            </div>

            <!--Request Email-->
            <div class="form-space">
                <label id="email-label" for="email">
                    - Email:
                </label>

                <!--required i used where the user must insert something for submit-->
                <input id="email" name="email" type="email" placeholder="Adresse mail du / de la destinataire: my.love@forevermore.com" class="form-input-css" required />
            </div>

            <!--Request EmaDateil-->
            <div class="form-space">
                <label id="date-label" for="date">
                    - Date:
                </label>

                <!--required i used where the user must insert something for submit-->
                <!-- Toutes les dates ne sont pas disponibles https://epic.gsfc.nasa.gov/api/natural/available-->
                <input id="date" name="date" type="date" placeholder="Date" class="form-input-css" min="2015-06-13" required />
            </div>

            <!--group of element for comment-->
            <div class="form-space">

                <label>
                    -Un message pour accompagner ce romantisme dégoulinant
                </label>

                <!--text area tag: tag defines a multi-line text input control-->
                <textarea class="textarea_css" name="message" id="message" placeholder="Allez, on écrit un petit mot, soyez chou ;-)"></textarea>

            </div>

            <!-- https://blog.clever-age.com/fr/2014/06/25/owasp-cross-site-request-forgery-csrf-ou-xsrf -->
            <input type="hidden" value="ici_on_met_un_jeton_csrf_de_malade" name="csrf-token"/>

            <!--submit button-->
            <div class="form-space">
                <button type="submit" id="submit" class="submit-button-class">
                    <b><i>Envoyer tout mon amour</i></b>
                </button>
            </div>

        </form>

        <script type="text/javascript">
            let availableDates = '';

            /**
             * Execution quand la page est chargée
             */
            document.addEventListener("DOMContentLoaded", function() {
                // Récupération des dates possibles à partir du site de la NASA
                fetch('https://epic.gsfc.nasa.gov/api/natural/available')
                    .then( response => {
                        return response.json();
                    })
                    .then( data => {
                        // LEs dates disponibles sont récupérées dans un objet de type tableau JSON
                        availableDates = data;

                        // Ajout d'un événement sur le champ date
                        document.getElementById("date").addEventListener('change', isAValidDate);
                    })
                    .catch( error => {
                        console.log(error);
                    });
            });

            /**
             * Méthode pour valider la date saisie avec le tableau des dates valides
             * @param element
             */
            function isAValidDate(element)
            {
                // Si les dates disponibles ont bien été récupérées, on teste
                if (typeof availableDates == 'object')
                {
                    console.log("On teste la date reçue " + element.target.value);

                    // @TODO: Vérification de la date saisie
                    const selectedDate = new Date(element.target.value);

                    // La date reçue est-elle dans le tableau récupéré depuis le lien de la NASA?
                    const isOK = availableDates.includes( element.target.value );

                    // Si pas Ok, on passe le input en error
                    if (!isOK){
                        // Changement du innerText
                        document.getElementById('date-label').innerText = "- Date: Cette date n'est pas disponible!";
                        // On ajoute la classe is-invalid
                        document.getElementById('date').setAttribute('class', 'form-input-css is-invalid');
                        // Désactivation du submit
                        document.getElementById('submit').disabled = true;
                    }else{
                        // innerText remis à zéro
                        document.getElementById('date-label').innerText = "- Date:";
                        // Classe remise à zéro
                        document.getElementById('date').setAttribute('class', 'form-input-css');
                        // Activation du submit
                        document.getElementById('submit').disabled = false;
                    }
                }
            }
        </script>
    </body>
</html>
