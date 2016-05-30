<?

require_once("global.inc.php");
$g->checkLogin();

if ($g->zugriffsLevel >= ZUGRIFFSLEVEL_TRAINER) {
  if ($_POST['TerminIDAbmelden'] > 0 && $_POST['MitgliedsID'] > 0) {
    $g->query("REPLACE INTO vb_abmeldungen SET TerminID = '{$_POST['TerminIDAbmelden'] }', MitgliedsID = '{$_POST['MitgliedsID']}', Datum = NOW()");
  }
  else if ($_POST['TerminIDAnmelden'] > 0 && $_POST['MitgliedsID'] > 0) {
    $g->query("DELETE FROM vb_abmeldungen WHERE TerminID = '{$_POST['TerminIDAnmelden'] }' AND MitgliedsID = '{$_POST['MitgliedsID']}'");
  }
}

if (isset($_GET['month']))
  $date = strtotime($_GET['month']);
else  
  $date = time();

$monat = strftime("%m", $date);
$jahr = strftime("%Y", $date);

$prevDate = $monat > 1 ? "$jahr-".($monat-1)."-01" : ($jahr-1)."-12-01";
$nextDate = $monat < 12 ? "$jahr-".($monat+1)."-01" : ($jahr+1)."-01-01";

$g->out.= "<a href='".$g->getSelfURI(array("month"))."month=$prevDate'>Einen Monat zurück</a> | <a href='".$g->getSelfURI(array("month"))."'>Aktueller Monat</a> | <a href='".$g->getSelfURI(array("month"))."month=$nextDate'>Einen Monat vor</a><p>";

