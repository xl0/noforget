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
$page_title='Index';
require_once('inc/head.php');
?>
<h2>NoForget!</h2>
<p class='center'><a href='admin.php'>admin page</a></p>

<div id='accordion'>
<?php
// list all events types
$sql = "SELECT id, name FROM events_types";
$req = $bdd->prepare($sql);
$req->execute();
while ($events_types = $req->fetch()) {
    echo "<h3><a href='#".$events_types['name']."'>".$events_types['name']."</a></h3>";
    echo "<div>";
    show($events_types['id']);
    echo "</div>";
}
?>
</div> <!-- end accordion -->
</section>

<script>
// ACCORDION
$(function() {
    $( "#accordion" ).accordion({ 
        autoHeight: false,
        animated: 'bounceslide',
        collapsible: true,
        active: false
    });
});
</script>
<?php require_once('inc/footer.php'); ?>
