<?php

/**
* CLasse de torrent
* 
* Cette classe reprend une partie des propriétés de l'objet torrent transmis par la classe transmissionRPC
* 
* @see <https://trac.transmissionbt.com/browser/trunk/extras/rpc-spec.txt>
* @package Torrents
*/
Class Torrent{
	
	/**
	* ID du torrent
	* @var int
	*/
	private $_id = 0;
	
	/**
	* Nom du torrent
	* @var string
	*/
	private $_name = '';
	
	/**
	* Timestamp de date d'ajout du torrent dans le client bt
	* @var int
	*/
	private $_addedDate = 0;
	
	/**
	* Timestamp de date de fin de téléchargement
	* @var int
	*/
	private $_doneDate = 0;
	
	/**
	* Statut du torrent
	* @var int
	* 
	* 0: Arrêté (aucune activité)
  * 1: En attente de vérification
  * 2: En cours de vérification
  * 3: En attente de téléchargement
  * 4: En cours de téléchargement
  * 5: En attente de partage
  * 6: En cours de partage
	*/
	private $_status = 0;
	
	/**
	* Libellés du statut
	* @var array()
	*/
	private $_statusLabels = array(
		0 => 'Arrêté',
		1 => 'En attente de vérification',
  	2 => 'En cours de vérification',
		3 => 'En attente de téléchargement',
		4 => 'En cours de téléchargement',
		5 => 'En attente de partage',
		6 => 'En cours de partage'
	);
	
	/**
	* Classe CSS à affecter au statut
	* @var array
	*/
	private $_statusCSSClass = array(
		0 => 'label-default',
		1 => 'label-danger',
  	2 => 'label-danger',
		3 => 'label-primary',
		4 => 'label-primary',
		5 => 'label-warning',
		6 => 'label-warning'
	);
	
	/**
	* Taille totale en octets
	* @var int
	*/
	private $_totalSize = 0;
	
	/**
	* Répertoire réel de téléchargement
	* @var string
	*/
	private $_downloadDir = '';
	
	/**
	* Libellés des répertoires de téléchargements
	* @var array()
	* 
	*/
	private $_downloadDirs = array();
	
	/**
	* Nombre d'octets partagés (envoyés vers d'autres peers)
	* @var int
	*/
	private $_uploadedEver = 0;
	
	/**
	* Torrent terminé ou non
	* @var bool
	*/
	private $_isFinished = false;
	
	/**
	* Nombre d'octets avant la fin du téléchargement
	* @var int
	*/
	private $_leftUntilDone = 0;
	
	/**
	* Pourcentage de téléchargement en décimal (de 0 à 1)
	* @var float
	*/
	private $_percentDone = 0;
	
	/**
	* Limite max de ratio partage/téléchargement
	* @var float
	*/
	private $_ratioLimit = 1;
	
	/**
	* Pourcentage d'accomplissement du ratio partage/téléchargement en décimal (de 0 à 1)
	* @var float
	*/
	private $_ratioPercentDone = 0;
	
	/**
	* Fichiers téléchargés par le torrent
	* @var array
	*/
	private $_files = array();
	
	/**
	* Temps estimé avant la fin du téléchargement
	* @var int
	*/
	private $_eta = 0;
	
	/**
	* Pourcentage de partage en décimal (de 0 à 1)
	* @var float
	*/
	private $_uploadRatio = 0;
	
	/**
	* Commentaire du torrent
	* @var string
	*/
	private $_comment = '';
	
	/**
	* Image du torrent (si présente)
	* @var string
	*/
	private $_img = '';
	
	/**
	* NFO du torrent (fichier explicatif, si présent)
	* @var string
	*/
	private $_nfo = '';
	
	/**
	* Construction de la classe
	* @param object $RPCTorrent Objet de torrent renvoyé par la classe RPCTransmission
	* 
	* @return void
	*/
	public function __construct($RPCTorrent){
		global $download_dirs;
		$this->_downloadDirs = $download_dirs;
				
		$RPCprops = get_object_vars($RPCTorrent);

		foreach ($RPCprops as $prop => $value){
			if (isset($this->{'_'.$prop})){
				$this->{'_'.$prop} = $value;
			}
		}
		$fileDesc = array();
		$torrentImg = array();
		$this->_files = TorrentsManager::sortObjectList($this->_files, 'name');
		foreach ($this->_files as $file){
			$fileInfo = pathinfo($file->name);
			$level = count(explode('/', $fileInfo['dirname']));
			switch ($fileInfo['extension']){
				case 'nfo':
					if ((empty($fileDesc['source']) or $fileDesc['level'] > $level) and file_exists($this->_downloadDir.'/'.$file->name)){
						$fileDesc['source'] = file_get_contents($this->_downloadDir.'/'.$file->name);
						$fileDesc['level'] = $level;
					}
					break;
				case 'jpg':
				case 'jpeg':
				case 'png':
				case 'gif':
					if ((empty($torrentImg['source']) or $torrentImg['level'] > $level)  and file_exists($this->_downloadDir.'/'.$file->name)){
						$torrentImg['source'] = $this->_downloadDir.'/'.$file->name;
						$torrentImg['level'] = $level;
					}
					break;
			}
			$this->_img = (!empty($torrentImg['source'])) ? urlencode($torrentImg['source']) : '';
			$this->_nfo = (!empty($fileDesc['source'])) ? $fileDesc['source'] : '';
		}
	}
	
	/**
	* Permet d'accéder aux propriétés de la classe
	* @param string $prop Propriété
	* 
	* @return mixed
	*/
	public function __get($prop){
		return $this->_get($prop);
	}
	
	/**
	* Met en forme et retourne les propriétés de la classe
	* 
	* Les propriétés de la classe étant privées, pour y accéder il suffit de demander la variable sans le préfixe '_'.
	* Ex : Pour obtenir la taille totale du torrent, qui est la propriété $_totalSize, il suffit de demander $torrent->totalsize ou encore $this->_get('totalSize') à l'intérieur de la classe
	* @param string $prop Propriété à retourner.
	* 
	* @return mixed
	*/
	private function _get($prop){
		switch ($prop){
			case 'addedDate':
			case 'doneDate':
				if ($this->{'_'.$prop} === 0){
					return 'Inconnu';
				}
				return date('d/m/Y H:i', $this->{'_'.$prop});
			case 'rawDoneDate':
				return $this->_doneDate;
			case 'totalSize':
			case 'leftUntilDone':
			case 'uploadedEver':
				return torrentsManager::octalHumanize($this->{'_'.$prop});
			case 'eta':
				return ($this->_eta != -1) ? torrentsManager::durationHumanize($this->{'_'.$prop}) : 'Inconnu';
			case 'isFinished':
				return ($this->_isFinished or $this->_percentDone === 1) ? true : false;
			case 'uploadRatio':
				return round($this->_uploadRatio, 2);
			case 'percentDone':
				return ($this->_percentDone != -1) ? round($this->_percentDone*100, 1) : 0;
			case 'comment':
				return $msg = preg_replace('/((http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?)/', '<a href="\1" target="_blank">\1</a>', $this->_comment);
			case 'id':
			case 'name':
			case 'nfo':
			case 'img':
				return $this->{'_'.$prop};
			case 'files':
				return $this->_files;
			case 'status':
				return $this->_statusLabels[$this->_status];
			case 'downloadDir':
				return (!array_search($this->_downloadDir, $this->_downloadDirs)) ? $this->_downloadDir : array_search($this->_downloadDir, $this->_downloadDirs);
			case 'rawDownloadDir':
				return $this->_downloadDir;
			case 'statusCSSClass':
				return $this->_statusCSSClass[$this->_status];
			case 'ratioPercentDone':
				return round(($this->_uploadRatio/$this->_ratioLimit)*100, 0);
			default:
				// Certaines propriétés étant des booléens, impossible de retourner false en cas de propriété inexistante.
				return 'Property not set !';
		}
	}
	
	/**
	* Affiche les information d'un torrent
	* 
	* @return void
	*/
	public function display(){
		?>
		<div class="panel" id="torrent_<?php echo $this->_id; ?>">
			<div class="panel-heading torrents">
				<h4><a data-toggle="collapse" data-parent="#torrent_<?php echo $this->_id; ?>" href="#collapse_details_<?php echo $this->_id; ?>"><?php echo $this->_name; ?></a> <span class="label <?php echo $this->_get('statusCSSClass'); ?>"><?php echo $this->_get('status'); ?></span> <span class="label label-primary"><?php echo $this->_get('downloadDir'); ?></span></h4>
				<div class="row">
					<div class="col-md-10">
						<div id="torrent-progress-bar-title_<?php echo $this->_id; ?>" class="progress tooltip-bottom progress-torrents" title="Terminé à <?php echo $this->_get('percentDone'); ?>%">
							<?php 
							if ($this->_get('percentDone') == 100){ 
								if ($this->_get('ratioPercentDone') == 100){
									$barColor = 'default';
								}else{
									$barColor = 'warning';
								}
							?>
							<div id="torrent-progress-bar-seed_<?php echo $this->_id; ?>" class="progress-bar progress-bar-<?php echo $barColor; ?>" role="progressbar" aria-valuenow="<?php echo $this->_get('ratioPercentDone'); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $this->_get('ratioPercentDone'); ?>%;">
								<span class="sr-only"><?php echo $this->_get('ratioPercentDone'); ?>% Complete</span>
							</div>
							<?php }else{ ?>
							<div id="torrent-progress-bar-dl_<?php echo $this->_id; ?>" class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="<?php echo $this->_get('percentDone'); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $this->_get('percentDone'); ?>%;">
								<span class="sr-only"><?php echo $this->_get('percentDone'); ?>% Complete</span>
							</div>
							<?php } ?>
						</div>
					</div>
					<div class="col-md-2">
						<!-- Actions sur les téléchargements -->
						<div class="btn-group btn-group-sm pull-right">
							<?php if ($this->_status == 3 or $this->_status == 4){ ?>
							<button class="btn tooltip-bottom" title="Vous ne pouvez pas déplacer un téléchargement en cours" disabled><span class="glyphicon glyphicon-share-alt"></span></button>
							<?php }else{ ?>
							<button class="btn tooltip-bottom" title="Déplacer le téléchargement" data-toggle="popover" data-placement="top" data-content='<?php echo show_move_torrent_popover($this->_id, array_search($this->_downloadDir, $this->_downloadDirs)); ?>'><span class="glyphicon glyphicon-share-alt"></span></button>
							<?php } ?>
							<button class="btn tooltip-bottom" id="del-popover_<?php echo $this->_id; ?>" title="Supprimer" data-toggle="popover" data-placement="top" data-content='<?php echo show_del_torrent_popover($this->_id); ?>'><span class="glyphicon glyphicon-trash tooltip-bottom"></span></button>
						</div>
					</div>
				</div>
			</div>
			<div class="row panel-body torrents collapse" id="collapse_details_<?php echo $this->_id; ?>">
				<ul class="col-md-11">
					<li>Début : <?php echo $this->_get('addedDate'); ?>, fin <?php echo ($this->_get('isFinished'))?': '.$this->_get('doneDate'):'estimée dans <span id="torrent_estimated_end_'.$this->_id.'">'.$this->_get('eta').'</span>'; ?></li>
					<?php if ($this->_get('isFinished')){ ?>
					<li>Ratio d'envoi/réception : <span id="torrent-ratio_<?php echo $this->_id; ?>"><?php echo $this->_get('uploadRatio').' ('.$this->_get('uploadedEver').' envoyés, '.$this->_get('ratioPercentDone').'% du ratio atteint)'; ?></span></li>
					<li>Taille : <?php echo $this->_get('totalSize'); ?></li>
					<?php }else{ ?>
					<li>Reste à télécharger : <span id="torrent-leftuntildone_<?php echo $this->_id; ?>"><?php echo $this->_get('leftUntilDone').'/'.$this->_get('totalSize'); ?></span></li>
					<?php } ?>
					<li>Téléchargé dans : <?php echo $this->_get('downloadDir'); ?></li>
					<?php if (!empty($this->_comment)){ ?>
					<li>Commentaire : <?php echo $this->_get('comment'); ?></li>
					<?php } ?>
					<li>
						<a data-toggle="collapse" data-parent="#torrent_<?php echo $this->_id; ?>" href="#collapse_<?php echo $this->_id; ?>">Liste des fichiers</a>
						<ul class="collapse" id="collapse_<?php echo $this->_id; ?>">
						<?php
						foreach ($this->_files as $file){
							?><li><?php echo $file->name; ?></li><?php
						}
						?>
						</ul>
					</li>
					<?php if (!empty($this->_nfo)){ ?>
					<li>
						<a data-toggle="collapse" data-parent="#torrent_<?php echo $this->_id; ?>" href="#collapse_nfo_<?php echo $this->_id; ?>">Informations sur le fichier principal</a>
						<ul class="collapse" id="collapse_nfo_<?php echo $this->_id; ?>"><pre><?php echo $this->_nfo; ?></pre></ul>
					</li>
					<?php } ?>
				</ul>
				<?php if (!empty($this->_img)){ ?>
				<div class="col-md-1 hidden-sm text-right">
					<img class="img-responsive torrent-img" src="index.php?action=torrentImg&source=<?php echo $this->_img; ?>" alt="<?php echo $this->_get('name'); ?>"/>
				</div>
			<?php } ?>
			</div>
		</div>
		<?php
	}

}
?>