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

var scale = 1;
var min_scale = 0.4;
var max_scale = 1.6;
var scale_eps = 0.005;

var bigCircleRadius = 20;
var littleCircleRadius = 10;

function Map() {
    this.places = {};
    this.events = {};
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

    var map = this;

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
    var e;
    var events = this.events[eventsType];
    for (e in events) {
        var place_id = events[e].events_place_id;
        if (this.places[place_id].circles > 0) {
            this.places[place_id].circles++;
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

        var map = this;

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

function onMouseWheel(e, delta, dx, dy) {

    // mozilla fix...
    if (e.originalEvent.detail) {
        delta = e.originalEvent.detail;
    }
    else {
        delta = e.originalEvent.wheelDelta;
    }

    if (delta !== 0) {
        e.preventDefault();
    }

    var _cur_scale;
    if (delta > 0) {
        _cur_scale = scale + Math.abs(delta / 640);
    } else {
        _cur_scale = scale - Math.abs(delta / 640);
    }

    if (_cur_scale > min_scale && _cur_scale < max_scale && Math.abs(_cur_scale - scale) > scale_eps) {

        var cur_scale = _cur_scale;

        var d = document.getElementById('field');
        var cnvsPos = getPos(d);
        var Apos = map.stage.getAbsolutePosition();
        var mousePos = map.stage.getPosition();

        var smallCalc = (e.originalEvent.pageY - Apos.x - cnvsPos.x) / scale;
        var smallCalcY = (e.originalEvent.pageY - Apos.y - cnvsPos.y) / scale;

        var endCalc = (e.originalEvent.pageY - cnvsPos.x) - cur_scale * smallCalc;
        var endCalcY = (e.originalEvent.pageY - cnvsPos.y) - cur_scale * smallCalcY;

        scale = cur_scale;

        map.stage.setPosition(endCalc, endCalcY);
        map.imageLayer.scaleX(cur_scale);
        map.imageLayer.scaleY(cur_scale);

        map.placesLayer.scaleX(cur_scale);
        map.placesLayer.scaleY(cur_scale);

        map.eventsLayer.scaleX(cur_scale);
        map.eventsLayer.scaleY(cur_scale);

        map.imageLayer.draw();
        map.placesLayer.draw();
        map.eventsLayer.draw();
    }

}

$(function () {

    $('#field').bind('mousewheel MozMousePixelScroll', function (event, delta, deltaX, deltaY) {
        event.preventDefault();
        onMouseWheel(event, delta, deltaX, deltaY);
    });

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
