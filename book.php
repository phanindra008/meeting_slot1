<?php
$mysqli = new mysqli('localhost', 'root', '', 'meeting');
if(isset($_GET['date'])){
    $date = $_GET['date'];
    $stmt = $mysqli->prepare("select * from bookings where date = ?");
    $stmt->bind_param('s', $date);
    $bookings = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                $bookings[] = $row['timeslot'];
            }
            
            $stmt->close();
        }
    }
}

if(isset($_POST['submit'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $timeslot = $_POST['timeslot'];

    $stmt = $mysqli->prepare("select * from bookings where date = ? AND timeslot= ?");
    $stmt->bind_param('ss', $date, $timeslot);
    //$bookings = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            $msg = "<div class='alert alert-danger'>Already Booked</div>";
        }else{ 
            $stmt = $mysqli->prepare("INSERT INTO bookings (name, timeslot, email, date) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $name, $timeslot, $email, $date);
            $stmt->execute();
            $msg = "<div class='alert alert-success'>Booking Successfull</div>";
           
            $stmt->close();
           // $mysqli->close();
            
        }
    }

 
}


$duration = 60;
$cleanup = 0;
$start = "09:00";
$end = "12:00";

$count = 9;



function timeslots($duration, $cleanup, $start, $end){
  
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = new DateInterval("PT".$duration."M");
    $cleanupInterval = new DateInterval("PT".$cleanup."M");
    $slots = array();
    
    for($intStart = $start; $intStart<$end; $intStart->add($interval)->add($cleanupInterval)){
        
        $endPeriod = clone $intStart;
        $endPeriod->add($interval);
        if($endPeriod>$end){
            break;
        }
        
        $slots[] = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
       
    }
    
    return $slots;
    
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

    <title></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
</head>

  <body>
 
  <div class="container-fluid text-center">    
    <div class="row content">
        <div class="col-sm-2 sidenav">
            
                <a href="book.php?date=<?= date('Y-m-d', strtotime('-1 day', strtotime($date))) ?>" class="previous round"><i class="fa fa-arrow-circle-left" style="font-size:36px"></i></a>
            
        </div>
        <div class="col-sm-8 text-left"> 
        <?php   
        $totalbookings = checkSlots($mysqli, $date); 
         $Available_slots=9-$totalbookings;
        ?>
        <h1 class="text-center">Book for Date: <?php echo date('F d, Y', strtotime($date)); ?></h1>
        <h4 class="text-center">Slots left: <?php echo $Available_slots ; ?></h4>
        
         </div>
         <div class="col-sm-2 sidenav">
             <a href="book.php?date=<?= date('Y-m-d', strtotime('+1 day', strtotime($date))) ?>" class="next round"><i class="fa fa-arrow-circle-right" style="font-size:36px"></i></a>
               
        </div>
    </div>
</div><hr>
<?php echo isset($msg)?$msg:""; ?>
<div class="container-fluid text-center">    
    <div class="row content">
        <div class="col-sm-2 sidenav">
            <p>Morning</p>
            <i class='fas fa-cloud-sun' style='font-size:36px; color:orange'></i>
            
        </div>
        <div class="col-sm-8 text-left"> 
            
        <div class="row">
        
        

        
          
             
        <?php $timeslots = timeslots($duration, $cleanup, $start, $end="12:00"); 
        foreach($timeslots as $ts ){ 
            global $count;
             ?>
        <div class="col-md-4 main">
        <div class="form-group">
           
            <?php if(in_array($ts, $bookings)) {?>
                <button class="btn btn-danger"><?php echo $ts; ?></button>
            <?php } else{?> 
             <button class="btn btn-primary book" data-timeslot="<?php echo $ts; ?>"><?php echo $ts; ?></button>

            <?php }?>
            

        </div>
        </div>
        
    <?php } ?>
            
        </div>
    </div>

            
         </div>
        
    </div>
</div><hr>

<div class="container-fluid text-center">    
    <div class="row content">
        <div class="col-sm-2 sidenav">
            <p>Afternoon</p>
            <i class='fas fa-sun' style='font-size:36px;color:red'></i>
            
        </div>
        <div class="col-sm-8 text-left"> 
        <div class="row">
        <div class="col-md-12">
        
        
      
        </div>  
      
        
        <?php $timeslots = timeslots($duration, $cleanup, $start="12:00", $end="15:00"); 
        foreach($timeslots as $ts){ 
            global $count;
            ?>
        <div class="col-md-4 main">
        <div class="form-group">
           
            <?php if(in_array($ts, $bookings)) {?>
                <button class="btn btn-danger"><?php echo $ts; ?></button>
            <?php } else{?>
             <button class="btn btn-primary book" data-timeslot="<?php echo $ts; ?>"><?php echo $ts; ?></button>

            <?php }?>
            

        </div>
        </div>
        
    <?php } ?>
            
        </div>
    </div>
            
         </div>
        
    </div>
</div><hr>

<div class="container-fluid text-center">    
    <div class="row content">
        <div class="col-sm-2 sidenav">
            <p>Evening</p>
            <i class='fas fa-cloud-moon' style='font-size:36px;color:blue'></i>
            
        </div>
        <div class="col-sm-8 text-left"> 
      
        <div class="row">
        <div class="col-md-12">
        
        
       
        </div>  
      
        
        <?php $timeslots = timeslots($duration, $cleanup, $start="15:00", $end="18:00"); 
        foreach($timeslots as $ts){ 
            global $count;
            ?>
        <div class="col-md-4 main">
        <div class="form-group">
           
            <?php if(in_array($ts, $bookings)) {?>
                <button class="btn btn-danger"><?php echo $ts; ?></button>
            <?php } else{?>
                
             <button class="btn btn-primary book" data-timeslot="<?php echo $ts; ?>"><?php echo $ts; ?></button>

            <?php $count--; }?>
            
            
        </div>
        </div>
        
    <?php } ?>
            
        </div>
    </div>  
         </div>
        
    </div>
</div><hr>




        
        


    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Booking for: <span id="slot"></span></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form action="" method="post">
                               <div class="form-group">
                                    <label for="">Time Slot</label>
                                    <input  type="text"  class="form-control" id="timeslot" name="timeslot">
                                </div>
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input required type="text"  class="form-control" name="name">
                                </div>
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input required type="email"   class="form-control" name="email">
                                </div>
                                <div class="form-group pull-right">
                                    <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </div>

    <script>
      
    $(".book").click(function(){
        var timeslot = $(this).attr('data-timeslot');
    $("#slot").html(timeslot);
    $("#timeslot").val(timeslot);
    $("#myModal").modal("show");
    })
    </script>


    
  </body>

</html>