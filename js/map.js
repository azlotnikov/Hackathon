var eventsTypesConsts = {
    'party': 2,
    'service': 1,
    'leisure': 3
};

var eventsCircleOffset = {
    1: {
        x: -23,
        y: 21
    },
    2: {
        x: 0,
        y: -20
    },
    3: {
        x: 23,
        y: 21
    }
}

var eventsColorsConsts = {
    1: 'blue',
    2: 'red',
    3: 'yellow'
};

var min_scale = 0.3;
var max_scale = 1;

var bigCircleRadius = 20;
var littleCircleRadius = 10;

function Map() {
    this.scale = 0.7;
    this.places = {};
    this.events = {};
//    this.activePlace = {};
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
        handleLayers();
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
    var e;
    for (p in this.places) {
//        for (e in eventsTypesConsts) { TODO whats the fuck?
            this.places[p].circles = [];
            this.places[p]['circles'][1] = 0;
            this.places[p]['circles'][2] = 0;
            this.places[p]['circles'][3] = 0;
//        }
    }
};

Map.prototype.drawEventsNumbers = function () {
    var p;
    var e;
    for (p in this.places) {
        for (e in eventsTypesConsts) {
            if (this.places[p]['circles'][eventsTypesConsts[e]] < 2) {
                continue;
            }
            var center = getCenter(polygonFromString(this.places[p].places_polygon));

            center.x += eventsCircleOffset[eventsTypesConsts[e]].x;
            center.y += eventsCircleOffset[eventsTypesConsts[e]].y;

            var x = center.x + parseInt(bigCircleRadius / 2) + 3;
            var y = center.y - parseInt(bigCircleRadius / 2) - 3;

            var circle = new Kinetic.Circle({
                x: x, //!
                y: y,
                radius: littleCircleRadius,
                fill: 'purple',
                opacity: 0.9,
                strokeEnabled: false
            });
//
            this.eventsLayer.add(circle);

            var circleText = new Kinetic.Text({
                x: x - 4,
                y: y - 7,
                text: this.places[p]['circles'][eventsTypesConsts[e]],
                fontSize: 17,
                fontFamily: 'Calibri',
                fill: 'black'
            });
//
            this.eventsLayer.add(circleText);
        }
    }
    this.eventsLayer.draw();
};

Map.prototype.clearEvents = function () {
    this.eventsLayer.removeChildren();
    this.zerosEventsCirclesForPlaces();
};

Map.prototype.renderEvents = function () {
    this.eventsLayer.draw();
    this.drawEventsNumbers();
};

Map.prototype.addEvents = function (eventType) {
    var events = this.events[eventType];
    var e;
    for (e in events) {
        var place_id = events[e].events_place_id;
        if (this.places[place_id]['circles'][eventType] > 0) {
            this.places[place_id]['circles'][eventType]++;
            continue;
        }
//        console.log(this.places[place_id].places_polygon);
//        console.log(polygonFromString(this.places[place_id].places_polygon));
        var center = getCenter(polygonFromString(this.places[place_id].places_polygon));
        center.x += eventsCircleOffset[eventType].x;
        center.y += eventsCircleOffset[eventType].y;
        console.log(center);
        var circle = new Kinetic.Circle({
            x: center.x, //!
            y: center.y,
            radius: bigCircleRadius,
            fill: eventsColorsConsts[eventType],
            opacity: 0.5,
            strokeEnabled: false
        });

        this.places[place_id]['circles'][e]++;

        circle.eventId = events[e].events_id;

        circle.on('mousedown', function () {
            alert(this.eventId);
        });

        this.eventsLayer.add(circle);
    }
};

Map.prototype.initPlaces = function () {
    this.placesLayer.removeChildren();
    var p;
    for (p in this.places) {
//        console.log(this.places[p]);
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
        poly.on('click', function (e) {
            console.log('BTN: ' + JSON.stringify(e));
            var mousePos = map.stage.getPointerPosition();
            $('#event_place_id').val(this.placeId);
            $('#event_form').show('slow').css({left: mousePos.x, top: mousePos.y});
            map.activePlace = this;
        });

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

function handleLayers() {
    map.clearEvents();
    $('#layers input[name="events_layer"]:checked').each(function () {
        var arr = $(this).attr('id').split('_');
        map.addEvents(eventsTypesConsts[arr[2]]);
    });
    map.renderEvents();
}

$(function () {
    map.init();

    $('container').on("contextmenu", function (evt) {
        evt.preventDefault();
    });

    $('#layers input[name="events_layer"]').change(handleLayers);


    $('#event_form_close').click(function () {
        $('#event_form').hide();
    });

    $('#event_form form').submit(function () {
        var event_type = $('#event_type').find('option:selected').val();
        var place_id = $('#event_place_id').val();
        var event_id = addEvent(place_id, $('#event_header').val(), $('#event_description').val(), event_type);
        map.events[event_type].push({
            events_id: event_id,
            events_place_id: parseInt(place_id)
        });
        //TODO render events of this type if not
//        handleLayers();
//        map.renderEvents(event_type);
        return false;
    });

});

var map = new Map();