$res = &$g->query("SELECT
    NOT ISNULL(a.ID) AS Abgemeldet, m.ID AS MitgliedsID, t.ID AS TerminID, a.Datum 
  FROM 
    vb_termine t INNER JOIN vb_termintypen ty INNER JOIN vb_mitgliederteilnahmetypen mtty INNER JOIN vb_mitglieder m 
    LEFT JOIN vb_abmeldungen a ON a.TerminID = t.ID AND a.MitgliedsID = m.ID
  WHERE 
    t.TeamID = {$g->teamID} AND YEAR(t.DatumVon) = '$jahr' AND MONTH(t.DatumVon) = '$monat' AND
    m.TeamID = t.TeamID AND
    ty.ID = t.TermintypID AND
    mtty.TeilnahmetypID = ty.TeilnahmetypID AND
    mtty.MitgliedsID = m.ID");
while ($row = mysql_fetch_assoc($res)) {
  $status[$row['MitgliedsID']][$row['TerminID']] = $row['Abgemeldet'] ? $row['Datum'] : "";
}

$res = &$g->query("SELECT t.ID, tty.ID AS TeilnametypID, tty.Name AS Teilnahmetyp, ty.Namenszusatz AS TermintypZusatz, t.DatumVon, t.DatumBis, t.Name, t.Ort, t.DatumBis < NOW() AS Vergangen FROM vb_termine t LEFT JOIN vb_termintypen ty ON ty.ID = t.TermintypID LEFT JOIN vb_teilnahmetypen tty ON tty.ID = ty.TeilnahmetypID WHERE t.TeamID = {$g->teamID} AND YEAR(t.DatumVon) = '$jahr' AND MONTH(t.DatumVon) = '$monat' ORDER BY t.DatumVon");
while ($row = mysql_fetch_assoc($res)) {
  $termine[$row['ID']]['Name'] = $row['Name'];
  $termine[$row['ID']]['Teilnahmetyp'] = $row['Teilnahmetyp'];
  $termine[$row['ID']]['TermintypZusatz'] = $row['TermintypZusatz'];
  $termine[$row['ID']]['DatumVon'] = $row['DatumVon'];
  $termine[$row['ID']]['DatumBis'] = $row['DatumBis'];
  $termine[$row['ID']]['Vergangen'] = $row['Vergangen'];
  $termine[$row['ID']]['AngemeldeteSpieler'] = 0; //Wird später berechnet
  $teilnahmetypenList.= ($teilnahmetypenList != '' ? "," : "")."{$row['TeilnametypID']}";
}

$res = &$g->query("SELECT m.ID, m.Nachname, m.Vorname, m.EMail, m.SchiedsrichterlizenzGueltigBis FROM vb_mitglieder m INNER JOIN vb_mitgliederteilnahmetypen mtty WHERE m.TeamID = {$g->teamID} AND mtty.MitgliedsID = m.ID AND FIND_IN_SET(mtty.TeilnahmetypID,'$teilnahmetypenList') GROUP BY m.ID ORDER BY m.Nachname, m.Vorname");
while ($row = mysql_fetch_assoc($res)) {
  $mitglieder[$row['ID']]['Nachname'] = $row['Nachname'];
  $mitglieder[$row['ID']]['Vorname'] = $row['Vorname'];
  $mitglieder[$row['ID']]['EMail'] = $row['EMail'];
}

if ($g->zugriffsLevel >= ZUGRIFFSLEVEL_TRAINER)
  $g->out.= "<a href=\"javascript:editFenster('termindaten.php?new=$jahr-$monat-01')\">Neuen Termin anlegen</a>";
$g->out.= "<h3>Termine im ".$g->getMonthName($monat)." $jahr</h3>";  
if (is_array($termine)) {
  $g->out.= "<table border=1 cellspacing=0 cellpadding=5 class='daten'><tr><th>&nbsp;</th>";
  foreach ($termine as $id => $felder) {
    $lab =  "<small>{$felder['Teilnahmetyp']}</small><br><a href='termin.php?id=$id'>{$felder['Name']}</a>".($g->zugriffsLevel >= ZUGRIFFSLEVEL_TRAINER ? "&nbsp;<a href=\"javascript:editFenster('termindaten.php?edit=$id')\"><img src='edit.gif' border=0></a>&nbsp;<a href=\"javascript:editFenster('termindaten.php?del=$id')\"><img src='del.gif' border=0></a>&nbsp;<a href=\"javascript:editFenster('termindaten.php?deactivate=$id')\"><img src='deactivate.gif' border=0></a>" : "");
    $zus = $g->getDateString($felder['DatumVon']) == $g->getDateString($felder['DatumBis']) ? "<br>".$g->getTimeString($felder['DatumVon'])." - ".$g->getTimeString($felder['DatumBis']) : "&nbsp;".$g->getTimeString($felder['DatumVon'])."&nbsp;Uhr -<br>".$g->getDayNameShort($felder['DatumBis'])."&nbsp;".$g->getDateString($felder['DatumBis'])."&nbsp;".$g->getTimeString($felder['DatumBis']);
    $class = $felder['Vergangen'] ? ' class="vergangen"' : "";
    $g->out.= "<th$class>$lab<br><small>".$g->getDayNameShort($felder['DatumVon'])."&nbsp;".$g->getDateString($felder['DatumVon'])."$zus&nbsp;Uhr</small></th>";
  }  
  $g->out.= "</tr>\n";  
  foreach ($mitglieder as $mid => $mfelder) {
    $g->out.= "<tr>";  
    $g->out.= "<td class='footer'>{$mfelder['Vorname']} {$mfelder['Nachname']}".($mfelder['EMail'] != "" ? "&nbsp;<a href='mailto:{$mfelder['EMail']}'><img src='email.gif' width='17' height='10' border=0></a>" : "")."</td>";
    foreach ($termine as $tid => $tfelder) {
      if (isset($status[$mid][$tid]))
        $termine[$tid]['AktiveSpieler']++;
      if ($status[$mid][$tid] === "")
        $termine[$tid]['AngemeldeteSpieler']++;
      $but = $g->zugriffsLevel >= ZUGRIFFSLEVEL_TRAINER ? "<input type='submit' value='Sw.'>" : "";
      if (!isset($status[$mid][$tid]))
        $g->out.= "<td class='nicht_aktiv'>-</td>";
      else {
        $vergangen = $termine[$tid]['Vergangen'] ? "_vergangen" : "";
		if ($status[$mid][$tid] != "")
          $td = "<td class='abgemeldet$vergangen'>Abgemeldet $but<br><small>".$g->getDateString($status[$mid][$tid])." ".$g->getTimeString($status[$mid][$tid])."&nbsp;Uhr</small></td>";
        else
          $td = "<td class='angemeldet$vergangen'>Angemeldet $but</td>";
        if ($g->zugriffsLevel >= ZUGRIFFSLEVEL_TRAINER)
          $g->out.= "<form method='POST' action='".$g->getSelfUri()."'>$td <input type='hidden' name='".($status[$mid][$tid] != "" ? 'TerminIDAnmelden' : 'TerminIDAbmelden')."' value='$tid'><input type='hidden' name='MitgliedsID' value='$mid'></form>";
        else  
          $g->out.= "$td";
      }    
    }  
    $g->out.= "</tr>\n";  
  }    
  $g->out.= "<tr><td class='footer'>Angemeldete Spieler:</td>";
  foreach ($termine as $tid => $tfelder) {
    $vergangen = $termine[$tid]['Vergangen'] ? "_vergangen" : "";
    $g->out.= "<td style='text-align:right' class='footer$vergangen'>{$termine[$tid]['AngemeldeteSpieler']} / {$termine[$tid]['AktiveSpieler']}</td>";
  }  
  $g->out.= "</tr>";
  $g->out.= "</table>\n";  
}  

$g->doOutput("Anwesenheitsliste - ".$g->getMonthName($monat)." $jahr");   

?>