var Player = function(id, name, element){
    this.id = id;
    this.name = name;
    this.element = element;
};

var Team = function(container, inputname){
    this.players = [];
    this.container = container;
    this.inputname = inputname;
};

var search_items = [];
var hometeam = new Team(document.getElementById("hometeam-players"), "hometeam_player");
var awayteam = new Team(document.getElementById("awayteam-players"), "awayteam_player");

function create_search_item(id, name){
    var item_template = '\
    <a class="panel-block player-item" data-id={id} data-name={name}>\
        <span class="panel-icon">\
            <i class="fas fa-user" aria-hidden="true"></i>\
        </span>\
        {name}\
    </a>';
    var d = document.createElement("div");
    d.innerHTML = item_template
        .replace("{id}", id)
        .replace("{name}", name)
        .replace("{name}", name);

    return d.children[0];
}

function ajax(url, method, callback, error) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            callback(this.responseText);
        } else {
            error(this.readyState, this.status);
        }
    };
    xhttp.open(method, url, true);
    xhttp.send();
}

function show_search_results(players){
    var search_results = document.getElementById("search-results");
    search_results.innerHTML = "";
    search_items = [];
    for(var i = 0; i < players.length; i++){
        var item = create_search_item(players[i].id, players[i].name);
        search_results.append(item);
        search_items.push(new Player(players[i].id, players[i].name, item));
    }
}

function search_players(){
    var query = document.getElementById("search-input").value;
    ajax('?api=1&action=search&query=' + query, 'GET', function(result) {
        result = JSON.parse(result);
        if( result["success"] ){
            show_search_results(result["result"]);
        } else {
            alert("C'è stato un errore nella ricerca(" + result["message"] + ")");
        }
    }, function(readyState, status){});
}

function delete_item(element){
    element
    .parentNode
    .parentNode
    .removeChild(
        element.parentNode
    );
}

function create_team_item(id, name, inputname){
    var template = '\
    <div class="panel-block" data-id={id} data-name={name}>\
        <span class="panel-icon">\
            <i class="fas fa-user" aria-hidden="true"></i>\
        </span>\
        {name}\
        <input type="hidden" name="{inputname}[]" value={id} />\
        <button type="button" class="delete is-small" style="background-color: red;" onclick="delete_item(this);" style="color: red"></button>\
    </div>';
    var d = document.createElement("div");
    d.innerHTML = template
        .replace("{id}", id)
        .replace("{name}", name)
        .replace("{name}", name)
        .replace("{inputname}", inputname)
        .replace("{id}", id);
    
    return d.children[0];
}

function add_player(id, name, team, otherteam){
    if( !team.players.find((x) => x.id == id) && !otherteam.players.find((x) => x.id == id) ) {
        var player_element = create_team_item(id, name, team.inputname);
        team.players.push(new Player(id, name, player_element));
        team.container.append(player_element);
    } else {
        alert("You already added that player to a team!");
    }
}

// See: JS Event Delegation for why
document.addEventListener("click", function(event){
    if( event.target.classList.contains("player-item") ){
        var player_items = document.getElementsByClassName("player-item");
        for(var i = 0; i < player_items.length; i++){
            player_items[i].classList.remove("is-active");
        }
        event.target.classList.add("is-active");
    }
});

document.getElementById("button-addhometeam").addEventListener("click", function(event){
    for(var i = 0; i < search_items.length; i++){
        if( search_items[i].element.classList.contains("is-active") ){
            add_player(search_items[i].id, search_items[i].name, hometeam, awayteam);
        }
    }
});

document.getElementById("button-addawayteam").addEventListener("click", function(event){
    for(var i = 0; i < search_items.length; i++){
        if( search_items[i].element.classList.contains("is-active") ){
            add_player(search_items[i].id, search_items[i].name, awayteam, hometeam);
        }
    }
});

window.onload = function(event){
    var match_id = document.getElementById("match_id").value;
    console.log("ciao");
    ajax("?api=1&action=status&match=" + match_id, "GET", function(result){
        var result = JSON.parse(result);
        console.log(result);
        if( result.success ){
            var hometeam_players = result.result.hometeam;
            var awayteam_players = result.result.awayteam;
            for(var i = 0; i < hometeam_players.length; i++){
                add_player(
                    hometeam_players[i].id, 
                    hometeam_players[i].name, 
                    hometeam, 
                    awayteam
                );
            }
            for(var i = 0; i < awayteam_players.length; i++){
                add_player(
                    awayteam_players[i].id, 
                    awayteam_players[i].name, 
                    awayteam, 
                    hometeam
                );
            }
        } else {
            alert("Error: could not load the current players list. You should reload or you might lose data");
            console.error(result);
        }
    }, function(readyState, status){});
};