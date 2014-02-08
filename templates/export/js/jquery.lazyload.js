/* lazyLoad */
(function($) {
	$.fn.lazyload = function() {
		
		var images = this,
			classname = this.selector.replace('img.', '');
		
		showVisible();
		
		$(window).scroll(function() {
			showVisible();
		})
		
		function showVisible(){
			images.each(function() {
				var img = $(this);
				
				if(img.hasClass(classname)) {
					var imgTop = img.offset().top,
						wTop = $(window).scrollTop() + $(window).height() + 100;
					
					if(wTop > imgTop)
						img.removeClass(classname).attr('src', img.data('src'))
				}
			});
		}

	};
})(jQuery);
