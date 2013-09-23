<?php
/********************************************************************************
*                                                                               *
*   Copyright 2012 Nicolas CARPi (nicolas.carpi@gmail.com)                      *
*   http://www.elabftw.net/                                                     *
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
$page_title='Admin';
require_once('inc/head.php');
?>

<h2>NoForget!</h2>
<p class='center'><a href='index.php'>main page</a></p>

<div id='accordion'>
    <!-- MANAGE EVENTS -->
    <h3><a href='#'>Add event</a></h3>
    <div>
        <form method='post' action='admin-exec.php'>
            <h4>When</h4> <input id='datepicker' name='date' />
            <?php
            $sql = "SELECT * FROM rooms ORDER BY room_name";
            $req = $bdd->prepare($sql);
            $req->execute();
            echo "<h4>Where</h4> <select name='room'>";
            while($rooms = $req->fetch()) {
                echo "<option ";
                echo "value='".$rooms['id']."'>".$rooms['room_name']."</option>";
            }
            echo "</select>";
            ?>
            <h4> What</h4>
            <select name='type'>
            <?php // Show event types
            $sql = "SELECT id, name FROM events_types";
            $req = $bdd->prepare($sql);
            $req->execute();
            while ($types = $req->fetch()) {
            echo "<option value='".$types['id']."'>".$types['name']."</option>";
            }
            ?>
            </select>
            <br /><h4>Who</h4><br />
            <!-- DISPLAY TEAMS -->
            <section>
            <?php
            $sql = "SELECT id FROM teams";
            $req = $bdd->prepare($sql);
            $req->execute();
            while ($teams = $req->fetch()) {
                display_team($teams['id']);
            }
            ?>
            </section>
            <input type='hidden' name='add_event' />
            <div class='center'><input type='submit' class='button' value='Add event' /></div>
        </form>
    </div>

    <h3><a href='#'>Edit existing events</a></h3>
    <div>
Filter by type of event :
        <select onChange='showEvents(this.value)'>
        <option value=''>--- Select an event type ---</option>
            <?php
            // list all events type
            $sql = "SELECT id, name FROM events_types";
            $req = $bdd->prepare($sql);
            $req->execute();
            while ($events_types = $req->fetch()) {
                echo "<option value='". $events_types['id'] ."'>". $events_types['name'] ."</option>";
            }
            ?>
            </select>
            <script>
            function showEvents(id) {
                if (id == '') {
                    return;
                }
                location = "edit_event.php?event_type=" + id;
            }
            </script>

</div>

    <h3><a href='#'>Add new type of event</a></h3>
    <div>
        <form method='post' action='admin-exec.php'>
            Name : <input name='name' /><br />
            Subject of email : <input name='mail_subject' /><br />
            Text of email :<br />
            You can use :firstname, :lastname, :fullname, :room as jokers.<br />
            <textarea name='mail_body' cols='42' rows='15' /></textarea><br />
            Send a reminder the day before to team(s) :
            <ul>
                <?php
                $sql = "SELECT id, team_name FROM teams";
                $req = $bdd->prepare($sql);
                $req->execute();
                while ($teams = $req->fetch()) {
                    echo "<li>
                        <input id='".$teams['id']."' 
                        type='checkbox' 
                        name='reminder_team_id[]' 
                        value='".$teams['id']."' /><label for='".$teams['id']."'>".$teams['team_name']."
                        </li>";
                }
                ?>
            </ul>
            <label for='reminder_week_before'>Send a reminder the week before to the individual(s) : </label>
            <input id='reminder_week_before' type='checkbox' name='reminder_week_before' value='1' />
            <p>Body of the reminder email that will be sent a week before :</p>
            <textarea name='reminder_mail_body' cols='42' rows='15' /></textarea><br />
            <input type='hidden' name='add_event_type' />
            <div class='center'><input type='submit' class='button' value='Add event' /></div>
        </form>
    </div>


    <h3><a href='#'>Edit existing type of events</a></h3>
    <div>
        <?php
        $sql = "SELECT * FROM events_types ORDER BY id";
        $req = $bdd->prepare($sql);
        $req->execute();
        while($events_types = $req->fetch()) {
            echo "<form method='post' action='admin-exec.php'>";
            echo "<p class='align_right'>";
            echo "<a class='button' href='delete.php?type=event_type&event_type_id=".$events_types['id']."'>Delete this event type</a>";
            echo "</p>";
            echo "Name : <input name='name' value='".$events_types['name']."' /><br />";
            echo "Subject of email : <input name='mail_subject' value='".$events_types['mail_subject']."' /><br />";
            echo "<div class='align_right'><input type='submit' class='button' value='Edit this event' /></div>";
            echo "Text of email :<br />
                You can use :firstname, :lastname, :fullname, :room as jokers.<br /><textarea cols='42' rows='15' name='mail_body' />".$events_types['mail_body']."</textarea><br />";
            echo "For labmeetings, send reminders to team :";
            echo "<ul>";
            $team_sql = "SELECT id, team_name FROM teams";
            $team_req = $bdd->prepare($team_sql);
            $team_req->execute();
            // get an array of teams id that participate in the event
            $reminder_team_id_array = explode(',', $events_types['reminder_team_id']);
            // we remove the last entry of the array
            array_pop($reminder_team_id_array);
            while ($teams = $team_req->fetch()) {
                echo "<li>
                    <input id='".$teams['id']."' 
                    type='checkbox' 
                    name='reminder_team_id[]' 
                    value='".$teams['id']."' ";
                if (in_array($teams['id'], $reminder_team_id_array)) {
                    echo "checked='checked'";
                }
                echo "/><label for='".$teams['id']."'>".$teams['team_name']."
                </li>";
            }
            ?>
            </ul>
            <label for='reminder_week_before'>Send a reminder the week before to the individual(s) : </label>
            <input id='reminder_week_before' type='checkbox' name='reminder_week_before' value='1' 
            <?php
            if ($events_types['reminder_week_before'] == 1) {
                echo " checked='checked'";
            }
            ?>
             />
            <p>Body of the reminder email that will be sent a week before :</p>
            <textarea name='reminder_mail_body' cols='42' rows='15' /><?php
            if (!is_null($events_types['reminder_mail_body'])) {
                echo $events_types['reminder_mail_body'];
            }
?></textarea><br />
            <input type='hidden' name='edit_event_type' />
            <input type='hidden' name='event_type_id' value='<?php echo $events_types['id']; ?>' />
            </form>
            <hr>
<?php
        }
?>
    </div>

    <!-- MANAGE USERS -->
    <h3><a href='#'>Add new individual</a></h3>
    <div>
        <form method='post' action='admin-exec.php'>
            Firstname : <input name='firstname' /><br />
            Lastname : <input name='lastname' /><br />
            Email : <input name='email' /><br />
            Team : <select name='team' /><br />
            <?php // Show teams
            $sql = "SELECT id, team_name FROM teams";
            $req = $bdd->prepare($sql);
            $req->execute();
            while ($teams = $req->fetch()) {
                echo "<option value='".$teams['id']."'>".$teams['team_name']."</option>";
            }
            ?>
            </select><br />
            <input type='hidden' name='add_individual' />
            <div class='center'><input type='submit' class='button' value='Add individual' /></div>
        </form>
    </div>
    <h3><a href='#'>Edit existing individuals</a></h3>
    <div>
        <?php
        $sql = "SELECT * FROM individuals ORDER BY team, lastname ";
        $req = $bdd->prepare($sql);
        $req->execute();

        $sql = "SELECT id, team_name FROM teams";
        $teamsreq = $bdd->prepare($sql);

        while ($individuals = $req->fetch()) {
            echo "<form method='post' action='admin-exec.php'>";
            echo "<p class='align_right'>";
            echo "<a class='button' href='delete.php?type=individual&individual_id=".$individuals['id']."'>Delete this individual</a>";
            echo "</p><br />";
            echo "Firstname : <input name='firstname' value='".$individuals['firstname']."' /><br />";
            echo "Lastname : <input name='lastname' value='".$individuals['lastname']."' /><br />";
            echo "<div class='align_right'><input type='submit' class='button' value='Edit individual' /></div>";
            echo "Email : <input name='email' type='email' value='".$individuals['email']."' /><br />";
            echo "Team : ";
            // get the full list of teams
            $teamsreq->execute();
            echo "<select name='team'>";
            while ($teams = $teamsreq->fetch()) {
                echo "<option value='".$teams['id']."' ";
                if ($individuals['team'] == $teams['id']) {
                    echo "selected='selected'";
                }
                echo ">".$teams['team_name']."</option>";
            }
            echo "</select><br />";
            echo "<input type='hidden' name='edit_individual' />";
            echo "<input type='hidden' name='individual_id' value='".$individuals['id']."' />";
            echo "</form>";
            echo "<hr>";
            }
        ?>
    </div>

    <!-- MANAGE TEAM -->
    <h3><a href='#'>Add new team</a></h3>
    <div>
        <form method='post' action='admin-exec.php'>
            Team name : <input name='team_name' /><br />
            <input type='hidden' name='add_team' />
            <div class='center'><input type='submit' class='button' value='Add team' /></div>
        </form>
    </div>

    <h3><a href='#'>Edit existing teams</a></h3>
    <div>
        <?php
        $sql = "SELECT * FROM teams ORDER BY team_name";
        $req = $bdd->prepare($sql);
        $req->execute();
        while($teams = $req->fetch()) {
            echo "<form method='post' action='admin-exec.php'>";
            echo "<p class='align_right'>";
            echo "<a class='button' href='delete.php?type=team&team_id=".$teams['id']."'>Delete this team</a>";
            echo "</p>";
            echo "Team name : <input name='team_name' value='".$teams['team_name']."' /><br />";
            echo "<input type='hidden' name='edit_team' />";
            echo "<input type='hidden' name='team_id' value='".$teams['id']."' />";
            echo "<div class='center'><input type='submit' class='button' value='Edit team' /></div>";
            echo "</form>";
            echo "<hr>";
        }
        ?>
    </div>

    <!-- MANAGE ROOM -->
    <h3><a href='#'>Add new room</a></h3>
    <div>
        <form method='post' action='admin-exec.php'>
            <input type='hidden' name='add_room' />
            Room name : <input name='room_name' />
            <div class='center'><input type='submit' class='button' value='Add room' /></div>
        </form>
    </div>

    <h3><a href='#'>Edit existing rooms</a></h3>
    <div>
        <?php
        $sql = "SELECT * FROM rooms ORDER BY room_name";
        $req = $bdd->prepare($sql);
        $req->execute();
        while($rooms = $req->fetch()) {
            echo "<form method='post' action='admin-exec.php'>";
            echo "<p class='align_right'>";
            echo "<a class='button' href='delete.php?type=room&room_id=".$rooms['id']."'>Delete this room</a>";
            echo "</p>";
            echo "<input type='hidden' name='edit_room' />";
            echo "<input type='hidden' name='room_id' value='".$rooms['id']."' />";
            echo "Name : <input name='room_name' value='".$rooms['room_name']."' /><br />";
            echo "<div class='center'><input type='submit' class='button' value='Edit room' /></div>";
            echo "</form>";
            echo "<hr>";
        }

        ?>
    </div>


</div> <!-- END ACCORDION -->

<script>
$(document).ready(function() {
    // DATEPICKER
    $( "#datepicker" ).datepicker({dateFormat: 'ymmdd'});
    // ACCORDION
    $( "#accordion" ).accordion({ 
        autoHeight: false,
        animated: 'bounceslide',
        collapsible: true,
        active: false
        });
});
</script>
<?php require_once('inc/footer.php'); ?>
