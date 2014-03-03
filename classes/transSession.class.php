<?php

/**
* Classe de session Transmission
* 
* @see <https://trac.transmissionbt.com/browser/trunk/extras/rpc-spec.txt>
* @package Torrents
*/
class TransSession {

		/**
		* Limite max de ratio partage/téléchargement
		* @var float
		*/
		private $_ratioLimit = 1;
		
		/**
		* Débit descendant (en ko/s)
		* @var int
		*/
		private $_dlSpeed = 350;

		/**
		* Débit montant (en ko/s)
		* @var int
		* 
		*/
		private $_upSpeed = 90;
		
		/**
		* Débit descendant alternatif (en ko/s)
		* @var int
		*/
		private $_altDlSpeed = 80;
		
		/**
		* Débit montant alternatif (en ko/s)
		* @var int
		* 
		*/
		private $_altUpSpeed = 30;
		
		/**
		* Vitesses de transfert alternatives actives
		* @var bool
		*/
		private $_altSpeedEnabled = false;
		
		/**
		* Heure quotidienne du basculement sur les vitesses alternatives (exprimé en minutes depuis 0h00)
		* 
		* 450 minutes = 7h30
		* @var int
		*/
		private $_altBegin = 450;
		
		/**
		* Heure quotidienne de la fin d'utilisation des vitesses alternatives (exprimé en minutes depuis 0h00)
		* 
		* 1410 minutes = 23h30
		* @var int
		*/
		private $_altEnd = 1410;
		
		/**
		* Jours d'activation des vitesses alternatives
		* 
		* Dimanche					= 1			(binary: 0000001)
	  * Lundi							= 2			(binary: 0000010)
	  * Mardi							= 4			(binary: 0000100)
	  * Mercredi					= 8			(binary: 0001000)
	  * Jeudi							= 16		(binary: 0010000)
	  * Vendredi					= 32		(binary: 0100000)
	  * Samedi						= 64		(binary: 1000000)
	  * Jours ouvrés			= 62		(binary: 0111110)
	  * Weekend						= 65		(binary: 1000001)
	  * Toute la semaine	= 127		(binary: 1111111)
	  * Aucun							= 0			(binary: 0000000)
	  * 
	  * Il suffit d'additionner les jours pour en cumuler plusieurs. Ex : lundi, mardi et mercredi : 14
		* @var int
		* 
		*/
		private $_altDaysEnabled = 127;
		
		/**
		* Objet transmissionRPC
		* @var object
		* 
		*/
		private $_transRPC = null;
		
		/**
		* Construction de la classe
		* 
		* @param object objet de session transmissionRPC
		* @return void
		*/
		public function __construct($transRPC){
			//On va utiliser des dates, il faut donc renseigner le fuseau horaire
			date_default_timezone_set('Europe/Paris');
		
			//Utilisation de la classe de transmission
			require_once('TransmissionRPC.class.php');
			
			$this->_populate($transRPC);
		}
		
		/**
		* Récupère les paramètres de transmission
		* @param object $transRPC objet transmissionRPC
		* 
		* @return void
		*/
		private function _populate($transRPC){
			$settings = $transRPC->sget()->arguments;
			
			$this->_ratioLimit			= (float)$settings->seedRatioLimit;
			$this->_dlSpeed					= (int)$settings->speed_limit_down;
			$this->_upSpeed					= (int)$settings->speed_limit_up;
			$this->_altDlSpeed			= (int)$settings->alt_speed_down;
			$this->_altUpSpeed			= (int)$settings->alt_speed_up;
			$this->_altSpeedEnabled	= (int)$settings->alt_speed_enabled;
			$this->_altBegin				= (int)$settings->alt_speed_time_begin;
			$this->_altEnd					= (int)$settings->alt_speed_time_end;
			$this->_altDaysEnabled	= (int)$settings->alt_speed_time_day;
		}
		
		/**
		* Permet d'accéder aux propriétés de la classe
		* @param string $prop Propriété
		* 
		* @return mixed
		*/
		public function __get($prop){
			switch ($prop){
				case 'ratioLimit':
				case 'dlSpeed':
				case 'upSpeed':
				case 'altDlSpeed':
				case 'altUpSpeed':
				case 'altSpeedEnabled':
					return $this->{'_'.$prop};
				case 'altBegin':
				case 'altEnd':
					return gmdate('H:i', floor($this->{'_'.$prop} * 60));
				case 'altDaysEnabled':
				  $days = $this->_altDaysEnabled;
				  if ($days == 127) return 'Tous les jours';
				  if ($days == 65)	return 'Le weekend';
				  if ($days == 62)	return 'Du lundi au vendredi';
				  if ($days === 0)	return 'Jamais';
				  $daysArr = array();
				  if ($this->_nbit($days, 1) === 1) $daysArr[] = 'Dimanche';
				  if ($this->_nbit($days, 2) === 1) $daysArr[] = 'Lundi';
				  if ($this->_nbit($days, 3) === 1) $daysArr[] = 'Mardi';
				  if ($this->_nbit($days, 4) === 1) $daysArr[] = 'Mercredi';
				  if ($this->_nbit($days, 5) === 1) $daysArr[] = 'Jeudi';
				  if ($this->_nbit($days, 6) === 1) $daysArr[] = 'Vendredi';
				  if ($this->_nbit($days, 7) === 1) $daysArr[] = 'Samedi';
				  return implode(', ', $daysArr);
				default:
					return false;
			}
		}
		
		/**
		* Retourne le bit à la position $n dans un nombre 
		* @param int $number Nombre
		* @param int $n Position (commence à 1)
		* 
		* @return
		*/
		private function _nbit($number, $n) { 
			return ($number >> $n-1) & 1;
		}
}
?>