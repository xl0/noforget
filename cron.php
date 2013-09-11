<?php
/********************************************************************************
*                                                                               *
*   Copyright 2012 Nicolas CARPi (nicolas.carpi@gmail.com)                      *
*                                                                               *
********************************************************************************/

/********************************************************************************
*  This file is part of noforget.                                                *
*                                                                               *
*    noforget is free software: you can redistribute it and/or modify            *
*    it under the terms of the GNU Affero General Public License as             *
*    published by the Free Software Foundation, either version 3 of             *
*    the License, or (at your option) any later version.                        *
*                                                                               *
*    noforget is distributed in the hope that it will be useful,                 *
*    but WITHOUT ANY WARRANTY; without even the implied                         *
*    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR                    *
*    PURPOSE.  See the GNU Affero General Public License for more details.      *
*                                                                               *
*    You should have received a copy of the GNU Affero General Public           *
*    License along with noforget.  If not, see <http://www.gnu.org/licenses/>.   *
*                                                                               *
********************************************************************************/
// cron.php -- Run everyday from /etc/cron.daily
// or put this in your crontab
// # send noforget email everyday 6:00 AM
// # 0 6 * * * cd /var/www/noforget; php cron.php
require_once('inc/common.php');
require_once('lib/swift_required.php');

// check if it's run from cli (cron) or webserver; do nothing if it's from webserver
$debug = false;
if(php_sapi_name() != 'cli' || !empty($_SERVER['REMOTE_ADDR'])) {
    $debug = true;
    echo "<h2>DEBUG MODE :: NO EMAIL SENT</h2>";
    require_once('inc/head.php');
}


/*
 * DATES
 */

// TODAY
$today = date_create(date('ymd'));
// TOMORROW
$tomorrow = date_add($today, date_interval_create_from_date_string('1 day'));
if ($debug) { echo "Tomorrow = ".date_format($tomorrow, 'ymd')."<br />"; }
$tomorrow = date_format($tomorrow, 'ymd');
// NEXTWEEK is in fact J-4
$nextweek_obj = date_add($today, date_interval_create_from_date_string('4 days'));
if ($debug) { echo "Nextweek = ".date_format($nextweek_obj, 'ymd')."<br /><hr>"; }
$nextweek = date_format($nextweek_obj, 'ymd');

/*
 * END DATES
 */

/*
 * EMAIL INVARIABLES
 */
// FROM in the email doesn't change
$from = array($ini_arr['from_email'] => $ini_arr['from']);
// FOOTER
$footer = '


* This is an automatic reminder sent from NoForget *
* '.$ini_arr['url'].' *';

/*
 * END EMAIL INVARIABLES
 */

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// SHIT BEGINS HERE
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
 * ROUND 1
 * Send emails to people in the team if the event has reminder_team_id not null
 * or send emails to individuals.
 */

// Main SQL for selecting events happening tomorrow.
$sql = "SELECT * FROM events WHERE date = :date";
$req = $bdd->prepare($sql);
$req->execute(array(
    'date' => $tomorrow
));

