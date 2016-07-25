<?php
//Author:Bo Wang

error_reporting(E_ALL ^ E_NOTICE);
$dir = 'sqlite:brisbane.db';

// Instantiate PDO connection object and failure msg //
$dbh = new PDO($dir) or die("cannot open database");

$methodName = $_GET["method"];;

if ($methodName=="getRoutesByType"){
        $routeType = $_GET["routeType"];
        $sql = getRoutesByType($routeType);        
}else if ($methodName=="getStopsByRouteType"){
         $routeType = $_GET["routeType"];
        $sql=getStopsByRouteType($routeType);
}else if ($methodName=="getStopsByStopFrom"){
        $routeType = $_GET["routeType"];
        if (!$routeType){
	   $routeType = "";
	}
        $stopFromId = $_GET["stopFromId"];
        $todayStr = $_GET["todayStr"];
        $sql = getStopsByStopFrom($stopFromId,$routeType,$todayStr);
}else if ($methodName=="getStopsByRouteTypeAndStopName"){
        $routeType = $_GET["routeType"];
        $stopNameLike = $_GET["stopNameLike"];
        $sql = getStopsByRouteTypeAndStopName($routeType,$stopNameLike);
}else if ($methodName=="getStopsByRouteAndFrom"){
        $routeType = $_GET["routeType"];
        $routeId = $_GET["routeId"];
        $stopId = $_GET["stopId"];
        $todayStr = $_GET["todayStr"];
        $sql = getStopsByRouteAndFrom($routeType,$stopId,$routeId,$todayStr);
}else if ($methodName=="getStopByStopId"){
        $stopId = $_GET["stopId"];
        $sql = getStopByStopId($stopId);
}else if ($methodName=="getStopsByRoute"){
        $routeId = $_GET["routeId"];
        $routeType = $_GET["routeType"];
        $sql = getStopsByRoute($routeId,$routeType);
}else if ($methodName=="getStopsByRouteAndStopFrom"){
        $routeId = $_GET["routeId"];
        $directionId = $_GET["directionId"];
        $stopSequence = $_GET["stopSequence"];
        $sql = getStopsByRouteAndStopFrom($routeId,$directionId,$stopSequence);
}else if ($methodName=="getTimeTable"){
        $routeType = $_GET["routeType"];
        $fromStopId = $_GET["fromStopId"];
        $toStopId = $_GET["toStopId"];
        $todayStr = $_GET["todayStr"];
        $day = $_GET["day"];
        $sql = getTimeTable($routeType,$day,$fromStopId,$toStopId,$todayStr);
}else if ($methodName=="getOneRoutes"){
         $fromStopId = $_GET["fromStopId"];
        $toStopId = $_GET["toStopId"];
        $sql = getOneRoutes($fromStopId,$toStopId);
}else if ($methodName == "getStopsByLatLot"){
        $nwLat = $_GET["nwLat"];
        $nwLon = $_GET["nwLon"];
        $seLat = $_GET["seLat"];
        $seLon = $_GET["seLon"];
        $sql = getStopsByLatLot($nwLat,$nwLon,$seLat,$seLon);
}else if ($methodName=="getTripDetails"){
        $tripId = $_GET["tripId"];
        $sql = getTripDetails($tripId);
}else if ($methodName=="getStopsByName"){
        $stopName = $_GET["stopName"];
        $sql = getStopsByName($stopName);
}else if ($methodName=="getTimeTableByStopId"){
        $stopId = $_GET["stopId"];
        $theTime = $_GET["theTime"];
        $todayStr = $_GET["todayStr"];
        $day = $_GET["day"];
        $sql = getTimeTableByStopId($stopId,$day,$theTime,$todayStr);
}
//print($sql);
foreach ($dbh->query($sql) as $row) {
                $flag[] = $row;
}
        

print(json_encode($flag));



function getRoutesByType($routeType){
        $sql = "select distinct route_id,route_short_name,route_long_name,route_desc from routes where route_type = '$routeType'";
        return $sql;        
}

function getStopsByRouteType($routeType){
        $sql = "";
        if ($routeType=='2'){
                $sql = "select distinct s.* from  stops s, route_stop r where r.route_type='2' and s.stop_id=r.parent_station order by s.stop_name";        
        }else{
                $sql = "SELECT  distinct * FROM route_stop WHERE route_type ='$routeType' order by stop_name";
        }
        return $sql;
}

