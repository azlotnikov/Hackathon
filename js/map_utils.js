function getCurrentDate() {
    var d = new Date();
    var curr_date = d.getDate();
        curr_date = curr_date < 10 ? '0' + curr_date : curr_date;

    var curr_month = d.getMonth() + 1;
        curr_month = curr_month < 10 ? '0' + curr_month : curr_month;

    var curr_year = d.getFullYear();

    var curr_hour = d.getHours();
        curr_hour = curr_hour < 10 ? '0' + curr_hour : curr_hour;

    var curr_minutes = d.getMinutes();
        curr_minutes = curr_minutes < 10 ? '0' + curr_minutes : curr_minutes;

    var curr_seconds = d.getSeconds();
        curr_seconds = curr_seconds < 10 ? '0' + curr_seconds : curr_seconds;

    return curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hour + ':' + curr_minutes + ':' + curr_seconds; 
}