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
	
	$(document).on('change', '#filterBy', function(e){
		console.log('changed !');
		var filter = $(this).val();
		$.ajax({
		  url: "index.php",
			cache: false,
			dataType: 'html',
			data: {
				action			: 'filtering',
				filteredBy	: filter
			}
		 }).done(function(data) {
			//console.log('refreshing data...');
			$('#torrentsPage').html(data);
		});
	});

	function refreshTorrentsData(){
		$.ajax({
		  url: "index.php",
			cache: false,
			dataType: 'json',
			data: {
				action			: 'refreshTorrents'
			}
		 }).done(function(data) {
			//console.log( "Sample of data:", data.slice( 0, 1000 ) );
			//console.log('refreshing data...');
			$.each(data, function(i, item){
				$('#torrent-progress-bar-title_'+item.id).attr('title', "Terminé à "+(ditem.percentDone)+'%');
				$('#torrent-progress-bar-title_'+item.id).attr('data-original-title', "Terminé à "+(item.percentDone)+'%');
				$('#torrent-progress-bar-dl_'+item.id).css('width', (item.percentDone)+'%');
				$('#torrent-progress-bar-dl_'+item.id).attr('aria-valuenow', (item.percentDone));
				$('#torrent-progress-bar-seed_'+item.id).css('width', item.ratioPercentDone+'%');
				$('#torrent-progress-bar-seed_'+item.id).attr('aria-valuenow', item.ratioPercentDone);
				$('#torrent_estimated_end_'+item.id).html(item.eta);
				$('#torrent-ratio_'+item.id).html(item.uploadRatio+' ('+item.uploadedEver+' envoyés, '+item.ratioPercentDone+'% du ratio atteint)');
				$('#torrent-leftuntildone_'+item.id).html(item.leftUntilDone+'/'+item.totalSize);
			});
		});
	}
});