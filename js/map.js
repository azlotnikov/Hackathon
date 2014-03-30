Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};


var eventsObjects = {
    'service': {
        id: 1,
        offsetX: -23,
        offsetY: 21,
        color: 'blue'
    },
    'party': {
        id: 2,
        offsetX: 0,
        offsetY: -20,
        color: 'red'
    },
    'leisure': {
        id: 3,
        offsetX: 23,
        offsetY: 21,
        color: 'yellow'
    }
};

var min_scale          = 0.3,  //минимальный масштаб карты
    max_scale          = 1,    //максимальный мастштаб карты
    bigCircleRadius    = 20,   //радиус большого кружка события
    littleCircleRadius = 10;   //радиус маленького кружка события

function Map() {               //самый главный объект карта
    this.scale        = 0.7;         //начальный масштаб
    this.places       = {};          //массив всех мест: ключ - id места
    this.events       = {};          //массив всех событий
//  this.activePlace  = {};
    this.cachedEvents = {};
    this.stage        = new Kinetic.Stage({        //канвас
        container: 'container',
        width: $(document).width() - 100,
        height: $(document).height() - 100,
        draggable: true
    });
}

Map.prototype.init = function () {
    this.getInitInfo();                             //получение информации обо всем на карте ajax

    var layer    = new Kinetic.Layer(),
        imageObj = new Image();

    imageObj.onload = function () {

        var imageMap = new Kinetic.Image({
            x: 1,
            y: 1,
            image: imageObj
        });

        map.imageLayer  = new Kinetic.Layer();
        map.placesLayer = new Kinetic.Layer();
        map.eventsLayer = new Kinetic.Layer();

        map.stage.add(map.imageLayer);
        map.stage.add(map.placesLayer);
        map.stage.add(map.eventsLayer);

        map.imageLayer.add(imageMap);
        map.imageLayer.draw();

        layer.draw();
        map.initPlaces();       //нарисовать скрытый слой с местами
        handleEventsLayers();   //определяет какие события из чекбоксов нарисовать

        imageMap.on("click", function() {  //скрыть формочку при клике на пустое место
            $("#event_form").hide();
        });
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

Map.prototype.initPlaces = function () {
    this.placesLayer.removeChildren();
    var p;
    for (p in this.places) {
        var poly = new Kinetic.Line({
            points: this.places[p].places_polygon.split(','),
            strokeWidth: 6,
            opacity: 0.8,
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
        poly.on('click', function (e) {
            var mousePos = map.stage.getPointerPosition();
            $('#event_place_id').val(this.placeId);
            $('#event_form').show('fast').css({left: mousePos.x + $("#container").position().left, top: mousePos.y + $("#container").position().top});
            //map.activePlace = this;
        });

        this.placesLayer.add(poly);
    }
    this.placesLayer.draw();
};

Map.prototype.drawEventsNumbers = function () {
    var p;
    var e;
    for (p in this.places) {
        for (e in eventsObjects) {
            if (this.places[p].circles.length == 0) {
                continue;
            }
            // alert(this.places[p])
            if (this.places[p].circles[eventsObjects[e].id] < 2) {
                continue;
            }
            var center = getCenter(polygonFromString(this.places[p].places_polygon));

            center.x += eventsObjects[e].offsetX;
            center.y += eventsObjects[e].offsetY;

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
                text: this.places[p].circles[eventsObjects[e].id],
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


function handleEventsLayers() {
    map.clearEvents(); //удаление всего связанного с событиями
    $('#layers input[name="events_layer"]:checked').each(function () {
        map.addEvents($(this).attr('id').split('_')[2]); //например event_view_service --  
    });
    map.renderEvents();
}

Map.prototype.clearEvents = function () {
    this.eventsLayer.removeChildren();
    this.zerosEventsCirclesForPlaces();  //создает и обнуляет счетчики кружочков событий
};

Map.prototype.zerosEventsCirclesForPlaces = function () {
    var p;
    var e;
    for (p in this.places) {
        this.places[p].circles = [];
        for (e in eventsObjects) {
            this.places[p].circles[eventsObjects[e].id] = 0;
        }
    }
};

Map.prototype.renderEvents = function () {
    this.drawEventsNumbers();
    this.eventsLayer.draw();
};

Map.prototype.addEvents = function (eventType) {   //строка типа party
    eventTypeId = eventsObjects[eventType].id;
    var events = this.events[eventTypeId];
    var e;
    for (e in events) {
        var placeId = events[e].events_place_id;
        if (this.places[placeId].circles[eventTypeId] > 0) {
            this.places[placeId].circles[eventTypeId]++;
            continue;
        }
        this.places[placeId].circles[eventTypeId]++;
        var center = getCenter(polygonFromString(this.places[placeId].places_polygon));
        center.x += eventsObjects[eventType].offsetX;
        center.y += eventsObjects[eventType].offsetY;
        var circle = new Kinetic.Circle({
            x: center.x, //!
            y: center.y,
            radius: bigCircleRadius,
            fill: eventsObjects[eventType].color,
            opacity: 0.6,
            strokeEnabled: false
        });

        circle.eventId = e;
        circle.eventType = eventTypeId;
        circle.placeId = placeId;

        circle.on('mousedown', function () {
            if (this.eventId in map.cachedEvents) {
                alert(JSON.stringify(map.cachedEvents[this.eventId]));
            } else {
                var p;
                var events = [];
                for (p in map.events[this.eventType]) {
                    if (map.events[this.eventType][p].events_place_id == this.placeId) {
                        events.push(map.events[this.eventType][p].events_id);
                    }
                }
//                var $this = this;
                $.ajax({
                    type: 'POST',
                    url: '/scripts/handlers/handler.Map.php',
                    data: {
                        action: "getEventInfo",
                        data: JSON.stringify(events)
                    },
                    success: function (data) {
                        if (data.hasOwnProperty('result')) {
                            if (data.result) {
                                //$this
                                map.cachedEvents = map.cachedEvents.concat(data.data).unique();
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
            }
        });

        this.eventsLayer.add(circle);
    }
};

/*
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
*/


$(function () {
    map.init();

    $('#layers input[name="events_layer"]').change(handleEventsLayers);


    $('#event_form_close').click(function () {
        $('#event_form').hide();
    });

    $('#event_form form').submit(function () {
        var event_type = $('#event_type').find('option:selected').val(),
            place_id   = $('#event_place_id').val(),
            event_id   = addEvent(place_id, $('#event_header').val(), $('#event_description').val(), event_type);
        map.events[event_type][event_id] = {
            events_id: event_id,
            events_place_id: parseInt(place_id)
        };
        //TODO render events of this type if not
       handleEventsLayers();
        return false;
    });

});

var map = new Map();