$(document).ready(function() {

	// Laaaaaaaaazy loading :)
	$("img.lazy").lazyload();

	$(".tooltip.east").tipsy({
		html : true,
		gravity : 'e'
	});
	$(".tooltip.south").tipsy({
		html : true,
		gravity : 's'
	});
	$(".tooltip.north").tipsy({
		html : true,
		gravity : 'n'
	});
	$(".tooltip:not(.south,.north,.east)").tipsy({
		html : true,
		gravity : 'w'
	});

});