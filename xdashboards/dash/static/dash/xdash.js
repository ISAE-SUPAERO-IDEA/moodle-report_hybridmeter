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

function color_scale(r,g,b,s) {
    r = Math.floor(r * s);
    g = Math.floor(g * s);
    b = Math.floor(b * s);
    return `rgb(${r},${g},${b})`;
    
}

var get_node = function(conf) {
    node= {
        color: {},
        size:20,
        shape:'hexagon',
        id: conf.id,
        label: conf.label,
        title: conf.title
    }
    if (conf.system==="https://adn.isae-supaero.fr") {
            node.color.background = '#FFFF00';
            node.color.border= '#FFA500';
        }
    if (conf.verb === "s'est connecté(e)") {
        node.size = 5;
        node.label="";
        node.color.background = "#000000";
        node.color.border = "#000000";
    }
    if (conf.type === "course") { 
        node.shape= 'dot';
        node.size = 15;
    }
    if (typeof conf.score === "number") { 
        if (conf.system==="https://adn.isae-supaero.fr") {
            node.color.background = color_scale(255, 255, 0, conf.score_scaled);
            //color.border = color_scale(255, 165, 0, trace.score_scaled);
        }
        node.shape= 'star';
        node.title = `${conf.score}/${conf.score_max}`;
    }
    return node;
}