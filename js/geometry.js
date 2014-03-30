function calcPos(a, b, p) {
    return (b.x - a.x) * (p.y - a.y) - (b.y - a.y) * (p.x - a.x);
}

function abs(x) {
    return x >= 0 ? x : -x;
}

// function isPointInPolygon(_point, _polygon) {
//     var vectors = [];
//     var p = {x: _point[0], y: _point[1]};
//     var pos = 1;
//     for (var i = 2; i < _polygon.length; i += 2){
//         var a = {x: _polygon[i - 2], y: _polygon[i - 1]};
//         var b = {x: _polygon[i], y: _polygon[i + 1]};
//         var curPos = calcPos(a, b, p) ;
//         if (!curPos){
//             return 0;
//         }
//         curPos /= abs(curPos);
//         if (i == 2){
//             pos = curPos;
//         }
//         else if (curPos != pos){
//             return -1;
//         }
//     }
//     var a = {x: _polygon[_polygon.length - 2], y: _polygon[_polygon.length - 1]};
//     var b = {x: _polygon[0], y: _polygon[1]};
//     var curPos = calcPos(a, b, p);
//     if (!curPos){
//         return 0;
//     }
//     else{
//         curPos /= abs(curPos);
//     }
//     return pos == curPos ? 1 : -1;
// }

// function isPolygonInPolygon(_polygon_in, _polygon_out) {
//     for (var i = 1; i < _polygon_in.length; i += 2){
//         var p = [_polygon_in[i - 1], _polygon_in[i]];
//         if (isPointInPolygon(p, _polygon_out) < 0){
//             return false;
//         }
//     }
//     return true;
// }

function convert(_item, _i, _arr) {
   _arr[_i] = parseInt(_item);
}

function polygonFromString(_poly_str) {
   var _arr = _poly_str.split(',');
   _arr.forEach(convert);
   return _arr;
}

function getCenter(_polygon) {
   var c = {x: 0, y: 0};
   var d = 0;
   var xMin = 100000, yMin = 100000;
   for (var i = 0; i < _polygon.length - 2; i += 2){
      xMin = Math.min(_polygon[i], xMin);
      yMin = Math.min(_polygon[i + 1], yMin);
   }

   for (var i = 0; i < _polygon.length - 2; i += 2){
      var a = {x: _polygon[i] - xMin, y: _polygon[i + 1] - yMin};
      var b = {x: _polygon[i + 2] - xMin, y: _polygon[i + 3] - yMin};
      var k = a.x * b.y - a.y * b.x;
      c.x += (a.x + b.x) * k;
      c.y += (a.y + b.y) * k;
      d += k;
   }
   d /= 2; 
   return {
      x: c.x / (6 * d) + xMin, 
      y: c.y / (6 * d) + yMin
   };
}

// function pow(_x){
//    return _x * _x;
// }

// function getDist(_a, _b){
//    return Math.sqrt(pow(_b.x - _a.x) + pow(_b.y - _a.y)));
// }

// function getRectCoords(_xDim, _yDim, _polygon){
//    var p = [];
//    for (var i = 1; i < _polygon.length; i += 2){
//       p.push(
//          {
//             x: _polygon[i - 1],
//             y: _polygon[i];
//          }
//       );
//    }
//    switch (p.length){
//       case 4:
//          var center = getCenter(_polygon);


//    }
// }

// function getCenters(_cnt, _dia, delta, _polygon) { //_dia = [_dia1, _dia2] delta = [dx, dy]
//    var rect = getRectCoords(_dia[0] + _dia[1] - delta[0], 
//                            (_dia[0] + _dia[1] - delta[1]) * _cnt,
//                            _polygon
//    );
//    var delta = getDist(rect[1], rect[0]);
//    var result = [];
//    for (var i = 0; i < _cnt; i++){
//       result.concat(
//          getCenter(
//             rect[0].x, rect[0].y + i * delta,
//             rect[0].x, rect[0].y + (i + 1) * delta,
//             rect[1].x, rect[1].y + i * delta;
//             rect[1].x, rect[1].y + (i + 1) * delta)
//       );
//    }
// }



function test() {
   // 636,2032,636,1907,1010,1908,1008,2027
   var t_poly_str = '636,2032,636,1907,1010,1908,1008,2027';
   // point = [0, 0];
   var res = getCenter(polygonFromString(t_poly_str)); 
   alert(res.x + " " + res.y);
}