jQuery(document).ready(function ($) {
	$('.thumbnail').click(function(){
  	$('.modal-body').empty();
  	var title = $(this).attr("title");
  	$('.modal-title').html(title);
  	$($(this).html()).appendTo('.modal-body');
  	$('#lightbox').modal({show:true});
	});
	/*$('#stopped-tab').addClass('hidden');
	$('#show-stopped').click(function(e){
		e.preventDefault();
		$this = $(this);
		$st = $('#stopped-tab');
		if ($st.hasClass('hidden')){
			$st.removeClass('hidden');
			$this.html('Masquer les téléchargements arrêtés');
		}else{
			$st.addClass('hidden');
			$this.html('Montrer les téléchargements arrêtés');
		}
	});*/
});