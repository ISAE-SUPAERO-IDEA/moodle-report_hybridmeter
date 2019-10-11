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
var get_param = function(key) {
    var searchParams = get_search_params();
    return searchParams.has(key) ? searchParams.get(key) : undefined;   
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

function color_scale(r1,g1,b1,r2,g2,b2,s) {
    r = Math.floor(r1 + (r2 - r1) * s);
    g = Math.floor(g1 + (g2 - g1) * s);
    b = Math.floor(b1 + (b2 - b1) * s);
    return `rgb(${r},${g},${b})`;
    
}

entry_point_objects = [
    "https://online.isae-supaero.fr",
    "https://online.isae-supaero.fr/xapi/activities/course/cbe5d5cd-59d6-4877-a147-85f66f017589"

]

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
    if (conf.type === "course") { 
        node.shape= 'dot';
        node.size = 15;
    }
    if (conf.type_link === "http://vocab.xapi.fr/activities/system" || entry_point_objects.indexOf(conf.id)>=0) {
        node.size = 5;
        node.label="";
        node.shape= 'diamond';
        node.color.border = "#000";
    }
    if (conf.verb === "https://w3id.org/xapi/adl/verbs/logged-in") {
        node.color.border = "#999";
    }
    if (conf.verb === "https://w3id.org/xapi/adl/verbs/logged-out") {
        node.color.border = "#000";
    }
    if (typeof conf.score === "number") { 
        if (conf.system==="https://adn.isae-supaero.fr") {
            node.color.background = color_scale(255, 0, 0, 0, 255, 0, conf.score_scaled);
            //color.border = color_scale(255, 165, 0, trace.score_scaled);
        }
        node.shape= 'star';
        node.title = `${conf.score}/${conf.score_max}`;
    }
    return node;
}