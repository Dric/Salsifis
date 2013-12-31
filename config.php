<?php
/**
* 
* Fichier de configuration de l'interface Web de Salsifis
* 
* Afin d'éviter de perdre vos paramètres, veuillez plutôt faire une copie de ce fichier en config_local.php
* 
*/
// Partition contenant les données
$partition = '/media/salsifis';
//Chemin vers les fichiers du dlna
$dlna_path = '/media/salsifis/dlna';
//Répertoires de téléchargements
$download_dirs = array(	
	'Vidéos'			=> $dlna_path.'/videos',
	'Séries'			=> $dlna_path.'/videos/Séries',
	'Tout public'	=> $dlna_path.'/videos/Tout public',
	'Musique'			=> $dlna_path.'/musique',
	'Photos'			=> $dlna_path.'/photos',
	'Jeux'				=> '/media/salsifis/jeux'
	);
// Type d'explorateur de fichiers (jFM ou Pydio)
$fm = 'jFM';
?>