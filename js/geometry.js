function calcPos(a, b, p){
    return (b.x - a.x) * (p.y - a.y) - (b.y - a.y) * (p.x - a.x);
}

function abs(x){
    return x >= 0 ? x : -x;
}

function isInPolygon(_point, _polygon) {
    var vectors = new Array();
    var p = {x: _point[0], y: _point[1]};
    var pos = 1;
    for (var i = 2; i < _polygon.length; i += 2){
        var a = {x: _polygon[i - 2], y: _polygon[i - 1]};
        var b = {x: _polygon[i], y: _polygon[i + 1]};
        var curPos = calcPos(a, b, p) ;
        if (!curPos){
            return "on";
        }
        curPos /= abs(curPos);
        if (i == 2){
            pos = curPos;
        }
        else if (curPos != pos){
            return "out";
        }
    }
    var a = {x: _polygon[_polygon.length - 2], y: _polygon[_polygon.length - 1]};
    var b = {x: _polygon[0], y: _polygon[1]};
    var curPos = calcPos(a, b, p);
    if (!curPos){
        return "on";
    }
    else{
        curPos /= abs(curPos);
    }
    return pos == curPos ? "in" : "out";
}

function getCenter(_polygon){
    var c = {x: 0, y: 0};
    var d = 0;
    for (var i = 0; i < _polygon.length - 2; i += 2){
        var a = {x: _polygon[i], y: _polygon[i + 1]};
        var b = {x: _polygon[i + 2], y: _polygon[i + 3]};
        var k = a.x * b.y - a.y * b.x;
        c.x += (a.x + b.x) * k;
        c.y += (a.y + b.y) * k;
        d += k;
    }
    d /= 2; 
    return [c.x / (6 * d), c.y / (6 * d)];
}



function test() {
    polygon = [ 1,  1, 
                3.5,  1, 
                2, 3
              ];
    point = [0, 0];
    alert(getCenter(polygon));
}