jQuery(document).ready(function ($) {
	$('.thumbnail').click(function(){
  	$('.modal-body').empty();
  	var title = $(this).attr("title");
  	$('.modal-title').html(title);
  	$($(this).html()).appendTo('.modal-body');
  	$('#lightbox').modal({show:true});
	});
});