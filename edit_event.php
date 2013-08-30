<?php
require_once('inc/common.php');
require_once('inc/head.php');
?>

<h2>NoForget!</h2>
<p class='center'><a href='index.php'>main page</a> > <a href='admin.php'>admin page</a></p>

<?php
// show all events associated with the wanted event type
$sql = "SELECT * FROM events WHERE type = :event_type";
$req = $bdd->prepare($sql);
$req->bindParam(':event_type', filter_input(INPUT_GET, 'event_type', FILTER_SANITIZE_NUMBER_INT), PDO::PARAM_INT);
$req->execute();
    echo "There is " . $req->rowCount() . " events of this type.<br />";

// rooms
$rooms_sql = "SELECT * FROM rooms ORDER BY room_name";
$rooms_req = $bdd->prepare($rooms_sql);

// events types
$event_types_sql = "SELECT id, name FROM events_types";
$event_types_req = $bdd->prepare($event_types_sql);

// teams
$teams_sql = "SELECT id FROM teams";
$teams_req = $bdd->prepare($teams_sql);

while ($events = $req->fetch()) {
    // get an array of individuals id that participate in the event
    $individuals_array = explode(',', $events['individuals']);
    // we remove the last entry of the array
    array_pop($individuals_array);

    echo "<section class='edit_event_item'>
        <p class='align_right'>
        <a href='delete.php?type=event&event_id=".$events['id']."'>Delete this event</a>
        </p>
        
        <form method='post' action='edit_event-exec.php'>
            <h4>When</h4> <input class='datepicker' name='date' value='". $events['date'] ."'/>";

            $rooms_req->execute();
            echo " <h4>Where</h4> <select name='room'>";
            while($rooms = $rooms_req->fetch()) {
                echo "<option ";
                echo "value='".$rooms['id']."' ";
                if ($events['room'] == $rooms['id']) {
                    echo "selected='selected'";
                }
                echo ">".$rooms['room_name']."</option>";
            }
            echo "</select>";
            echo "<h4> What </h4><select name='type'>";
            // Show event types
            $event_types_req->execute();
            while ($types = $event_types_req->fetch()) {
                echo "<option value='".$types['id']."' ";
                if ($events['type'] == $types['id']) {
                    echo "selected='selected'";
                }
                echo ">".$types['name']."</option>";
            }
            echo "</select>
            <br /><h4>Who</h4><br />




            <section>";
            $teams_req->execute();
            //show all the teams
            while ($teams = $teams_req->fetch()) {
                $team_id = $teams['id'];
                // get team name
                $team_name_sql = "SELECT team_name FROM teams WHERE id = :team_id";
                $team_name_req = $bdd->prepare($team_name_sql);
                $team_name_req->execute(array(
                    'team_id' => $team_id
                ));
                $team_info = $team_name_req->fetch();

                // get list of individuals
                $indiv_list_sql = "SELECT * FROM individuals WHERE team = :team_id ORDER BY firstname";
                $indiv_list_req = $bdd->prepare($indiv_list_sql);
                $indiv_list_req->execute(array(
                    'team_id' => $team_id
                ));

                echo "<div class='team'>TEAM ".$team_info['team_name']." :<br />";
                while($individuals = $indiv_list_req->fetch()) {
                    echo "<span><input type='checkbox' name='individuals[]' value='".$individuals['id']."' ";
                    if (in_array($individuals['id'], $individuals_array)) {
                        echo "checked='checked'";
                    }
                    echo ">".$individuals['firstname']." ".$individuals['lastname']."</span>";
                }
                echo "</div>";

                }
                        echo "</section>
                        <input type='hidden' name='edit_event' />
                        <input type='hidden' name='event_id' value='". $events['id']."' />
                        <div class='center'><input type='submit' class='button' value='Edit this event' /></div>
                    </form></section>";
}
?>

<script>
$(document).ready(function() {
    // DATEPICKER
    $( ".datepicker" ).datepicker({dateFormat: 'ymmdd'});
});
</script>
<?php require_once('inc/footer.php'); ?>
