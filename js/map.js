var eventsTypesConsts = {
    'party': 2,
    'service': 1,
    'leisure': 3,
    'all': 0
};

var eventsColorsConsts = {
    1: 'blue',
    2: 'red',
    3: 'black'
};

var min_scale = 0.3;
var max_scale = 1;
var scale_eps = 0.005;

var bigCircleRadius = 20;
var littleCircleRadius = 10;

function Map() {
    this.scale = 0.7;
    this.places = {};
    this.events = {};
    this.activePlace = {};
    this.cachedEvents = {};
    this.stage = new Kinetic.Stage({
        container: 'container',
        width: $(document).width() - 100,
        height: $(document).height() - 100,
        draggable: true
    });
}

Map.prototype.init = function () {
    this.getInitInfo();
    var layer = new Kinetic.Layer();

    var imageObj = new Image();

//    var map = this;

    imageObj.onload = function () {

        var imageMap = new Kinetic.Image({
            x: 1,
            y: 1,
            image: imageObj
        });

        map.imageLayer = new Kinetic.Layer();
        map.placesLayer = new Kinetic.Layer();
        map.eventsLayer = new Kinetic.Layer();

        map.stage.add(map.imageLayer);
        map.stage.add(map.placesLayer);
        map.stage.add(map.eventsLayer);

        map.imageLayer.add(imageMap);

        map.imageLayer.draw();

        layer.draw();

        map.initPlaces();
        map.renderEvents(eventsTypesConsts['service']);
    };

    imageObj.src = '/img/map.jpg';
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
    var events = [];
    if (eventsType != 0) {
        events = this.events[eventsType];
    }
    var i;
    var e;
//    for (i in this.events) {
        for (e in events) {
            var place_id = events[e].events_place_id;
            if (this.places[place_id].circles > 0) {
                this.places[place_id].circles++;
                continue;
            }
            var center = getCenter(this.places[place_id].places_polygon.split(','));
            var circle = new Kinetic.Circle({
                x: center.x, //!
                y: center.y,
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
//    }
    this.eventsLayer.draw();
    this.drawEventsNumbers();
};

Map.prototype.initPlaces = function () {
    this.placesLayer.removeChildren();
    var p;
    for (p in this.places) {
        console.log(this.places[p]);
        var poly = new Kinetic.Line({
            points: this.places[p].places_polygon.split(','),
//            fill: 'red',
            strokeWidth: 6,
            opacity: 0.8,
            closed: true
        });

        poly.placeId = this.places[p].places_id;

//        var map = this;

        poly.on('mouseover', function () {
            this.setStroke('red');
            map.placesLayer.draw();
        });
        poly.on('mouseout', function () {
            this.setStroke('');
            map.placesLayer.draw();
        });
        poly.on('mousedown', function (e) {
            var mousePos = map.stage.getPointerPosition();
            $('#event_place_id').val(this.placeId);
            $('#event_form').show('slow').css({left: mousePos.x, top: mousePos.y});
            map.activePlace = this;
        });
//        poly.on('mouseup', function() {
//
//        });

        this.placesLayer.add(poly);
    }

    this.placesLayer.draw();

};

function getPos(el) {
    for (var lx = 0, ly = 0;
         el != null;
         lx += el.offsetLeft, ly += el.offsetTop, el = el.offsetParent);
    return {x: lx, y: ly};
}


Map.prototype.changeScale = function (new_scale) {
    if (new_scale > min_scale && new_scale < max_scale) {
        var d = document.getElementById('field');
        var canvasPos = getPos(d);
        var absPos = this.stage.getAbsolutePosition();
//        var mousePos = map.stage.getPosition();

        var smallCalc = (this.stage.width / 2 - absPos.x - canvasPos.x) / this.scale;
        var smallCalcY = (this.stage.height / 2 - absPos.y - canvasPos.y) / this.scale;

        var endCalc = (this.stage.width / 2 - canvasPos.x) - new_scale * smallCalc;
        var endCalcY = (this.stage.height / 2 - canvasPos.y) - new_scale * smallCalcY;

        this.stage.setPosition(endCalc, endCalcY);
        this.imageLayer.scaleX(new_scale);
        this.imageLayer.scaleY(new_scale);

        this.placesLayer.scaleX(new_scale);
        this.placesLayer.scaleY(new_scale);

        this.eventsLayer.scaleX(new_scale);
        this.eventsLayer.scaleY(new_scale);

        this.imageLayer.draw();
        this.placesLayer.draw();
        this.eventsLayer.draw();

        var eventForm = $('#event_form');
        if (eventForm.css('display') != 'none') {
//            var x = map.activePlace.x;
//            var y = map.activePlace.y;
//            console.log('x: ' + x);
//            console.log('y: ' + y);
//            eventForm.css({left: x, top: y});
        }

        this.scale = new_scale;
    }
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

    $('#event_form_close').click(function () {
        $('#event_form').hide();
    });

    $('#show_events_all').click(function () {
        map.renderEvents(eventsTypesConsts['all']);
    });

    $('#show_events_party').click(function () {
        map.renderEvents(eventsTypesConsts['party']);
    });

    $('#show_events_leisure').click(function () {
        map.renderEvents(eventsTypesConsts['leisure']);
    });

    $('#show_events_service').click(function () {
        map.renderEvents(eventsTypesConsts['service']);
    });

    $('#event_add').click(function () {
        var event_type = $('#event_type').find('option:selected').val();
        var place_id = $('#event_place_id').val();
        var event_id = addEvent(place_id, $('#event_header').val(), $('#event_description').val(), event_type);
        console.log(event_id);
        console.log(map.events[event_type]);
        map.events[event_type].push({
            events_id: event_id,
            events_place_id: parseInt(place_id)
        });
        console.log(map.events[event_type]);
        map.renderEvents(event_type);
    });

});

var map = new Map();

map.init();
