<?

require_once("global.inc.php");
$g->checkLogin(ZUGRIFFSLEVEL_TRAINER);

function getSqlValues($daten, $datumVon, $datumBis) {
  return "TermintypID = '{$daten['TermintypID']}', DatumVon = '".strftime("%Y-%m-%d %H:%M:%S", $datumVon)."', DatumBis = '".strftime("%Y-%m-%d %H:%M:%S", $datumBis)."', Name = '{$daten['Name']}', Ort = '{$daten['Ort']}', Bemerkungen = '{$daten['Bemerkungen']}'";
}

if ($_GET['edit'] > 0 || $_GET['new'] != '') {
  $keineausgabe = false;
  if (isset($_POST['Name'])) {
    $daten = $_POST;
    $daten['DatumVon'] = strtotime("{$daten['DatumVonJahr']}-{$daten['DatumVonMonat']}-{$daten['DatumVonTag']} {$daten['DatumVonStunden']}:{$daten['DatumVonMinuten']}:00");   
    $daten['DatumBis'] = strtotime("{$daten['DatumBisJahr']}-{$daten['DatumBisMonat']}-{$daten['DatumBisTag']} {$daten['DatumBisStunden']}:{$daten['DatumBisMinuten']}:00");   
    if (!preg_match('/^.+$/', $daten['Name']))
      $fehler['Name'] = "Sie müssen einen Namen angeben";
    if ($daten['DatumBis'] < $daten['DatumVon'])
      $fehler['Datum'] = "Der Beginn muss vor dem Ende liegen";
    if (isset($fehler)) {
      $g->out.= "<b>Fehlerhafte Eingaben:</b>\n";
      foreach($fehler as $feld => $text)
        $g->out.= "<li>$feld: $text<br>\n";
      $g->out.= "<p>";  
    }
    else {
      if ($_GET['edit'] > 0)
        $g->query("UPDATE vb_termine SET ".getSqlValues($daten, $daten['DatumVon'], $daten['DatumBis'])." WHERE ID = '{$_GET['edit']}'");
      else { 
        for ($i = 0; $i <= $daten['CopyWeeks']; $i++) {
          $datumOffset = 60 * 60 * 24 * 7 * $i;
          $g->query("INSERT INTO vb_termine SET TeamID = '{$g->teamID}', ".getSqlValues($daten, $daten['DatumVon'] + $datumOffset, $daten['DatumBis'] + $datumOffset));
        }  
      }  
      $g->out.= "Eingaben gespeichert.";
      $g->out.= "<script language='javascript'>doParentReload();window.close()</script>";
      $keineausgabe = true;
    }
  }
  elseif ($_GET['edit'] > 0) {
    $res = $g->query("SELECT 
        TermintypID, DatumVon, DatumBis, Name, Ort, Bemerkungen
      FROM
        vb_termine
      WHERE
        ID = '{$_GET['edit']}'");
    $daten = mysql_fetch_assoc($res); 
    $daten['DatumVon'] = strtotime($daten['DatumVon']);   
    $daten['DatumBis'] = strtotime($daten['DatumBis']);   
  }
  else {
    $daten['DatumVon']  = strtotime($_GET['new']." 12:00");
    $daten['DatumBis']  = strtotime($_GET['new']." 16:00");
    $daten['CopyWeeks'] = 0;
  }
  if (!$keineausgabe) {
    $g->out.= "<form action='".$g->getSelfURI()."' method='POST'><table>\n";
    $g->out.= "<tr><td>Typ:</td><td colspan=3><select name='TermintypID'>";
    $res = $g->query("SELECT ty.ID, CONCAT_WS(' - ', tty.Name, ty.Namenszusatz) AS Name FROM vb_termintypen ty LEFT JOIN vb_teilnahmetypen tty ON ty.TeilnahmetypID = tty.ID ORDER BY tty.Name, Namenszusatz");
    while ($row = mysql_fetch_assoc($res))
      $g->out.= "<option value='{$row['ID']}'".($row['ID'] == $daten['TermintypID'] ? ' selected' : '').">{$row['Name']}</option>";
    $g->out.= "</td></tr>\n";
    $g->out.= "<tr><td>Name:</td><td colspan=3><input type='text' name='Name' value='{$daten['Name']}' size=55></td></tr>\n";
    $g->out.= "<tr><td>Ort:</td><td colspan=3><input type='text' name='Ort' value='{$daten['Ort']}' size=55></td></tr>\n";
    $g->out.= "<tr><td>Beginn:</td><td colspan=3><input type='text' name='DatumVonTag' value='".strftime("%d", $daten['DatumVon'])."' size=2 maxlength=2>.<input type='text' name='DatumVonMonat' value='".strftime("%m", $daten['DatumVon'])."' size=2 maxlength=2>.<input type='text' name='DatumVonJahr' value='".strftime("%Y", $daten['DatumVon'])."' size=4 maxlength=4>&nbsp;&nbsp;&nbsp;<input type='text' name='DatumVonStunden' value='".strftime("%H", $daten['DatumVon'])."' size=2 maxlength=2>:<input type='text' name='DatumVonMinuten' value='".strftime("%M", $daten['DatumVon'])."' size=2 maxlength=2> Uhr</td></tr>\n";
    $g->out.= "<tr><td>Ende:</td><td colspan=3><input type='text' name='DatumBisTag' value='".strftime("%d", $daten['DatumBis'])."' size=2 maxlength=2>.<input type='text' name='DatumBisMonat' value='".strftime("%m", $daten['DatumBis'])."' size=2 maxlength=2>.<input type='text' name='DatumBisJahr' value='".strftime("%Y", $daten['DatumBis'])."' size=4 maxlength=4>&nbsp;&nbsp;&nbsp;<input type='text' name='DatumBisStunden' value='".strftime("%H", $daten['DatumBis'])."' size=2 maxlength=2>:<input type='text' name='DatumBisMinuten' value='".strftime("%M", $daten['DatumBis'])."' size=2 maxlength=2> Uhr</td></tr>\n";
    $g->out.= "<tr><td>Bemerkungen:</td><td colspan=3><textarea name='Bemerkungen' rows=7 cols=40>".htmlspecialchars($daten['Bemerkungen'])."</textarea></td></tr>\n";
    if ($_GET['new'] != '')
      $g->out.= "<tr><td colspan=4>Termin für die nächsten <input type='text' name='CopyWeeks' value='{$daten['CopyWeeks']}' size=3> Wochen kopieren</td></tr>\n";
    $g->out.= "<tr><td colspan=4 style='text-align: center'><input type='submit'></td></tr>\n</table></form>\n";
  }
}
elseif ($_GET['del'] > 0) {
  $g->out.= "<h3>Termin löschen</h3>";
  if ($_POST['checked'] > 0) {
    $g->query("DELETE FROM vb_termine WHERE ID='{$_GET['del']}' AND TeamID='{$g->teamID}'");
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
    
$g->doOutput("Termindaten", "window");   

?>