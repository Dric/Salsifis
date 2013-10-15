<?php
if (isset($_GET['get'])){
	$get = htmlentities($_GET['get']);
	switch ($get){
		case "system-state":
			system_display();
			break;
		case "processes":
			processes();
			break;
	}
}

function process_running($process){
	exec("pgrep ".$process, $output, $return);
	if ($return == 0) {
	    return true;
	}
	return false;
}

function get_server_memory_usage(){
	$free = shell_exec('free');
	$free = (string)trim($free);
	$free_arr = explode("\n", $free);
	$mem = explode(" ", $free_arr[1]);
	$mem = array_filter($mem);
	$mem = array_merge($mem);
	$memory_usage = $mem[2]/$mem[1]*100;
	$free = octal_humanize(($mem[1]-$mem[2])*1024);
	$total = octal_humanize($mem[1]*1024);
	return array('free'=>$free, 'total'=>$total, 'percent'=>$memory_usage);
}
function get_server_cpu_usage(){
	//occupation système - moins précis mais immédiat
	$load = sys_getloadavg();
	return array('free'=>(100-$load[0]).'%', 'total'=>'', 'percent'=>$load[0]);
}

function get_server_disk_usage(){
	$free = disk_free_space(".");
	$total = disk_total_space("."); 
	$occupation = $total - $free;
	$occ_percent = (($total - $free)/$total)*100;
	$free_disp = octal_humanize($free);
	$total_disp =  octal_humanize($total);
	return array('free'=>$free_disp, 'total'=>$total_disp, 'percent'=>$occ_percent);
}

function octal_humanize($value){
	$si_prefix = array( 'o', 'Ko', 'Mo', 'Go', 'To', 'Eo', 'Zo', 'Yo' );
	$base = 1024;
	$class = min((int)log($value , $base) , count($si_prefix) - 1);
	return sprintf('%1.2f' , $value / pow($base,$class)) . ' ' . $si_prefix[$class];
}

function system_progress_bar($tab){
	if ($tab['percent'] > 75){
		$level = 'danger';
	}elseif($tab['percent'] < 50){
		$level = 'success';
	}else{
		$level = 'warning';
	}
	if (!empty($tab['total'])){
		$libelle = $tab['free'].' libres sur '.$tab['total'];
	}else{
		$libelle = $tab['free'].' inoccupé';
	}
	$disp = '<div class="progress tooltip-bottom" title="'.$libelle.'">
						<div class="progress-bar progress-bar-'.$level.'" role="progressbar" aria-valuenow="'.round($tab['percent'], 1).'" aria-valuemin="0" aria-valuemax="100" style="width: '.round($tab['percent'], 1).'%;">
    					<span class="sr-only">'.$tab['percent'].'% Complete</span>
						</div>
					</div>';
	return $disp;
}

function disp_running($test){
	if ($test){
		$color = 'success';
		$label = 'Lancé';
	}else{
		$color = 'danger';
		$label = 'Stoppé';
	}
	echo '<span class="label label-'.$color.'">'.$label.'</span>';
}

function processes(){
	$transbt = process_running('transmission-da');
	$minidlna = process_running('minidlna');
	$samba = process_running('smbd');
	?>
		<h3>Suivi des services :</h3>
		<table class="table">
			<tr><td>Transmission</td><td><span class="tooltip-bottom" title="Bitorrent est un protocle de téléchargement. Il est zieuté par Hadopi (ou ce qu'il en reste), aussi n'utilisez que des trackers privés !">bittorrent</span></td><td><?php disp_running($transbt); ?></td></tr>
			<tr><td>MiniDLNA</td><td><span class="tooltip-bottom" title="Si votre téléviseur, votre décodeur, votre smartphone/tablette ou votre box adsl sont compatibles avec la norme DLNA, vous pourrez lire vos films, musiques et photos directement depuis ceux-ci.">serveur média</span></td><td><?php disp_running($minidlna); ?></td></tr>
			<tr><td>Samba</td><td><span class="tooltip-bottom" title="Pour pouvoir accéder à vos fichiers depuis Windows">partage de fichiers</span></td><td><?php disp_running($samba); ?></td></tr>
		</table>
		
  <script>
    $('.tooltip-bottom[title]').tooltip({placement: 'bottom'});
    $('.tooltip-bottom[alt]').tooltip({placement: 'bottom', title: function(){return $(this).attr('alt');}});
  </script>
	<?php
}

function system_display(){
	$disk_usage = get_server_disk_usage();
	$mem_usage = get_server_memory_usage();
	$cpu_usage = get_server_cpu_usage();
	?>
		<h3>Etat du serveur</h3>
		Occupation de l'espace disque :
		<?php echo system_progress_bar($disk_usage); ?>
		Occupation mémoire vive (RAM) :
		<?php echo system_progress_bar($mem_usage); ?>
		Occupation du système :
		<?php echo system_progress_bar($cpu_usage); ?>
  <script>
    $('.tooltip-bottom[title]').tooltip({placement: 'bottom'});
    $('.tooltip-bottom[alt]').tooltip({placement: 'bottom', title: function(){return $(this).attr('alt');}});
  </script>
	<?php
}