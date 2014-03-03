<?php
/**
* 
* Fichier de configuration de l'interface Web de Salsifis
* 
* Afin d'éviter de perdre vos paramètres, veuillez plutôt faire une copie de ce fichier en config_local.php
* 
*/
define('TRANSMISSION_URL', 'http://localhost:9091/bt/rpc');

// Partition contenant les données
$partition = '/media/salsifis';
//Chemin vers les fichiers du dlna
$dlna_path = '/media/salsifis/dlna';
//Répertoires de téléchargements
$download_dirs = array(	
	//'Vidéos'			=> $dlna_path.'/videos',
	'Vidéos/Séries'			=> $dlna_path.'/videos/Séries',
	'Vidéos/Enfants'			=> $dlna_path.'/videos/Enfants',
	'Vidéos/Adultes'			=> $dlna_path.'/videos/Adultes',
	'Musique'			=> $dlna_path.'/musique',
	'Photos'			=> $dlna_path.'/photos',
	'Jeux'				=> '/media/salsifis/jeux'
	);
// Type d'explorateur de fichiers (jFM ou Pydio)
$fm = 'jFM';
?>