function getStopsByStopFrom($stopFromId,$routeType,$todayStr){
        $sql = "";
        if ($routeType=='2'){
                $sql = "select * from stops where stop_id in (SELECT distinct end_s.parent_station FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id   INNER JOIN stop_times end_st ON t.trip_id = end_st.trip_id   INNER JOIN stops end_s ON end_st.stop_id = end_s.stop_id  WHERE  start_s.parent_station='$stopFromId'  and end_st.arrival_time > start_st.arrival_time and CAST(c.end_date as integer)>=$todayStr and CAST(c.start_date as integer)<=$todayStr)";
        }else{
                $sql = "select * from stops where stop_id in (SELECT distinct end_s.stop_id FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id   INNER JOIN stop_times end_st ON t.trip_id = end_st.trip_id   INNER JOIN stops end_s ON end_st.stop_id = end_s.stop_id  WHERE  start_s.stop_id='$stopFromId'  and end_st.arrival_time > start_st.arrival_time and CAST(c.end_date as integer)>=$todayStr and CAST(c.start_date as integer)<=$todayStr)";
        }
        return $sql;
}

function getStopsByRouteTypeAndStopName($routeType,$stopNameLike){
        $sql = "";
        if ($stopNameLike!=""){
             $sql =   "SELECT  distinct * FROM route_stop WHERE route_type ='$routeType' and lower(stop_name) like '%$stopNameLike%' order by stop_sequence";
        }
        return $sql;
}

function getStopsByRouteAndFrom($routeType,$stopId,$routeId,$todayStr){
        $sql = "";
        if ($routeType=='2'){
                //$sql = "select * from stops where stop_id in (SELECT distinct end_s.parent_station FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id   INNER JOIN stop_times end_st ON t.trip_id = end_st.trip_id   INNER JOIN stops end_s ON end_st.stop_id = end_s.stop_id  WHERE  start_s.parent_station='$stopId' and r.route_id='$routeId' and end_st.arrival_time > start_st.arrival_time and CAST(c.end_date as integer)>=$todayStr and CAST(c.start_date as integer)<=$todayStr)";
                $sql = "select * from stops where stop_id in (SELECT distinct end_s.parent_station FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id   INNER JOIN stop_times end_st ON t.trip_id = end_st.trip_id   INNER JOIN stops end_s ON end_st.stop_id = end_s.stop_id  WHERE  start_s.parent_station='$stopId' and r.route_id='$routeId' )";
        }else{
                $sql = "select * from stops where stop_id in (SELECT distinct end_s.stop_id FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id   INNER JOIN stop_times end_st ON t.trip_id = end_st.trip_id   INNER JOIN stops end_s ON end_st.stop_id = end_s.stop_id  WHERE  start_s.stop_id='$stopId' and r.route_id='$routeId' )";
        }
	//print($sql);
        return $sql;
}

function getStopByStopId($stopId){
        $sql = "select * from stops where stop_id = '$stopId'";
        return $sql;
}

function getStopsByRoute($routId,$routeType){
        $sql = "";
        if ($routeType=='2'){
                $sql = " select distinct * from stops where stop_id in (SELECT DISTINCT stops.parent_station FROM trips INNER JOIN stop_times ON stop_times.trip_id = trips.trip_id  INNER JOIN stops ON stops.stop_id = stop_times.stop_id  WHERE route_id = '$routId')";        
        }else{
                $sql = " SELECT DISTINCT stops.stop_id, stops.stop_name FROM trips INNER JOIN stop_times ON stop_times.trip_id = trips.trip_id  INNER JOIN stops ON stops.stop_id = stop_times.stop_id  WHERE route_id = '$routId'";
        }
        return $sql;
}

function getStopsByRouteAndStopFrom($routeId,$directionId,$stopSequence){
        $sql = "SELECT  distinct stop_id, stop_name,  direction_id,stop_sequence FROM route_stop WHERE route_id ='$routeId' and direction_id='$directionId' and CAST(stop_sequence as integer) > $stopSequence order by CAST(stop_sequence as integer) ";
        return $sql;
}

