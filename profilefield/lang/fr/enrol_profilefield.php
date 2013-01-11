<?php // $Id: enrol_manual.php,v 1.1.1.1 2010/06/11 09:25:49 vf Exp $ 
      // enrol_manual.php - created with Moodle 1.7 beta + (2006101003)

$string['profilefield:config'] = 'Peut configurer une inscription sur profil';
$string['profilefield:enrol'] = 'Peut s\'inscrire par son profil';
$string['profilefield:unenrol'] = 'Peut désinscire des personnes inscrites par profil';
$string['profilefield:unenrolself'] = 'Peu se désinscrire d\'une inscription par profil';
$string['profilefield:manage'] = 'Peut gérer les inscriptions par profil';

$string['assignrole'] = 'Assigner un role';
$string['badprofile'] = 'Désolé, mais votre profil ne vous permet pas d\'accéder à ce cours. Si vous devez y entrer pour une raison légitime, contactez les administrateurs de la plate-forme qui modifieront votre profil de façon adéquate.';
$string['course'] = 'Cours : $a';
$string['profilefield:unenrolself'] = 'Peut se désenroler du cours';
$string['enrolenddate'] = 'Date de fin';
$string['enrolenddate_help'] = 'Si elle est activée, cette date marque la fin de la période pendant laquelle les enrollements sont autorisés.';
$string['enrolenddaterror'] = 'La date de fin de la fenêtre d\'enrollement ne peut être avant son début';
$string['enrolme'] = 'M\'inscrire au cours';
$string['enrolmentconfirmation'] = 'Bienvenue. votre profil vous autorise la participation à ce cours. Voulez-vous vous inscrire ? ';
$string['enrolname'] = 'Inscription basée sur le profil utilisateur';
$string['enrolperiod'] = 'Durée de la période d\'enrollement';
$string['enrolperiod_help'] = 'Durée de l\'inscription, à partir de la date effective d\'enrollement. Si elle est désactivée, l\'inscription est illimitée dans le temps.';
$string['enrolstartdate'] = 'Date de début';
$string['enrolstartdate_help'] = 'Si elle est activée, les particicpants ne peivent s\'inscrire qu\'à partir de cette date.';
$string['grouppassword'] = 'Mot de passe pour l\'inscription à un groupe, s\'il est connu.';
$string['newcourseenrol'] = 'Un nouveau participant s\'est inscrit au cours {$a}';
$string['nonexistantprofilefielderror'] = 'Ce champ personnalisé de profil utilisateur n\'existe pas (ou plus)';
$string['notificationtext'] = 'Modèle de notification aux enseignants';
$string['notificationtext_help'] = 'Le contenu de la notification envoyée aux enseignants du cours peut être écrite ici, en utilisant des emplacements &lt;%%USERNAME%%&gt;, &lt;%%COURSE%%&gt;, &lt;%%URL%%&gt; and &lt;%%TEACHER%%&gt;. Notez que les balises multilingues seront traitées, selon la langue du destinataire.';
$string['notifymanagers'] = 'Notifier les enseignants?';
$string['pluginname'] = 'Inscription basée sur le profil utilisateur';
$string['pluginname_desc'] = 'Cette méthode permet une inscription directe si un champ du profil utilisateur contient une valeur attendue.';
$string['profilefield'] = 'Champ du profil utilisateur';
$string['profilevalue'] = 'Valeur attendue';
$string['status'] = 'Autoriser à utiliser le profil pour l\'enrollement';
$string['unenrolself'] = 'Se désinscrire du cours "{$a}"?';
$string['unenrolselfconfirm'] = 'Voulez-vous vraiment vous désinscrire du cours "{$a}"?';

?>