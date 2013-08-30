<?php

require_once('inc/common.php');

// EDIT EVENT
if (isset($_POST['edit_event'])) {
    // check we got everything
    if (!empty($_POST['event_id']) && 
        !empty($_POST['date']) && 
        !empty($_POST['individuals']) && 
        !empty($_POST['room']) && 
        !empty($_POST['type'])) {
        // loop for individuals
        $indiv_list = "";
        foreach($_POST['individuals'] as $individual) {
            $indiv_list .= $individual.',';
        }
        // Insert
        $sql = "UPDATE events SET 
            date = :date, 
            individuals = :individuals, 
            room = :room, 
            type = :type 
        WHERE id = :event_id";
        $req = $bdd->prepare($sql);
        $req->bindParam(':date', filter_input(INPUT_POST, 'date', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $req->bindParam(':individuals', $indiv_list);
        $req->bindParam(':room', filter_input(INPUT_POST, 'room', FILTER_SANITIZE_STRING));
        $req->bindParam(':type', filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING));
        $req->bindParam(':event_id', filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $result = $req->execute();
    } else {
        die('You missed a field.');
    }

    if ($result) {
        header('Location: index.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end edit event
