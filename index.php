<?php
$version = '1.2.1';


require_once('config.php');
if (file_exists(config_local.php)){
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
				<a href="<?php echo ($fm == 'jFM')?'?page=files':'http://'.$server.'fichiers'; ?>" class="btn btn-primary btn-block">Accéder aux fichiers</a>
				<button class="btn btn-primary btn-block">Depuis Windows : <code>\\<?php echo $server; ?>\</code></button>
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