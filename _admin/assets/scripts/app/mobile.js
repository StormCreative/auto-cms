require.config({ 
	paths: {
		jquery   : '../utils/jquery',
		tpl      : '../plugins/tpl',
		backbone : '../utils/backbone'
	}
});

require(['mobilenav', 'filter', 'settings', 'delete-popup']);
