<?php // $Id: enrol_manual.php,v 1.1.1.1 2010/06/11 09:25:49 vf Exp $ 
      // enrol_manual.php - created with Moodle 1.7 beta + (2006101003)

$string['profilefield:config'] = 'Configurare l\'istanza di iscrizione';
$string['profilefield:enrol'] = 'Iscrivere utilizzando il metodo basato sul campo del profilo utente';
$string['profilefield:unenrol'] = 'Disiscrivere utenti iscritti con il metodo basato sul campo del profilo utente';
$string['profilefield:unenrolself'] = 'Disiscrivere se stessi quando iscritto con il metodo basato sul campo del profilo utente';
$string['profilefield:manage'] = 'Gestire iscrizioni con metodo basato sul campo del profilo utente';
$string['enrol/profilefield:unenrolself'] = 'Disiscrivere se stessi dal corso';

$string['assignrole'] = 'Assegna ruolo';
$string['badprofile'] = 'Ci dispiace, le informazioni del tuo profilo utente ti impediscono l\'iscrizione a questo corso. Comunque, se pensi di aver diritto di accedere a questo corso, per favore contatta un amministratore, il quale potrà correggere le il tuo profilo.';
$string['course'] = 'Corso: $a';
$string['enrolenddate'] = 'Data di termine';
$string['enrolenddate_help'] = 'Se abilitato, gli utenti potranno iscriversi solo fino a questa data.';
$string['enrolenddaterror'] = 'La data di termine iscrizioni non può essere antecendente a quella di inizio.';
$string['enrolme'] = 'Iscrivimi a questo corso';
$string['enrolmentconfirmation'] = 'Benvenuto. Le informazioni del tuo profilo utente ti permettono l\'accesso a questo corso. Procedere? ';
$string['enrolname'] = 'Iscrizione basata sul campo del profilo utente';
$string['enrolperiod'] = 'Durata dell\'iscrizione';
$string['enrolperiod_desc'] = 'Durata standard di validità dell\'iscrizione (in secondi). Se impostata a zero, la durata dell\'iscrizione sarà illimitata.';
$string['enrolperiod_help'] = 'Durata di validità dell\'iscrizione, partendo dal momento di iscrizione da parte dell\'utente. Se disabilitato, la durata dell\'iscrizione sarà illimitata.';
$string['enrolstartdate'] = 'Data di inizio';
$string['enrolstartdate_help'] = 'Se abilitato, gli utenti potranno iscriversi solo dopo questa data.';
$string['grouppassword'] = 'Password di accesso ad un gruppo, se conosciuta.';
$string['newcourseenrol'] = 'Un nuovo partecipante si è iscritto al corso {$a}';
$string['nonexistantprofilefielderror'] = 'Questo campo non è definito tra i campi dell\'utente.';
$string['notificationtext'] = 'Modello di notifica';
$string['notificationtext_help'] = 'Il contenuto della mail può essere scritto qui, usando &lt;%%USERNAME%%&gt;, &lt;%%COURSE%%&gt;, &lt;%%URL%%&gt; e &lt;%%TEACHER%%&gt; come campi. Si fa notare che ogni tag span multilingua sarà processato in base alla lingua del destinatario.';
$string['notifymanagers'] = 'Notifica i responsabili?';
$string['pluginname'] = 'Iscrizione basata sul campo del profilo utente';
$string['pluginname_desc'] = 'Questo metodo permette l\'iscrizione spontanea al corso se l\'utente ha un campo del profilo impostato al valore desiderato';
$string['profilefield'] = 'Campo del profilo utente';
$string['profilefield_desc'] = 'Un puntatore al campo del profilo utente';
$string['profilevalue'] = 'Valore desiderato';
$string['profilevalue_desc'] = '';
$string['status'] = 'Permetti l\'utilizzo del profilo per l\'iscrizione';
$string['unenrolself'] = 'Disiscriversi dal corso "{$a}"?';
$string['unenrolselfconfirm'] = 'Se proprio sicuro di volerti disiscrivere dal corso "{$a}"?';

$string['defaultnotification'] = '
Caro <%%TEACHER%%>,

l\'utente <%%USERNAME%%> si è iscritto (profilo conferme) nel tuo corso <%%COURSE%%>.

Puoi controllare il suo profilo utente <a href="<%%URL%%>">qui</a> dopo aver effettuato l\'accesso.
';

?>
