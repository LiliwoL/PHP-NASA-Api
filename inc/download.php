<?php

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
    // Log
    error_log("Récupération des données de l'url\n", 3, LOG_FILE);

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

        // Progression
        $progressOutput = getProgressString('parse_and_download_data');
        writeProgressToFile( sprintf($progressOutput, sizeof($data), 0) );
        $index = 1;
        // Log
        error_log("Téléchargement $index\n", 3, LOG_FILE);
        // #Progression

        foreach ( $data as $key => $value )
        {
            echo "Caption " . $value->caption . "\n";
            echo "Image " . $value->image . "\n";

            // Download Image
            $url = "https://epic.gsfc.nasa.gov/archive/enhanced/" . str_replace('-', '/', substr( $value->date, 0,10)) . "/png/" . $value->image . ".png";

            // Appel de la fonction pour récupérer le fichier
            download_file( $url, './images/' . substr( $value->date, 0,10) . "/", $value->image . ".png");

            // Progression
            writeProgressToFile( sprintf($progressOutput, sizeof($data), $index) );
            // Log
            error_log("Téléchargement $index -- OK\n", 3, LOG_FILE);
            $index++;
            // #Progression
        }

        return (true);
    }

} #parse_data($data)

/**
 * Download du fichier fourni
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
        CURLOPT_FILE 			 => $fh,
        CURLOPT_USERAGENT 		 => $user_agent,
        CURLOPT_NOPROGRESS       => false,
        CURLOPT_PROGRESSFUNCTION => 'Progress'
    ),
    );

    // Executing curl
    curl_exec($ch);
    curl_close($ch);

    displayMessage( "----------------------------------------- ", 31, ['bottom']);

} #download_file( $url, $folder, $filename )