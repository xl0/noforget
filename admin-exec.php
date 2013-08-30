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
require_once('inc/common.php');

// ADD EVENT
if (isset($_POST['add_event'])) {
    // check we got everything
    if (!empty($_POST['date']) && !empty($_POST['individuals']) && !empty($_POST['room']) && !empty($_POST['type'])) {
        // loop for individuals
        $indiv_list = "";
        foreach($_POST['individuals'] as $individual) {
            $indiv_list .= $individual.',';
        }
        // Insert
        $sql = "INSERT INTO events (date, individuals, room, type) VALUES(:date, :individuals, :room, :type)";
        $req = $bdd->prepare($sql);
        $req->bindParam(':date', filter_input(INPUT_POST, 'date', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        // $req->bindParam(':individual', filter_input(INPUT_POST, 'individual', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $req->bindParam(':individuals', $indiv_list);
        $req->bindParam(':room', filter_input(INPUT_POST, 'room', FILTER_SANITIZE_STRING));
        $req->bindParam(':type', filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING));
        $result = $req->execute();
    } else {
        die('You missed a field.');
    }

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end add event

// EDIT EXISTING EVENT is on edit_event-exec.php

// ADD NEW TYPE OF EVENT
if (isset($_POST['add_event_type'])) {
    // Sanitize value
    if (isset($_POST['name']) && !empty($_POST['name'])) {
        $name= filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to specify a name for this event.');
    }
    if (isset($_POST['mail_subject']) && !empty($_POST['mail_subject'])) {
        $mail_subject= filter_var($_POST['mail_subject'], FILTER_SANITIZE_STRING);
    } else {
        die('You need a subject to your email.');
    }
    if (isset($_POST['mail_body']) && !empty($_POST['mail_body'])) {
        $mail_body= filter_var($_POST['mail_body'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to input some text.');
    }
    if (isset($_POST['reminder_mail_body']) && !empty($_POST['reminder_mail_body'])) {
        $reminder_mail_body= filter_var($_POST['reminder_mail_body'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to input some text.');
    }
    $reminder_team_id = '';
    if (isset($_POST['reminder_team_id']) && !empty($_POST['reminder_team_id'])) {
        foreach($_POST['reminder_team_id'] as $id) {
            $reminder_team_id .= $id.",";
        }
    }
    $reminder_week_before = 0;
    if (isset($_POST['reminder_week_before']) && !empty($_POST['reminder_week_before'])) {
        if ($_POST['reminder_week_before'] == 1) {
            $reminder_week_before = 1;
        }
    }
    // SQL
    $sql = "INSERT INTO events_types (
        name, 
        mail_subject, 
        mail_body, 
        reminder_mail_body, 
        reminder_team_id, 
        reminder_week_before
    ) VALUES(
        :name, 
        :mail_subject, 
        :mail_body, 
        :reminder_mail_body, 
        :reminder_team_id, 
        :reminder_week_before
    )";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'name' => $name,
        'mail_subject' => $mail_subject,
        'mail_body' => $mail_body,
        'reminder_mail_body' => $reminder_mail_body,
        'reminder_team_id' => $reminder_team_id,
        'reminder_week_before' => $reminder_week_before
    ));

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end add new type of event

// EDIT EXISTING TYPE OF EVENTS
if (isset($_POST['edit_event_type'])) {
    // Sanitize value
    if (isset($_POST['name']) && !empty($_POST['name'])) {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to specify a name for this event.');
    }
    if (isset($_POST['mail_subject']) && !empty($_POST['mail_subject'])) {
        $mail_subject = filter_var($_POST['mail_subject'], FILTER_SANITIZE_STRING);
    } else {
        die('You need a subject to your email.');
    }
    if (isset($_POST['mail_body']) && !empty($_POST['mail_body'])) {
        $mail_body = filter_var($_POST['mail_body'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to input some text.');
    }
    if (isset($_POST['reminder_mail_body']) && !empty($_POST['reminder_mail_body'])) {
        $reminder_mail_body = filter_var($_POST['reminder_mail_body'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to input some text.');
    }
    if (isset($_POST['reminder_team_id']) && !empty($_POST['reminder_team_id'])) {
        foreach($_POST['reminder_team_id'] as $id) {
            $reminder_team_id .= $id.",";
        }
    } else {
        $reminder_team_id = null;
    }
    if (isset($_POST['reminder_week_before']) && !empty($_POST['reminder_week_before'])) {
        $reminder_week_before = 0;
        if ($_POST['reminder_week_before'] == 1) {
            $reminder_week_before = 1;
        }
    }
    if (isset($_POST['event_type_id']) && !empty($_POST['event_type_id'])) {
        $event_type_id = filter_var($_POST['event_type_id'], FILTER_VALIDATE_INT);
    }

    // Insert
    $sql = "UPDATE events_types SET 
        name = :name,
        mail_subject = :mail_subject,
        mail_body = :mail_body,
        reminder_mail_body = :reminder_mail_body,
        reminder_team_id = :reminder_team_id,
        reminder_week_before = :reminder_week_before
       WHERE  id = :event_type_id";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'name' => $name,
        'mail_subject' => $mail_subject,
        'mail_body' => $mail_body,
        'reminder_mail_body' => $reminder_mail_body,
        'reminder_team_id' => $reminder_team_id,
        'reminder_week_before' => $reminder_week_before,
        'event_type_id' => $event_type_id
    ));

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end edit existing type of event

// ADD INDIVIDUAL
if (isset($_POST['add_individual'])) {
    // Sanitize values & check email is valid
    if (isset($_POST['firstname']) && isset($_POST['lastname']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
        $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
        $team = filter_var($_POST['team'], FILTER_VALIDATE_INT);
    } else {
        die('Either you missed a field or the email is invalid');
    }

    // Insert
    $sql = "INSERT INTO individuals (firstname, lastname, email, team) VALUES(:firstname, :lastname, :email, :team)";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $_POST['email'],
        'team' => $team
    ));

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end add individual

// EDIT EXISTING INDIVIDUAL
if (isset($_POST['edit_individual'])) {
    // check if variables are here
    if (isset($_POST['individual_id']) && 
        isset($_POST['firstname']) && 
        isset($_POST['lastname']) && 
        isset($_POST['email']) &&
        isset($_POST['team'])) {

        // check if variables are valid
        if (filter_var($_POST['individual_id'], FILTER_VALIDATE_INT)) {
            $individual_id = $_POST['individual_id'];
        } else {
            die('id is not int');
        }

        $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);

        $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);

        if (filter_var($_POST['team'], FILTER_VALIDATE_INT)) {
            $team = $_POST['team'];
        } else {
            die('team is not int');
        }

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $email = $_POST['email'];
        } else {
            die('email is not valid');
        }

        // SQL
        $sql = "UPDATE individuals SET
           firstname = :firstname,
           lastname = :lastname,
           email = :email,
           team = :team
           WHERE id = :individual_id";
        $req = $bdd->prepare($sql);
        $result = $req->execute(array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'team' => $team,
            'individual_id' => $individual_id
        ));

        echo "result = ". $result;
        if ($result) {
            header('Location: admin.php');
        } else {
            die('Something went wrong in the database query. Check the flux capacitor.');
        }
    }
} // end edit existing individual


// ADD ROOM
if (isset($_POST['add_room'])) {
    // Sanitize value
    if (isset($_POST['room_name'])) {
        $room_name = filter_var($_POST['room_name'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to specify a room.');
    }

    // Insert
    $sql = "INSERT INTO rooms (room_name) VALUES(:room_name)";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'room_name' => $room_name
    ));

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end add room

// ADD TEAM
if (isset($_POST['add_team'])) {
    // Sanitize value
    if (isset($_POST['team_name'])) {
        $team_name = filter_var($_POST['team_name'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to specify a team.');
    }

    // Insert
    $sql = "INSERT INTO teams (team_name) VALUES(:team_name)";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'team_name' => $team_name
    ));

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end add team


// EDIT TEAM
if (isset($_POST['edit_team'])) {
    // Sanitize value
    if (isset($_POST['team_name'])) {
        $team_name = filter_var($_POST['team_name'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to specify a team.');
    }
    if (isset($_POST['team_id']) && !empty($_POST['team_id']) && is_pos_int($_POST['team_id'])) {
        $team_id = $_POST['team_id'];
    }

    // SQL
    $sql = "UPDATE teams SET
        team_name = :team_name
        WHERE id = :team_id";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'team_name' => $team_name,
        'team_id' => $team_id
    ));

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end edit team


// EDIT ROOM
if (isset($_POST['edit_room'])) {
    // Sanitize value
    if (isset($_POST['room_name'])) {
        $room_name = filter_var($_POST['room_name'], FILTER_SANITIZE_STRING);
    } else {
        die('You need to specify a room.');
    }
    if (isset($_POST['room_id']) && !empty($_POST['room_id']) && is_pos_int($_POST['room_id'])) {
        $room_id = $_POST['room_id'];
    }

    // SQL
    $sql = "UPDATE rooms SET
        room_name = :room_name
        WHERE id = :room_id";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'room_name' => $room_name,
        'room_id' => $room_id
    ));

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end edit room


