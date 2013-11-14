<?php
$version = '1.5';


require_once('config.php');
if (file_exists('config_local.php')){
	require_once('config_local.php');
}

$alert = null;
if (isset($_GET['ajax_files']) and htmlentities($_GET['ajax_files']) == 'ajax'){
	show_files(true);
	return;
}
?>
<!DOCTYPE html>
<html lang="fr-FR">
	<head>
		<title>Salsifis home server</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/salsifis.css" />
		<?php
		if (isset($_GET['page']) and htmlentities($_GET['page']) == 'files'){
			echo '		<link rel="stylesheet" href="css/jquery_fm.css" />';
		}
		?>
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
		<script src="js/jquery-1.10.2.min.js"></script>
	</head>
	<body>
		<div id="wrap">
			<div class="jumbotron">
			  <div class="container">
					<div class="row">
						<div class="col-md-8 text-center col-md-offset-2">
							<img src="img/logo-100.jpg" alt="Nous sommes des salsifis !" class="pull-left img-circle tooltip-bottom hidden-xs" style="vertical-align: baseline">
					    <h1> <a href="./">Salsifis Home Server</a></h1>
					    <p class="hidden-xs"><em>One salsify a day doesn't keep anything away.</em></p>
						</div>
					</div>
			  </div>
			</div>
			
			<!-- Affichage des alertes s'il y en a -->
			<?php if (!empty($alert)){ ?>
				<div class="container">
					<div id="alert-container" class="col-md-4 col-md-offset-4">
					<?php echo $alert; ?>
					</div>
				</div>
			<?php } ?>
			<div class="container">
				<?php
				if (isset($_GET['page'])){
					switch (htmlentities($_GET['page'])){
						case 'build-server':
							build_server();
							break;
						case 'files':
							show_files();
							break;
						case 'faq':
							show_faq();
							break;
					}
				}elseif(isset($_POST['check'])) {
					if (isset($_POST['shutdown'])){
						shutdown();
					}elseif(isset($_POST['reboot'])){
						reboot();
					}else{
						admin();
					}
				}else{
					admin();
				}
				?>
			</div>
		</div>
		<div class="container">
			<div class="text-right text-muted">Salsifis Home Server <span class="text-info"><?php echo $version; ?></span> - <small><a href="?page=build-server" title="Comment construire Salsifis">&Agrave; propos</a></small></div>
		</div>
		<!-- le JS -->
		
		<script src="js/bootstrap.min.js"></script>
		<script src="js/salsifis.js"></script>
		<?php
		if (isset($_GET['page']) and htmlentities($_GET['page']) == 'files'){
			echo '		<script src="js/jquery_fm.js"></script>';
		}
		?>
		<script>
			jQuery(document).ready(function ($) {
				/** Gestion des infobulles
				* Une infobulle peut être affichée à gauche, à droite, en haut et en bas du conteneur auquel elle est reliée.
				* Il suffit pour celà d'employer les classes suivantes :
				* - tooltip-left
				* - tooltip-right
				* - tooltip-top
				* - tooltip-bottom (défaut)
				* Il faut également renseigner une balises title ou alt.
				*/
			  $('.tooltip-right[title]').tooltip({placement: 'right'});
			  $('.tooltip-left[title]').tooltip({placement: 'left'});
			  $('.tooltip-bottom[title]').tooltip({placement: 'bottom'});
			  $('.tooltip-top[title]').tooltip({placement: 'top'});
			  $('.tooltip-right[alt]').tooltip({placement: 'right', title: function(){return $(this).attr('alt');}});
			  $('.tooltip-left[alt]').tooltip({placement: 'left', title: function(){return $(this).attr('alt');}});
			  $('.tooltip-bottom[alt]').tooltip({placement: 'bottom', title: function(){return $(this).attr('alt');}});
			  $('.tooltip-top[alt]').tooltip({placement: 'top', title: function(){return $(this).attr('alt');}});
			  $('a').tooltip({placement: 'bottom'});
				$('#system-state').load('ajax.php?get=system-state');
				$('#processes').load('ajax.php?get=processes');
			});
		</script>
	</body>
</html>
<?php

