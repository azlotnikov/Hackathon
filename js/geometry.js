function calcPos(a, b, p) {
    return (b.x - a.x) * (p.y - a.y) - (b.y - a.y) * (p.x - a.x);
}

function abs(x) {
    return x >= 0 ? x : -x;
}

function isPointInPolygon(_point, _polygon) {
    var vectors = [];
    var p = {x: _point[0], y: _point[1]};
    var pos = 1;
    for (var i = 2; i < _polygon.length; i += 2){
        var a = {x: _polygon[i - 2], y: _polygon[i - 1]};
        var b = {x: _polygon[i], y: _polygon[i + 1]};
        var curPos = calcPos(a, b, p) ;
        if (!curPos){
            return 0;
        }
        curPos /= abs(curPos);
        if (i == 2){
            pos = curPos;
        }
        else if (curPos != pos){
            return -1;
        }
    }
    var a = {x: _polygon[_polygon.length - 2], y: _polygon[_polygon.length - 1]};
    var b = {x: _polygon[0], y: _polygon[1]};
    var curPos = calcPos(a, b, p);
    if (!curPos){
        return 0;
    }
    else{
        curPos /= abs(curPos);
    }
    return pos == curPos ? 1 : -1;
}

function isPolygonInPolygon(_polygon_in, _polygon_out) {
    for (var i = 1; i < _polygon_in.length; i += 2){
        var p = [_polygon_in[i - 1], _polygon_in[i]];
        if (isPointInPolygon(p, _polygon_out) < 0){
            return false;
        }
    }
    return true;
}

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

function pow(_x){
   return _x * _x;
}

function getDist(_a, _b){
   return Math.sqrt(pow(_b.x - _a.x) + pow(_b.y - _a.y));
}

function getLineParams(_p1, _p2){
   return {
      A: _p1.y - _p2.y,
      B: _p2.x - _p1.x,
      C: _p1.x * _p2.y - _p2.x * _p1.y
   };
}

function solveQuadroEq(_a, _b, _c){
   //console.log("EQU");
   //console.log(_a + " " + _b + " " + _c);
   var D = pow(_b) - 4 * _a * _c;
   return [
      (-1 * _b + Math.sqrt(D)) / (2 * _a), 
      (-1 * _b - Math.sqrt(D)) / (2 * _a)
   ];
}

function slidePointOnLineInPolygon(_lineParams, _point, _dist, _polygon){
   var p = _point;
   if (_lineParams.A == 0){
      p = _point;
      p.x -= _dist;
      //console.log("A == 0");
      //console.log(JSON.stringify(p));
      return p;
   }
   else if (_lineParams.B == 0){
      p = _point;
      p.y -= _dist;
      //console.log("B == 0");
      //console.log(JSON.stringify(p));
      return p;   
   }
   else{
      with (_lineParams){
         //console.log("lineParams");
         //console.log(JSON.stringify(_lineParams));
         //console.log("delta");
         //console.log(_dist);
         //console.log("point");
         //console.log(JSON.stringify(_point));
         var solutions = solveQuadroEq(
            1 + pow(B / A),
            2 * _point.x * B / A - 2 * _point.y + 2 * B * C / pow(A),
            pow(_point.x) + 2 * C * _point.x / A + pow(C / A) + pow(_point.y) - pow(_dist)
         );
         //console.log("SOLUTIONS");
         //console.log(JSON.stringify(solutions));
         p.y = solutions[0];
         p.x = -1 * (C + B * p.y) / A;
         if (isPointInPolygon(p, _polygon) >= 0){
            //console.log("SOL[0]");
            //console.log(JSON.stringify(p));
            return p;
         }
         else{
            p.y = solutions[1];
            p.x = -1 * (C + B * p.y) / A;
            //console.log("SOL[1]");
            //console.log(JSON.stringify(p));
            return p;
         }
      }
   }
}

function slidePolygonOnLineInPolygon(_lineParams, _polygonA, _polygon, _delta){
   var res = [];
   for (var i = 0; i < _polygonA.length; i++){
      res.push(slidePointOnLineInPolygon(_lineParams, _polygonA[i], _delta, _polygon));
   }
   return res;
}

function getRectCoords(_xDim, _yDim, _polygon){
   var maxL = Math.max(_xDim, _yDim);
   var minL = Math.min(_xDim, _yDim);
   var minX = 100000;
   var minY = 100000;
   var pf = [];
   for (var i = 1; i < _polygon.length; i += 2){
      pf.push(
         {
            x: _polygon[i - 1],
            y: _polygon[i]
         }
      );
      minX = Math.min(minX, _polygon[i - 1]);
      minY = Math.min(minY, _polygon[i]);
   }
   for (var i = 0; i < pf.length; i++){
      pf[i].x -= minX;
      pf[i].y -= minY;
   }

   //console.log(JSON.stringify(pf));
   //console.log(pf.length);
   switch (pf.length){
      case 4:
         var line01 = getLineParams(pf[0], pf[1]);
         //console.log("line01");
         //console.log(JSON.stringify(line01));
         var line12 = getLineParams(pf[1], pf[2]);
         // var line23 = getLineParams(p[2], p[3]);
         // var line30 = getLineParams(p[3], p[0]);

         var dist01 = getDist(pf[0], pf[1]);
         var dist12 = getDist(pf[1], pf[2]);
         // var res = p;

         if (dist01 > dist12){
            //console.log("getRectCoords, if1");
            //console.log(JSON.stringify(pf));
            if (maxL < dist01){
               pf = slidePolygonOnLineInPolygon(line01, pf, _polygon, (dist01 - maxL) / 2);
            }
            // if (minL < dist12){
            //    pf = slidePolygonOnLineInPolygon(line12, pf, _polygon, (dist12 - minL) / 2);
            // }
            //console.log("after");
            //console.log(JSON.stringify(pf));
         }
         else{
            //console.log("getRectCoords, if2");
            if (minL < dist01){
               pf = slidePolygonOnLineInPolygon(line01, pf, _polygon, (dist01 - minL) / 2);
            }
            if (maxL < dist12){
               pf = slidePolygonOnLineInPolygon(line12, pf, _polygon, (dist12 - maxL) / 2);
            }
         }
         //console.log(JSON.stringify(pf));
         for (var i = 0; i < pf.length; i++){
            pf[i].x += minX;
            pf[i].y += minY;
   }
         return pf;
   }
}

function getCenters(_cnt, _dia, delta, _polygon) { //_dia = [_dia1, _dia2] delta = [dx, dy]
   var rect = getRectCoords(_dia[0] + _dia[1] - delta[0], 
                           (_dia[0] + _dia[1] - delta[1]) * _cnt,
                           _polygon
   );
   var delta = getDist(rect[1], rect[0]);
   // //console.log("input");
   // //console.log(_polygon);
   //console.log("rect");
   //console.log(JSON.stringify(rect));
   var result = [];
   for (var i = 0; i < _cnt; i++){
      result.push(
         getCenter([
            rect[0].x, rect[0].y + i * delta,
            rect[0].x, rect[0].y + (i + 1) * delta,
            rect[1].x, rect[1].y + i * delta
            // rect[1].x, rect[1].y + (i + 1) * delta
            ])
      );
   }
   // //console.log(JSON.stringify(result));
   //console.log("");
   return result;
}



function test() {
   alert("qwe");
   var t_poly_str = '636,2032,636,1907,1010,1908,1008,2027';
   // point = [0, 0];
   var res = getCenters(2, [20, 10], [5, 5], polygonFromString(t_poly_str)); 
   for (var i = 0; i < res.length; i++){
      alert (res[i].x + " " + res[i].y);
   }
}