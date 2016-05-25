<?

require_once("global.inc.php");
$g->checkLogin();

$res = &$g->query("SELECT 
    m.ID, m.Nachname, m.Vorname, m.Strasse, m.Hausnummer, m.PLZ, m.Ort, m.EMail, m.TelefonPrivat, m.TelefonGeschaeftlich, m.TelefonHandy, m.ICQNr, m.SchiedsrichterlizenzGueltigBis, NOT ISNULL(mtty.TeilnahmetypID) AS AktiverSpieler, tty.Name AS Teilnahmetyp
  FROM
    vb_mitglieder m
    LEFT JOIN vb_mitgliederteilnahmetypen mtty ON mtty.MitgliedsID = m.ID
    LEFT JOIN vb_teilnahmetypen tty ON mtty.TeilnahmetypID = tty.ID
  WHERE
    m.TeamID = {$g->teamID}
  ORDER BY
    ISNULL(mtty.TeilnahmetypID), m.Nachname, m.Vorname, tty.Name
");
if ($g->zugriffsLevel >= ZUGRIFFSLEVEL_SUPERVISOR)
  $g->out.= "<a href=\"javascript:editFenster('mitgliedsdaten.php?new={$g->teamID}')\">Neues Mitglied hinzufügen</a>";
if (mysql_num_rows($res) > 0) {
  $g->out.= "<h3>Mannschaftsmitglieder</h3>";
  $g->out.= "<table border=1 cellspacing=0 cellpadding=5 class='daten'><tr><th>Name</th><th>Strasse</th><th>Ort</th><th>Tel. Pivat</th><th>Tel. Geschäftl.</th><th>Handy</th><th>ICQ/AIM</th><th>Nimmt teil an</th><th>&nbsp;</th></tr>\n";
  do {
    $row = mysql_fetch_assoc($res);
    if ($rowlast['ID'] == $row['ID'])
      $rowlast['Teilnahmetyp'].= ", ".$row['Teilnahmetyp'];
    else { 
      if ($rowlast['ID'] != '') {  
        $g->out.= "<tr class='".($rowlast['AktiverSpieler'] ? "angemeldet" : "abgemeldet")."'>";
        $g->out.= "<td>".$g->text($rowlast['Vorname'])." ".$g->text($rowlast['Nachname'])."&nbsp;".($rowlast['EMail'] != "" ? "<a href='mailto:{$rowlast['EMail']}'><img src='email.gif' width='17' height='10' border=0></a>" : "")."</td>";
        $g->out.= "<td>".$g->text($rowlast['Strasse']." ".$rowlast['Hausnummer'])."</td>";
        $g->out.= "<td>".$g->text($rowlast['PLZ']." ".$rowlast['Ort'])."</td>";
        $g->out.= "<td>".$g->text($rowlast['TelefonPrivat'], "-")."</td>";
        $g->out.= "<td>".$g->text($rowlast['TelefonGeschaeftlich'], "-")."</td>";
        $g->out.= "<td>".$g->text($rowlast['TelefonHandy'], "-")."</td>";
        if ((string)(int)$rowlast['ICQNr'] == $rowlast['ICQNr']) {
          $lnka = "<a href='http://www.icq.com/whitepages/cmd.php?uin={$rowlast['ICQNr']}&action=add'>";
          $g->out.= "<td>$lnka{$rowlast['ICQNr']}</a>&nbsp;$lnka<img src='http://status.icq.com/online.gif?icq={$rowlast['ICQNr']}&img=5' border=0 height='18' width='18'></a></td>";
        }  
        else
          $g->out.= "<td>".$g->text($rowlast['ICQNr'], "-")."</td>";
        $g->out.= "<td>".($rowlast['AktiverSpieler'] ? $rowlast['Teilnahmetyp'] : "Nicht aktiv")."</td>";
        $g->out.= "<td>".(($g->zugriffsLevel >= ZUGRIFFSLEVEL_SUPERVISOR || $rowlast['ID'] == $g->mitgliedsID) ? "<a href=\"javascript:editFenster('mitgliedsdaten.php?edit={$rowlast['ID']}')\"><img src='edit.gif' border=0></a>" : "&nbsp;")." ".($g->zugriffsLevel >= ZUGRIFFSLEVEL_SUPERVISOR ? "<a href=\"javascript:editFenster('mitgliedsdaten.php?del={$rowlast['ID']}')\"><img src='del.gif' border=0></a>" : "&nbsp;")."</td>";
        $g->out.= "</tr>\n";
        if ($rowlast['ID'] != $g->mitgliedsID)
          $email.= $rowlast['EMail'] != "" ? ($email != "" ? "," : "").$rowlast['EMail'] : "";
      }   
      $rowlast = $row;
    }   
  } while ($row);
  $g->out = "<a href='mailto:$email'>E-Mail direkt an alle</a> (Nicht über den Verteiler [schneller bei der Zustellung])<p>".$g->out."</table>\n";
}
else {
  $g->out.= "Keine Mitglieder in dieser Mannschaft gefunden.";
}
    
$g->doOutput("Mannschaftsliste", $template);   

?>