
$(document).ready(function(){
	$("#highlight").click(function(){
		$("span.spellcheck").css("color","#ff0000").css("font-weight","bold").css("cursor","pointer");
		
		$("span.spellcheck").each(function(){
			$(this).attr("title",$(this).attr("data-orig"));
			$(this).addClass('highlighted');
		});
	});
	
	$("span.spellcheck").click(function(){
		var orig = $(this).html();
		$(this).html($(this).attr('title'));
		$(this).attr('title',orig);
	});
	
	$("#db").click(function(){
		$("div#debugging").toggle();
		$("span.spellcheck").css("color","#ff0000").css("font-weight","bold").css("cursor","pointer");
		
		$("span.spellcheck").each(function(){
			$(this).attr("title",$(this).attr("data-orig"));
			$(this).addClass('highlighted');
		});
	});

	$("#reload").click(function(){
		window.location.reload();
	})
});