function show_faq(){
	$server = rtrim($_SERVER['HTTP_HOST'], '/');
	?>
	<h2>Aide à l'utilisation des Salsifis</h2>
	<div class="panel-group" id="accordion">
	  <div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
	          Comment je télécharge des trucs ?
	        </a>
	      </h4>
	    </div>
	    <div id="collapse1" class="panel-collapse collapse">
	      <div class="panel-body">
	      	<p>
						Le serveur Salsifis se sert de bittorrent pour télécharger des trucs. Les torrents sont des petits fichiers qui contiennent les informations nécessaires au téléchargement du fichier que vous avez demandé.<br />
						<blockquote>Nous vous rappelons que le piratage c'est mal, et que c'est presque aussi vilain que de vous refaire payer l'intégralité d'un film pour l'avoir en HD alors que vous l'avez déjà en DVD, ou pour pouvoir le lire confortablement depuis votre canapé sans avoir à changer de bluray chaque fois.</blockquote>
						Vous vous doutez bien que je ne vais pas inscrire ici d'adresse pour télécharger vos films de vacances. Ceci dit, lorsque vous êtes sur la page de téléchargement d'un torrent sur un site, faites un clic droit et choisissez "sauvegarder sous..." (cette mention varie un peu suivant votre navigateur, mais vous voyez le principe). Sauvegardez-le dans <code>\\<?php echo $server; ?>\Torrents</code>.<br />
						<br />Et c'est tout.<br /><br />
						Pour info, le serveur Salsifis va détecter le torrent et l'ajouter à sa liste de téléchargements tout seul comme un grand. il vous suffira d'aller voir dans les <a href="http://<?php echo $server; ?>:9091" title="Vos téléchargements">téléchargements</a> si vous voulez vérifier qu'il est en route.
					</p>
				</div>
	    </div>
	  </div>
		<div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
	          Comment accéder à mes fichiers depuis Windows ?
	        </a>
	      </h4>
	    </div>
	    <div id="collapse2" class="panel-collapse collapse">
	      <div class="panel-body">
	      	<ul>
						<li>Dans l'explorateur Windows, il vous faut aller dans la rubrique "Réseau" (c'est la dernière dans la fenêtre de gauche). Vous devriez apercevoir un ordinateur appelé "Salsifis".</li>
						<li>Sous Windows 7 : Dans le menu démarrer, saisissez <code>\\<?php echo $server; ?></code>. Validez avec <code>Entrée</code>.</li>
						<li>Sous Windows 7 et 8 : Appuyez sur les touches <code>WINDOWS</code> + <code>R</code> (la touche windows est entre <code>Ctrl</code> et <code>Alt</code>) et saisissez <code>\\<?php echo $server; ?></code> dans le champ. Validez avec <code>Entrée</code>.</li>						
					</ul>
					<p>Vous devriez créer un raccourci vers les répertoires du serveur qui vous intéressent, afin d'éviter de chercher par le réseau chaque fois.</p>
				</div>
	    </div>
	  </div>
		<div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
	          Comment copier des fichiers depuis mon serveur vers un disque ou une clé usb ?
	        </a>
	      </h4>
	    </div>
	    <div id="collapse3" class="panel-collapse collapse">
	      <div class="panel-body">
	      	<p>Il faut brancher le disque ou la clé sur votre PC et copier les fichiers depuis Windows. Il n'y a pas actuellement de méthode simple pour brancher un disque directement sur le serveur Salsifis.</p>
				</div>
	    </div>
	  </div>
		<div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
	          Comment accéder au serveur hors de chez moi ?
	        </a>
	      </h4>
	    </div>
	    <div id="collapse4" class="panel-collapse collapse">
	      <div class="panel-body">
	      	<p>Salsifis n'est absolument pas prévu pour être accédé depuis l'extérieur, parce qu'il n'est pas du tout sécurisé. Le but des Salsifis était de privilégier le confort d'utilisation, ce qui se fait au détriment de la sécurité. Chez vous, ce n'est absolument pas grave, mais si vous permettez qu'on y accède de l'extérieur, c'est tout autre chose.<br /> Vous pourriez vous faire pirater, et les vilains pirates pourraient se servir de cet accès pour faire n'importe quoi sur votre PC également.<br /><br />Donc non, on ne doit pas faire ça.</p>
				</div>
	    </div>
	  </div>
		<div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapselegal">
	          Est-ce que c'est bien légal tout ça ?
	        </a>
	      </h4>
	    </div>
	    <div id="collapselegal" class="panel-collapse collapse">
	      <div class="panel-body">
	      	<p>
						Disons que c'est borderline. Rien dans ce serveur n'est illégal en soi, toutefois vous pouvez très bien en faire une utilisation pas du tout légale.<br />
						Pour ma part, j'estime que si j'ai acheté un film je devrais avoir le droit de le visionner comme je le souhaite. Ça ne me pose donc aucun problème moral de télécharger une version numérique (un film en .avi, .mkv, etc.) <strong>sans</strong> <abbr class="tooltip-bottom initialism" title="Verrou numérique">DRM</abbr> si j'ai déjà acheté le film en Bluray.
					</p>
				</div>
	    </div>
	  </div>
		<div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapsenom">
	          Pourquoi avoir choisi Salsifis comme nom de serveur ?
	        </a>
	      </h4>
	    </div>
	    <div id="collapsenom" class="panel-collapse collapse">
	      <div class="panel-body">
	      	<p>La plupart des noms idiots sont déjà pris, et puis comme les salsifis c'est tout pourri et que c'était aussi le cas de ce serveur, ça m'a semblé assez adpaté.</p>
				</div>
	    </div>
	  </div>
		<div class="panel panel-default">
	    <div class="panel-heading">
	      <h4 class="panel-title">
	        <a data-toggle="collapse" data-parent="#accordion" href="#collapseemail">
	          J'ai une question mais la réponse n'est pas ici (ou bien je n'ai rien compris aux réponses apportées) !
	        </a>
	      </h4>
	    </div>
	    <div id="collapseemail" class="panel-collapse collapse">
	      <div class="panel-body">
	      	<p>Téléphonez-donc à l'abruti qui vous a installé ce serveur à la noix.</p>
				</div>
	    </div>
	  </div>
	</div>
	<br /><br />
	<?php
}

