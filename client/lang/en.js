if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
    console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('en', {
        'Analytics.DailyHits': 'Daily hits',
        'Analytics.UniqueHits': 'Unique hits',
        'Analytics.Hits': 'Hits',
        'Analytics.BrowserShare': 'Share',
        'Analytics.Pages': 'Pages',
        'Analytics.URLs': 'URLs'
    });
}