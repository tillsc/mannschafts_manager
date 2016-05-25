<?

require_once("global.inc.php");
$g->checkLogin();

$title = "Termin nicht gefunden";

$res = &$g->query("SELECT
    t.ID, t.DatumVon, t.DatumBis, t.Name, t.Ort, t.Bemerkungen, ty.TeilnahmetypID, tty.Name AS Termintyp1, ty.Namenszusatz AS Termintyp2
  FROM 
    vb_termine t INNER JOIN vb_termintypen ty
    LEFT JOIN vb_teilnahmetypen tty ON ty.TeilnahmetypID = tty.ID
  WHERE 
    t.TermintypID = ty.ID AND
    t.TeamID = {$g->teamID} AND t.ID = '{$_GET['id']}'");
if ($row = mysql_fetch_assoc($res)) {
  $title = "{$row['Termintyp1']} {$row[Name]}";
      $datum = $g->getDayNameShort($row['DatumVon'])." ".$g->getDateString($row['DatumVon'])."&nbsp;".$g->getTimeString($row['DatumVon'])."&nbsp;Uhr&nbsp;- ";
      if ($g->getDateString($row['DatumVon']) != $g->getDateString($row['DatumBis']))
        $datum.= $g->getDateString($row['DatumBis'])."&nbsp;";
      $datum.= $g->getTimeString($row['DatumBis'])."&nbsp;Uhr";   
  $editlink = $g->zugriffsLevel >= ZUGRIFFSLEVEL_TRAINER ? "&nbsp;<a href=\"javascript:editFenster('termindaten.php?edit={$row['ID']}')\"><img src='edit.gif' border=0></a>" : "";    
  $g->out.= "<table border=1 cellspacing=0 cellpadding=5 class='daten'>\n";
  $g->out.= "<tr><th>Name:</th><td>".$g->text($row['Name'])."</td></tr>\n";  
  $g->out.= "<tr><th>Typ:</th><td>".$g->text($row['Termintyp1']).($row['Termintyp2'] != '' ? "<br><small>{$row['Termintyp2']}</small>" : '')."</td></tr>\n";  
  $g->out.= "<tr><th>Datum:</th><td>$datum</td></tr>\n";  
  $g->out.= "<tr><th>Ort:</th><td>".$g->text($row['Ort'])."</td></tr>\n";  
  $g->out.= "<tr><th>Bemerkungen:</th><td>".nl2br($g->text($row['Bemerkungen']))."</td></tr>\n";  
  $g->out.= "</table>\n"; 
  $res2 = &$g->query("SELECT
      m.Vorname, m.Nachname, NOT ISNULL(a.ID) AS Abgemeldet
    FROM 
      vb_mitglieder m INNER JOIN vb_mitgliederteilnahmetypen mtty
      LEFT JOIN vb_abmeldungen a ON a.MitgliedsID = m.ID AND a.TerminID = '{$row['ID']}'
    WHERE 
      m.TeamID = '".$g->teamID."' AND
      mtty.MitgliedsID = m.ID AND mtty.TeilnahmetypID = '{$row['TeilnahmetypID']}' 
    ORDER BY Nachname, Vorname");
  $g->out.= "<h3>Teilnehmer</h3><table border=1 cellspacing=0 cellpadding=5 class='daten'>\n<tr><th>Name</th><th>Status</th></tr>\n";
  $angemeldete = 0;
  while ($row2 = mysql_fetch_assoc($res2)) {
    $g->out.= "<tr class='".($row2['Abgemeldet'] ? 'abgemeldet' : 'angemeldet')."'><td>{$row2['Vorname']} {$row2['Nachname']}</td><td>".($row2['Abgemeldet'] ? "Abgemeldet" : "Angemeldet")."</td></tr>\n";  
    if (!$row2['Abgemeldet'])
      $angemeldete++;
  } 
  $g->out.= "<tr><td>Angemeldete Spieler:</td><td>$angemeldete</td></tr>\n</table>\n"; 
}  
$g->out = "<h3>$title$editlink</h3>\n".$g->out;

$g->doOutput($title);   

?>