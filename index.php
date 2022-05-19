<?php

function build_calendar($month, $year){

    $mysqli = new mysqli('localhost', 'root', '', 'meeting');
    $stmt = $mysqli->prepare("select * from bookings where MONTH(date) = ? AND YEAR (date) = ?");
    $stmt->bind_param('ss', $month, $year);
    $bookings = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                $bookings[] = $row['date'];
            }
            
            $stmt->close();
        }
    }
    
        
    $daysOfWeek = array( 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');

    $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
   
    $numberDays = date('t',$firstDayOfMonth);

    $dateComponents = getdate($firstDayOfMonth);

    $monthName = $dateComponents['month'];

    $dayOfWeek = $dateComponents['wday'];  
    
    if($dayOfWeek==0){
        $dayOfWeek=6;
    }else{
        $dayOfWeek = $dayOfWeek-1;
    }
    
    $dateToday = date('Y-m-d');

     
    $prev_month =  date('m', mktime(0, 0, 0, $month-1, 1, $year));
    $prev_year = date('Y', mktime(0, 0, 0, $month-1, 1, $year));
    $next_month = date('m', mktime(0, 0, 0, $month+1, 1, $year));
    $next_year = date('Y', mktime(0, 0, 0, $month+1, 1, $year));

    
    $calendar ="<center><h2>$monthName $year</h2>";
    $calendar.= "<a class='btn btn-xs btn-primary' href ='?month=".$prev_month ."&year=". $prev_year."'>Previous Month</a> "; 
    $calendar.= "<a  class='btn btn-xs btn-primary' href ='?month=".date('m')."&year=".date('Y')."'>Current Month</a> ";
    $calendar.= "<a class='btn btn-xs btn-primary' href ='?month=".$next_month."&year=$next_year'>Next Month</a></center><br> ";

    $calendar .="<br><table class ='table table-bordered'>";
    $calendar .= "<tr>";
    
    foreach($daysOfWeek as $day) { 
        $calendar .= "<th class='header'>$day</th>"; 
       } 

       $calendar .= "</tr><tr>";   
       $currentDay = 1;
       if($dayOfWeek > 0) { 
        for($k=0;$k<$dayOfWeek;$k++){ 
            $calendar .= "<td class='empty'></td>"; 
            } 
        }

    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    
    while ($currentDay <= $numberDays) { 
     
    if ($dayOfWeek == 7) { 
        $dayOfWeek = 0; 
        $calendar .= "</tr><tr>"; 
    }
    $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
    $date = "$year-$month-$currentDayRel";
    $dayname = strtolower(date('l', strtotime($date)));
    $eventNum = 0;

    $today = $date==date('Y-m-d')? "today" : "";

    if($dayname =='Saturday' || $dayname =='Sunday'){

        $calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>Holiday</button>";

    }elseif($date<date('Y-m-d')){

            $calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>N/A</button>";
        }
         
        
        else{

            
            $totalbookings = checkSlots($mysqli, $date);
            if($totalbookings ==9){
                $calendar.="<td class='$today'><h4>$currentDay</h4> <a href='#' class='btn btn-danger btn-xs'>Booked</a></td>";
            }else{
               $Availableslots = 9- $totalbookings ;
            $calendar.="<td class='$today'><h4>$currentDay</h4> <a href='book.php?date=".$date."' class='btn btn-success btn-xs'>Book</a><small><i>$Availableslots slots left</i></small>";
            

                       

        }

        
    }
    

  
    /*if(in_array($date, $bookings)){
        $calendar .= "<td class='$today'><h4>$currentDay</h4><a class ='btn btn-danger btn-xs'>Booked</a></td>";
    }else{
        $calendar .= "<td class='$today'><h4>$currentDay</h4><a href='book.php?date=".$date."' class ='btn btn-success btn-xs'>Book</a></td>";  
    }*/

    
    $currentDay++; 
    $dayOfWeek++; 
  
    }

    if ($dayOfWeek < 7) { 
        $remainingDays = 7 - $dayOfWeek; 
        for($l=0;$l<$remainingDays;$l++){ 
            $calendar .= "<td class='empty'></td>"; 
        } 
        } 

        $calendar .= "</tr></table>";

        return $calendar;

        if ($dayOfWeek != 7) { 
            $remainingDays = 7 - $dayOfWeek; 
            for($l=0;$l<$remainingDays;$l++){ 
                $calendar .= "<td class='empty'></td>"; 
            } 
            } 
        
            $calendar .= "</tr>"; 
            $calendar .= "</table>";
            
            echo $calendar;
}

        function checkSlots($mysqli, $date){
            $stmt = $mysqli->prepare("select * from bookings where date = ?");
            $stmt->bind_param('s', $date);
            $totalbookings = 0;
            if($stmt->execute()){
                $result = $stmt->get_result();
                    if($result->num_rows>0){
                         while($row = $result->fetch_assoc()){
                            $totalbookings++;
                        }
            
                        $stmt->close();
                    }
            }
            return  $totalbookings;
        }

?> 

<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Calender Page</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="style.css">
  </head>
  <body> 
 <div class="container"> 
    <div class="row"> 
        <div class="col-md-12"> 
            <div id="calendar"> 
                <?php 
                $dateComponents = getdate(); 
                if(isset($_GET['month'])&&isset($_GET['year'])){
                    $month = $_GET['month']; 
                    $year = $_GET['year']; 
                    }else{
                    $month = $dateComponents['mon'];
                    $year = $dateComponents['year'];
                    } 
                 echo build_calendar($month,$year); 
                ?> 
            </div>
        </div> 
    </div> 
 </div> 
</body>
</html>