// loop over all the events happening tomorrow
while ($events = $req->fetch()) {

    // Get room name
    $roomq = "SELECT room_name FROM rooms WHERE id = :event_room";
    $roomreq = $bdd->prepare($roomq);
    $roomreq->execute(array(
        'event_room' => $events['room']
    ));
    $room = $roomreq->fetch();

    // check if we need to send a reminder for teams
    // we need to get info on the type of event
    $event_type_sql = "SELECT * FROM events_types WHERE id = :events_type";
    $event_type_req = $bdd->prepare($event_type_sql);
    $event_type_req->execute(array(
    'events_type' => $events['type']
    ));
    $event_type = $event_type_req->fetch();

    // If there is no team to be reminded, we loop on each individual
    if ($event_type['reminder_team_id'] === null) {
        // LOOP ON THE LIST OF INDIVIDUALS
        // create array with indiv id
        $indiv_arr = explode(",", $events['individuals']);
        // remove last entry
        array_pop($indiv_arr);
        // loop on each indiv to send an email
        foreach($indiv_arr as $individual_id) {
            // Get info on the individual
            $individuals_sql = "SELECT * FROM individuals WHERE id = :individual_id";
            $individuals_req = $bdd->prepare($individuals_sql);
            $individuals_req->execute(array(
                'individual_id' => $individual_id
            ));
            $individual = $individuals_req->fetch();

            /*
             * EMAIL
             */
            
            // TO
            $to = array($individual['email'] => $individual['firstname']);

            // PARSE BODY AND REPLACE :indiv and :room
            $body = $event_type['mail_body'];
            $body = str_replace(':firstname', $individual['firstname'], $body);
            $body = str_replace(':lastname', $individual['lastname'], $body);
            $body = str_replace(':fullname', $individual['firstname'].' '.$individual['lastname'], $body);
            $body = str_replace(':room', $room['room_name'], $body);
            // deal with html special chars
            $body = htmlspecialchars_decode($body, ENT_QUOTES);
            $subject = htmlspecialchars_decode($event_type['mail_subject'], ENT_QUOTES);

            // SENDING EMAIL
            $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setBody($body.$footer);
                
            $transport = Swift_SmtpTransport::newInstance($ini_arr['smtp_address'], $ini_arr['smtp_port'], $ini_arr['smtp_encryption'])

                ->setUsername($ini_arr['smtp_username'])
                ->setPassword($ini_arr['smtp_password']);
                $mailer = Swift_Mailer::newInstance($transport);

               if ($debug) {
                   echo 'ROUND 1 : Sent to : '.var_dump($to).'<br />
                       Type : '.$events['type'].' in '.$room['room_name'].'<br />
                       Subject : '.$event_type['mail_subject'].' <br />
                       Body : '.$body.'
                       <hr>';
               } else {
                   // we loop on the $to array
                   foreach($to as $address => $name) {
                           $message->setTo(array($address => $name));
                           $mailer->send($message);
                   }
               }
            } //end foreach individual
   } else { // event has a team reminder
        $reminder_team_id_array = explode(',', $event_type['reminder_team_id']);
        // we remove the last entry of the array
        array_pop($reminder_team_id_array);
        // now we loop on each email in each team
        $to = array();
        foreach($reminder_team_id_array as $team_id) {
            // get all emails from the teams
            $reminder_team_sql = "SELECT firstname, email FROM individuals WHERE team = :team_id";
            $reminder_team_req = $bdd->prepare($reminder_team_sql);
            $reminder_team_req->execute(array(
                'team_id' => $team_id
            ));
            while ($individual_team = $reminder_team_req->fetch()) {
                // we want an array with email => name
                $to[$individual_team['email']] = $individual_team['firstname'];
            }
        }
        // now we have our $to array with everyone in the teams

        // now we want to have a list of individuals participating in the event.

        // create array with indiv id
        $indiv_arr = explode(",", $events['individuals']);
        // remove last entry
        array_pop($indiv_arr);
        // loop on each indiv to get the speaker(s) list
        $individuals_list = '';
        foreach($indiv_arr as $individual_id) {

            // Get info on the individual
            $individuals_sql = "SELECT * FROM individuals WHERE id = :individual_id";
            $individuals_req = $bdd->prepare($individuals_sql);
            $individuals_req->execute(array(
                'individual_id' => $individual_id
            ));
            $individual = $individuals_req->fetch();
            // we make a list of individuals participating in the labmeeting/journal club
            $individuals_list .= $individual['firstname'] .' '. $individual['lastname'] . ', ';
        }
        // remove the last ,
        $individuals_list = substr($individuals_list, 0, -2);

        /*
         * EMAIL
         */
    
        // PARSE BODY AND REPLACE :indiv and :room
        $body = $event_type['mail_body'];
        $body = str_replace(':firstname', $individual['firstname'], $body);
        $body = str_replace(':lastname', $individual['lastname'], $body);
        $body = str_replace(':fullname', $individual['firstname'].' '.$individual['lastname'], $body);
        $body = str_replace(':speaker', $individuals_list, $body);
        $body = str_replace(':room', $room['room_name'], $body);

        // deal with html special chars
        $body = htmlspecialchars_decode($body, ENT_QUOTES);
        $subject = htmlspecialchars_decode($event_type['mail_subject'], ENT_QUOTES);

        // SENDING EMAIL
        $message = Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom($from)
        ->setBody($body.$footer);
            
        $transport = Swift_SmtpTransport::newInstance($ini_arr['smtp_address'], $ini_arr['smtp_port'], $ini_arr['smtp_encryption'])
            ->setUsername($ini_arr['smtp_username'])
            ->setPassword($ini_arr['smtp_password']);
            $mailer = Swift_Mailer::newInstance($transport);

           if ($debug) {
               echo 'ROUND 1 : Sent to : '.var_dump($to).'<br />
                   Type : '.$events['type'].' in '.$room['room_name'].'<br />
                   Subject : '.$event_type['mail_subject'].' <br />
                   Body : '.$body.'
                   <hr>';
           } else {
               // we loop on the $to array
               foreach($to as $address => $name) {
                       $message->setTo(array($address => $name));
                       $mailer->send($message);
               }
           }




            // end if event has reminder
} // end foreach event ID
}