function show_files($process = false){
	global $dlna_path;
	require('file_manager.php');
	$manager = new FileManager();
	$manager->path = $dlna_path;
	$manager->ajax_endpoint = '?page=files&ajax_files=ajax';
	if ($process) {
    $manager->process_request();
    return;
	}
	echo $manager->render();
	?>
	<div id="lightbox" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h3 class="modal-title">Heading</h3>
				</div>
				<div class="modal-body">
				Test
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal">Fermer</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function build_server(){
	require("markdown.php");
	$text = '';
	$file_handle = fopen("README.md", "r");
	while (!feof($file_handle)) {
	   $text .= fgets($file_handle);
	}
	fclose($file_handle);
	echo Markdown($text);
}

function reboot(){
	$server = rtrim($_SERVER['HTTP_HOST'], '/');
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="jumbotron">
					<h2 class="text-danger text-center" style="font-size: 2em" >Redémarrage en cours !</h2>
				</div>
				<p>Le redémarrage ne devrait pas excéder 5 minutes.<br />Si ce délai est dépassé et que vous n'arrivez toujours pas à accéder à votre serveur, il y a de fortes chances pour que quelque chose cloche.</p>
				<p>Cliquez sur le lien ci-dessous pour retourner à la page d'accueil. Vous autrez une erreur tant que le serveur n'aura pas redémarré.<br />
				Il vous suffit d'actualiser la page (F5 sur un PC) régulièrement pour que l'interface apparaisse une fois le serveur redémarré.</p>
				<div class="text-center"><a class="btn btn-lg btn-primary" title="Cliquez ici et soyez patient !" href="http://<?php echo $server; ?>">Revenir à la page d'accueil de Salsifis Home Server</a></div>
			</div>
		</div>
	</div>
	<?php
	exec("/usr/local/bin/reboot_suid");
}

function shutdown(){
	$server = rtrim($_SERVER['HTTP_HOST'], '/');
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="jumbotron">
					<h2 class="text-danger text-center" style="font-size: 2em" >Arrêt en cours !</h2>
				</div>
				<p>Votre serveur de salsifis va s'arrêter. Vous devrez appuyer sur le bouton d'alimentation de la machine pour la redémarrer.</p>
				<p>Vous pourrez ensuite vous connecter sur l'interface de Salsifis Home Server avec ce lien : </p>
				<div class="text-center"><h2><strong>http://<?php echo $server; ?></strong></h2></div>
				<p class="text-center">Vous pouvez fermer cette fenêtre.</p>
			</div>
		</div>
	</div>
	<?php
	exec("/usr/local/bin/shutdown_suid");
}

function admin(){
	global $fm;
	$server = rtrim($_SERVER['HTTP_HOST'], '/');
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-4" id="system-state"></div>
			<div class="col-md-4" id="processes"></div>
			<div class="col-md-4">
				<h3>Accès</h3>
				<a href="http://<?php echo $server; ?>:9091" class="btn btn-primary btn-block">Accéder aux téléchargements</a>
				<a href="<?php echo ($fm == 'jFM')?'?page=files':'http://'.$server.'/fichiers'; ?>" class="btn btn-primary btn-block">Accéder aux fichiers</a>
				<button class="btn btn-primary btn-block">Depuis Windows : <code>\\<?php echo $server; ?>\</code></button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-8 col-md-offset-2 col-sm-12">
				<h2>Vous êtes perdu(e) ?</h2>
				<p>Pas de panique. Cliquez sur ce gros bouton rassurant, tout vous sera expliqué.</p>
				<a href="?page=faq" title="Je suis un gros bouton rassurant" class="btn btn-primary btn-lg btn-block">Aide</a>
			</div>
		</div>
		<div class="row">
			<div class="col-md-8 col-md-offset-2 col-sm-12">
				<h2>Éteindre/redémarrer Salsifis Home Server</h2>
				<p class="text-danger">Attention : Vérifiez bien que personne n'est en train de transférer des fichiers (les téléchargements ne comptent pas), sans quoi des données peuvent être perdues !</p>
				<form method="POST">
					<button type="submit" name="shutdown" class="btn btn-danger btn-lg btn-block">Arrêter les salsifis</button>
					<button type="submit" name="reboot" class="btn btn-danger btn-lg btn-block">Redémarrer les salsifis</button>
					<input type="hidden" name="check" value="yes">
				</form>
				<br />
				<br />
				<br />
			</div>
		</div>
	</div>
	<?php
}
?>