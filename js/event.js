function addEvent(place_id, header, description, type) {
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "addEvent",
            header: header,
            description: description,
            type: type,
            place_id: place_id
//            due_date: due_date
        },
        success: function (data) {
            if (data.hasOwnProperty('result')) {
                if (data.result) {

                } else {
                    alert(data.message);
                }
            } else {
                alert('Unknown error!');
            }
        },
        contentType: 'application/json'
    });
}