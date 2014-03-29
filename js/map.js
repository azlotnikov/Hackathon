var eventsTypesConsts = {
    'party': 2,
    'service': 1,
    'leisure': 3
};

var eventsColorsConsts = {
    1: 'blue',
    2: 'red',
    3: 'black'
};

var bigCircleRadius = 20;
var littleCircleRadius = 10;

function Map() {
    this.places = {};
    this.events = {};
    this.cachedEvents = {};
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
    this.renderEvents(eventsTypesConsts['service']);
    this.drawEventsNumbers();
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

Map.prototype.zerosEventsCirclesForPlaces = function () {
    var p;
    for (p in this.places) {
        this.places[p].circles = 0;
    }
};

Map.prototype.drawEventsNumbers = function () {
    var p;
    for (p in this.places) {
        if (this.places[p].circles < 2) {
            continue;
        }
        var points = this.places[p].places_polygon.split(',');
        var x = parseInt(points[0]) + parseInt(bigCircleRadius / 2) + 3;
        var y = parseInt(points[1]) - parseInt(bigCircleRadius / 2) - 3;
        var circle = new Kinetic.Circle({
            x: x, //!
            y: y,
            radius: littleCircleRadius,
            fill: 'purple',
            opacity: 0.9,
            strokeEnabled: false
        });

        this.eventsLayer.add(circle);

        var circleText = new Kinetic.Text({
            x: x - 4,
            y: y - 7,
            text: this.places[p].circles,
            fontSize: 17,
            fontFamily: 'Calibri',
            fill: 'black'
        });

        this.eventsLayer.add(circleText);
    }
    this.eventsLayer.draw();
};

Map.prototype.renderEvents = function (eventsType) {
    this.eventsLayer.removeChildren();
    this.zerosEventsCirclesForPlaces();
    var e;
    var events = this.events[eventsType];
    for (e in events) {
        var place_id = events[e].events_place_id;
        if (this.places[place_id].circles > 0) {
            this.places[place_id].circles++;
//            console.log(this.places[place_id].circles);
            continue;
        }
        var points = this.places[place_id].places_polygon.split(',');
        var circle = new Kinetic.Circle({
            x: points[0], //!
            y: points[1],
            radius: bigCircleRadius,
            fill: eventsColorsConsts[eventsType],
            opacity: 0.5,
            strokeEnabled: false
        });

        this.places[place_id].circles++;

        circle.eventId = events[e].events_id;

        circle.on('mousedown', function () {
            alert(this.eventId);
        });

        this.eventsLayer.add(circle);


    }

    this.eventsLayer.draw();
};

Map.prototype.initPlaces = function () {
    this.placesLayer.removeChildren();
    var p;
    for (p in this.places) {
        console.log(this.places[p]);
        var poly = new Kinetic.Line({
            points: this.places[p].places_polygon.split(','),
//            fill: 'red',
            strokeWidth: 3,
            opacity: 0.5,
            closed: true
        });

        poly.placeId = this.places[p].places_id;

        poly.on('mouseover', function () {
            this.setStroke('red');
            map.placesLayer.draw();
        });
        poly.on('mouseout', function () {
            this.setStroke('');
            map.placesLayer.draw();
        });
        poly.on('mousedown', function () {
            var mousePos = map.stage.getPointerPosition();
            var eventForm = $('#eventAddForm');
            $('#event_place_id').val(this.placeId);
            eventForm.show();
            eventForm.css({left: mousePos.x, top: mousePos.y});
        });
//        poly.on('mouseup', function() {
//
//        });

        this.placesLayer.add(poly);
    }

    this.placesLayer.draw();

};

$(function () {

    $('#view_parties').click(function () {
        map.renderEvents(eventsTypesConsts['party']);
    });

    $('#view_services').click(function () {
        map.renderEvents(eventsTypesConsts['service']);
    });

    $('#view_leisure').click(function () {
        map.renderEvents(eventsTypesConsts['leisure']);
    });

    $('#event_add').click(function () {
        addEvent($('#event_place_id').val(), $('#event_header').val(), $('#event_description').val(), $('#event_type').find('option:selected').val());
        location.reload();
    });

});

var map = new Map();

map.init();
