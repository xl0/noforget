<?php

require_once('inc/common.php');

// DELETE EVENT
if (isset($_GET['type']) && ($_GET['type'] === 'event')) {
    if (!empty($_GET['event_id']) && is_pos_int($_GET['event_id'])) {
        // SQL
        $sql = "DELETE FROM events WHERE id = :event_id";
        $req = $bdd->prepare($sql);
        $req->bindParam(':event_id', filter_input(INPUT_GET, 'event_id', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $result = $req->execute();
    } else {
        die('You missed a field.');
    }

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end delete event

// DELETE EVENT TYPE
if (isset($_GET['type']) && ($_GET['type'] === 'event_type')) {
    if (!empty($_GET['event_type_id']) && is_pos_int($_GET['event_type_id'])) {
        // SQL
        $sql = "DELETE FROM events_types WHERE id = :event_type_id";
        $req = $bdd->prepare($sql);
        $req->bindParam(':event_type_id', filter_input(INPUT_GET, 'event_type_id', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $result = $req->execute();
    } else {
        die('You missed a field.');
    }

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end delete event type

// DELETE INDIVIDUAL
if (isset($_GET['type']) && ($_GET['type'] === 'individual')) {
    if (!empty($_GET['individual_id']) && is_pos_int($_GET['individual_id'])) {
        // SQL
        $sql = "DELETE FROM individuals WHERE id = :individual_id";
        $req = $bdd->prepare($sql);
        $req->bindParam(':individual_id', filter_input(INPUT_GET, 'individual_id', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $result = $req->execute();
    } else {
        die('You missed a field.');
    }

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end delete individual

// DELETE TEAM
if (isset($_GET['type']) && ($_GET['type'] === 'team')) {
    if (!empty($_GET['team_id']) && is_pos_int($_GET['team_id'])) {
        // SQL
        $sql = "DELETE FROM teams WHERE id = :team_id";
        $req = $bdd->prepare($sql);
        $req->bindParam(':team_id', filter_input(INPUT_GET, 'team_id', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $result = $req->execute();
        // delete also all the individuals associated with this team
        $sql = "DELETE FROM individuals WHERE team = :team_id";
        $req = $bdd->prepare($sql);
        $req->bindParam(':team_id', filter_input(INPUT_GET, 'team_id', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $result = $req->execute();
    } else {
        die('You missed a field.');
    }

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end delete team

// DELETE ROOM
if (isset($_GET['type']) && ($_GET['type'] === 'room')) {
    if (!empty($_GET['room_id']) && is_pos_int($_GET['room_id'])) {
        // SQL
        $sql = "DELETE FROM rooms WHERE id = :room_id";
        $req = $bdd->prepare($sql);
        $req->bindParam(':room_id', filter_input(INPUT_GET, 'room_id', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
        $result = $req->execute();
    } else {
        die('You missed a field.');
    }

    if ($result) {
        header('Location: admin.php');
    } else {
        die('Something went wrong in the database query. Check the flux capacitor.');
    }
} // end delete room
