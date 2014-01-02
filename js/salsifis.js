jQuery(document).ready(function ($) {
	$('.thumbnail').click(function(){
  	$('.modal-body').empty();
  	var title = $(this).attr("title");
  	$('.modal-title').html(title);
  	$($(this).html()).appendTo('.modal-body');
  	$('#lightbox').modal({show:true});
	});

	$(document).on('click', '.close-popover', function(e){
		var id = $(this).data('close-popover');
		$('#'+id).popover('toggle');
		e.preventDefault();
	});
	
	$(document).popover({
		selector: '[data-toggle="popover"]',
		html:true
	});
	
	if ($('#system-state').length > 0){
		loadServerState();
		setInterval(loadServerState, 10000);
	}
	
	function loadServerState(){
		//console.log('refreshing server state...');
		$('#system-state').load('ajax.php?get=system-state');
		$('#processes').load('ajax.php?get=processes');
	}
	
	
	//Si ratio est défini, on est dans les torrents
  if (typeof(ratio) != "undefined" && ratio !== null){
		setInterval(refreshTorrentsData, 10000); 
	}
	

	function refreshTorrentsData(){
		$.ajax({
		  url: "ajax.php",
			cache: false,
			dataType: 'json',
			data: {
				get: 'refresh-torrents'
			}
		 }).done(function(data) {
			//console.log( "Sample of data:", data.slice( 0, 1000 ) );
			//console.log('refreshing data...');
			$.each(data, function(i, item){
				var percentDone = (item.percentDone !== null)?item.percentDone:0;
				var uploadRatio = (item.uploadRatio !== null && item.uploadRatio != -1)?item.uploadRatio:0;
				var ratio_percent = ((uploadRatio/ratio)*100).toFixed(0);
				//$('#torrent-progress-bar-title_'+item.id).attr('title', "Terminé à "+(percentDone*100)+'%');
				$('#torrent-progress-bar-title_'+item.id).attr('data-original-title', "Terminé à "+(percentDone*100)+'%');
				$('#torrent-progress-bar-dl_'+item.id).css('width:'+(percentDone*100)+'%');
				$('#torrent-progress-bar-dl_'+item.id).attr('aria-valuenow', (percentDone*100));
				$('#torrent-progress-bar-seed_'+item.id).css('width:'+(ratio_percent*100)+'%');
				$('#torrent-progress-bar-seed_'+item.id).attr('aria-valuenow', (ratio_percent*100));
				$('#torrent_estimated_end_'+item.id).html(duree_humanize(item.eta));
				$('#torrent-ratio_'+item.id).html(uploadRatio+' ('+octal_humanize(item.uploadedEver)+' envoyés, '+Math.round((uploadRatio/ratio)*100)+'% du ratio atteint)');
				$('#torrent-leftuntildone_'+item.id).html(octal_humanize(item.leftUntilDone)+'/'+octal_humanize(item.totalSize));
			});
		 });
	}
	
	function octal_humanize(value){
		if (value === 0){
			return '0 o';
		}
		var si_prefix = ['o', 'Ko', 'Mo', 'Go', 'To', 'Eo', 'Zo', 'Yo'];
		var base = 1024;
		var classe = Math.min(parseInt(Math.log(value)/Math.log(base)) , si_prefix.length - 1);
		return (value / Math.pow(base, classe)).toFixed(2)+' '+si_prefix[classe]
	}
	
	function duree_humanize(value){
		if (value < 0){
			return 'Inconnu';
		}
		var days = Math.floor(value/60/60/24);
		var hours = Math.floor(value/60/60)%24;
		var mins = Math.floor(value/60)%60;
		var secs = value%60;
		 var ret = '';
		if (days > 0){
			ret += days+' jour';
			if (days > 1){
				ret +='s';
			}
			ret += ' ';
		}
		if (hours > 0){
			ret += hours+' heure';
			if (hours > 1){
				ret +='s';
			}
			ret += ' ';
		}
		if (mins > 0){
			ret += mins+' minute';
			if (mins > 1){
				ret +='s';
			}
			ret += ' ';
		}
		if (secs > 0){
			ret += 'et '+secs+' seconde';
			if (secs > 1){
				ret +='s';
			}
		}
		return ret;
	}
});