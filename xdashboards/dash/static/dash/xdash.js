var change_param = function(key, value) {
    var loc = window.location;
    var searchParams = new URLSearchParams(loc.search);
    searchParams.set(key, value);
    url = `${loc.protocol}//${loc.hostname}:${loc.port}${loc.pathname}?${searchParams.toString()}`;
    window.location = url;
}
var switch_class = function(elem, class1, class2, current_state) {
    if (current_state==false) {
        $(elem).removeClass(class2);$(elem).addClass(class1);
    }
    else {
        $(elem).addClass(class2);$(elem).removeClass(class1);   
    }
    return !current_state;
    
}
var hour_to_range = function(hour) {
    return hour<=21 ? `${hour}h-${hour+3}h` : "";    
}
