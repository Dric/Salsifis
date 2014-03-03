<?php
require_once('transSession.class.php');

/**
* Classe de gestion des torrents
* 
* @package Torrents
*/
Class TorrentsManager {
	
	/**
	* Répertoires de téléchargements
	* @var array()
	*/
	private $_downloadDirs = array();
	
	/**
	* Liste des torrents
	* @var array()
	*/
	private $_torrentsList = array();
	
	/**
	* Session transmission
	* @var object
	*/
	private $_transSession = null;
	
	/**
	* Objet transmissionRPC
	* @var object
	*/
	private $_transRPC = null;
	
	/**
	* Filtres généraux des torrents
	* @var array
	*/
	private $_selectFilters = array(
		'all'			=> 'Tous les torrents',
		'inDl'		=> 'Les torrents en cours de téléchargement',
		'done'		=> 'Les torrents en cours de partage',
		'last10'	=> 'Les 10 derniers torrents terminés',
		'noCat'		=> 'Les torrents non affectés',
		'stopped'	=> 'Les torrents supprimables'
	);
	
	/**
	* Construction de la classe
	* 
	* @return void
	*/
	public function __construct(){
		global $download_dirs;
		
		//On va utiliser des dates, il faut donc renseigner le fuseau horaire
		date_default_timezone_set('Europe/Paris');
		
		$this->_downloadDirs = $download_dirs;
		
		//Utilisation de la classe de transmission
		require_once('TransmissionRPC.class.php');
		$this->_transRPC = new TransmissionRPC(TRANSMISSION_URL);
		
		$this->_transSession = $this->_getTransSession();
		
		$this->_torrentsList = $this->_getTorrentsObjects();
	}
	
	public function displayPage($filteredBy = 'all'){
		?>
		<h2>Liste des téléchargements</h2>
		<div id="speedAlert"><?php $this->_displaySpeedAlert(); ?></div>
		<p>Cliquez sur les téléchargements pour en afficher les détails.</p>
		<form class="form-horizontal" role="form">
			<div class="form-group">
		    <label for="filterBy" class="col-sm-2 control-label">Afficher : </label>
		    <div class="col-sm-10">
		      <select name="filterBy" id="filterBy" class="form-control">
		      	<?php
		      	foreach ($this->_selectFilters as $value => $label){
							?><option value="<?php echo $value; ?>"<?php echo ($value == $filteredBy) ? ' selected' : '';?>><?php echo $label; ?></option><?php
						}
					  ?>
					  <optgroup label="Types de téléchargements">
					  	<?php 
					  	foreach ($this->_downloadDirs as $label => $downloadDir){
								?><option value="<?php echo $label; ?>"<?php echo ($label == $filteredBy) ? ' selected' : '';?>><?php echo $label; ?></option><?php
							}
							?>
					  </optgroup>
					</select>
		    </div>
		  </div>
		</form>
		<script>
			var ratio = <?php echo $this->_transSession->ratioLimit; ?>;
		</script>
		<?php
		if (array_key_exists($filteredBy, $this->_downloadDirs)){
			$this->displayTorrents(array('downloadDir' => array('=', $filteredBy)));
		}else{
			switch ($filteredBy){
				case 'all':
					$this->displayTorrents();
					break;
				case 'inDl':
					$this->displayTorrents(array('percentDone' => array('<', 100)));
					break;
				case 'done':
					$this->displayTorrents(array('percentDone' => array('=', 100)));
					break;
				case 'last10':
					$this->displayTorrents(null, 'rawDoneDate', 'DESC', 10);
					break;
				case 'noCat':
					$this->displayTorrents(array('downloadDir' => array('!in', array_keys($this->_downloadDirs))));
					break;
				case 'stopped':
					$this->displayTorrents(array('ratioPercentDone' => array('=', 100)));
					break;
			}
		}
	}
	
	/**
	* Affiche l'alerte de vitesse de téléchargement
	* 
	* @return void
	*/
	private function _displaySpeedAlert(){
		if ($this->_transSession->altSpeedEnabled) {
			?>
			<div class="alert alert-warning">Les Salsifis sont actuellement en mode tortue (de <?php echo $this->_transSession->altBegin; ?> à <?php echo $this->_transSession->altEnd; ?>), ils sont bridés à <?php echo $this->_transSession->altDlSpeed; ?>ko/s en téléchargement et <?php echo $this->_transSession->altUpSpeed; ?>ko/s en partage. <span class="glyphicon glyphicon-question-sign help-cursor tooltip-bottom" title="Afin d'éviter de pourrir votre connexion internet pendant la journée au moment où vous en avez besoin, les Salsifis ne piquent pas toute la bande passante lorsqu'ils sont en mode tortue."></span></div>
			<?php	}else{	?>
			<div class="alert alert-info">Les Salsifis téléchargent actuellement à pleine puissance ! (<?php echo $this->_transSession->dlSpeed; ?>ko/s en téléchargement et <?php echo $this->_transSession->upSpeed; ?>ko/s en partage)</div>
			<?php 
		}
	}
	
	/**
	* Traitement des requêtes (POST, GET)
	* 
	* @return bool
	*/
	public function requests(){
		if (isset($_REQUEST['action'])){
			switch (htmlspecialchars($_REQUEST['action'])){
				case 'refreshTorrents':
					header("Content-Type: application/json");
					echo $this->_getJSONTorrentsData();
					return true;
				case 'torrentImg':
					self::_getImg(htmlspecialchars(urldecode($_REQUEST['source'])));
					return true;
				case 'filtering':
					$this->displayPage(htmlspecialchars($_REQUEST['filteredBy']));
					return true;
				case 'moveTorrent':
					$id = (int)$_REQUEST['torrent_id'];
					$dir = htmlspecialchars($_REQUEST['new_dir']);
					if ($this->_moveTorrent($id, $dir)){
						$this->displayTorrents(array('id' => array('=', $id)));
					}else{
						echo '<div class="alert alert-danger"><a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>Erreur : Impossible de déplacer le téléchargement !</div>';
					}
					return true;
				case 'delTorrent':
					$id = (int)$_REQUEST['torrent_id'];
					$delLocalFiles = (isset($_REQUEST['delLocalFiles'])) ? true : false;
					if ($this->_delTorrent($id, $delLocalFiles)){
						echo '<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>Téléchargement supprimé !';
						echo ($delLocalFiles) ? '<br>Les fichiers sur le disque ont également été effacés.' : '';
						echo '</div>';
					}else{
						echo '<div class="alert alert-danger"><a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>Erreur : Impossible de supprimer le téléchargement !</div>';
					}
			}
		}
		return false;
	}
	
	/**
	* Retourne un tableau d'objets torrents formaté en JSON
	* 
	* @return string
	*/
	private function _getJSONTorrentsData(){
		$torrentListJSON = array();
		$props = array('id', 'status', 'doneDate', 'totalSize', 'uploadedEver', 'isFinished', 'leftUntilDone', 'percentDone', 'eta', 'uploadRatio', 'ratioPercentDone');
		// Les propriétés des objets torrents sont privées, il faut donc recréer un objet avec les mêmes propriétés, mais publiques
		foreach ($this->_torrentsList as $torrent){
			$tJSON = new stdClass;
			foreach ($props as $prop){
				$tJSON->$prop = $torrent->$prop;
			}
			
			$torrentListJSON[] = $tJSON;
		}
		return json_encode($torrentListJSON);
	}
	
	/**
	* Supprime un torrent
	* 
	* @param int $id ID du torrent
	* @param bool $delLocalFiles Supprime également les fichiers téléchargés si true
	* @return bool
	*/
	private function _delTorrent($id, $delLocalFiles = false){
		if (!is_int($id) or $id == 0){
			return false;
		}
		$res = $this->_transRPC->remove($id, $delLocalFiles);
		if ($res->result == 'success'){
			return true;
		}
		return false;
	}
	
	/**
	* Déplace un torrent
	* 
	* @param int $id ID du torrent
	* @param string $toDir Répertoire de destination
	* @return bool
	*/
	private function _moveTorrent($id, $toDir){
		if (is_int($id) and $id !== 0 and in_array($toDir, $this->_downloadDirs)){
			$res = $this->_transRPC->move($id, $toDir);
			if ($res->result == 'success'){
				return true;
			}
		}
		return false;
	}
	
	/**
	* Retourne les informations de la session Transmission (paramétrage, ratios, mode tortue, etc.)
	* 
	* @return object
	*/
	private function _getTransSession(){
		
		return new TransSession($this->_transRPC);
	}
	
	/**
	* Retourne la liste des torrents
	* 
	* @return array
	*/
	private function _getTorrentsObjects(){
		
		require_once('torrent.class.php');
		$torrentsList = array();
		// Voir https://trac.transmissionbt.com/browser/trunk/extras/rpc-spec.txt
		$torrents = $this->_transRPC->get(array(), array('id', 'name', 'addedDate', 'status', 'doneDate', 'totalSize', 'downloadDir', 'uploadedEver', 'isFinished', 'leftUntilDone', 'percentDone', 'files', 'eta', 'uploadRatio', 'comment'))->arguments->torrents;
		
		foreach ($torrents as $torrent){
			
			//On ajoute la limite de ratio à l'objet torrent
			$torrent->ratioLimit = $this->_transSession->ratioLimit;
			$torrentsList[] = new Torrent($torrent);
		}
		
		return $torrentsList;
	}
	
	/**
	* Affiche une liste de torrents
	* @param array $filters Filtres à appliquer de la forme ('propriété' => (comparateur , valeur à comparer))
	* * Comparateurs acceptés :
	* *		'='		: égal à
	* *		'>'		: supérieur à
	* *		'<'		: inférieur à
	* *		'>='	: supérieur ou égal à
	* *		'<='	: inférieur ou égal à
	* *		'!='	: différent de
	* *		'in'	: compris dans (la valeur à comparer doit être un tableau)
	* *		'!in'	: non compris dans (la valeur à comparer doit être un tableau)
	* *
	* * Ex : array('name' => ('!=', 'moisi'), 'status' => ('in', array(3,4)))
	* @param string $sortedBy Critère de tri
	* @param int $limit Nombre de torrents à afficher (0 = tous)
	* 
	* @return void
	*/
	public function displayTorrents($filters = array(), $sortedBy = 'name', $sortedOrder = 'ASC', $limit = 0){
		/**
		* Filtres validés
		* @var bool
		*/
		$validatedFilters = false;
		
		/**
		* Comparateurs de filtres acceptés
		* @var array
		*/
		$compArray = array('=', '>', '<', '>=', '<=', '!=', 'in', '!in');
		
		/**
		* Tableau des objets après un éventuel tri
		* @var array
		*/
		$torrentsList = array();
		
		/**
		* Nombre de torrents affichés
		* @var int
		*/
		$nb = 0;
		
		if (!empty($this->_torrentsList)){
			
			// Application des critères de tri
			if (!empty($sortedBy)){
				$torrentsList = self::sortObjectList($this->_torrentsList, $sortedBy, $sortedOrder);
			}else{
				$torrentsList = $this->_torrentsList;
			}
			foreach ($torrentsList as $torrent){
				
				// Application des filtres
				$isUnfiltered = true;
				if (!empty ($filters)){
					foreach ($filters as $filter => $filterCriteria){
						if (!$validatedFilters){
							/*
							* On vérifie que les filtres sont valides.
							* Après la première itération, il ne restera que les filtres valides
							*/ 
							if ($torrent->$filter == 'Property not set !' or !is_array($filterCriteria) or !in_array($filterCriteria[0], $compArray) or (($filterCriteria[0] == 'in' or $filterCriteria[0] == '!in') and !is_array($filterCriteria[1]))){
								unset($filters[$filter]);
							}
							$validatedFilters = true;
						}
						// Comparons un peu...
						switch ($filterCriteria[0]){
							case '=':
								if ($torrent->$filter != $filterCriteria[1]){
									$isUnfiltered = false;
								}
								break;
							case '>':
								if ($torrent->$filter <= $filterCriteria[1]){
									$isUnfiltered = false;
								}
								break;
							case '<':
								if ($torrent->$filter >= $filterCriteria[1]){
									$isUnfiltered = false;
								}
								break;
							case '>=':
								if ($torrent->$filter < $filterCriteria[1]){
									$isUnfiltered = false;
								}
								break;
							case '<=':
								if ($torrent->$filter > $filterCriteria[1]){
									$isUnfiltered = false;
								}
								break;
							case '!=':
								if ($torrent->$filter == $filterCriteria[1]){
									$isUnfiltered = false;
								}
								break;
							case 'in':
								if (!in_array($torrent->$filter, $filterCriteria[1])){
									$isUnfiltered = false;
								}
								break;
							case '!in':
								if (in_array($torrent->$filter, $filterCriteria[1])){
									$isUnfiltered = false;
								}
								break;
						}
					}
				}
				// Affichage du torrent
				if ($isUnfiltered){
					$torrent->display();
					$nb++;
				}
				if ($limit > 0 and $nb == $limit){
					break;
				}
			}
		}
	}
	
	/**
	* Trie un tableau d'objets selon les propriétés de ceux-ci.
	* 
	* Le tableau d'origine n'est pas affecté
	* @param array $array Tableau d'objets à trier
	* @param array $props Tableau contenant les propriétés sur lesquelles faire le tri
	* 
	* @return array Tableau trié
	*/
	static function sortObjectList(&$arrayOrig, $props, $sortOrder = 'ASC')	{
		$array = $arrayOrig;
		
		if (!is_array($props)){
			$props = array($props);
		}
		if (!is_array($sortOrder)){
			$sortOrder = array($sortOrder);
		}
	  usort($array, function($a, $b) use (&$props, &$sortOrder) {
			foreach ($props as $i => $prop) {
				if (isset ($sortOrder[$i]) and $sortOrder[$i] == 'DESC'){
					if (is_numeric($a->$prop) and is_numeric($b->$prop)){
						return $b->$prop - $a->$prop;
					}
					return strcasecmp($b->$prop, $a->$prop);
				}else{
					if (is_numeric($a->$prop) and is_numeric($b->$prop)){
						return $a->$prop - $b->$prop;
					}
					return strcasecmp($a->$prop, $b->$prop);
				}
	      
	    }
	    return 0;
		});

		return $array;	
	}
	
	/**
	* Retourne une image
	* 
	* @return void
	*/
	static function _getImg($source){
		$imginfo = getimagesize($source);
		header('Content-type: '.$imginfo['mime']);
		readfile($source);
	}
	
	/**
	* Converti une valeur en octets en taille lisible (Mo, Go, etc.)
	* @param int $value Taille en octets
	* 
	* @return string
	*/
	static function octalHumanize($value){
		$si_prefix = array( 'o', 'Ko', 'Mo', 'Go', 'To', 'Eo', 'Zo', 'Yo' );
		$base = 1024;
		$class = min((int)log($value , $base) , count($si_prefix) - 1);
		return sprintf('%1.2f' , $value / pow($base,$class)) . ' ' . $si_prefix[$class];
	}
	
	/**
	* Convertit une durée en jours, minutes, et secondes
	* @param int $value Durée en secondes
	* 
	* @return string
	*/
	static function durationHumanize($value){
		if ($value < 0){
			return 'Inconnu';
		}
		$days = floor($value/60/60/24);
		$hours = $value/60/60%24;
		$mins = $value/60%60;
		$secs = $value%60;
		$ret = '';
		if ($days > 0){
			$ret .= $days.' jour';
			if ($days > 1){
				$ret .='s';
			}
			$ret .= ' ';
		}
		if ($hours > 0){
			$ret .= $hours.' heure';
			if ($hours > 1){
				$ret .='s';
			}
			$ret .= ' ';
		}
		if ($mins > 0){
			$ret .= $mins.' minute';
			if ($mins > 1){
				$ret .='s';
			}
			$ret .= ' ';
		}
		if ($secs > 0){
			$ret .= 'et '.$secs.' seconde';
			if ($secs > 1){
				$ret .='s';
			}
		}
		return $ret;
	}
}
?>