function addEvent(header, description, type) {
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "addEvent",
            header: header,
            description: description,
            type: type
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