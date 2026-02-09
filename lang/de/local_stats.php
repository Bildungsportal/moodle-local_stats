<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_stats
 * @copyright  2023 Austrian Federal Ministry of Education
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['cron'] = 'Cron';
$string['data'] = 'Datentabelle';
$string['keep_data'] = 'Daten behalten';
$string['keep_data:description'] = 'Bitte geben Sie an, wie lange Logdaten behalten werden sollen.';
$string['messageprovider:reporting'] = 'Benachrichtigungen über neue Berichterstattungen';
$string['orientation'] = 'Orientierung';
$string['orientation:landscape'] = 'Querformat';
$string['orientation:portrait'] = 'Hochformat';
$string['periodid'] = 'Zeitraum';
$string['pluginname'] = 'Statistiken';
$string['pluginname:settings'] = 'Statistiken Einstellungen';
$string['privacy:export:local_stats'] = 'Export Rohdaten für Nutzer-Statistiken';
$string['raw_data'] = 'Untersuche Logdaten';
$string['report:amount:keep'] = 'Behalte Perioden';
$string['report:amount:keep_help'] = 'Legt fest, wieviele Perioden behalten werden. Bei 0 wird eine unbegrenzte Anzahl gespeichert.';
$string['report:amount:show'] = 'Zeige Perioden';
$string['report:amount:show_help'] = 'Legt fest, wieviele Perioden gezeigt werden. Bei 0 wird eine unbegrenzte Anzahl gezeigt.';
$string['report:change_warning'] = 'Achtung: eine Änderung der SQL Query hat einen Effekt auf die Berichte. Falls Sie nicht sicherstellen können, dass die neuen Werte kompatibel sind, sollten Sie die Daten einmalig zurücksetzen!';
$string['report:colorcodes'] = 'Color Codes';
$string['report:confirmsqlchange'] = 'SQL Änderung bestätigen';
$string['report:confirmsqlchange:required'] = 'Sie müssen bestätigen, dass Sie die SQL Query ändern möchten!';
$string['report:confirmsqlchange:text'] = 'Ja, ich bestätige hiermit, dass ich die SQL Query ändern möchte!';
$string['report:hide_empty_subids'] = 'Leere Sub-IDs verstecken';
$string['report:hide_empty_subids_help'] = 'Wenn für eine Sub-ID in keiner Periode ein Wert vorhanden ist, dann die Sub-ID in Diagrammen und Tabellen nicht zeigen.';
$string['report:max_number_for_col_captions'] = 'Maximale Spaltenüberschriften';
$string['report:max_number_for_col_captions_help'] = 'Gibt an, für wie viele Spalten die vollständige Überschrift in Datentabellen von Berichten gedruckt werden soll. Falls die tatsächliche Anzahl der Spalten diese Grenze überschreitet, werden alle Spalten nummeriert über der Datentabelle gedruckt und die Spalten werden entsprechend nummeriert!';
$string['report:sort_by'] = 'Sortierfeld';
$string['report:sort_by_help'] = 'Sortiere Daten innerhalb einer Periode nach SubID-Name oder -wert (Standard: Name)';
$string['report:sort_type'] = 'Sortierung';
$string['report:sort_type_help'] = 'Sortiere Daten innerhalb einer Periode aufsteigend (ASC) oder absteigend (DESC) (Standard: ASC)';
$string['report:sumgraph'] = 'Summengraph';
$string['report:sumgraph:text'] = 'Zeige einen Graphen, der alle Spalten summiert als Linie zeigt.';
$string['report:switch_axes_data'] = 'Achsen tauschen (Daten)';
$string['report:switch_axes_data_help'] = 'Tausche Zeilen und Spalten bei Datentabelle';
$string['report:switch_axes_graph'] = 'Achsen tauschen (Diagramm)';
$string['report:switch_axes_graph_help'] = 'Tausche Zeilen und Spalten beim Diagramm';
$string['report:wipedata'] = 'Daten immer löschen';
$string['report:wipedata:text'] = 'Ja, alle Daten vor jedem Durchlauf des Berichts löschen!';
$string['report:wipedataonce'] = 'Daten einmalig löschen';
$string['report:wipedataonce:text'] = 'Ja, alle Daten einmalig löschen!';
$string['report:lasttimecreated'] = 'Letzter Zeitstempel';
$string['report:lasttimecreated_help'] = 'Der Zeitstempel des letzten analysierten Eintrags. Normalerweise muss dieser Wert nicht händisch verändert werden!';
$string['report:query'] = 'Abfrage';
$string['report:query_help'] = 'Definieren Sie die SQL-Abfrage. Diese muss als Rückgabewerte folgende Spalten beinhalten: period, subid,value und lasttimecreated.';
$string['report:query:contains_malicious_sql'] = 'Die SQL-Abfrage enthält ein verbotenes Schlüsselwort: {$a}';
$string['report:query:empty'] = 'Keine SQL-Abfrage definiert';
$string['report:query:invalid'] = 'Ungültige SQL-Abfrage';
$string['report:query:template'] = 'SQL Vorlage für Berichte';
$string['errorwhilerunningreports'] = 'Fehler bei der Ausführung von Berichten: {$a}';
$string['report:reset_data'] = 'Alle bisherigen Daten zum Bericht "{$a->name}" wurden gelöscht und werden bald neu berechnet.';
$string['report:run:successfully'] = 'Der Bericht "{$a->name}" wurde erfolgreich ausgeführt!';
$string['report:saved'] = 'Der Bericht "{$a->name}" wurde erfolgreich gespeichert.';
$string['reportings'] = 'Berichterstattungen';
$string['reporting'] = 'Berichterstattung';
$string['reporting:email:footer'] = '';
$string['reporting:email:footerlink'] = '<br/>Bericht kann <a href="{$a}">hier</a> geöffnet werden.';
$string['reporting:email:header'] = 'Liebe/r {$a},<br /><br />';
$string['reporting:mode:scheduled'] = 'Zeitgesteuerter Modus';
$string['reporting:mode:scheduled_help'] = 'Im zeitgesteuerten Modus werden die Berichte gemäß dem Cron-Ausdruck gesendet.';
$string['reporting:mode:triggered'] = 'Auslösungsmodus';
$string['reporting:mode:triggered_help'] = 'Im Auslösungsmodus wird die Meldung gesendet, sobald eine der folgenden Bedingungen erfüllt ist.';
$string['reporting:nextrun'] = 'Nächste Ausführung';
$string['reporting:run:successfully'] = 'Die Berichterstattung "{$a->name}" wurde erfolgreich ausgeführt!';
$string['reporting:sendto_email'] = 'Emailempfänger';
$string['reporting:sendto_email_help'] = 'Bitte geben Sie alle E-Mailadressen zeilenweise ein, die eine Berichterstattung erhalten sollen';
$string['reporting:sendto_notification'] = 'Benachrichtigungen';
$string['reporting:sendto_notification_help'] = 'Bitte geben Sie Personen an, die eine Benachrichtigung erhalten sollen';
$string['reports'] = 'Berichte';
$string['reports:graph'] = 'Diagramme';
$string['reports:table'] = 'Tabellen';
$string['subid:empty'] = 'Unspezifiziert';
$string['task:cleanup'] = 'Statistik-Logdaten bereinigen';
$string['task:run_reports'] = 'Statistik-Berichte erstellen';
$string['task:run_reportings'] = 'Berichterstattungen durchführen';
$string['trigger'] = 'Auslöser';
$string['trigger:fired'] = '{$a->reportname}, Auslöser für "<strong>{$a->subid}</strong>"!<br /><small>=> Bedingung {$a->curvalue} {$a->operator} {$a->firevalue}, Regex war {$a->regex}.</small>';
$string['trigger:modeendless'] = 'Endlosmodus';
$string['trigger:modeendless_help'] = 'Im Endlosmodus sendet der Auslöser immer eine Benachrichtigung, andernfalls nur eine Benachrichtigung pro Subid, und die Subid muss erneut aktiviert werden.';
$string['trigger:operator'] = 'Operator';
$string['trigger:operator:invalid'] = 'Ungültigen Operator gefunden: {$a}';
$string['trigger:regex'] = 'Regex';
$string['trigger:regex_help'] = 'Lassen Sie dieses Feld leer, um diese Regel auf alle Subids anzuwenden, oder geben Sie einen gültigen Regex ein, um nach bestimmten Subids zu filtern. Bitte verwenden Sie das Schlüsselwort {empty}, um leere Subids zu finden!';
$string['trigger:regex:invalid'] = 'Ungültiger Regex-Ausdruck gefunden.';
$string['trigger:unarmed_subids'] = 'Inaktive Sub-IDs';
$string['trigger:unarmed_subids_help'] = 'Um eine Sub-ID zu reaktivieren, entfernen Sie diese bitte aus dem Textfeld.';
$string['trigger:value'] = 'Wert';
