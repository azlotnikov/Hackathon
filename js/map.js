function Map() {
    this.places = {};
    this.events = {};
    this.stage = new Kinetic.Stage({
        container: 'container',
        width: 1000,
        height: 600
    });
    this.placesLayer = new Kinetic.Layer();
    this.eventsLayer = new Kinetic.Layer();
}

Map.prototype.init = function () {
    this.getInitInfo();
    var layer = new Kinetic.Layer();

    var mapImage = new Image();
    mapImage.onload = function () {
        var image = new Kinetic.Image({
            x: 1,
            y: 1,
            image: image,
            width: 106,
            height: 118
        });

        layer.add(image);

        map.stage.add(layer);
    };
    mapImage.src = 'http://www.html5canvastutorials.com/demos/assets/yoda.jpg';

    this.initPlaces();
};

Map.prototype.getInitInfo = function () {
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "getInitInfo"
        },
        success: function (data) {
            if (data.hasOwnProperty('result')) {
                if (data.result == 'true') {
                    this.places = data.places;
                    this.events = data.events;
                } else {
                    alert(data.message);
                }
            } else {
                alert('Unknown error!');
            }
        },
        contentType: 'application/json'
    });
};

Map.prototype.renderEvents = function (eventsTypeName) {
    this.eventsLayer.removeChildren();
    var e;
    for (e in this.events[eventsTypeName]) {

    }
};

Map.prototype.initPlaces = function() {
    this.placesLayer.removeChildren();
    var p;
    for (p in this.places) {
        var poly = new Kinetic.Line({
            points: JSON.parse(p.polygon),
            fillEnabled: false,
            strokeEnabled: true, //!!! for testing
            stroke: 'red',
            strokeWidth: 4,
            closed: true
        });
        this.placesLayer.add(poly);
    }

};

$(document).ready(function () {

    $('#view_parties').click(function () {
        map.renderEvents('party');
    });

    $('#view_services').click(function () {
        map.renderEvents('service');
    });

    $('#view_leisure').click(function () {
        map.renderEvents('leisure');
    });

    $('#event_add').click(function () {
        addEvent($('event_description').val(), $('event_type').val());
    });

});

var map = new Map();

map.init();
