<?php
function show($type) {
    // to display on index page
$sql = "SELECT * FROM events WHERE type = :type ORDER BY date";
global $bdd;
$req = $bdd->prepare($sql);
$req->execute(array(
    'type' => $type
));
    while($events = $req->fetch()) {
    // display old events in grey
    if ($events['date'] < date('ymd')) {
        echo "<ul class='old'>";
    } else {
        echo "<ul>";
    }
        // Get room name
        $roomq = "SELECT room_name FROM rooms WHERE id = :id";
        $roomreq = $bdd->prepare($roomq);
        $roomreq->execute(array(
            'id' => $events['room']
        ));
        $room = $roomreq->fetch();
        echo "<strong>".$events['date']."</strong> in ".$room['room_name'];
        // create array with indiv id
        $indiv_arr = explode(",", $events['individuals']);
        // remove last entry
        array_pop($indiv_arr);
        foreach($indiv_arr as $indiv) {
            // Get info on the individual
            $infoq = "SELECT * FROM individuals WHERE id = ".$indiv;
            $infosreq = $bdd->prepare($infoq);
            $infosreq->execute();
            $infos = $infosreq->fetch();
            $firstname = $infos['firstname'];
            $lastname = $infos['lastname'];
                echo "<li>".$firstname." ".$lastname."</li>";
        }
        echo "</ul>";
    }
}
function display_team($team_id) {
    global $bdd;
    // get team name
    $sql = "SELECT team_name FROM teams WHERE id = :team_id";
    $req = $bdd->prepare($sql);
    $req->execute(array(
        'team_id' => $team_id
    ));
    $team_info = $req->fetch();

    // get list of individuals
    $sql = "SELECT * FROM individuals WHERE team = :team_id ORDER BY firstname";
    $req = $bdd->prepare($sql);
    $req->execute(array(
        'team_id' => $team_id
    ));

    echo "<div class='team'>TEAM ".$team_info['team_name']." :<br />";
    while($individuals = $req->fetch()) {
        echo "<span>
            <label>
        ".$individuals['firstname']." ".$individuals['lastname']."
        <input id='chkbx_".$individuals['id']."' type='checkbox' name='individuals[]' value='".$individuals['id']."'></label></span>";
    }
    echo "</div>";
}

// check if $int is a positive integer
function is_pos_int($int) {
    $filter_options = array(
        'options' => array(
            'min_range' => 1
        ));
    return filter_var($int, FILTER_VALIDATE_INT, $filter_options);
}

