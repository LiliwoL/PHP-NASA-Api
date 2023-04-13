<?php
include_once('./vendor/autoload.php');


use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

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
