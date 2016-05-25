<?

require_once("global.inc.php");
$g->checkLogin();
if ($_GET['edit'] != $g->mitgliedsID) //Nur wenn man sich selbst "editiert", muss man kein Supervisor sein
  $g->checkLogin(ZUGRIFFSLEVEL_SUPERVISOR);

if ($_GET['edit'] > 0 || $_GET['new'] > 0) {
  $keineausgabe = false;
  if (isset($_POST['EMail'])) {
    $daten = $_POST;
    if (!preg_match('/^.+@.+\..+$/', $daten['EMail']))
      $fehler['E-Mail'] = "Die E-Mail-Adresse ist falsch formartiert";
    if (!preg_match('/^.+$/', $daten['Nachname']))
      $fehler['Nachnamen'] = "Sie müssen einen Nachnamen angeben";
    if (!preg_match('/^.+$/', $daten['Vorname']))
      $fehler['Vorname'] = "Sie müssen einen Vornamen angeben";
    if ($_GET['edit'] <= 0 && $daten['Passwort1'] == '')
      $fehler['Passwort'] = "Kein Passwort angegeben";
    elseif ($daten['Passwort1'].$daten['Passwort2'] != "" && $daten['Passwort1'] != $daten['Passwort2'])
      $fehler['Passwort'] = "Passwörter stimmen nicht überein";
    if ($g->zugriffsLevel >= ZUGRIFFSLEVEL_SUPERVISOR && $daten['ZugriffsLevel'] > $g->zugriffsLevel)
      $fehler['ZugriffsLevel'] = "Das Zugriffslevel ist höher als dein eigenes";
    if (isset($fehler)) {
      $g->out.= "<b>Fehlerhafte Eingaben:</b>\n";
      foreach($fehler as $feld => $text)
        $g->out.= "<li>$feld: $text<br>\n";
      $g->out.= "<p>";  
    }
    else {
      $sqlvalues = "Vorname = '{$daten['Vorname']}', Nachname = '{$daten['Nachname']}', Strasse = '{$daten['Strasse']}', Hausnummer = '{$daten['Hausnummer']}', PLZ = '{$daten['PLZ']}', Ort = '{$daten['Ort']}', EMail = '{$daten['EMail']}', TelefonPrivat = '{$daten['TelefonPrivat']}', TelefonGeschaeftlich = '{$daten['TelefonGeschaeftlich']}', TelefonHandy = '{$daten['TelefonHandy']}', ICQNr = '{$daten['ICQNr']}', SchiedsrichterlizenzID = '{$daten['SchiedsrichterlizenzID']}', SchiedsrichterlizenzGueltigBis = '{$daten['SchiedsrichterlizenzGueltigBis']}'";
      if ($g->zugriffsLevel >= ZUGRIFFSLEVEL_SUPERVISOR)
        $sqlvalues.= ", ZugriffsLevel = '{$daten['ZugriffsLevel']}'";
      if ($daten['Passwort1'] != '')  
        $sqlvalues.= ", Passwort = MD5('{$daten['Passwort1']}')";
      if ($_GET['edit'] > 0) {
        $g->query("UPDATE vb_mitglieder SET $sqlvalues WHERE ID = '{$_GET['edit']}'");
        $id = $_GET['edit'];
      }  
      else {
        $g->query("INSERT INTO vb_mitglieder SET TeamID = '{$_GET['new']}', $sqlvalues");
        $id = mysql_insert_id($g->db);
      }
      $g->query("DELETE FROM vb_mitgliederteilnahmetypen WHERE MitgliedsID = '$id'");  
      $ttysql = '';
      if (is_array($daten['Teilnahmetypen']))
        foreach ($daten['Teilnahmetypen'] as $ttyid) 
          $ttysql.= ($ttysql != '' ? ", " : "")."($id, $ttyid)";
      if ($ttysql != '')
        $g->query("INSERT INTO vb_mitgliederteilnahmetypen (MitgliedsID, TeilnahmetypID) VALUES $ttysql");
      $g->out.= "Eingaben gespeichert.";
      $g->out.= "<script language='javascript'>doParentReload();window.close()</script>";
      $keineausgabe = true;
    }
  }
  elseif ($_GET['edit'] > 0) {
    $res = $g->query("SELECT 
        Vorname, Nachname, Strasse, Hausnummer, PLZ, Ort, EMail, ZugriffsLevel, TelefonPrivat, TelefonGeschaeftlich, TelefonHandy, ICQNr, SchiedsrichterlizenzID, SchiedsrichterlizenzGueltigBis
      FROM
        vb_mitglieder
      WHERE
        ID = '{$_GET['edit']}'");
    $daten = mysql_fetch_assoc($res);    
    $res = $g->query("SELECT TeilnahmetypID FROM vb_mitgliederteilnahmetypen WHERE MitgliedsID = '{$_GET['edit']}'");
    while ($row2 = mysql_fetch_assoc($res)) {
      $daten['Teilnahmetypen'][] = $row2['TeilnahmetypID'];
    }
  }
  else {
    //$daten['']  = DefaultWert
  }
  if (!$keineausgabe) {
    $g->out.= "<form action='".$g->getSelfURI()."' method='POST'><table>\n";
    $g->out.= "<tr><td>Vorname:</td><td><input type='text' name='Vorname' value='{$daten['Vorname']}'></td><td>Nachname:</td><td><input type='text' name='Nachname' value='{$daten['Nachname']}'></td></tr>\n";
    $g->out.= "<tr><td>E-Mail:</td><td><input type='text' name='EMail' value='{$daten['EMail']}'></td><td>Zugriffslevel:</td><td><select name='ZugriffsLevel'>";
    foreach (array("Normal" => ZUGRIFFSLEVEL_NORMAL, "Trainer" => ZUGRIFFSLEVEL_TRAINER, "Supervisor" => ZUGRIFFSLEVEL_SUPERVISOR, "Admin" => ZUGRIFFSLEVEL_ADMIN) as $label => $zl)
      if ($zl <= $g->zugriffsLevel)
        $g->out.= "<option value='$zl'".($zl == $daten['ZugriffsLevel'] ? ' selected' : '').">$label</option>";
    $g->out.= "</td></tr>\n";
    $g->out.= "<tr><td>Passwort:<br><small>Leer => Keine Änderung</small></td><td><input type='password' name='Passwort1'></td><td>Wiederholen:</td><td><input type='password' name='Passwort2'></td></tr>\n";
    $g->out.= "<tr><th colspan=4>Spielt mit bei:</th></tr>\n";
    $res2 = $g->query("SELECT ID, Name FROM vb_teilnahmetypen tty ORDER BY Name");
    while ($row2 = mysql_fetch_assoc($res2)) {
        $g->out.= "<tr><td>&nbsp;</td><td colspan=3><input type='checkbox' value='{$row2['ID']}' name='Teilnahmetypen[]'".((is_array($daten['Teilnahmetypen']) && in_array($row2['ID'], $daten['Teilnahmetypen'])) ? " checked" : "")."> {$row2['Name']}</td></tr>\n";
    }    
    $g->out.= "<tr><th colspan=4>Kontaktdaten:</th></tr>\n";
    $g->out.= "<tr><td>Strasse:</td><td colspan=3><input type='text' name='Strasse' value='{$daten['Strasse']}' size=50> <input type='text' name='Hausnummer' size=5 value='{$daten['Hausnummer']}'></td></tr>\n";
    $g->out.= "<tr><td>PLZ Ort:</td><td colspan=3><input type='text' name='PLZ' value='{$daten['PLZ']}' size=10> <input type='text' name='Ort' size=30 value='{$daten['Ort']}'></td></tr>\n";
    $g->out.= "<tr><td>Telefon (Privat):</td><td><input type='text' name='TelefonPrivat' value='{$daten['TelefonPrivat']}'></td><td>Telefon (Geschäftlich):</td><td><input type='text' name='TelefonGeschaeftlich' value='{$daten['TelefonGeschaeftlich']}'></td></tr>\n";
    $g->out.= "<tr><td>Handy:</td><td><input type='text' name='TelefonHandy' value='{$daten['TelefonHandy']}'></td><td>ICQ/AIM:</td><td><input type='text' name='ICQNr' value='{$daten['ICQNr']}'></td></tr>\n";
    $g->out.= "<tr><td colspan=4 style='text-align: center'><input type='submit'></td></tr>\n</table></form>\n";
  }
}
elseif ($_GET['del'] > 0) {
  $g->out.= "<h3>Mitglied löschen</h3>";
  if ($_POST['checked'] > 0) {
    $g->query("DELETE FROM vb_mitglieder WHERE ID='{$_GET['del']}' AND TeamID='{$g->teamID}'");
    $g->out.= "Datensatz gelöscht.";
    $g->out.= "<script language='javascript'>doParentReload();window.close()</script>";
  }
  else {
    $g->out.= "<form action='".$g->getSelfURI()."' method='POST'><input type='hidden' name='checked' value='1'>\n";
    $g->out.= "Bist Du sicher?<br><br><input type='submit' value='Ja'> <input type='button' value='Nö' onclick='window.close()'>\n";
    $g->out.= "</form>";
  }
}
else {
  doDie("Parameterfehler", "Kein Parameter spezifiziert.");
}
    
$g->doOutput("Mitgliedsdaten", "window");   

?>