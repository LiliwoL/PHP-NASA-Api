<?php
include_once('./vendor/autoload.php');


use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;


/**
 * Chargement des variables d'environnement
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

(string) $api_endpoint = $_ENV['API_ENDPOINT'];
(string) $api_key = $_ENV['API_KEY'];
(string) $mailer_dsn = $_ENV['MAILER_DSN'];


// ###################################################

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
            $output = '<label for="file">File progress:</label>
                        <progress id="file" max="100" value="%d"> %d% </progress>';
        default:
            $output = '';
    }

    return $output;
}

/**
 * Récupération des données depuis l'API à partir d'un jour donné
 *
 * @param [type] $endpoint
 * @return string | array
 * Renvoi d'une erreur ou la chaine JSON
 * @todo Use Guzzle instead of curl
 */
function get_day_data( string $endpoint ) : string|array
{
    // Amélioration: https://www.geeksforgeeks.org/why-use-guzzle-instead-of-curl-in-php/?ref=rp

    // Pour faire la requête, on va utiliser cUrl
    $ch = curl_init();

    // User Agent
    $user_agent = 'Mozilla HotFox 1.0';

    curl_setopt_array($ch, array(
            CURLOPT_URL              => $endpoint,
            // Telling curl to store JSON
            // data in a variable instead
            // of dumping on screen
            CURLOPT_RETURNTRANSFER   => true,
            CURLOPT_HEADER           => 0,
            CURLOPT_USERAGENT 		 => $user_agent
        ),
    );

    // Executing curl
    $response = curl_exec($ch);

    // Checking if any error occurs
    // during request or not
    if($e = curl_error($ch))
    {
        echo $e;
        $output = $e;
    } else {
        // Decoding JSON data in associative array or object
        $decodedData = json_decode($response);

        // Outputting JSON data in decoded form
        $output = $decodedData;
    }

    // Closing curl
    curl_close($ch);

    return ($output);
} #get_day_data( $endpoint )


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
 * Lecture d'un JSON renvoyé par la NASA
 * Le contenu est lu et chaque entrée contient une image qui est téléchargée
 *
 * @param [type] $data
 * @return boolean |
 */
function parse_and_download_data ( $data ): bool
{
    //@todo: vérifier le contenu
    if ( !is_array($data)  )
    {
        // Tableau vide, données inexistantes, on essaie le jour suivant?
        return (false);
    } else {

        foreach ( $data as $key => $value )
        {
            echo "Caption " . $value->caption . "\n";
            echo "Image " . $value->image . "\n";

            // Download Image
            $url = "https://epic.gsfc.nasa.gov/archive/enhanced/" . str_replace('-', '/', substr( $value->date, 0,10)) . "/png/" . $value->image . ".png";

            // Appel de la fonction pour récupérer le fichier
            download_file( $url, './images/' . substr( $value->date, 0,10) . "/", $value->image . ".png");
        }

        return (true);

    }

} #parse_data($data)


/**
 * Undocumented function
 *
 * @param [type] $url
 * @param [type] $folder
 * @param [type] $filename
 * @return void
 */
function download_file( $url, $folder, $filename ): void
{
    displayMessage( "----------------------------------------- ", 31, ['top']);

    // Vérification si le dossier existe, sinon, on le crée
    if (! is_dir($folder))
    {
        mkdir( $folder );
    }

    $fh = fopen($folder.  $filename, "w");
    $ch = curl_init();

    // User Agent
    $user_agent = 'Mozilla HotFox 1.0';

    curl_setopt_array($ch, array(
        CURLOPT_URL              => $url,
        CURLOPT_FILE 						 => $fh,
        CURLOPT_USERAGENT 			 => $user_agent,
        CURLOPT_NOPROGRESS       => false,
        CURLOPT_PROGRESSFUNCTION => 'Progress'
    ),
    );

    // Executing curl
    curl_exec($ch);
    curl_close($ch);

    displayMessage( "----------------------------------------- ", 31, ['bottom']);

} #download_file( $url, $folder, $filename )


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
    displayMessage("Réduction de la taille des images", 32, ['top', 'bottom']);
    reduceFileSize( $folder );
    displayMessage("--> OK", 32, ['none']);

    /*
        Les images doivent être en png
    */
    $command = "convert -delay 100 -loop 0 " . $folder . "/*.png ./gif/" . $filename . "/" . $filename . ".gif";
    //echo $command . "\n";

    displayMessage("Conversion en GIF", 32, ['top', 'bottom']);

    // On échappe la commande pour éviter les caractères non prévus
    $escaped_command = escapeshellcmd($command);
    // exec — Exécute un programme externe
    // system — Exécute un programme externe et affiche le résultat
    // shell_exec — Exécute une commande via le Shell et retourne le résultat sous forme de chaîne
    exec($escaped_command);

    displayMessage("--> OK   / Nom du fichier: " . getcwd() . "/gif/" . $filename . "/" . $filename . ".gif" , 32, ['none']);

    return ( getcwd() . "/gif/" . $filename . "/" . $filename . ".gif" );
} #makeAGif ( $folder, $filename )

// #################################################################################################################

//   __  __       _ _
// |  \/  | __ _(_) |
// | |\/| |/ _` | | |
// | |  | | (_| | | |
// |_|  |_|\__,_|_|_|

/**
 * Envoi d'un mail en utiloisant le DSN défini dans le fichier .env
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $filename
 * @return void
 * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
 */
function sendMail (string $to, string $subject, string $message, string $filename ): void
{
    $mailer = initMailer();

    // Création du message
    $email = (new Email())
        ->from('hello@example.com')
        ->to($to)
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject($subject)
        ->text($message)
        ->html('
            <p>' . $message . '</p>
            <img src="cid:earth_animation">
        ')
        ->embed(fopen($filename, 'r'), 'earth_animation');

    // Try to send mail
    try {
        // Envoi
        $mailer->send($email);
    } catch (TransportExceptionInterface $e) {
        // some error prevented the email sending; display an
        // error message or try to resend the message

        die ($e);
    }

} #sendMail ( $to, $subject, $message, $filename )


/**
 * Méthode pour initialiser le mailer
 * On va aller chercher le MAILER_DSN définit plus haut en global, d'où le mot clé GLOBAL
 *
 * @return Mailer
 */
function initMailer(): Mailer
{
    // On fait appel à la variable définie tout au début
    GLOBAL $mailer_dsn;

    $transport = Transport::fromDsn( $mailer_dsn );
    $mailer = new Mailer($transport);

    return ( $mailer );
} #initMailer()


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
