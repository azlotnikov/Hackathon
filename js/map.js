function Map() {
    this.places = {};
    this.events = {};
}

Map.prototype.init = function () {
    this.getInitInfo();
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
//    this.events[eventsTypeName]
};

$(document).ready(function () {
    var map = new Map();
    map.init();

    $('#view_parties').click(function () {
        map.renderEvents('party');
    });

    $('#view_services').click(function () {
        map.renderEvents('service');
    });

    $('#view_leisure').click(function () {
        map.renderEvents('leisure');
    });

});