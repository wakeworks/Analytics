if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
    console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('de', {
        'Analytics.DailyHits': 'Seitenaufrufe',
        'Analytics.UniqueHits': 'Besucher',
        'Analytics.Hits': 'Aufrufe',
        'Analytics.BrowserShare': 'Anteil'
    });
}