/*
 * ROUND 2
 * Check for events with a next week reminder and a date of nextweek.
 */

// Main SQL for selecting events happening next week and having a reminder_week_before true
$sql2 = "SELECT DISTINCT events.* FROM events, events_types 
    WHERE events.date = :date 
    AND events_types.reminder_week_before = 1";
$req2 = $bdd->prepare($sql2);
$req2->execute(array(
    'date' => $nextweek
));

// Loop all the events that need to be reminded a week befor and send the reminder_mail_body
while ($events = $req2->fetch()) {
    // Get room name
    $roomq = "SELECT room_name FROM rooms WHERE id = :event_room";
    $roomreq = $bdd->prepare($roomq);
    $roomreq->execute(array(
        'event_room' => $events['room']
    ));
    $room = $roomreq->fetch();

    // we need to get info on the type of event to have the body and subject of email
    $event_type_sql = "SELECT * FROM events_types WHERE id = :events_type";
    $event_type_req = $bdd->prepare($event_type_sql);
    $event_type_req->execute(array(
    'events_type' => $events['type']
    ));
    $event_type = $event_type_req->fetch();

    // LOOP ON THE LIST OF INDIVIDUALS
    // create array with indiv id
    $indiv_arr = explode(",", $events['individuals']);
    // remove last entry
    array_pop($indiv_arr);
    // loop on each indiv to send an email
    foreach($indiv_arr as $individual_id) {
        // Get info on the individual
        $individuals_sql = "SELECT * FROM individuals WHERE id = :individual_id";
        $individuals_req = $bdd->prepare($individuals_sql);
        $individuals_req->execute(array(
            'individual_id' => $individual_id
        ));
        $individual = $individuals_req->fetch();
    /*
     * EMAIL
     */
    
    // TO
        $to = array($individual['email'] => $individual['firstname']);

        // PARSE BODY AND REPLACE :indiv and :room
        if ($event_type['reminder_week_before'] == 1) {
            $body = $event_type['reminder_mail_body'];
        } else {
        $body = $event_type['mail_body'];
        }
        $body = str_replace(':firstname', $individual['firstname'], $body);
        $body = str_replace(':lastname', $individual['lastname'], $body);
        $body = str_replace(':fullname', $individual['firstname'].' '.$individual['lastname'], $body);
        $body = str_replace(':room', $room['room_name'], $body);

        // deal with html special chars
        $body = htmlspecialchars_decode($body, ENT_QUOTES);
        $subject = htmlspecialchars_decode($event_type['mail_subject'], ENT_QUOTES);

    // SENDING EMAIL
    $message = Swift_Message::newInstance()
    ->setSubject($subject)
    ->setFrom($from)
    ->setBody($body.$footer);
        
    $transport = Swift_SmtpTransport::newInstance($ini_arr['smtp_address'], $ini_arr['smtp_port'], $ini_arr['smtp_encryption'])
        ->setUsername($ini_arr['smtp_username'])
        ->setPassword($ini_arr['smtp_password']);
        $mailer = Swift_Mailer::newInstance($transport);

       if ($debug) {
           echo 'ROUND 2 : Sent to : '.var_dump($to).'<br />
               Type : '.$events['type'].' in '.$room['room_name'].'<br />
               Subject : '.$event_type['mail_subject'].' <br />
               Body : '.$body.'
               <hr>';
       } else {
           // we loop on the $to array
           foreach($to as $address => $name) {
                   $message->setTo(array($address => $name));
                   $mailer->send($message);
           }
       }

    } //end foreach individual
}

