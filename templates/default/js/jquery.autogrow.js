(function($) {
	$.fn.autogrow = function(options) {

		if (!options)
			options = {
				minHeight : 23,
				resizeParentElement : false
			}

		this.filter('textarea').each(function() {
			var $this = $(this), minHeight = options.minHeight;

			var shadow = $('<div></div>').css({
				position : 'fixed',
				bottom : -1000,
				left : -1000,
				width : $(this).width(),
				fontSize : $this.css('fontSize'),
				fontFamily : $this.css('fontFamily'),
				lineHeight : $this.css('lineHeight'),
				"word-wrap": "break-word"
			}).appendTo(document.body);

			var update = function() {
				var val = this.value.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/&/g, '&amp;').replace(/\r/g, '<br/>').replace(/\n/g, '<br/>').replace(/\s/g, '&nbsp;');
				shadow.html($.trim(val));
				$(this).css('height', Math.max(shadow.height(), minHeight));

				if (options.resizeParentElement) {
					if ($(this).parent().parent().parent())
						$(this).parent().parent().parent().css('height', Math.max(shadow.height() + options.minHeight, minHeight * 2.2));
					else
						$(this).parent().parent().parent().parent().css('height', Math.max(shadow.height() + options.minHeight, minHeight * 2.2));
				}

			}
			$(this).change(update).keyup(update).keydown(update).keypress(update);
			update.apply(this);
		});
		return this;
	}
})(jQuery);