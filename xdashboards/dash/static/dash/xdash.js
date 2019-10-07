var get_search_params = function() {
    var loc = window.location;
    var searchParams = new URLSearchParams(loc.search);
    return searchParams;
}
var set_search_params = function(searchParams) {
    var loc = window.location;
    url = `${loc.protocol}//${loc.hostname}:${loc.port}${loc.pathname}?${searchParams.toString()}`;
    window.location = url;
}
var change_param = function(key, value) {
    var searchParams = get_search_params();
    searchParams.set(key, value);
    set_search_params(searchParams);
}
var remove_param = function(key) {
    var searchParams = get_search_params();
    searchParams.delete(key);
    set_search_params(searchParams);
}
var has_param = function(key) {
    var searchParams = get_search_params();
    return searchParams.has(key);
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
