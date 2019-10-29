<?php 
/**
 * Time management and calculation class, written by 3m1n3nc3
 */
class marxTime
{
    /**
     * Time Difference function
     */     
    function timeAgo($time, $x=0)
    {
        // Use strtotime() function to convert your time stamps before sending to the plugin

        $time_difference = time() - $time;

        if($time_difference < 1 && $x==0) { return 'less than 1 second ago'; }
        $seconds = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                    30 * 24 * 60 * 60       =>  'month',
                    24 * 60 * 60            =>  'day',
                    60 * 60                 =>  'hour',
                    60                      =>  'minute',
                    1                       =>  'second', 
                   -1                       =>  'millisecond' 
        );

        foreach( $seconds as $secs => $ret )
        {
            $diff = $time_difference / $secs;

            if( $diff >= 1 )
            {
                $t = round( $diff );
                $y = $ret == 'hour' || $ret == 'minute' || $ret == 'second' || $ret == 'millisecond' ? true : false;
                // Check the request type
                if ($x == 1) {
                    if ($ret == 'day' && $t==1) {
                        // If the time is been more than a day but less than two show yesterday
                        return date('h:i A', $time).' | Yesterday'; 
                    } elseif ($ret == 'year') {
                        // If the time is been up to a year show full year
                        return date('h:i A', $time).' | '.date('F j Y', $time); 
                    } elseif ($y) {
                        // If the time is been less than or equal to a day show today
                        return date('h:i A', $time).' | Today'; 
                    } else {
                        // If the time is been more than two days show the date
                        return date('h:i A', $time).' | '.date('F j', $time); 
                    }                   
                } elseif ($x == 2) {
                    // Show only date
                    if ($ret == 'year' && $t==1) {
                        // If the time is been more than a day but less than two show yesterday
                        return date('M j Y', $time);
                    } else {
                        return date('M j', $time);
                    }
                } else {
                    return 'About ' . $t . ' ' . $ret . ( $t > 1 ? 's' : '' ) . ' ago';
                }
                
            }
        }
    }

    // Time to go function
    function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        
        $interval = date_diff($datetime1, $datetime2);
        
        return $interval->format($differenceFormat);
        
    }

    // Event Time remaining
    function time2go($event_date, $event_time)
    {
        $today = date("Y-m-d h:i:s");
        $event_dt = $event_date;
        $event_tm = $event_time;
        $event = date('Y-m-d h:i:s', strtotime("$event_dt $event_tm"));
        $echo = dateDifference($today , $event , $differenceFormat = '%a Days, %h Hours, %i Minutes to go!' );
        return $echo;
    }

    // Combine two different date and time string to make a datetime stamp
    function timemerger($date, $time)
    {
        $event_dt = $date;
        $event_tm = $time;
        $event = date('Y-m-d h:i:s', strtotime("$event_dt $event_tm"));  
        return $event;  
    }

    function get_percentage($event_date, $event_time)
    {
        $today = date("Y-m-d h:i:s");
        $event_dt = $event_date;
        $event_tm = $event_time;
        $event = date('Y-m-d h:i:s', strtotime("$event_dt $event_tm"));
        $swap_diff = dateDifference($event, $today);
        $diff_swap = dateDifference($today, $event);

        return ($diff_swap / $swap_diff) * 100.0;
    }

    function date_progress($start, $end, $today = null) 
    {
        $date = $date ?: time();
        return (($date - $start) / ($end - $start)) * 100;
    }

    function dateFormat($date, $type = 0) 
    {
        $d=strtotime($date);

        if ($type == 0) {
            $time = date("D M d - h:i:s A", $d);
        } elseif ($type == 1) {
            $time = date("d/m/Y, h:i A", $d);
        }
        return $time;
    }
}
$marxTime = new marxTime;
?>