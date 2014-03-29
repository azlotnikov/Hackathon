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

    this.stage.add(this.placesLayer);
    this.stage.add(this.eventsLayer);
}

Map.prototype.init = function () {
    this.getInitInfo();
    var layer = new Kinetic.Layer();

    var mapImage = new Image();
    mapImage.onload = function () {
        var imageMap = new Kinetic.Image({
            x: 1,
            y: 1,
            image: imageMap,
            width: 106,
            height: 118
        });

        layer.add(imageMap);

        map.stage.add(layer);

        layer.draw();
    };
    mapImage.src = 'http://www.html5canvastutorials.com/demos/assets/yoda.jpg';

    this.initPlaces();
};

Map.prototype.getInitInfo = function () {
    var $this = this;
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "getInitInfo",
            floor: "1"
        },
        success: function (data) {
            if (data.hasOwnProperty('result')) {
                if (data.result) {
                    $this.places = data.data.places;
                    $this.events = data.data.events;
                } else {
                    alert(data.message);
                }
            } else {
                alert('Unknown error!');
            }
        },
        dataType: 'json',
        async: false
    });
};

Map.prototype.renderEvents = function (eventsTypeName) {
    this.eventsLayer.removeChildren();
    var e;
    for (e in this.events[eventsTypeName]) {

    }
};

Map.prototype.initPlaces = function () {
    this.placesLayer.removeChildren();
    var p;
    for (p in this.places) {
        console.log(this.places[p].places_polygon.split(','));
        var poly = new Kinetic.Line({
            points: this.places[p].places_polygon.split(','),
            strokeWidth: 3,
            opacity: 0.3,
            closed: true
        });

        console.log(this.places[p].places_id);

        poly.placeId = this.places[p].places_id;

        poly.on('mouseover', function () {
            this.setStroke('red');
            map.placesLayer.draw();
        });
        poly.on('mouseout', function () {
            this.setStroke('');
            map.placesLayer.draw();
        });
        poly.on('mousedown', function() {
            var mousePos = map.stage.getPointerPosition();
            var eventForm = $('#eventAddForm');
            $('event_place_id').val(this.placeId);
            eventForm.show();
            eventForm.css({left: mousePos.x, top: mousePos.y});
        });
        poly.on('mouseup', function() {

        });

        this.placesLayer.add(poly);
    }

    this.placesLayer.draw();

};

$(function () {

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
        addEvent($('#event_header').val(), $('#event_description').val(), $('#event_type').find('option:selected').val());
    });

});

var map = new Map();

map.init();
