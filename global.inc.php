<?
require_once("config.inc.php");

session_start();
//phpinfo();
function doDie($error, $details) {
  echo("<br><br><b>$error:</b><br>$details<br><br>");
  die("$error - $details");
}

define("ZUGRIFFSLEVEL_NORMAL" , 1);
define("ZUGRIFFSLEVEL_TRAINER" , 50);
define("ZUGRIFFSLEVEL_SUPERVISOR" , 100);
define("ZUGRIFFSLEVEL_ADMIN" , 150);

class G {
  var $eingeloggt;
  var $mitgliedsID;
  var $conf;
  var $db;
  
  function G() {
    global $conf;
    $this->conf = $conf;
    $this->scrollOffset = max(0, $_GET['offs']);
    unset($_GET['offs']);
    $this->db = mysql_connect($this->conf['db_host'], $this->conf['db_user'], $this->conf['db_password']) or doDie("MySQL-Connect fehlgeschlagen", mysql_error());
    mysql_select_db($this->conf['db_name'], $this->db) or doDie("MySQL-Connect fehlgeschlagen", mysql_error($this->db));
    $this->getLoginData();
  }

  function getLoginData() {
    $this->eingeloggt = false;
	$this->mitgliedsID = "";
	//if ($_COOKIE['MitgliedsID'] != '') {
    if ($_SESSION['MitgliedsID'] != '') {
	  $res = &$this->query("SELECT m.ID, m.Vorname, m.Nachname, m.TeamID, m.ZugriffsLevel, t.TrainerEMail FROM vb_mitglieder m LEFT JOIN vb_teams t ON t.ID = m.TeamID WHERE m.ID = {$_SESSION['MitgliedsID']}");
	  if ($row = mysql_fetch_assoc($res)) {
        $this->eingeloggt = true;
	    $this->mitgliedsID = $_SESSION['MitgliedsID'];
	    //$this->mitgliedsID = $row['MitgliedsID'];
	    $this->zugriffsLevel = $row['ZugriffsLevel'];
	    $this->teamID = $row['TeamID'];
	    $this->mitgliedsVorname = $row['Vorname'];
	    $this->mitgliedsNachname = $row['Nachname'];
	    $this->trainerEMail = $row['TrainerEMail'];
      }
	}
	
  }
  
  function checkLogin($level = ZUGRIFFSLEVEL_NORMAL) {
	if (!$this->eingeloggt)
      doDie("Du bist nicht eingeloggt", "Bitte Logge Dich ein.");
   if ($this->zugriffsLevel < $level)
     doDie("Du hast nicht die notwendigen Zugriffsrechte", "Level $level benötigt. Du hast {$this->zugriffsLevel}.");  

  }
  
  function getSelfURI($ignore = array()) {
    foreach ($_GET as $k => $v)
      if (!in_array($k, $ignore))
        $params.= "$k=$v&";
    return getenv("SCRIPT_NAME")."?".$params;
  }
  
  function doOutput($titel, $template = "") {
    if ($template == "")
      $template = "default";
    $template.= ".html";
    $f = fopen($template, "r") or doDie("Template-Fehler", "Template-Datei '$template' nicht gefunden!");
    $tr = array (
      "%main%" => $this->out,
      "%titel%" => "Mannschafts-Manager".($titel != "" ? " - $titel" : ""),
      "%selfuri%" => $this->getSelfURI(),
      "%offs%" => $this->scrollOffset,
    );
    while (!feof($f)) {
      $str = fgets($f) or doDie("Template-Fehler", "Dateilesefehler");
      print(strtr($str, $tr));
    }  
    fclose($f);
  }
  
  function &query($sql) {
    $res = mysql_query($sql, $this->db) or doDie("MySQL-Query fehlgeschagen", mysql_error($this->db));
    return $res;
  }
  
  function getDateString($mysqlDate) {
    return strftime("%d.%m.%Y", strtotime($mysqlDate));
  }
  
  function getTimeString($mysqlDate, $mitSekunden = false) {
    return strftime("%H:%M".($mitSekunden ? ":%S" : ""), strtotime($mysqlDate));
  }
  
  function getMonthName($month) {
    $m = array("Januar","Feburar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");
    return $m[$month - 1]; 
  }
  
  function getDayNameShort($mysqlDate) {
    $d = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
    $dayOfWeek = strftime("%w", strtotime($mysqlDate));
    return $d[$dayOfWeek]; 
  }
  
  function text($str, $leerstr = "&nbsp;") {
    $str = trim($str);
    return $str == "" ? $leerstr : htmlspecialchars($str);
  }
}  

$g = new G();

?>