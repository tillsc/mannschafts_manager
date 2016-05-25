-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
--
-- Tabellenstruktur für Tabelle `vb_abmeldungen`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_abmeldungen` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TerminID` int(10) unsigned NOT NULL DEFAULT '0',
  `MitgliedsID` int(10) unsigned NOT NULL DEFAULT '0',
  `Datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `MitgliedTermin` (`MitgliedsID`,`TerminID`),
  KEY `TerminID` (`TerminID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- RELATIONEN DER TABELLE `vb_abmeldungen`:
--   `TerminID`
--       `vb_termine` -> `ID`
--   `MitgliedsID`
--       `vb_mitglieder` -> `ID`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vb_mannschaft`
--
-- Erstellt am: 09. Apr 2014 um 00:29
-- Zuletzt aktualisiert: 09. Apr 2014 um 00:29
-- Letzte Prüfung: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_mannschaft` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `mitgliedID` int(11) DEFAULT NULL,
  `mannschaftID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `mitgliedID` (`mitgliedID`,`mannschaftID`),
  KEY `mannschaftID` (`mannschaftID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Tabellenstruktur für Tabelle `vb_mitglieder`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_mitglieder` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TeamID` int(10) unsigned NOT NULL DEFAULT '0',
  `AktiverSpieler` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `Vorname` varchar(200) NOT NULL DEFAULT '',
  `Nachname` varchar(200) NOT NULL DEFAULT '',
  `Strasse` varchar(200) DEFAULT NULL,
  `Hausnummer` varchar(20) DEFAULT NULL,
  `PLZ` varchar(10) DEFAULT NULL,
  `Ort` varchar(200) DEFAULT NULL,
  `EMail` varchar(200) NOT NULL DEFAULT '',
  `Passwort` varchar(255) NOT NULL DEFAULT '',
  `ZugriffsLevel` int(10) unsigned NOT NULL DEFAULT '0',
  `TelefonPrivat` varchar(200) DEFAULT NULL,
  `TelefonGeschaeftlich` varchar(200) DEFAULT NULL,
  `TelefonHandy` varchar(200) DEFAULT NULL,
  `ICQNr` varchar(100) NOT NULL DEFAULT '',
  `SchiedsrichterlizenzID` int(10) unsigned DEFAULT NULL,
  `SchiedsrichterlizenzGueltigBis` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `TeamID` (`TeamID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- RELATIONEN DER TABELLE `vb_mitglieder`:
--   `TeamID`
--       `vb_teams` -> `ID`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vb_mitgliederteilnahmetypen`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_mitgliederteilnahmetypen` (
  `MitgliedsID` int(10) unsigned NOT NULL DEFAULT '0',
  `TeilnahmetypID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`MitgliedsID`,`TeilnahmetypID`),
  KEY `TeilnahmetypID` (`TeilnahmetypID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- RELATIONEN DER TABELLE `vb_mitgliederteilnahmetypen`:
--   `TeilnahmetypID`
--       `vb_teilnahmetypen` -> `ID`
--   `MitgliedsID`
--       `vb_mitglieder` -> `ID`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vb_schiedsrichterlizenzen`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_schiedsrichterlizenzen` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

--
-- Daten für Tabelle `vb_schiedsrichterlizenzen`
--

INSERT INTO `vb_schiedsrichterlizenzen` (`ID`, `Name`) VALUES
(1, 'A-Lizenz'),
(2, 'B-Lizenz'),
(3, 'C-Lizenz'),
(4, 'D-Lizenz'),
(5, '1'),
(6, '1'),
(7, '1'),
(8, '1'),
(9, '1'),
(10, '1'),
(11, '1'),
(12, '1'),
(13, '1'),
(14, '1'),
(15, '1'),
(16, '1'),
(17, '1'),
(18, '1'),
(19, '1'),
(20, '1'),
(21, '1'),
(22, '1'),
(23, 'TeamID');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vb_teams`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_teams` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) DEFAULT NULL,
  `TrainerEMail` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vb_teilnahmetypen`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_teilnahmetypen` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `vb_teilnahmetypen`
--

INSERT INTO `vb_teilnahmetypen` (`ID`, `Name`) VALUES
(3, 'Beachvolleyball'),
(4, 'Freizeit'),
(2, 'Spiel'),
(1, 'Training');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vb_termine`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_termine` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TermintypID` int(10) unsigned NOT NULL DEFAULT '0',
  `TeamID` int(10) unsigned NOT NULL DEFAULT '0',
  `DatumVon` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DatumBis` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Name` varchar(200) NOT NULL DEFAULT '',
  `Ort` varchar(200) NOT NULL DEFAULT '',
  `Bemerkungen` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `TermintypID` (`TermintypID`),
  KEY `TeamID` (`TeamID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

--
-- RELATIONEN DER TABELLE `vb_termine`:
--   `TermintypID`
--       `vb_termintypen` -> `ID`
--   `TeamID`
--       `vb_teams` -> `ID`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vb_termintypen`
--
-- Erstellt am: 09. Apr 2014 um 00:29
--

CREATE TABLE IF NOT EXISTS `vb_termintypen` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TeilnahmetypID` int(10) unsigned NOT NULL DEFAULT '0',
  `Namenszusatz` varchar(200) DEFAULT NULL,
  `Deadline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TeilnahmetypID` (`TeilnahmetypID`,`Namenszusatz`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- RELATIONEN DER TABELLE `vb_termintypen`:
--   `TeilnahmetypID`
--       `vb_teilnahmetypen` -> `ID`
--

--
-- Daten für Tabelle `vb_termintypen`
--

INSERT INTO `vb_termintypen` (`ID`, `TeilnahmetypID`, `Namenszusatz`, `Deadline`) VALUES
(1, 1, NULL, 1),
(2, 2, 'Einzelspieltag (Heimspiel)', 5),
(4, 2, 'Turnier', 5),
(8, 2, 'Einzelspieltag (Auswärts)', 5),
(9, 2, 'Terminvorschlag', 3),
(10, 3, 'freies beachen', 0),
(14, 4, 'Turnier', 5),
(15, 4, 'Feteneinladung', 0);

-- --------------------------------------------------------