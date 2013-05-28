require.config({ 
	paths: {
		jquery      : '../utils/jquery',
		tpl         : '../plugins/tpl',
		backbone    : '../utils/backbone',
		jcarousel   : '../utils/jquery.carousel'
	}
});

require(['testimonials', 'carousel']);