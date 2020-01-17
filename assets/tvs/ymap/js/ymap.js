(function ($) {
    $.fn.ymapTV = function (options) {
        var settings = $.extend({
            coords: '0,0',
            tv: '',
            zoom: 15,
            noKey: true
        }, options);
        var mapPlaceholder = this[0].id;
        settings.coords = settings.coords.split(',', 2);
        settings.tv = $(settings.tv);
        var map = {
            placemark: {},
            init: function () {
                var myMap = new ymaps.Map(mapPlaceholder, {
                    center: settings.coords,
                    zoom: settings.zoom,
                    behaviors: ['default', 'scrollZoom'],
                    controls: [
                        "zoomControl",
                        "fullscreenControl",
                        "geolocationControl",
                        "typeSelector"
                    ]
                });
                if (!settings.noKey) {
                    var SearchControl = new ymaps.control.SearchControl({
                        options: {
                            useMapBounds: true,
                            noPlacemark: true
                        }
                    });
                    SearchControl.events.add('resultselect', function (e) {
                        var results = SearchControl.getResultsArray(),
                            selected = e.get('index'),
                            coords = results[selected].geometry.getCoordinates();

                        map.save(coords);
                    });
                    myMap.controls.add(SearchControl);
                }

                map.placemark = new ymaps.Placemark(
                    settings.coords,
                    {},
                    {preset: "twirl#redIcon", draggable: true});

                myMap.geoObjects.add(map.placemark);

                map.placemark.events.add("dragend", function (e) {
                    var coords = e.get('target').geometry.getCoordinates();
                    map.save(coords);
                }, map.placemark);

                myMap.events.add('click', function (e) {
                    var coords = e.get('coords');
                    map.save(coords);
                });
            },
            save: function (coords) {
                map.placemark.geometry.setCoordinates(coords);
                settings.tv.val(coords.join(','));
            }
        }
        ymaps.ready(map.init);
    };
})(jQuery);
