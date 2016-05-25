<?

require_once("global.inc.php");
//ini_set('session.use_only_cookies',0);
if ($_GET[logout] == 1) {
  unset($_SESSION['MitgliedsID']);
  $g->getLoginData();
  unset($_GET[logout]);
}

if (!$g->eingeloggt) {
  $data = $_POST;
  if ($data['EMail'] != '') {
    $res = &$g->query("SELECT ID FROM vb_mitglieder WHERE TeamID = '{$data['TeamID']}' AND EMail = '{$data['EMail']}' AND Passwort = MD5('{$data['Passwort']}')");
    if ($row = mysql_fetch_assoc($res)) {
      $_SESSION['MitgliedsID'] = $row['ID'];
      $g->getLoginData();
	  //print "index.php: Session_ID:".$_SESSION['MitgliedsID'];
	
      setcookie('LastEMail', $data['EMail'], time()+60*60*24*300);
      setcookie('LastTeamID', $data['TeamID'], time()+60*60*24*300);
	  setcookie('MitgliedsID',$data['MitgliedsID'], time()+60*60*24*300);
    }
    else {
      $g->out.= "<div style='font-weight: bold; color: red'>Login fehlgeschlagen!</div><br>\n";
    }
  }
  else {
    $data['EMail'] = $_COOKIE['LastEMail'];
    $data['TeamID'] = $_COOKIE['LastTeamID'];
	$data['MitgliedsID'] = $_COOKIE['MitgliedsID'];
  }
  if (!$g->eingeloggt) {
    $g->out.= "<h3>Bitte logge dich ein:</h3>\n<form action='".$g->getSelfURI()."' method='POST'>\n<table cellspacing=5 cellpadding=5>\n<tr><td>Mannschaft:</td><td><select name='TeamID'>\n";
    $res = &$g->query("SELECT ID, Name FROM vb_teams ORDER BY Name");
    while ($row = mysql_fetch_assoc($res))
      $g->out.= "<option value='{$row['ID']}'".($row['ID'] == $data['TeamID'] ? ' selected' : '').">{$row['Name']}</option>\n";
    $g->out.= "</select>\n</td></tr>\n<tr><td>EMail:</td><td><input type='text' name='EMail' value='{$data['EMail']}'></td></tr>\n";
    $g->out.= "<tr><td>Passwort:</td><td><input type='password' name='Passwort'></td></tr>\n<tr><td colspan='2'><input type='submit'></td></tr>\n</table>\n</form>\n";
    $template = "login";
    $titel = "Login";
  }  
}
if ($g->eingeloggt) {
  $g->out.= "<h2>Hallo {$g->mitgliedsVorname}</h2>\n";
  if ($_POST['TerminIDAbmelden'] > 0) {
    $g->query("REPLACE INTO vb_abmeldungen SET TerminID = '{$_POST['TerminIDAbmelden'] }', MitgliedsID = '{$g->mitgliedsID}', Datum = NOW()");
  }
  else if ($_POST['TerminIDAnmelden'] > 0) {
    $g->query("DELETE FROM vb_abmeldungen WHERE TerminID = '{$_POST['TerminIDAnmelden'] }' AND MitgliedsID = '{$g->mitgliedsID}'");
  }
  $res = &$g->query("SELECT 
      t.ID, t.DatumVon, t.DatumBis, t.Name, t.Ort, t.Bemerkungen,
      tty.Name AS Typ1, ty.Namenszusatz AS Typ2, 
      DATE_SUB(t.DatumVon, INTERVAL ty.Deadline DAY) > NOW() AS IstAbmeldbar, 
      DATE_SUB(t.DatumVon, INTERVAL ty.Deadline DAY) AS AbmeldbarBis,
      NOT ISNULL(ab.ID) AS Abgemeldet, ab.Datum AS AbmeldeDatum, ab.ID AS AbmeldeID,
      COUNT(mtty_all.MitgliedsID) AS GesamtSpieler, SUM(ISNULL(ab_all.ID)) AS AngemeldeteSpieler
    FROM 
      vb_termine t INNER JOIN vb_termintypen ty INNER JOIN vb_mitgliederteilnahmetypen mtty INNER JOIN
      vb_mitglieder m_all INNER JOIN vb_mitgliederteilnahmetypen mtty_all
      LEFT JOIN vb_teilnahmetypen tty ON tty.ID = ty.TeilnahmetypID 
      LEFT JOIN vb_abmeldungen ab ON ab.TerminID = t.ID AND ab.MitgliedsID = '{$g->mitgliedsID}'
      LEFT JOIN vb_abmeldungen ab_all ON ab_all.TerminID = t.ID AND ab_all.MitgliedsID = mtty_all.MitgliedsID
    WHERE 
      t.DatumBis > NOW() AND t.TeamID = '{$g->teamID}'  AND
      ty.ID = t.TermintypID AND
      m_all.TeamID = '{$g->teamID}' AND 
      m_all.ID = mtty_all.MitgliedsID AND mtty_all.TeilnahmetypID = ty.TeilnahmetypID AND
      mtty.MitgliedsID = '{$g->mitgliedsID}' AND mtty.TeilnahmetypID = ty.TeilnahmetypID 
    GROUP BY
      t.ID
    ORDER BY DatumVon
  ");
  if (mysql_num_rows($res) > 0) {
    $g->out.= "<h3>Deine zukünftigen Termine</h3>";
    $g->out.= "<table border=1 cellspacing=0 cellpadding=5 class='daten'><tr><th>Datum</th><th>Typ</th><th>Name</th><th>Dabei</th><th>Details</th><th>Abmeldbar bis</th><th>Dein Status</th><th>&nbsp;</th></tr>\n";
    while ($row = mysql_fetch_assoc($res)) {
      $datum = $g->getDayNameShort($row['DatumVon'])." ".$g->getDateString($row['DatumVon']);
      if ($g->getDateString($row['DatumVon']) == $g->getDateString($row['DatumBis']))
        $datum.= "<br><small>".$g->getTimeString($row['DatumVon'])."&nbsp;-&nbsp;".$g->getTimeString($row['DatumBis']);
      else
        $datum.= "&nbsp;".$g->getTimeString($row['DatumVon'])."&nbsp;Uhr&nbsp;-<br><small>".$g->getDayNameShort($row['DatumBis'])." ".$g->getDateString($row['DatumBis'])."&nbsp;".$g->getTimeString($row['DatumBis']);
      $datum.= "&nbsp;Uhr</small>"; 
        if ($row['Abgemeldet']) {
          $spielersatus = "Abgemeldet<br><small>(".$g->getDateString($row['AbmeldeDatum'])."&nbsp;".$g->getTimeString($row['AbmeldeDatum'])."&nbsp;Uhr)</small>"; 
          $aktionen = "<form action='".$g->getSelfURI()."' METHOD='POST'>\n<td>\n<input type='hidden' name='TerminIDAnmelden' value='{$row['ID']}'>\n<input type='submit' value='Wieder anmelden'>\n</td>\n</form>\n";
        }
        else {
          $spielersatus = "Du bist dabei!";
          if ($row['IstAbmeldbar'])
            $aktionen = "<form action='".$g->getSelfURI()."' METHOD='POST'>\n<td>\n<input type='hidden' name='TerminIDAbmelden' value='{$row['ID']}'>\n<input type='submit' value='".($row['IstAbmeldbar'] ? 'Abmelden' : 'Abmelden [unter Protest!]')."'>\n</td>\n</form>\n";
          else 
            $aktionen = "<td>Abmeldung nur noch über <a href='mailto:{$g->trainerEMail}'>Trainer</a></td>"; 
        }    
      $abmeldbarBisDatum = $g->getDayNameShort($row['AbmeldbarBis'])." ".$g->getDateString($row['AbmeldbarBis'])."<br><small>".$g->getTimeString($row['AbmeldbarBis'])."&nbsp;Uhr</small>";
      $details = "<div id='det{$row['ID']}' class='popup' style='position: absolute; width: 300px; height: 150px; visibility: hidden' onmouseout=\"det{$row['ID']}.style.visibility='hidden'\">\n<b>Ort:</b> ".$g->text($row['Ort'])."<br><b>Bemerkungen:</b> ".nl2br($g->text($row['Bemerkungen'], "-"))."\n</div>\n<div onmouseover=\"showPopupHint('det{$row['ID']}');\">Ort, ...</div>";
      $g->out.= "<tr class='".(($row['Abgemeldet']) ? "abgemeldet" : "angemeldet")."'><td>$datum</td><td>{$row['Typ1']}".($row['Typ1'] != '' ? "<br><small>{$row['Typ2']}</small>" : "")."</td><td><a href='termin.php?id={$row['ID']}'>".$g->text($row['Name'])."</a></td><td>{$row['AngemeldeteSpieler']} von {$row['GesamtSpieler']}</td><td>$details</td><td>".($row['IstAbmeldbar'] ? $abmeldbarBisDatum : "Nicht mehr abmeldbar!<br><small>War bis: $abmeldbarBisDatum</small>")."</td><td>$spielersatus</td>$aktionen</tr>\n";
    }
    $g->out.= "</table>\n";
  }
  else {
    $g->out.= "Keine Termine gefunden.";
  }
  $titel = "Hauptseite";
}
    
$g->doOutput($titel, $template);   

?>