function getTimeTable($routeType,$day,$fromStopId,$toStopId,$todayStr){
        $sql = "";
        if ($routeType=='2'){
                $sql = "SELECT distinct t.trip_id,t.trip_headsign, r.route_short_name as route_name, start_s.stop_name as departure_stop, start_st.departure_time as departure_time, end_s.stop_name as arrival_stop,end_st.arrival_time as arrival_time 	FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id   INNER JOIN stop_times end_st ON t.trip_id = end_st.trip_id   INNER JOIN stops end_s ON end_st.stop_id = end_s.stop_id  WHERE c.$day= '1'  AND start_s.parent_station='$fromStopId'  AND end_s.parent_station='$toStopId'  and CAST(c.end_date as integer)>=$todayStr and CAST(c.start_date as integer)<=$todayStr";
        }else{
                $sql = "SELECT distinct t.trip_id,t.trip_headsign, r.route_short_name as route_name, start_s.stop_name as departure_stop, start_st.departure_time as departure_time, end_s.stop_name as arrival_stop,end_st.arrival_time as arrival_time FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id   INNER JOIN stop_times end_st ON t.trip_id = end_st.trip_id   INNER JOIN stops end_s ON end_st.stop_id = end_s.stop_id  WHERE c.$day= '1'  AND start_s.stop_id='$fromStopId'  AND end_s.stop_id='$toStopId'  and CAST(c.end_date as integer)>=$todayStr and CAST(c.start_date as integer)<=$todayStr";
        }
        return $sql;
}

function getOneRoutes($fromStopId,$toStopId){
        $sql = "select * from routes where route_id in (select distinct (r.route_id) from route_stop r,route_stop b where r.stop_id = '$fromStopId' and b.stop_id = '$toStopId' and r.route_id = b.route_id)";
        return $sql;
}

function getStopsByLatLot($nwLat,$nwLon,$seLat,$seLon){
        $sql = "SELECT * from stops where CAST(stop_lat as double) >= $nwLat and CAST(stop_lat as double)<= $seLat and CAST(stop_lon as double) >= $nwLon and CAST(stop_lon as double) <=  $seLon";
        return $sql;
}
function getTripDetails($tripId){
        $sql = "SELECT s.stop_id as stop_id, s.stop_lat as stop_lat, s.stop_lon as stop_lon,stop_name, arrival_time, stop_sequence FROM stop_times st JOIN stops s ON s.stop_id=st.stop_id WHERE trip_id='$tripId' order by CAST(stop_sequence as integer)";
        return $sql;
}

function getStopsByName($stopName){
        $sql = "SELECT  distinct * FROM route_stop WHERE lower(stop_name) like '%$stopName%' order by stop_sequence";
        return $sql;
}

function getTimeTableByStopId($stopId,$day,$theTime,$todayStr){
        $sql="SELECT distinct t.trip_id,t.trip_headsign, r.route_short_name as route_name, start_s.stop_name as departure_stop, start_st.departure_time as departure_time, t.trip_headsign as trip_short_name FROM trips t INNER JOIN calendar c ON t.service_id = c.service_id   INNER JOIN routes r ON t.route_id = r.route_id  INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id   INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id and c.$day = '1'  AND start_s.stop_id='$stopId' and CAST(start_st.departure_time as integer)>=$theTime and CAST(c.end_date as integer)>=$todayStr and CAST(c.start_date as integer)<=$todayStr";
//	print($sql);
	//$sql = "SELECT DISTINCT t.trip_id,t.trip_headsign, r.route_short_name as route_name, start_s.stop_name as departure_stop, start_st.departure_time as departure_time, t.trip_headsign as trip_short_name,ST.departure_time FROM stop_times ST JOIN trips T ON T.service_id = ST.trip_id JOIN routes r on t.route_id = r.route_id INNER JOIN stop_times start_st ON t.trip_id = start_st.trip_id INNER JOIN stops start_s ON start_st.stop_id = start_s.stop_id JOIN (SELECT service_id FROM calendar WHERE start_date <= $todayStr AND end_date >= $todayStr AND $day = 1 UNION SELECT service_id FROM calendar_dates WHERE date = $todayStr AND exception_type = 1 EXCEPT SELECT service_id FROM calendar_dates WHERE date = $todayStr AND exception_type = 2 ) ASI ON ASI.service_id = T.service_id WHERE ST.stop_id = $stopId AND ST.departure_time >= $theTime ORDER BY ST.departure_time LIMIT 20";
//	print($sql);
        return $sql;
}
?>


