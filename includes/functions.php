<?php 
function errorMessage($str) {
    $string = "
            <div style='text-align: center; padding: 0.35rem 1.01rem;' class='alert alert-danger' role='alert'>
                <strong>".$str."</strong>
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
        ";
    return $string;
}

function successMessage($str) {
    $string = "
            <div style='text-align: center; padding: 0.35rem 1.01rem;' class='alert alert-success' role='alert'>
                <strong>".$str."</strong>
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
        ";
    return $string;
}

function warningMessage($str) {
    $string = "
            <div style='text-align: center; padding: 0.35rem 1.01rem;' class='alert alert-warning' role='alert'>
                <strong>".$str."</strong>
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
        ";
    return $string;
}

function infoMessage($str) {
    $string = "
            <div style='text-align: center; padding: 0.35rem 1.01rem;' class='alert alert-info' role='alert'>
                <strong>".$str."</strong>
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
        ";
    return $string;
}

function globalTemplate($type, $full=false) {
    global $LANG, $SETT, $PTMPL, $configuration, $framework, $user, $page_name;
    if ($type == 1) {
        $theme = new themer('global/header'); $section = '';
    } elseif ($type == 2) {
        $theme = new themer('global/footer'); $section = '';
    }
    if (isset($_GET['section'])) {
        $page = $_GET['section'];
        if (isset($_GET['about'])) {
           $page = $_GET['about'];
        }
    } else {
        $page = $page_name;
    }
    
    $hide_more = 0;
    if ($user) {
       $hide_more = $page == 'account' || $page == 'account_var' || $page == 'register_courses' || $page == 'login' || $page == 'register' ? 1 : 0;
    }
    $PTMPL['pager'] = $page;
    $PTMPL[$page.'_tab'] = 'active';
    if ($hide_more == 0 && $full == 1) {
        $footer = new themer('global/navigation_footer');
        $header = new themer('global/navigation_bar');
        $PTMPL['navigation_footer'] = $footer->make();
        $PTMPL['navigation_bar'] = $header->make();
    }
 
    $section = $theme->make();
    return $section;
} 

//Encryption function
function easy_crypt($string, $type = 0) {
    if ($type == 0) {
        return base64_encode($string . "_@#!@/");
    } else {
        $str = base64_decode($string);
        return str_replace("_@#!@/", "", $str);        
    }
    
} 

// Return a random value
function db_prepare_input($string) {
    return trim(addslashes($string));
}

// Generate a random token
function accountToken($length = 10, $type = 0) {
    $str = '';
    $characters = array_merge(range('A','Z'), range('a','z'), range(0,9));
    for($i=0; $i < $length; $i++) {
        $str .= $characters[array_rand($characters)];
    }
    if ($type == 1) {
        return password_hash($str.time(), PASSWORD_DEFAULT);
    } else {
        return hash('md5', $str.time());
    }
}

// Fetch url content via curl
function fetch($uri) {
    if(function_exists('curl_exec')) {
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
        $response = curl_exec($ch);
    }
    if(empty($response)) {
        $response = file_get_contents($uri);
    }
    return $response;
}

function deleteImages($images, $type) {
    // Type 0: Delete covers
    // Type 1: Delete profile
    // Type 2: Delete headshot
    // Type 3: Delete bodyshot
    // Type 4: Delete contest cover
    // Type 5: Delete gallery photo
    
    if($type == 0) {
        $path = 'cover';
    } elseif($type == 1) {
        $path = 'faces';
    } elseif($type == 2) {
        $path = 'contest/head';
    } elseif($type == 3) {
        $path = 'contest/body';
    } elseif($type == 4) {
        $path = 'cover/contest';
    } elseif($type == 5) {
        $path = 'gallery';
    }
    if ($images !== 'default.jpg') {
        if(file_exists('../uploads/'.$path.'/'.$images)) {
            unlink('../uploads/'.$path.'/'.$images); 
        }
    }
    return 1; 
}

function contestTypes($value) { 

    $list = array('' => '', "0" => "pageant", "1" => "election", "2" => "popularity", "3" => "photo", "4" => "others");

    $rows = '';
    foreach($list as $code => $name) {
        $label = ucfirst($name);
        if($value == $name) {
            $selected = 'selected="selected"';
        } else { 
            $selected = '';
        }
        $rows .= '<option value="'.$name.'" '.$selected.'>'.$label.'</option>';
    }  
    return $rows;
}   

function contestCards(){
    global $LANG, $PTMPL, $CONF, $user, $settings; 

    // Pagination Navigation settings
    $perpage = $settings['per_contest'];  //Results to show per page

    if(isset($_GET['more']) & !empty($_GET['more'])){
        $curpage = $_GET['more'];
    } else{
        $curpage = 1;
    }
    $start = ($curpage * $perpage) - $perpage;

    $gett = new contestDelivery;

    if (isset($_GET['u'])) {
        $u = '&u='.$_GET['u'];

        if ($_GET['u'] == $user['username']) {//Show contests created by the logged in user
            if (isset($_GET['id'])) {                  
                $contest = $gett->getContest($_GET['u'], $_GET['id']);       
            } else { 
                // Count users own contest
                $count = count( $gett->getContest($_GET['u'])); 

                $gett->limit = $perpage;
                $gett->start = $start;                
                $contest = $gett->getContest($_GET['u']);       
            }
        } else {
            // Count all active contests
            $count = count($gett->getContest()); 

            $gett->limit = $perpage;
            $gett->start = $start;            
            $contest = $gett->getContest();
        }
    } else { //Show active contest
        $u = '';

        if (isset($_GET['id'])) {  
            $contest = $gett->getContest($user['username'], $_GET['id']);        
        } else { 
            // Count all active contests
            $count = count($gett->getContest());

            $gett->limit = $perpage;
            $gett->start = $start;
            $contest = $gett->getContest();     
        }       
    }

    // Pagination Logic
    $endpage = ceil($count/$perpage);
    $startpage = 1;
    $nextpage = $curpage + 1;
    $previouspage = $curpage - 1;

    $theme = new themer('contest/contest_cards'); $cards = '';
    $PTMPL['contests'] = $divider = '';

    if ($user['role'] !== 'agency' && isset($_GET['u']) && $_GET['u'] == $user['username']) {
         $PTMPL['contests'] = '<h2 class="text-center text-warning">'.$LANG['not_created'].'</h2>';
    } else {
        if ($contest) {
            foreach ($contest as $rs => $key) {
                if (isset($_GET['u']) && $_GET['u'] == $user['username']) {
                    // Show the edit button
                    $edit = '<a href="'.permalink($CONF['url'].'/index.php?a=contest&d=create&id='.$key['id']).'"><i class="fa fa-edit" data-toggle="tooltip" data-placement="right" title="Quick Edit Contest"></i></a>';
                    
                    // Show the manage button
                    $manage = '<a href="'.permalink($CONF['url'].'/index.php?a=contest&manage='.$key['id']).'" class="btn btn-info btn-sm">'.$LANG['manage'].'</a>';
                    
                     // Show the view applications button
                    $applications = '<a href="'.permalink($CONF['url'].'/index.php?a=contest&applications='.$key['id']).'" class="btn btn-light btn-sm">'.$LANG['applications'].'</a>';

                    // Dont show any button if the user did not create the contest
                } else {
                    $edit = ''; $manage = ''; $applications = '';
                }

                if($key) {
                    if ($key['active'] == 1) {
                        if ($key['votes']>0) {
                            $d = $key['votes'].' Votes'; $c = 'badge-success';
                        } else {
                            $d = 'No Votes'; $c = 'badge-warning';
                        }
                    } else {
                        $d = 'Inactive'; $c = 'badge-danger';
                    }
                    $PTMPL['contests'] .= $divider.'
                    <div class="col-md-12">
                      <!-- Contestants to vote card-->
                      <div class="card m-1">

                        <!-- Card image -->
                        <div class="view overlay">
                          <img class="card-img-top" src="'.$PTMPL['site_url'].'/uploads/cover/contest/'.$key['cover'].'" alt="'.$key['title'].'" style="display: block; object-position: 50% 50%; width: 100%; height: 25vh;   object-fit: cover;" id="photo_'.$key['id'].'">
                          <a  onclick="profileModal('.$key['id'].', '.$key['id'].', 2)">
                            <div class="mask rgba-white-slight"></div>
                          </a>
                        </div>

                        <!-- Card content -->
                        <div class="card-body cloudy-knoxville-gradient"> 

                          <!-- Title -->
                          <h4> 
                            <a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$key['id']).'" class="black-text" id="contest-url'.$key['id'].'">'.$key['title'].' <i class="fa fa-angle-double-right"></i></a>
                            <span class="badge badge-pill '.$c.'">'.$d.'</span>
                          </h4>
                          <hr>
                          <!-- Social shares button -->
                          <a onclick="shareModal(1, '.$key['id'].')" class="activator waves-effect waves-light mr-2 mb-2"><i class="fa fa-share-alt"></i></a> 
                          <!-- Text -->
                          <p class="card-text m-2 text-justify">'.myTruncate($key['intro'], 200).'</p>
                          <!-- Link -->
                          <a href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$key['id']).'" class="btn btn-secondary btn-sm">'.$LANG['view_details'].'</a>
                          '.$manage.$applications.$edit.'
                        </div>

                      </div>
                      <!-- Contestants to vote card-->
                    </div>';        
                } 
            }
            
        } else {
            $PTMPL['contests'] = '<h2 class="text-center text-warning">'.$LANG['not_created'].'</h2>';
        } 

             // Table Navigation
        $navigation = '';
        if ($endpage > 1) {
            if($curpage != $startpage){
                $navigation .= ' <a class=" mx-2" href="'.permalink($CONF['url'].'/index.php?a=contest'.$u).'&more='.$startpage.'" data-toggle="tooltip" data-placement="left" title="First Page"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></a> ';                 
            }
            if($curpage >= 2){
                $navigation .= ' <a class=" mx-2" href="'.permalink($CONF['url'].'/index.php?a=contest'.$u).'&more='.$previouspage.'" data-toggle="tooltip" data-placement="left" title="Previous Page"><i class="fa fa-chevron-left"></i></a> ';                  
            }
            $navigation .= ' <a class=" mx-2" href="'.permalink($CONF['url'].'/index.php?a=contest'.$u).'&more='.$curpage.'" data-toggle="tooltip" data-placement="left" title="Current Page"><i class="fa fa-th"></i></a> '; 

            if($curpage != $endpage){
                $navigation .= ' <a class=" mx-2" href="'.permalink($CONF['url'].'/index.php?a=contest'.$u).'&more='.$nextpage.'" data-toggle="tooltip" data-placement="left" title="Next Page"> <i class="fa fa-chevron-right"></i></a> ';

                $navigation .= ' <a class=" mx-2" href="'.permalink($CONF['url'].'/index.php?a=contest'.$u).'&more='.$endpage.'" data-toggle="tooltip" data-placement="left" title="Last Page"> <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></a> ';                                
            }
        }
        $PTMPL['navigation'] = $navigation;

        $cards = $theme->make(); 
        return $cards;
    }   
} 

function scheduleList(){
    global $LANG, $PTMPL, $CONF, $user; 

    $gett = new contestDelivery;
    $scheduler = $gett->getScheduleCategory($_GET['manage'], 0);
    $category = $gett->getScheduleCategory($_GET['manage'], 1);
    $count_appl = count($gett->viewApplications($_GET['manage'], 1));
    $gett->contest_id = $_GET['manage'];
    $count_apprv = count($gett->getApprovedList(/*$start, $perpage*/));
    
    if (isset($user['username'])) {

        $theme = new themer('contest/schedule'); $schedule = '';       

        // Get schedules forr management
        $PTMPL['scheduler'] = $divider = '';
        if ($scheduler) {
            //print_r($links);
            foreach ($scheduler as $rs => $key) { 
                if($key) {
                    $PTMPL['scheduler'] .= $divider.' 
                          <li class="list-group-item list-group-item-action" id="schedule_'.$key['id'].'">'.$key['activity'].' <a href="#" onclick="delete_the('.$key['id'].', 0)"><i class="fa fa-trash text-danger"></i></a> </li> ';                
                } 
            }             
        }

        // Get category for management
        $PTMPL['categorizer'] = $divided = '';
        if ($category) {
            //print_r($links);
            foreach ($category as $rs => $key) { 
                if($key) {
                    $PTMPL['categorizer'] .= $divider.' 
                          <li class="list-group-item list-group-item-action" id="category_'.$key['id'].'">'.$key['category'].' <a href="#" onclick="delete_the('.$key['id'].', 1)"><i class="fa fa-trash text-danger"></i></a> </li> ';                
                } 
            }             
        }
        $schedule = $theme->make(); 
        return $schedule;
    }   
} 

function applicationList(){
    global $DB, $LANG, $PTMPL, $CONF, $user, $contest_id, $settings; 

    $gett = new contestDelivery;
 
    $perpage = $settings['per_table']; 
    if(isset($_GET['more']) & !empty($_GET['more'])){
        $curpage = $_GET['more'];
    } else{
        $curpage = 1;
    }
    $start = ($curpage * $perpage) - $perpage;

    $count = count($gett->viewApplications($_GET['applications'], 1));

    $gett->limit = $perpage;
    $gett->start = $start;
    $list = $gett->viewApplications($_GET['applications'], 1); 

    $endpage = ceil($count/$perpage);
    $startpage = 1;
    $nextpage = $curpage + 1;
    $previouspage = $curpage - 1;
    $nb = 0;

    if ($user !== FALSE) {

        $theme = new themer('contest/applications_list'); $appl = '';
        $PTMPL['applist'] = $divider = '';

        if ($list) { 
            foreach ($list as $rs => $key) {
              $PTMPL['view_btn'] = '<a href="'.permalink($CONF['url'].'/index.php?a=enter&viewdata='.$key['user_id']).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['view_data'].'" class="btn btn-info btn-sm">'.$LANG['view'].'</a>';

              $approve_btn = '<a href="#" id="approve'.$key['user_id'].'" onclick="approveApplication('.$key['user_id'].', '.$key['contest_id'].')" data-toggle="tooltip" data-placement="right" title="'.$LANG['approve'].'"><i class="px-1 fa fa-check-circle fa-lg text-success"></i></a>';

              // Decline the users application to this contest
              $decline_btn = '<a href="#" id="approve'.$key['user_id'].'" onclick="delete_the('.$key['user_id'].', 4, '.$key['contest_id'].')" data-toggle="tooltip" data-placement="right" title="'.$LANG['decline'].'"><i class="px-1 fa fa-times-circle fa-lg text-danger"></i></a>';

               $nb = $nb+1;  
                if($key) { 
                    $PTMPL['applist'] .= $divider.' 
                                  <tbody id=row'.$key['user_id'].'>
                                    <tr>
                                      <th scope="row">'.$nb.'</th>
                                      <td id=name'.$key['user_id'].'>'.$key['firstname'].' '.$key['lastname'].'</td>
                                      <td>'.$key['city'].'</td>
                                      <td>'.$key['state'].'</td>
                                      <td>'.$key['country'].'</td>  
                                      <td>
                                        <div class="p-1">'.$PTMPL['view_btn'].'</div>
                                        <div class="p-1">'.$approve_btn.$decline_btn.'</div> 
                                        <div class="saving-load" id="saving-load'.$key['user_id'].'"></div>
                                      </td>  
                                    </tr> 
                                  </tbody>';              
                } 
            }
       } else $PTMPL['applist'] .= $divider.'<tbody><tr><td colspan="6" class="h3 text-info">No pending applications<hr></td></tr></tbody>';
             // Table Navigation
        if ($endpage > 1) {
            if($curpage != $startpage){
                $PTMPL['navigation1'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&applications='.$_GET['applications']).'&more='.$startpage.'" data-toggle="tooltip" data-placement="left" title="First Page"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></a> ';                 
            }
            if($curpage >= 2){
                $PTMPL['navigation2'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&applications='.$_GET['applications']).'&more='.$previouspage.'" data-toggle="tooltip" data-placement="left" title="Previous Page"><i class="fa fa-chevron-left"></i></a> ';                  
            }
            $PTMPL['navigation0'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&applications='.$_GET['applications']).'&more='.$curpage.'" data-toggle="tooltip" data-placement="left" title="Current Page"><i class="fa fa-th mx-2"></i></a> '; 

            if($curpage != $endpage){
                $PTMPL['navigation3'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&applications='.$_GET['applications']).'&more='.$nextpage.'" data-toggle="tooltip" data-placement="left" title="Next Page"> <i class="fa fa-chevron-right mx-2"></i></a> ';

                $PTMPL['navigation4'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&applications='.$_GET['applications']).'&more='.$endpage.'" data-toggle="tooltip" data-placement="left" title="Last Page"> <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></a> ';                                
            }
        }
       
        $appl = $theme->make(); 
        
        return $appl;
    }  
}

function approvedList(){
    global $DB, $LANG, $PTMPL, $CONF, $user, $contest_id, $settings; 

    $perpage = $settings['per_table'];
    if(isset($_GET['more']) & !empty($_GET['more'])){
        $curpage = $_GET['more'];
    } else{
        $curpage = 1;
    }
    $start = ($curpage * $perpage) - $perpage;

    $gett = new contestDelivery; 
    $gett->contest_id = $_GET['approved'];
    $data = $gett->getApprovedList($start, $perpage);
    $count = count($gett->getApprovedList(/*$start, $perpage*/));
    $nb = 0;

    $endpage = ceil($count/$perpage);
    $startpage = 1;
    $nextpage = $curpage + 1;
    $previouspage = $curpage - 1;

    if ($user) {

        $theme = new themer('contest/approved_list'); $appl = ''; 

        $uc = new userCallback;

        $PTMPL['approved_c'] = $divider2 = '';
        if ($data && isset($_GET['approved'])) { 
            foreach ($data as $rs => $key) {

                $uc->user_id = $key['contestant_id'];
                $userdata = $uc->userData(0, 1)[0];  

                $nb = $nb+1;  
                if($key) { 

                $view_btn = '<a href="'.permalink($CONF['url'].'/index.php?a=enter&viewdata='.$key['contestant_id']).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['users_data'].'" class="py-1 mt-1 btn btn-sm btn-info btn-rounded">'.$LANG['view'].'</a>';

                $remove_btn = '<a href="#" id="approve'.$key['contestant_id'].'" onclick="delete_the('.$key['contestant_id'].', 5, '.$key['contest_id'].')" data-toggle="tooltip" data-placement="right" title="'.$LANG['remove'].'" class="py-1 mt-1 btn btn-sm btn-danger btn-rounded">'.$LANG['remove'].'</a>';

                    $PTMPL['approved_c'] .= $divider2.' 
                      <tbody id=row'.$key['contestant_id'].'>
                        <tr>
                          <th scope="row">'.$nb.'</th>
                          <td><a data-toggle="tooltip" data-placement="right" title="View Public Profile" href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$userdata['username']).'">'.$key['name'].'</a>
                          </td>
                          <td>'.$key['city'].'</td>
                          <td>'.$key['state'].'</td>
                          <td>'.$key['country'].'</td> 
                          <td>'.$key['votes'].'</td> 
                          <td> 
                            <div class="p-1"><span>'.$view_btn.'</span><span>'.$remove_btn.'</span></div> 
                            <div class="saving-load" id="saving-load'.$key['contestant_id'].'"></div>
                          </td>  
                        </tr>   
                        </tr> 
                      </tbody>';              
                } 
            }
        } else $PTMPL['approved_c'] .= $divider2.'<tbody><tr><td colspan="6" class="h3 text-info">There are no contestants for this contest<hr></td></tr></tbody>'; 

             // Table Navigation
        if ($endpage > 1) {
            if($curpage != $startpage){
                $PTMPL['navigation1'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&approved='.$_GET['approved']).'&more='.$startpage.'" data-toggle="tooltip" data-placement="left" title="First Page"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></a> ';                 
            }
            if($curpage >= 2){
                $PTMPL['navigation2'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&approved='.$_GET['approved']).'&more='.$previouspage.'" data-toggle="tooltip" data-placement="left" title="Previous Page"><i class="fa fa-chevron-left"></i></a> ';                  
            }
            $PTMPL['navigation0'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&approved='.$_GET['approved']).'&more='.$curpage.'" data-toggle="tooltip" data-placement="left" title="Current Page"><i class="fa fa-th mx-2"></i></a> '; 

            if($curpage != $endpage){
                $PTMPL['navigation3'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&approved='.$_GET['approved']).'&more='.$nextpage.'" data-toggle="tooltip" data-placement="left" title="Next Page"> <i class="fa fa-chevron-right mx-2"></i></a> ';

                $PTMPL['navigation4'] = ' <a href="'.permalink($CONF['url'].'/index.php?a=contest&approved='.$_GET['approved']).'&more='.$endpage.'" data-toggle="tooltip" data-placement="left" title="Last Page"> <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></a> ';                                
            }
        }

        $appl = $theme->make(); 
        
        return $appl;
    }  
}

// Contest details and general information
function detailsCards(){
    global $DB, $LANG, $PTMPL, $CONF, $user, $contest_id; 

    $gett = new contestDelivery;   
    $contest_by_id = $gett->getContest(NULL, isset($_GET['id']) ? $_GET['id'] : '');
    $contest_by_s = $gett->getContest(NULL, isset($_GET['s']) ? $_GET['s'] : '', 'safelink');
    $contest_id_cr = $gett->getContest($user['username'], isset($_GET['id']) ? $_GET['id'] : '');
    $contest_s_cr = $gett->getContest($user['username'], isset($_GET['s']) ? $_GET['s'] : '', 'safelink');      

    if (isset($_GET['id']) && $_GET['id'] == $contest_by_id['id']) { 
         $contest = $contest_by_id; 
    } elseif (isset($_GET['s']) && $_GET['s'] == $contest_by_s['safelink']) {
         $contest = $contest_by_s; 
    } elseif (isset($contest_id_cr['creator']) ? $contest_id_cr['creator'] : '' == $user['username'] && isset($_GET['id']) && $_GET['id'] == $contest_id_cr['id']) {
         $contest = $contest_id_cr; 
    } elseif (isset($contest_s_cr['creator']) ? $contest_s_cr['creator'] : '' == $user['username'] && isset($_GET['s']) && $_GET['s'] == $contest_s_cr['safelink']) {
         $contest = $contest_s_cr; 
    }   

    $list_schedule = $gett->getScheduleCategory($contest['id'], 0);
    $list_category = $gett->getScheduleCategory($contest['id'], 1);

    $theme = new themer('contest/details_cards'); $c_cards = '';

    // View schedule for contest details
    $PTMPL['view_schedule'] = $divider = '';
    if ($list_schedule) { 
        foreach ($list_schedule as $rs => $key) { 

            if($key) { 
                $PTMPL['view_schedule'] .= $divider.' 
                    <div class="col-md-3"> 
                        <p class="font-weight-bold text-left">'.$key['time'].'<br>'.$key['date'].'<br>'.$key['activity'].'</p>
                    </div>
                    <div class="col-md-9"> 
                        <h5 class="text-left"> '.$key['description'].' </h5>
                        <hr class="bg-white">
                    </div>';              
            } 
        }
    }

    // View categories for contest details
    $PTMPL['view_categories'] = $divider2 = '';
    if ($list_category) { 
        foreach ($list_category as $rs => $key) { 
            if($key) { 
                $PTMPL['view_categories'] .= $divider2.' 
                  <div class="col-md-3"> 
                    <p class="font-weight-bold text-left">'.$key['category'].'</p>
                  </div>
                  <div class="col-md-9"> 
                      <div class="text-left">
                        <span class="font-weight-bold text-left">Description</span>
                        <p class="text-left">'.$key['description'].'</p>
                      </div>

                      <div class="text-left">
                        <span class="font-weight-bold text-left">Requirements</span>
                        <p class="text-left">'.$key['requirements'].'</p> 
                      </div>
                      <hr class="bg-white">                                                         
                  </div>';              
            }
        }
    }    

    // Venue and location information
    $PTMPL['contest_venue'] = $contest['venue'];

    // Elegibility information
    $PTMPL['contest_elig'] = $contest['eligibility'];

    // Elegibility information
    $PTMPL['contest_prize'] = $contest['prize'];

    $c_cards = $theme->make(); 
    return $c_cards;
}

function masterCraft(){ 
    global $DB, $LANG, $PTMPL, $CONF, $user, $contest_id, $profiles; 
 
    $cd = new contestDelivery;
    $cd->contestant_id = $profiles['id'];
    $contest = $cd->getUsersCurrent();
    $created = $cd->getContest($profiles['username'], 0, 0, 'AND active = \'1\' LIMIT 6');
    $quickinfo = $cd->viewApplications(0, 0, $profiles['id']);

    // Determine whether the contestant is male or female
    if ($quickinfo['gender'] == 'male') {
        $sex = 'he';
    } else {
        $sex ='she';
    }

    if ($profiles !== FALSE) {

        $theme = new themer('profile/mastercraft'); $mastercraft = '';
        $PTMPL['username'] =  ucfirst($profiles['username']);
        $PTMPL['profile_id'] = $profiles['id'];

        // Users current contests
        $PTMPL['c_contests'] = $divider = '';
        if ($contest) { 
            foreach ($contest as $rs => $key) { 
                $requested = $cd->getContest(0, $key['contest_id']); 
                if ($key['votes'] <=0) {
                    $vote_count = '<span class="text-danger"> 0 '.$LANG['vote'].'s</span>';
                } else {
                    $vote_count = '<span class="text-success font-weight-bold">'.$key['votes'].' '.$LANG['vote'].'s</span>';
                }  
                if ($requested['status']) { 
                    $vote_button = '
                    <a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$key['contest_id'].'&user='.$profiles['username']).'" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="right" title="Goto user voting page to vote">'.$LANG['vote'].'</a>';            
                } else {
                    $vote_button = '';
                }

                if($key) { 
                    $PTMPL['c_contests'] .= $divider.' 
                        <h6 class="text-left">
                          <a href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$key['contest_id']).'" class="black-text text-left"> '.$requested['title'].' <i class="fa fa-angle-double-right"></i> </a> <br>
                            '.$vote_button.' '.$vote_count.'
                        </h6><hr class="bg-white">';  
                  
                } 
            }
        } else {
            $PTMPL['c_contests'] = ''.sprintf($LANG['has_not_entered'], $profiles['username']);
        }

        // Users current created contests
        $PTMPL['c_created'] = $divider = '';
        if ($created) { 
            foreach ($created as $rs => $key) { 
                $requested = $cd->getContest(0, $key['id']); 
                if ($key['votes'] <=0) {
                    $vote_count = '0';
                } else {
                    $vote_count = $key['votes'];
                }
                if($key) { 
                    $PTMPL['c_created'] .= $divider.' 
                        <h6 class="text-left">
                          <a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$key['id']).'" class="black-text text-left"> '.$requested['title'].' <i class="fa fa-angle-double-right"></i> </a> <br> 
                          <a href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$key['id']).'" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="right" title="'.$LANG['view_this_datails'].'">'.$LANG['view_details'].' <i class="fa fa-eye"></i></a>
                          Total Votes: '.$vote_count.'
                        </h6><hr class="bg-white">';  
                  
                } 
            }
        } else {
            $PTMPL['c_created'] = ''.sprintf($LANG['has_not_created'], $profiles['username']);
        }

        // Users current created contests
        $PTMPL['c_info'] = $divider = '';
        if ($quickinfo) { 
            $dd = explode(',', $quickinfo['dob']);
            $dob = ($profiles['role'] == 'agency') ? 'a '.$dd[0] : $quickinfo['dob'];
            $PTMPL['c_info'] .= $divider.' 
                <h6 class="text-justify">
                  '.sprintf($LANG['quickinfo'], $quickinfo['firstname'], $quickinfo['lastname'], $quickinfo['city'], 
                    $quickinfo['state'], $quickinfo['country'], ucfirst($sex), $dob, $quickinfo['pob'], 
                    ucfirst($sex), $quickinfo['hobbies'], $quickinfo['activities'], $sex, $quickinfo['certificate'], 
                    $quickinfo['work'], $quickinfo['email']).'
                </h6><hr class="bg-white">';  

        } else {
            $PTMPL['c_info'] = ''.sprintf($LANG['has_not_completed'], $profiles['username']);
        }

    $mastercraft = $theme->make(); 
    return $mastercraft;
    }  
}
 
function get_isabi($code) {
    global $welcome, $user, $enc_key, $obhel;
    $url = oworgi($enc_key);
    $data = "a=isabi&token=".$code."&server=".$_SERVER['HTTP_HOST'];
    $return = false;
    $timeNow = time(); $refresh = 0;
    $rc = $timeNow - $welcome['time'];
    if ($rc > $obhel) {
        $sql = sprintf("UPDATE " .TABLE_WELCOME. " SET `time` = '%s'", $timeNow);
        $refresh = dbProcessor($sql, 0, 1);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0 Firefox/5.0');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    $contents = curl_exec($curl);
    $status = curl_getinfo($curl); 
    curl_close($curl);
    if ($refresh == 1) {
        if($status['http_code'] == 200) {
            $contents = json_decode($contents, true);
            if($contents['error']) {
                $_SESSION['RXJyb3IgTWVzc2FnZQ=='] = $contents['error']['message'].' Error Code #'.$contents['error']['code'];
                $_SESSION['ZmFrZV9zaXRl'] = 'RXJyb3I6IFRoaXMgc2l0ZSB3YXMgcmVnaXN0ZXJlZCB3aXRoIGEgZmFrZSBsaWNlbmNlIGtleS4uLiBXZSBhcmUgbm93IGRlbGV0aW5nIGFsbCBpbXBvcnRhbnQgZmlsZXMuLi4=';
                return false;
            }
            if (isset($_SESSION['ZmFrZV9zaXRl'])) {
                unset($_SESSION['ZmFrZV9zaXRl']);
                unset($_SESSION['RXJyb3IgTWVzc2FnZQ==']);
            }
            $return = true;
        } else {
            $_SESSION['RXJyb3IgTWVzc2FnZQ=='] = "Error Processing Request";
        }
    } else {
        $return = false;
    }
    return $return;
}

function votingCards(){
    global $DB, $LANG, $PTMPL, $CONF, $user, $contest_id, $profiles, $settings; 

    // Pagination Navigation settings
    $perpage = $settings['per_voting'];

    $gett = new contestDelivery;

    if(isset($_GET['page']) & !empty($_GET['page'])){
        $curpage = $_GET['page'];
    } else{
        $curpage = 1;
    }
    $start = ($curpage * $perpage) - $perpage;

    $gett->contest_id = $_GET['id'];
    $count = count($gett->getApprovedList());
    $contestants = $gett->getApprovedList($start, $perpage); 

    // Pagination Logic
    $endpage = ceil($count/$perpage);
    $startpage = 1;
    $nextpage = $curpage + 1;
    $previouspage = $curpage - 1;  
  
    $userApp = new userCallback;
    $voters = $gett->getVoters(1, $_GET['id']);

    $disd = $gett->getContest(0, $_GET['id']);
    $save = new siteClass;
    // Fetch the users balance
    $save->what = sprintf('user = \'%s\'', $user['id']);
    $get_credit = $save->passCredits(0)[0];

    $theme = new themer('voting/cards'); $cards = '';
    $PTMPL['contestants_c'] = $divider = '';
    
    // Check if the contest requires social interaction
    if ($disd['require_social']) { 
        $type = 0;  
    } else { 
        $type = 1;  
    }
    if ($contestants) { 
        foreach ($contestants as $rs => $key) {
            // Get the user data
            $userApp->user_id = $key['contestant_id'];
            $c_user = $userApp->userData(NULL, 1)[0]; 

            if ($user['username']) {
                if ($disd['status']) {
                    if ($disd['allow_vote'] != 1) {
                        // If voting settings is off in contest  remove the onclick action to prevent users from voting
                        $vote_button = '<button class="text-white btn btn-rounded btn-action ml-auto mr-4 aqua-gradient lighten-3" data-toggle="tooltip" data-placement="right" title="Voting is suspended" disabled> CANT VOTE <i class="fa fa-thumbs-up pl-1"></i></button>';
                    } elseif ($disd['require_social'] && !$voters['social']) {
                        // If the contest requires social follow, show the follow modal
                        $vote_button = '<button id="vote'.$key['contestant_id'].'" class="text-white btn btn-rounded btn-action ml-auto mr-4 peach-gradient lighten-3" data-toggle="tooltip" data-placement="right" title="Vote '.$key['name'].'" onclick="shareModal(3, '.$key['contest_id'].')"> VOTE <i class="fa fa-thumbs-up pl-1"></i></button>';
                    } else {
                        // Else If voting settings is on in contest allow users to vote
                        $vote_button = '<button id="vote'.$key['contestant_id'].'" class="text-white btn btn-rounded btn-action ml-auto mr-4 aqua-gradient lighten-3" data-toggle="tooltip" data-placement="right" title="Vote '.$key['name'].'" onclick="giveVote('.$key['contestant_id'].','.$key['contest_id'].', '.$type.')"> VOTE <i class="fa fa-thumbs-up pl-1"></i></button>';   
                    }                      
                } else {
                    $vote_button = '';
                } 
            } else { 
                $vote_button = '<a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$key['contest_id'].'&required=login_vote&referrer='.urlencode(urlReferrer(permalink($CONF['url'].'/index.php?a=voting&id='.$key['contest_id']), 0))).'" class="text-white btn btn-rounded btn-action ml-auto mr-4 peach-gradient lighten-3">VOTE</a>';
            } 
        
            // Check if this user has voted before
            $vote_warning = '';
            if ($get_credit['balance'] > $settings['pc_vote']) {
                $vote_btn = $vote_button;
                $vote_warning = sprintf($LANG['vote_warning'], $LANG['passcredit'].' '.$LANG['balance']);
            } elseif (isset($voters['voter_id']) && $voters['voter_id'] == $user['id'] && $voters['voted']) {
                $vote_btn = '';
            } else {
                $vote_btn = $vote_button;
            }

            $c_photo = ($c_user['photo']) ? $CONF['url'].'/uploads/faces/'.$c_user['photo'] : $CONF['url'].'/uploads/faces/default.jpg';
            isset($key['votes']) && $key['votes']>0?$vote_count = $key['votes']:$vote_count = '0'; 
            $d=strtotime($key["date"]);
            $date = date("M d - h:i A", $d);

            $intro = myTruncate($c_user['intro'], 220); 

            $gett->contestant_id = $key['contestant_id'];
            $gett->contest_id = $_GET['id'];
            $comments = $gett->doComments(1, 'post', 1);
            $comment_count = count($comments);

            if($key) {
                $PTMPL['contestants_c'] .= $divider.' 
                    
                <a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$key['contest_id'].'&user='.$c_user['username']).'" id="profile-url'.$c_user['id'].'" style="display: none;"> '.$key['name'].'</a>

                      <div class="col-md-12"> 
                        <div class="card cloudy-knoxville-gradient m-2"> 
                          <div class="view overlay d-flex justify-content-center""> 
                            <img class="card-img-top" src="'.$c_photo.'" alt="'.$c_user['username'].'" style="display: block; object-position: 50% 10%; width: 50%; height: 100%; object-fit: cover;" id="photo_'.$c_user['id'].'">

                            <a onclick="profileModal('.$key['contestant_id'].', '.$key['contest_id'].', 0)">
                              <div class="mask rgba-white-slight"></div>
                            </a>
                          </div> 
                          <span id="loader'.$key['contestant_id'].'" class="small-loader"></span>
                            '.$vote_btn.'
                          <div class="card-body cloudy-knoxville-gradient m-2"> 
                            <h5 class="card-title"><a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$c_user['username']).'" class="black-text text-left"> '.$key['name'].' <i class="fa fa-angle-double-right"></i> </a></h5> 
                            <p class="card-text text-justify">'.$intro.'. </p> 
                          </div>
                            <span class="peach-gradient text-white" id="vote-msg'.$key['contestant_id'].'"></span>  
                            <span id="charge'.$key['contestant_id'].'">'.$vote_warning.'</span>
                          <div class="rounded-bottom blue-gradient lighten-3 text-center pt-3">
                            <ul class="list-unstyled list-inline font-small">
                              <li class="list-inline-item pr-2 white-text"><i class="fa fa-clock-o pr-1"></i>'.$date.'</li>
                              <li class="list-inline-item pr-2 white-text"><a onclick="shareModal(2, '.$key['contestant_id'].')" class="white-text"><i class="fa fa-share pr-1"></i>Share</a></li>
                              <li class="list-inline-item pr-2"><a onclick="profileModal('.$key['contestant_id'].', '.$key['contest_id'].', 1)" class="white-text"><i class="fa fa-comments-o pr-1"></i>'.$comment_count.' Comments</a></li> 
                              <li class="list-inline-item white-text">
                                <i class="fa fa-thumbs-up pr-1"> </i><span id="count_votes'.$key['contestant_id'].'">'.$vote_count.' Votes</span></li>
                            </ul>
                          </div>
                        </div> 
                      </div>';              
            }
        }
    } else {
        $PTMPL['contestants_c'] .= $divider.'<div class="h3 text-center text-info p-5">'.$LANG['no_contestant'].'</div>';
    } 

    // Page navigation
    $navigation = '';

    if ($endpage > 1) {
        if ($curpage != $startpage) {
          $navigation .= '<a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$_GET['id']).'&page='.$startpage.'" class="text-black mx-1"><i class="fa fa-angle-double-left"></i></a>';
        }

        if ($curpage >= 2) {
          $navigation .= '<a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$_GET['id']).'&page='.$previouspage.'" class="text-black mx-1"><i class="fa fa-angle-left"></i></a>';
        }
          $navigation .= '<a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$_GET['id']).'&page='.$curpage.'" class="text-black mx-1"><i class="fa fa-th-large"></i></a>';

        if($curpage != $endpage){
          $navigation .= '<a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$_GET['id']).'&page='.$nextpage.'" class="text-black mx-1"><i class="fa fa-angle-right"></i></a>';

          $navigation .= '<a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$_GET['id']).'&page='.$endpage.'" class="text-black mx-1"><i class="fa fa-angle-double-right"></i></a>';
        }

        $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';

    } else {
        $navigation .= '';
    }
    $PTMPL['navigation'] = $navigation;                
    $cards = $theme->make(); 
    
    return $cards; 
}


function vote_user_card(){
    global $DB, $LANG, $PTMPL, $CONF, $user, $contest_id, $profiles, $settings; 

    $gett = new contestDelivery;
    $save = new siteClass;
    $userApp = new userCallback;

    $gett->contest_id = $_GET['id']; 
    $gett->username = $_GET['user']; 
    $contest = $gett->getUsersCurrent(2)[0]; 
  
    $voters = $gett->getVoters(1, $_GET['id']);

    $disd = $gett->getContest(0, $_GET['id']); 
 
    $theme = new themer('voting/cards'); $cards = '';
    $PTMPL['contestants_c'] = $divider = '';

    // Get the user data
    $userApp->user_id = $contest['contestant_id'];
    $c_user = $userApp->userData(NULL, 1)[0];  

    // Fetch the users balance
    $save->what = sprintf('user = \'%s\'', $user['id']);
    $get_credit = $save->passCredits(0)[0];

    // Check if the contest requires social interaction
    if ($disd['require_social']) { 
        $type = 0;  
    } else { 
        $type = 1;  
    }
    if ($user['username']) {
        if ($disd['status']) {
            if ($disd['allow_vote'] != 1) {
                // If voting settings is off in contest  remove the onclick action to prevent users from voting
                $vote_button = '<button id="vote'.$contest['contestant_id'].'" class="text-white btn btn-rounded btn-action ml-auto mr-4 aqua-gradient lighten-3" data-toggle="tooltip" data-placement="right" title="Voting is suspended" disabled> CANT VOTE <i class="fa fa-thumbs-up pl-1"></i></button>';
            } elseif ($disd['require_social'] && !$voters['social']) {
                // If the contest requires social follow, show the follow modal
                $vote_button = '<button id="vote'.$contest['contestant_id'].'" class="text-white btn btn-rounded btn-action ml-auto mr-4 peach-gradient lighten-3" data-toggle="tooltip" data-placement="right" title="Vote '.$contest['name'].'" onclick="shareModal(3, '.$contest['contest_id'].')"> VOTE <i class="fa fa-thumbs-up pl-1"></i></button>';
            } else {
                // Else If voting settings is on in contest allow users to vote
                $vote_button = '<button id="vote'.$contest['contestant_id'].'" class="text-white btn btn-rounded btn-action ml-auto mr-4 aqua-gradient lighten-3" data-toggle="tooltip" data-placement="right" title="Vote '.$contest['name'].'" onclick="giveVote('.$contest['contestant_id'].','.$contest['contest_id'].', '.$type.')"> VOTE <i class="fa fa-thumbs-up pl-1"></i></button>';   
            }                      
        } else {
            $vote_button = '';
        } 
    } else { 
        $vote_button = '<a  id="vote'.$contest['contestant_id'].'" href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$contest['contest_id'].'&user='.$c_user['username']).'&required=login_vote&referrer='.urlencode(urlReferrer(permalink($CONF['url'].'/index.php?a=voting&id='.$contest['contest_id'].'&user='.$c_user['username']), 0)).'" class="text-white btn btn-rounded btn-action ml-auto mr-4 peach-gradient lighten-3">VOTE</a>';
    }

    // Check if this user has voted before
    $vote_warning = '';
    if ($get_credit['balance'] > $settings['pc_vote']) {
        $vote_btn = $vote_button;
        $vote_warning = sprintf($LANG['vote_warning'], $LANG['passcredit'].' '.$LANG['balance']);
    } elseif (isset($voters['voter_id']) && $voters['voter_id'] == $user['id'] && $voters['voted']) {
        $vote_btn = '';
    } else {
        $vote_btn = $vote_button;
    }

    $c_photo = ($c_user['photo']) ? $CONF['url'].'/uploads/faces/'.$c_user['photo'] : $CONF['url'].'/uploads/faces/default.jpg';
    isset($contest['votes']) && $contest['votes']>0?$vote_count = $contest['votes']:$vote_count = '0'; 
    $d=strtotime($contest["date"]);
    $date = date("M d - h:i A", $d);

    $intro = myTruncate($c_user['intro'], 220); 

    $gett->contestant_id = $contest['contestant_id'];
    $gett->contest_id = $_GET['id'];
    $comments = $gett->doComments(1, 'post', 1);
    $comment_count = count($comments);

    if($contest) {
        $PTMPL['contestants_c'] .= $divider.' 

        <a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$contest['contest_id'].'&user='.$c_user['username']).'" id="profile-url'.$c_user['id'].'" style="display: none;"> '.$contest['name'].'</a>

              <div class="col-md-12">
                <div class="card cloudy-knoxville-gradient m-2"> 
                  <div class="view overlay d-flex justify-content-center">  
                    <img class="card-img-top" src="'.$c_photo.'" alt="'.$c_user['username'].'" style="display: block; object-position: 50% 10%; width: 60%; height: 100%; object-fit: cover;" id="photo_'.$c_user['id'].'">
                    <a onclick="profileModal('.$contest['contestant_id'].', '.$contest['contest_id'].', 0)">
                      <div class="mask rgba-white-slight"></div>
                    </a>
                  </div> 
                  <span id="loader'.$contest['contestant_id'].'" class="small-loader"></span>
                    '.$vote_btn.'
                  <div class="card-body cloudy-knoxville-gradient m-2"> 
                    <h5 class="card-title"><a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$c_user['username']).'" class="black-text text-left"> '.$contest['name'].' <i class="fa fa-angle-double-right"></i> </a></h5> 
                    <p class="card-text text-justify">'.$intro.'. </p> 
                  </div>
                    <span class="peach-gradient text-white" id="vote-msg'.$contest['contestant_id'].'"></span> 
                    <span id="charge'.$contest['contestant_id'].'">'.$vote_warning.'</span> 
                  <div class="rounded-bottom blue-gradient lighten-3 text-center pt-3">
                    <ul class="list-unstyled list-inline font-small">
                      <li class="list-inline-item pr-2 white-text"><i class="fa fa-clock-o pr-1"></i>'.$date.'</li>
                      <li class="list-inline-item pr-2 white-text"><a onclick="shareModal(2, '.$contest['contestant_id'].')" class="white-text"><i class="fa fa-share pr-1"></i>Share</a></li>
                      <li class="list-inline-item pr-2"><a onclick="profileModal('.$contest['contestant_id'].', '.$contest['contest_id'].', 1)" class="white-text"><i class="fa fa-comments-o pr-1"></i>'.$comment_count.' Comments</a></li> 
                      <li class="list-inline-item white-text">
                        <i class="fa fa-thumbs-up pr-1"> </i><span id="count_votes'.$contest['contestant_id'].'">'.$vote_count.' Votes</span></li>
                    </ul>
                  </div>
                </div> 
                <div class="row">
                <a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$contest['contest_id']).'" class="btn deep-blue-gradient col">VIEW ALL CONTESTANTS</a>
                <a href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$contest['contest_id']).'" class="btn winter-neva-gradient col">VIEW '.$disd['title'].'</a></div>
              </div>';              
    } else {
        $PTMPL['contestants_c'] .= $divider.'<div class="h3 text-center text-info p-5">'.$LANG['not_here'].'</div>';
    }

        $cards = $theme->make(); 
        
        return $cards; 
}

function badge($request=null, $name=null, $x=null) {
    global $LANG, $PTMPL, $CONF, $user, $settings, $profiles; 
    // x 1: name
    // x 2: badge
    // x 3: Integer

    // type 1: Premium Voter
    // type 2: C LEAD
    // type 3: C MARX
    // type 4: SLIGHT
    // type 5: LITE
    // ytpe 6: LIFE

    // Convert a plan name to a numeric string  
    if ($request) {
        $type = $request;
    } else { 
        if ($name == 'premium_plan') { 
            $type = 1;
        } elseif ($name == 'clead_plan') { 
            $type = 2;
        } elseif ($name == 'cmarx_plan') { 
            $type = 3;
        } elseif ($name == 'slight_plan') { 
            $type = 4;
        } elseif ($name == 'lite_plan') { 
            $type = 5;
        } elseif ($name == 'life_plan') { 
            $type = 6;
        } else {
            $type = 7;
        }        
    }
    if ($type == 1) {
        ($x == 2 || !$x) ? $file = $CONF['url'].'/'.$PTMPL['template_url'].'/img/badge/pvb.png' : '';
        $name = $LANG['premium_vp'];
    } elseif ($type == 2) {
        ($x == 2 || !$x) ? $file = $CONF['url'].'/'.$PTMPL['template_url'].'/img/badge/cld.png' : '';
        $name = $LANG['clead_p'];
    } elseif ($type == 3) {
        ($x == 2 || !$x) ? $file = $CONF['url'].'/'.$PTMPL['template_url'].'/img/badge/cmx.png' : '';
        $name = $LANG['cmarx_p'];
    } elseif ($type == 4) {
        ($x == 2 || !$x) ? $file = $CONF['url'].'/'.$PTMPL['template_url'].'/img/badge/slt.png' : '';
        $name = $LANG['slight_p'];
    } elseif ($type == 5) {
        ($x == 2 || !$x) ? $file = $CONF['url'].'/'.$PTMPL['template_url'].'/img/badge/lte.png' : '';
        $name = $LANG['lite_p'];
    } elseif ($type == 6) {
        ($x == 2 || !$x) ? $file = $CONF['url'].'/'.$PTMPL['template_url'].'/img/badge/lfe.png' : '';
        $name = $LANG['life_p'];
    } elseif ($type == 7) {
        ($x == 2 || !$x) ? $file = '' : '';
        $name = $LANG['free_p'];
    } 
     
    if (isset($x)){
        if ($x == 1) {
           return $name; 
        } elseif($x == 2) {
            if ($type == 7) {
                return $file; 
            } else {
                return '<img class="p-1" src="'.$file.'" alt="'.$name.'" height="auto" width="25px" id="premium-badge">';
            }
        } elseif($x == 3) {
            return $type; 
        }
    } else {
        if ($request == null) {
           return $name;
        } else {
            if ($type == 7) {
                return $file; 
            } else {
                return '<img src="'.$file.'" alt="'.$name.'" height="auto" width="20px" id="premium-badge">';
            }
        }        
    }
 
}

// Get recommended users
function recomended_users() {
    global $LANG, $CONF, $user, $settings;

    $userApp = new userCallback;
    $cd = new contestDelivery;

    $this_user = (isset($user['id'])) ? $user['id'] : 0;
    // If premium settings is on, recommend only premium contestant
    if ($settings['premium']) {
        $sql = sprintf("SELECT `contestant_id`, `contest_id`, `name`, `city`, `votes`, `status`, `plan` AS plan FROM " . TABLE_CONTESTANT . " AS contestant LEFT JOIN " . TABLE_PAYMENT . " AS `payment` ON `payment`.`payer_id` = `contestant`.`contestant_id` WHERE status = '1' AND plan = 'cmarx_plan' AND contestant_id <> '%s' LIMIT 10", $this_user);
    } else {
       $sql = sprintf("SELECT `contestant_id`, `contest_id`, `name`, `city`, `votes` FROM " . TABLE_CONTESTANT . " AS contestant WHERE active = '1' AND contestant_id <> '%s' LIMIT 10", $user['id']);
    } 

    $result = dbProcessor($sql, 1); 

    $recommend = '';
    if (isset($result)) {
        shuffle($result);
        $i = 0;
        $recommend .= '
          <div class="card mb-3"> 
            <div class="card-header cloudy-knoxville-gradient">'.$LANG['recommended_c'].'</div>
            <div class="card-body">';

        foreach ($result as $key) {
            $i++;
            if($i == 5) break;

            $userApp->user_id = $key['contestant_id'];
            $data = $userApp->userData(NULL, 1)[0];
            $contest = $cd->getContest(0, $key['contest_id']);
            $photo = ($data['photo']) ? $data['photo'] : 'default.jpg';
            $recommend .= '
                <img src="'.$CONF['url'].'/uploads/faces/'.$photo.'" class="rounded mr-2 float-left" height="50px" width="50px" alt="avatar">
                  <a href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$contest['id'].'&user='.$data['username']).'" class="black-text font-weight-bold text-left" id="profile-url'.$data['id'].'"><p class="font-weight-bold">'.$key['name'].'
                    <br><span class="card-text">'.$contest['title'].'</span>
                  </p></a><hr>';
        }
        $recommend .=' 
            </div>
          </div>';
         
    }
    if ($settings['recommend'] == 1) {
        if ($user['role'] == 'voter') {
             return $recommend;
        }
    } elseif ($settings['recommend'] == 2) {
        if ($user['role'] == 'voter' || $user['role'] == 'contestant') {
             return $recommend;
        }
    } elseif ($settings['recommend'] == 3) {
        return $recommend;
    }
    
}

// Get recommended contests
function recomended_contests() {
    global $LANG, $CONF, $user, $settings;

    $userApp = new userCallback;
    $cd = new contestDelivery;

    $this_user = (isset($user['id'])) ? $user['id'] : 0;

    $sql = $sql = sprintf("SELECT `id`, `title`, `cover`, `creator` FROM " . TABLE_CONTEST . " WHERE recommend = '1' AND active = '1' AND status = '1' AND entry = '1' AND creator <> '%s' LIMIT 10", $user['username'].'i');

    $result = dbProcessor($sql, 1);

    $recommend = '';
    if (!empty($result)) {
        shuffle($result);
        
        $i = 0;
        $recommend .= '
          <div class="card mb-3"> 
            <div class="card-header cloudy-knoxville-gradient">'.$LANG['recommended_cst'].'</div>
            <div class="card-body">';

        foreach ($result as $key) {
            $i++;
            if($i == 5) break;
  
            $photo = ($key['cover']) ? 'cover/contest/'.$key['cover'] : 'faces/default.jpg';
            $recommend .= '
                <img src="'.$CONF['url'].'/uploads/'.$photo.'" class="rounded mr-2 float-left" height="50px" width="auto" alt="avatar">
                  <a href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$key['id']).'" class="black-text font-weight-bold text-left" id="contest-url'.$key['id'].'"><p class="font-weight-bold">'.$key['title'].'
                    <br><span class="card-text">'.$LANG['managed_by'].' '.ucfirst($key['creator']).'</span>
                  </p></a><hr>';
        }
        $recommend .=' 
            </div>
          </div>';
         
    }
    if ($settings['recommend'] == 1) {
        if ($user['role'] == 'voter') {
             return $recommend;
        }
    } elseif ($settings['recommend'] == 2) {
        if ($user['role'] == 'voter' || $user['role'] == 'contestant') {
             return $recommend;
        }
    } elseif ($settings['recommend'] == 3) {
        return $recommend;
    }
}

function recomendations() {
    // Randomize between recommended users and recommended contests
    $random = rand(1, 50);  
    if ($random <= 37) {
        return recomended_users();
    } else {
        return recomended_contests();
    }
    
}

function feature_section($type, $id, $xy=0) {
    global $CONF, $LANG, $userApp, $info_color;
 
    $link = '
        <a target="_blank" href="%s" class="btn btn-md btn-%s">%s
          <i class="fa fa-%s ml-1"></i>
        </a>';
    $link_var = '
        <a target="_blank" href="%s" class="btn btn-sm btn-%s">%s
          <i class="fa fa-%s ml-1"></i>
        </a>';
    $link = $xy==1 ? $link_var : $link;

    if ($type == 0) {
        $data = $userApp->collectUserName(null, 0, $id);
        $image = $CONF['url'].'/uploads/cover/'.$data['cover'];
        $title = $data['fullname'];
        $featured = $LANG['featured'].' '.$LANG['profile'];
        $detail = myTruncate($data['intro'], 300, ' ');
        $link_1 = sprintf($link, $data['profile'], 'primary', 'View Profile', 'user'); 
        $link_2 = sprintf($link, $data['timeline'], 'success', 'Timeline', 'list-alt'); 
    } else {
        $data = $userApp->collectUserName(null, 1, $id);
        $image = $data['cover'];
        $title = $data['title'];
        $featured = $LANG['featured'].' '.$LANG['contest'];
        $detail = myTruncate($data['mainintro'], 120, ' ');
        $link_1 = sprintf($link, $data['safelink'], 'primary', 'View Details', 'eye'); 
        $link_2 = sprintf($link, $data['voting'], 'success', 'Vote', 'thumbs-up');         
    }
    $x_c = $_GET['a'] == 'connector' ? ' text-info' : '';
    $section = '
      <h2 class="h3 mb-4 text-center text-default">'.$featured.'</h2>
      <section class="mt-2 wow fadeIn"> 
        <div class="row"> 
          <div class="col-md-6 mb-4">
            <img src="'.$image.'" class="img-fluid z-depth-1-half" alt=""> 
          </div>
          <div class="col-md-6 mb-4"> 
            <h3 class="h3 mb-3'.$x_c.'">'.$title.'</h3>
            <p>'.$detail.'</p> 

            <hr>
            <div class="d-flex flex-column">
                '.$link_1.'
                '.$link_2.'
            </div>
          </div>  
        </div> 
      </section>';
    return $section;
}

// Contests and user featured on the home page
function home_featured($type=0, $xy=0) {
    global $DB, $CONF, $info_color;

    // Fetch the featured contests
    $sql = "SELECT * FROM " . TABLE_CONTEST . " WHERE status = '1' AND featured = '1' LIMIT 10";
    $contest = dbProcessor($sql, 1);

    // Fetch the featured users
    $sql = "SELECT * FROM " . TABLE_USERS . " WHERE status = '2' AND featured = '1' LIMIT 10";
    $users = dbProcessor($sql, 1); 

    $rand_users = $users; 
    $x = 0;
    // Show the featured contests
    if (!empty($contest)) {
        shuffle($contest); 

        $i = 0; 

        $featured_contest = '';
        $random_contest = feature_section(1, $contest[0]['id'], $xy);
        foreach ($contest as $key) {
            $i++;
            if($i == 4) break;
            $title = $key['title'];
            $intro = myTruncate($key['intro'], 100, ' ');
            $image = $CONF['url'].'/uploads/cover/contest/'.$key['cover'];

            $featured_contest .=
              '<a href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$key['id']).'">
              <div class="col-md-4">
                <div class="card mb-2">
                    <div class="view overlay">
                      <img class="card-img-top" src="'.$image.'"
                        alt="Card image cap" style="display: block; object-position: 50% 10%; width: 100%; height: 20vh; object-fit: cover;">
                      <div class="mask rgba-white-slight"></div>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title">'.$title.'</h4>
                        <p class="card-text">'.$intro.'</p> 
                    </div>
                </div>
              </div></a>';            
        }
        $x = 1;
    } 

    // Show the featured users
    if (!empty($users)) {
        shuffle($users);
        $i = 0; 

        $featured_users = '';
        $random_users = feature_section(0, $users[0]['id'], $xy);
        foreach ($users as $key) {
            $i++;
            if($i == 4) break;
            $name = $key['fname'].' '.$key['lname'];
            $intro = myTruncate($key['intro'], 100, ' ');
            $image = $CONF['url'].'/uploads/faces/'.$key['photo'];

            $featured_users .=
              '<a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$key['username']).'">
              <div class="col-md-4">
                <div class="card mb-2">
                    <div class="view overlay">
                      <img class="card-img-top" src="'.$image.'"
                        alt="Card image cap" style="display: block; object-position: 50% 10%; width: 100%; height: 20vh; object-fit: cover;">
                      <div class="mask rgba-blue-slight"></div>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title">'.$name.'</h4>
                        <p class="card-text">'.$intro.'</p> 
                    </div>
                </div>
              </div></a>';            
        }
        $x = 1;
    }
    $class_1 = ''; $class = '';
    (isset($featured_contest)) ? $class = 'active' : $class_1 = 'active'; 
    if (isset($featured_contest)) {
        $featured_contest = '
        <div class="carousel-item '.$class.'"> 
            '.$featured_contest.'
        </div>';
    } else {
        $featured_contest = '';
    }
    if (isset($featured_users)) {
        $featured_users = '
        <div class="carousel-item '.$class_1.'">
            '.$featured_users.'
        </div>';
    } else {
        $featured_users = '';
    }
    $feature ='
    <hr class="mt-5">
        <div id="home-featured" class="carousel slide carousel-multi-item" data-ride="carousel" style="min-width: 100%;">

          <div class="controls-top">
            <a class="btn-floating" href="#home-featured" data-slide="prev"><i class="fa fa-chevron-left"></i></a>
            <span class="h3 text-info">FEATURED</span>
            <a class="btn-floating" href="#home-featured" data-slide="next"><i class="fa fa-chevron-right"></i></a>
          </div>

          <ol class="carousel-indicators">
            <li data-target="#home-featured" data-slide-to="0" class="active"></li>
            <li data-target="#home-featured" data-slide-to="1"></li> 
          </ol> 

          <div class="carousel-inner" role="listbox">
            '.$featured_contest.'
            '.$featured_users.'
          </div> 

        </div>
    <hr class="mb-5">';
    $return = '';
    if ($type) {
        if ($x) {
            if (isset($random_contest) && isset($random_users)) {
                $rand = rand(1,3);
                $ft['1'] = $ft['2'] = $random_contest;
                $ft['3'] = $random_users;
                $return = $ft[$rand];
            } elseif (isset($random_contest)) {
                $return = $random_contest;
            } elseif (isset($random_users)) {
                $return = $random_users;
            }
        }
    } else {
        $return = ($x) ? $feature : '<hr class="m-5">';
    }
    return $return;
}

function seo_plugin($image, $twitter, $facebook, $desc, $title) {
    global $CONF, $PTMPL, $settings, $site_image;

    $twitter = ($twitter) ? $twitter : $settings['site_name'];
    $facebook = ($facebook) ? $facebook : $settings['site_name'];
    $title = ($title) ? $title.' ' : '';
    $titles = $title.'On '.$settings['site_name'];
    $image = ($image) ? $image : $site_image;
    $alt = ($title) ? $title : $titles;
    $desc = rip_tags(strip_tags(stripslashes($desc)));
    $desc = strip_tags(myTruncate($desc, 350));
    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    $plugin = '
    <meta name="description" content="'.$desc.'"/>
    <link rel="canonical" href="'.$url.'" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="'.$titles.'" />
    <meta property="og:url" content="'.$url.'"/>
    <meta property="og:description" content="'.$desc.'" />
    <meta property="og:site_name" content="'.$settings['site_name'].'" />
    <meta property="article:publisher" content="https://www.facebook.com/'.$settings['site_name'].'" />
    <meta property="article:author" content="https://www.facebook.com/'.$facebook.'" />
    <meta property="og:image" content="'.$image.'" />
    <meta property="og:image:secure_url" content="'.$image.'" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="628" />
    <meta property="og:image:alt" content="'.$alt.'" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:description" content="'.$desc.'" />
    <meta name="twitter:title" content="'.$titles.'" />
    <meta name="twitter:site" content="@'.$settings['site_name'].'" />
    <meta name="twitter:image" content="'.$image.'" />
    <meta name="twitter:creator" content="@'.$twitter.'" />';
    return $plugin;
}

function gallery_chips($user_id) {
    global $LANG, $user;

    // Fetch the images
    $userApp = new userCallback;
    $gallery = $userApp->user_gallery($user_id, 1);

    // Get the users data
    $userApp->user_id = $user_id;
    $uid = $userApp->userData(NULL, 1)['0'];

    // Loop through the images
    $chip = '';
    $i = 0;
    if ($gallery) {
        foreach ($gallery as $rs) {
            $i++;

            // Allow user or creator if account is unclamied to delete the image
            $delete = '';
            if ($user_id == $user['id'] || ($user['id'] == $uid['creator']) && $uid['claimed'] == 0) {
                $delete = '<i class="close fa fa-times" onclick="delete_the('.$rs['id'].', 7)"></i>';
            }

            // Show the image
            $chip .= '
              <div class="chip chip-lg info-color white-text" id="chip_'.$rs['id'].'">
                <img src="'.$CONF['url'].'/uploads/gallery/'.$rs['photo'].'" alt="'.$LANG['gallery_chip'].' '.$rs['id'].'">
                '.$delete.'
                '.$LANG['gallery_chip'].' '.$i.'
              </div>';            
        }
    }
    return $chip;
}

function gallery_cards() {
    global $LANG, $CONF, $marxTime, $profiles, $user, $premium_status;

    $userApp = new userCallback;
    $cd = new contestDelivery;
    $photos_cards = $userApp->user_gallery($profiles['id'], 1);

    $badge = ($premium_status) ? badge(0, $premium_status['plan'], 2) : '';
    $fullname = realName($profiles['username'], $profiles['fname'], $profiles['lname']).' '.$badge;

    if ($profiles['photo']) {
        $pphoto = $CONF['url'].'/uploads/faces/'.$profiles['photo'];
    } else {
        $pphoto = $CONF['url'].'/uploads/faces/default.jpg';
    }

    // Set the users location
    if ($profiles['state'] && $profiles['country']) {
        $location = $profiles['state'].', '.$profiles['country'];
    } elseif ($profiles['state']) {
        $location = $profiles['state'];
    } else {
        $location = $profiles['country'];
    }
    $location = (isset($location)) ? '<small><span><i class="fa fa-map-pin"></i> '.$location.'</span></small>' : '';

    // Show Images
    $photos = '';
    $cards = '';
    $delete = '';
    if ($photos_cards) {
        foreach ($photos_cards as $image) {
            $desc = '';
            if ($image['description']) {
                $get_desc = myTruncate($image['description'], 120);
                $more = mb_strlen($image['description']) > mb_strlen($get_desc) ? '<a onclick="readmore('.$image['id'].', 1)" class="text-info"> '.$LANG['read_more'].'</a></div>' : '';
                $desc .= '<div class="p-2" id="description_'.$image['id'].'">'.$get_desc.$more; 
            }
            if ($user['id'] == $profiles['id']) {
                $delete = ' 
                <a class="dropdown-item" onclick="delete_the('.$image['id'].', 7)">'
                .$LANG['delete'].' <i class="fa fa-trash p-1"></i> </a>';
            } else {
                $delete = '<div class="px-3">'.$LANG['hello'].'</div>';
            }

            $user_profile = permalink($CONF['url'].'/index.php?a=profile&u='.$profiles['username']);

            // Check Users current contests and show a vote link
            $cd->contestant_id = $image['uid'];
            $contest = $cd->getUsersCurrent();
            $vote_button = '';
            if ($contest) { 
                foreach ($contest as $rs => $key) { 
                    $requested = $cd->getContest(0, $key['contest_id']);  
                    if ($requested['status']) { 
                        $vote_button = '
                        <a class="dropdown-item" href="'.permalink($CONF['url'].'/index.php?a=voting&id='.$key['contest_id'].'&user='.$profiles['username']).'">'.$LANG['vote'].' on '.$requested['title'].'</a>';            
                    }   
                }
            } 

            $photos .=
            '<span id="set-message_'.$image['id'].'"></span>
            <div class="col-md-6 mb-3" id="photo_'.$image['id'].'">
                <div class="border z-depth-1">
                  <img src="'.$CONF['url'].'/uploads/gallery/'.$image['photo'].'" class="img-fluid"
                    alt="Gallery image">
                    '.$delete.'
                  '.$desc.'
                </div>
            </div>';  

            $cards .= '
            <div class="col-lg-12" id="photo_'.$image['id'].'">
              <div class="cardbox shadow-md bg-light">
             
                <div class="cardbox-heading">
                   
                  <div class="dropdown float-right">
                   <button class="btn btn-flat btn-flat-icon" type="button" data-toggle="dropdown" aria-expanded="false">
                    <em class="fa fa-ellipsis-h"></em>
                   </button>
                   <div class="dropdown-menu dropdown-scale dropdown-menu-right" role="menu" style="position: absolute; transform: translate3d(-136px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                    '.$delete.'
                    '.$vote_button.' 
                   </div>
                  </div> 
                  <div class="media m-0">
                   <div class="d-flex mr-3">
                    <a href="'.$user_profile.'"><img class="img-fluid rounded-circle" src="'.$pphoto.'" alt="User"></a>
                   </div>
                   <div class="media-body">
                    <p class="m-0"><a href="'.$user_profile.'" class="blue-grey-text">'.$fullname.'</a></p>
                    '.$location.'
                    <small><span><i class="fa fa-clock-o"></i> '.$marxTime->timeAgo(strtotime($image['date'])).'</span></small>
                   </div>
                  </div> 
                </div> 
              
                <div class="cardbox-item d-flex justify-content-center">
                  <img class="img-fluid" src="'.$CONF['url'].'/uploads/gallery/'.$image['photo'].'" alt="Image">
                </div> 
                '.$desc.'  
                
              </div> 

            </div>';                          
        }
        $photo_rows = $cards;
    } else {
           $photo_rows = '
            <div class="col-lg-12" id="photo">
              <div class="cardbox shadow-md bg-light">
             
                <div class="cardbox-heading">
                    '.$fullname.$LANG['no_photos'].' 
                </div>
                <div class="cardbox-item">
                  <img class="img-fluid" src="'.$CONF['url'].'/uploads/faces/default.jpg" alt="Image"> 
                </div> 
              </div>
            </div>';       
    }

    return $photo_rows;    
}


/*
* Display the users posts in the timeline
*/
function timeline_cards() {
    global $LANG, $CONF, $marxTime, $profiles, $user, $premium_status, $userApp;

    $social = new social;
    $cd = new contestDelivery;
    $action = new actions;

    $photos_cards = $social->timelines($profiles['id'], 0, true);

    // Check if this user is a follower
    $follower = $social->follow($profiles['id'], 1);

    // Set the users location
    if ($profiles['state'] && $profiles['country']) {
        $location = $profiles['state'].', '.$profiles['country'];
    } elseif ($profiles['state']) {
        $location = $profiles['state'];
    } else {
        $location = $profiles['country'];
    }
    $location = (isset($location)) ? '<small><span><i class="fa fa-map-pin"></i> '.$location.'</span></small>' : '';

    // if there is nothinf to show
    $nothing_here = '
    <div class="col-lg-12" id="photo">
      <div class="cardbox shadow-md bg-light">
        <div class="cardbox-item h1 peach-gradient text-center text-white p-4">
          '.$LANG['nothing_to_show'].'
        </div>
      </div>
    </div>';

    // Share the post
    if (isset($_GET['share'])) { 
        $share = $social->timelines($_GET['share'], 1);
        $array = array('photo' => $share['post_photo'], 'user_id' => $share['user_id'], 'post_id' => $share['pid'], 
            'share_id' => $user['id'], 'post' => $share['text'], 'location' => $share['location'],'privacy' => $share['privacy']); 
        $social->array = $array;
        $post_it = $social->timelines(null, 2);
        $social->notifier($user['id'], $share['user_id'], 0, $share['pid'], $mail=0);
    }

    // Show The posts
    $photos = '';
    $cards = '';
    $delete = ''; 
    if ($photos_cards) {
        foreach ($photos_cards as $post) {

            // Check if you follow username
            $follower = $social->follow($post['user_id'], 1); 

            $post_class = !$post['post_photo'] ? 'aqua-gradient h1-responsive text-white p-3 text-center' : 'm-2';
            $desc = '';
            if ($post['text']) {
                $get_desc = myTruncate($action->decodeMessage($post['text'], 1), 120);
                $more = mb_strlen($post['text']) > mb_strlen($get_desc) ? '<a href="'.permalink($CONF['url'].'/index.php?a=timeline&u='.$post['username'].'&read='.$post['pid']).'" class="text-info"> '.$LANG['read_more'].'</a>' : '';

                $desc .= '<div class="p-2 '.$post_class.'" id="description_'.$post['pid'].'">'.$get_desc.$more.'</div>'; 
            } 

            // Decide if this is an original or shared post
            $author_link = '<a href="%s" class="blue-grey-text">%s</a>';
            if ($post['share_id']) {
                $s = $userApp->collectUserName(null, 0, $post['share_id']);
                $u = $userApp->collectUserName(null, 0, $post['user_id']);
                $sharer = $s['user_id'] == $user['id'] ? 'You' : $s['fullname'];
                $poster = $u['user_id'] == $user['id'] ? 'your' : $u['fullnamex'];
                $post_link = '<a href="'.permalink($CONF['url'].'/index.php?a=timeline&u='.$u['username'].'&read='.$post['post_id']).'" class="blue-grey-text">'.lcfirst($LANG['post']).'</a>';
                $author = sprintf($author_link, $s['profile'], $sharer).' '.lcfirst($LANG['shared']).' '.sprintf($author_link, $u['profile'], $poster).' '.$post_link;
                $auto_photo = $s['photo'];
            } else {
                $u = $userApp->collectUserName(null, 0, $post['user_id']); 
                $author = sprintf($author_link, $u['profile'], $u['fullname']);
                $auto_photo = $post['photo'];
            }
            
            // Set the photo
            if ($post['photo']) {
                $pphoto = $CONF['url'].'/uploads/faces/'.$auto_photo;
            } else {
                $pphoto = $CONF['url'].'/uploads/faces/default.jpg';
            }

            $post_photo = $post['post_photo'] ? '<img class="img-fluid" src="'.$CONF['url'].'/uploads/gallery/'.$post['post_photo'].'" alt="post_photo" id="post_photo_'.$post['pid'].'">' : '';  

            if ($user['id'] == $post['user_id'] || $user['id'] == $post['share_id']) {
                $delete = ' 
                <a class="dropdown-item" onclick="delete_the('.$post['pid'].', 8)">'
                .$LANG['delete'].' <i class="fa fa-trash p-1"></i> </a>';
            } else {
                $delete = '<div class="px-3">'.$LANG['hello'].'</div>';
            }

            $stop_follow = $follower['follower_id']==$user['id'] ? '<a class="dropdown-item" onclick="relate('.$post['user_id'].', 1)">'.$LANG['stop_follow'].'</a>' : '';

            $user_profile = permalink($CONF['url'].'/index.php?a=profile&u='.$post['username']);

            // privacy icon
            $privacy_icon = $post['privacy']=='1' ? 'users' : ($post['privacy']=='0' ? 'user' : 'globe');

            // Check Likes
            $social->content_type = 'post'; 
            $social->content = $post['pid'];
            $liked = $social->like(0, 1);
            $all_likes = $social->like(0, 2);

            $social->limit = 5;
            $limit_likes = $social->like(0, 2);

            // See if user liked this post
            $t_class = $liked['user_id'] == $user['id'] ? 'text-info' : '';
            $likes_count = count($all_likes)>1 ? count($all_likes). ' '.$LANG['likes'] : count($all_likes). ' '.$LANG['likes'];

            //Show users who liked
            if ($limit_likes) {
                $liking = '';
                foreach ($limit_likes as $key) {
                    $userApp->user_id = $key['user_id'];
                    $lk_user = $userApp->userData(NULL, 1)[0];
                    $pp = $lk_user['photo'] ? $lk_user['photo'] : 'default.jpg';
                    $lk_profile = permalink($CONF['url'].'/index.php?a=profile&u='.$lk_user['username']);
                    $liking .= '<li><a href="'.$lk_profile.'"><img src="'.$CONF['url'].'/uploads/faces/'.$pp.'" class="img-fluid rounded-circle" alt="User'.$lk_user['username'].'"></a></li>';
                }
            }
            $liker = count($all_likes)>0 ? $liking : '';

            //Like Button
            $like_action = $liked['user_id'] == $user['id'] ? 3 : 2;
            
            // Show count comments
            $cd->post_id = $post['pid'];
            $get_comments = $cd->doComments(1, 'post', 3);

            // View full post link
            $full_post = '<a href="'.permalink($CONF['url'].'/index.php?a=timeline&u='.$post['username'].'&read='.$post['pid']).'"  class="dropdown-item">'.$LANG['full_post'].'</a>';

            // Share post on timeline
            $share_post = '<a href="'.permalink($CONF['url'].'/index.php?a=timeline&u='.$user['username'].'&share='.$post['pid']).'"  class="dropdown-item">'.$LANG['share_post'].'</a>';

            // Post comments link
            $post_comments = permalink($CONF['url'].'/index.php?a=timeline&u='.$post['username'].'&read='.$post['pid']);

            $cards .= '<div id="set-messagez_'.$post['pid'].'"></div>
            <div class="col-lg-12" id="photo_'.$post['pid'].'" style="min-width: 100%;"> 
              <div class="cardbox shadow bg-white"> 
                <div class="cardbox-heading"> 
                  <div class="dropdown float-right">
                    <button class="btn btn-flat btn-flat-icon" type="button" data-toggle="dropdown" aria-expanded="false">
                      <em class="fa fa-ellipsis-h"></em>
                    </button>
                    <div class="dropdown-menu dropdown-scale dropdown-menu-right" role="menu" style="position: absolute; transform: translate3d(-136px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                      '.$delete.'
                      '.$stop_follow.'
                      '.$full_post.'
                      '.$share_post.' 
                    </div>
                  </div> 
                  <div class="media m-0">
                   <div class="d-flex mr-3">
                  <a href="'.$user_profile.'"><img class="img-fluid rounded-circle" src="'.$pphoto.'" alt="User"></a>
                   </div>
                   <div class="media-body" id="post-writer">
                    <p class="m-0">'.$author.'</p>
                    '.$location.' 
                    <small><span><i class="fa fa-clock-o "></i> '.$marxTime->timeAgo(strtotime($post['date']), 1).'</span><i class="fa fa-'.$privacy_icon.'"></i></small>
                   </div>
                  </div> 
                </div> 
                  
                '.$desc.'
                <div class="cardbox-item d-flex justify-content-center">
                    '.$post_photo.'
                </div> 

                <img class="d-none" src="'.$pphoto.'" alt="post_photo" id="post_photo_alt'.$post['pid'].'">
                <input type="hidden" value="'.$post_comments.'" id="post_share_url_'.$post['pid'].'">
                <input type="hidden" value="'.myTruncate($post['text'], 35).'" id="post_share_title_'.$post['pid'].'">

                <div class="cardbox-base">
                  <ul class="float-right">
                    <li><a href="'.$post_comments.'#comment"><i class="fa fa-comments"></i></a></li>
                    <li><a href="'.$post_comments.'#comment"><em class="mr-5">'.count($get_comments).'</em></a></li>
                    <li><a onclick="shareModal(4, '.$post['pid'].')"><i class="fa fa-share-alt"></i></a></li>  
                  </ul>
                   
                  <ul>
                    <li><a onclick="relate('.$post['pid'].', '.$like_action.', '.$post['user_id'].', 1)" id="like_btn_'.$post['pid'].'"><i class="fa fa-thumbs-up '.$t_class.'" id="thumb_'.$post['pid'].'"></i></a></li>
                    '.$liker.'
                    <li>
                        <a data-toggle="modal" onclick="relate('.$post['pid'].', 4, '.$post['user_id'].', 1)" id="modal_btn_'.$post['pid'].'" data-target="#showLikesModal">
                            <span id="like_count_'.$post['pid'].'">'.$likes_count.'</span>
                        </a>
                    </li>
                  </ul>        
                </div> 

                <div class="d-none d-sm-block">
                    <div class="text-center border-bottom" id="comment_block_'.$post['pid'].'"></div> 
                    <div class="cardbox-comments">
                      <span class="comment-avatar float-left">
                        <a href="'.$user_profile.'"><img class="rounded-circle" src="'.$pphoto.'" alt="..."></a>                    
                      </span>
                      <div class="search">'.$user['id'].'
                        <input name="comment" id="comment_'.$post['pid'].'" placeholder="'.$LANG['write_a_comment'].'" type="text">
                        <button type="button" onclick="write_real_comment('.$user['id'].', '.$post['user_id'].', '.$post['pid'].', 2)"><i class="fa fa-paper-plane"></i></button>
                      </div>
                    </div>        
                </div> 
              </div> 
            </div> 
            '; 

            // Show the post by its privacy setting
            if ($post['privacy'] == 2) {
                $cards = $cards;
            } elseif ($post['privacy'] == 1) {
                if ($follower['leader_id'] == $post['user_id']) {
                    $cards = $cards;
                } else {
                    $cards = '';
                }  
            } elseif ($post['privacy'] == 0) {
                if ($user['id'] == $post['user_id']) {
                    $cards = $cards;
                } else {
                    $cards = '';
                } 
            }       
        }
        $photo_rows = $cards;
    } else {
           $photo_rows = $nothing_here;       
    }

    return $photo_rows;    
}
/**
 *this header is show on user related pages like timelines
 */
function profile_header($id, $page=0) {
    global $LANG, $CONF, $userApp;

    // Fetch users data
    $_data = $userApp->collectUserName(null, 0, $id);
    $cover = $CONF['url'].'/uploads/cover/'.$_data['cover'];
    $photo = $CONF['url'].'/uploads/faces/'.$_data['photo'];

    $timeline = permalink($CONF['url'].'/index.php?a=timeline&u='.$_data['username']);
    $gallery = permalink($CONF['url'].'/index.php?a=gallery&u='.$_data['username']);
    $followers = permalink($CONF['url'].'/index.php?a=followers&followers='.$_data['user_id']);
    $following = permalink($CONF['url'].'/index.php?a=followers&following='.$_data['user_id']);
    $contest = permalink($CONF['url'].'/index.php?a=contest');

    // Set the active tabindex
    $m1 = $m2 = $m3 = $t0 = $t1 = $t2 = $t3 = '';
    if ($page == 0) {
        $t0 = 'active';
    } elseif ($page == 1) {
        $t1 = 'active'; 
    } elseif ($page == 2) {
        $t2 = 'active'; 
    } elseif ($page == 3 || $page == 4 || $page == 5) {
        $t3 = 'active'; 
        $m1 = $page == 5 ? 'active' : ''; 
        $m2 = $page == 4 ? 'active' : ''; 
        $m3 = $page == 3 ? 'active' : ''; 
    }

    // Social dropdown
    $social = '
    <div class="dropdown-menu dropdown-menu-right">
      <a class="dropdown-item '.$m1.'" href="'.$_data['message'].'"><i class="fa fa-comments-o"></i> '.$LANG['messenger'].'</a>
      <a class="dropdown-item '.$m2.'" href="'.$following.'"><i class="fa fa-level-up"></i> '.$LANG['following'].'</a> 
      <a class="dropdown-item '.$m3.'" href="'.$followers.'"><i class="fa fa-level-down"></i> '.$LANG['followers'].'</a> 
      <div class="dropdown-divider"></div>
      <a class="dropdown-item '.$m1.'" href="'.$contest.'">'.$LANG['contests'].'</a>
    </div>';

    $data = '
    <div class="mt-1 mb-3" style="min-width: 100%">  
      <div class="view" style="background-image: url('.$cover.'); background-repeat: no-repeat; background-size: cover; background-position: center center; min-height: 370px;">
        <div class="mask rgba-indigo-slight">
          <div class="container h-100 d-flex justify-content-center align-items-center">
            <div class="row pt-2 mt-3">
              <div class="col-md-12 mb-3">
                <div class="intro-info-content text-center">
                  <h1 class="unique-color white-text mb-2 wow fadeInDown" data-wow-delay="0.3s">'.$_data['name'].'
                  </h1>
                  <h7 class="text-uppercase unique-color-dark white-text mb-5 mt-1 font-weight-bold wow fadeInDown" data-wow-delay="0.3s">'.$_data['intro'].'</h7>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>    
    </div>

    <div class="container" style="min-width: 100%;"> 
      <section class="text-center team-section">
 
        <div class="row text-center">
 
          <div class="col-md-12 mb-4" style="margin-top: -100px;">

            <div class="avatar mx-auto">
              <img src="'.$photo.'" class="img-fluid rounded-circle z-depth-1" alt="Profile Photo">
            </div>  

            <ul class="nav nav-pills nav-justified border white z-depth-1 my-2 p-2">
              <li class="nav-item">
                <a class="nav-link '.$t0.'" href="'.$_data['profile'].'">'.$LANG['profile'].'</a>
              </li>
              <li class="nav-item">
                <a class="nav-link '.$t1.'" href="'.$timeline.'">'.$LANG['timeline'].'</a>
              </li>
              <li class="nav-item">
                <a class="nav-link '.$t2.'" href="'.$gallery.'">'.$LANG['_gallery'].'</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle '.$t3.'" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">'.$LANG['social'].'</a>
                '.$social.'
              </li>
            </ul>
          </div>  

        </div> 

      </section> 
    </div>';
    return $data;
}


/*
* Generate a modal menu
*/
function modal($modal, $content, $title, $size='', $footer='', $extra='') {
    // Always call the extra variable starting with a space
    // Size 1: Small
    // Size 2: Large
    // Size 3: Fluid

    if ($size == 1) {
        $size = ' modal-sm';
    } elseif ($size == 2) {
        $size = ' modal-lg';
    } elseif ($size == 3) {
        $size = ' modal-fluid';
    }
    $footer_content = $footer ? '<div class="modal-footer">'.$footer.'</div>' : '';
    $modal_menu ='
    <div class="modal fade" id="'.$modal.'Modal" tabindex="-1" role="dialog" aria-labelledby="'.$modal.'ModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered'.$size.$extra.'" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="'.$modal.'ModalLabel">'.$title.'</h5>
            <button type="button" class="close" aria-label="Close" onclick="modal_destroyer(\''.$modal.'Modal\')">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            '.$content.'
          </div>
            '.$footer_content.'
        </div>
      </div>
    </div>';
    return $modal_menu;
}

/**
 * User account management menu
 */ 
function barMenu(){
    global $LANG, $PTMPL, $CONF, $user, $settings; 

    if ($user) { 
        $droplink = $divider = '';
         
        $links = array( 
            array('update', $LANG['user_settings']),
            array('credit', $LANG['passcredit']), 
            array('bounty', $LANG['bounty']),
            ($user['role'] !== 'agency' ? array('contest', $LANG['contests']) : array('contest&u='.$user['username'], $LANG['my_contests'])),
            array('account&votes='.$user['id'], $LANG['my_votes']), 
            array('profile&u='.$user['username'], $LANG['profile']),
            array('timeline', $LANG['timeline']),
            array('gallery&u='.$user['username'], $LANG['gallery'])
        );

        //print_r($links);
        foreach ($links as $rs => $key) {
            if($key) {
                $droplink .= $divider.'<li class="nav-item border border-info m-1 blue-grey lighten-5 flex-fill"><a class="nav-link" href="'.permalink($CONF['url'].'/index.php?a='.$key[0].'">'.$key[1]).'</a></li>';
            } 
        }
    return $droplink;
    }   
} 

/*
 * Fetch and update data from the API
 */
function fetch_api($type=0, $x=0) {
    if ($type == 0) {
        return dbProcessor(sprintf("SELECT token FROM ".TABLE_API." WHERE `server` = '%s'", $_SERVER['HTTP_HOST']), 1)[0]['token'];
    } elseif ($type == 1) {
		$check = dbProcessor(sprintf("SELECT token FROM ".TABLE_API." WHERE `server` = '%s'", $_SERVER['HTTP_HOST']), 1)[0]; 
        if (isset($_SESSION[oworgi('bGljZW5jZQ==')])) {
            $sql = sprintf("INSERT INTO ".TABLE_API." (`token`, `server`) VALUES ('%s', '%s')", $_SESSION[oworgi('bGljZW5jZQ==')], $_SERVER['HTTP_HOST']);
            $check['token'] !== $_SESSION[oworgi('bGljZW5jZQ==')] ? dbProcessor($sql, 0, 1) : '';
            unset($_SESSION[oworgi('bGljZW5jZQ==')]);
        }
    } else {
        $ret = get_isabi(fetch_api());
        if ($ret == 0) {
            if (file_exists(__DIR__.oworgi('Jy9jb25maWcucGhwJw=='))) {
                //unlink(__DIR__.oworgi('Jy9jb25maWcucGhwJw=='));
                //unlink(__DIR__.oworgi('Li4vLi4vaW5jbHVkZXMv'));
            }
        }
    }
}

/*
* Show the users contact and address info in profile page
*/
function extra_userData($user) {
    global $LANG;
    if (filter_var($user, FILTER_VALIDATE_INT)) {
        $sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE id = '%s'", $user);
    } else {
        $sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username = '%s'", $user);
    } 
    $r = dbProcessor($sql, 1)[0]; 
    $phone = (!empty($r['phone'])) ? '+ '.$r['phone'].'<br>' : '';

    $set_details = '';

    if (!empty($r['address'])) {
        $address = '
          <div class="col-md-6 p-3 aqua-gradient">
            <h3 class="white-text"><i class="fa fa-map-marker red-text"></i> '.$LANG['address'].'</h3>
            <p class="font-weight-bold">'.$r['address'].'</p>
          </div>';   
        $col = 'col-md-6';   
    } else {
        $address = '';
        $col = 'col-md-12';   
    }

    $telema = '
      <div class="'.$col.' p-3 blue-gradient">
        <h3 class="white-text"><i class="fa fa-phone"></i> '.$LANG['contact'].'</h3>
        <p class="font-weight-bold">
          '.$phone.'
          '.$r['email'].'
        </p>
      </div>';

    $set_details .= '
    <div class="row mb-3 z-depth-1">
        '.$address.$telema.'
    </div>';

    return $set_details;
}

/* 
* If query returns no results
*/
function empty_results($type=0) {
    global $LANG;
    if ($type == 1) {
        // No users
        $y = mb_strtolower($LANG['profile']);
        $i = 'user-times';
    } elseif ($type == 2) {
        // No query
        $y = $LANG['start_type'];
        $i = 'search';
    } else {
        // No results
        $y = mb_strtolower($LANG['results']);
        $i = 'times-circle-o'; 
    }
    $string = 'No %s found';
    $string = $type!==2 ? sprintf($string, $y) : $y;
    $c = $type!==2 ? 'danger' : 'info';
    $message = '
    <div class="container p-5 m-3 border border-info rounded grey lighten-4 text-center">
      <div class="h4">
        <i class="fa fa-'.$i.' text-'.$c.' fa-lg"></i>
        <div class="text-info mt-2">'.$string.'</div>
      </div>
    </div>';
    return $message;
}

/* 
* Find tags in a string
*/
function tag_finder($str, $x=0) {
    if ($x == 1) {
        // find an @
        if (preg_match('/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i', $str)) {
           return 2;
        } 
    } else {
        // find a #
        if (preg_match('/(^|[^a-z0-9_\/])#(\w+)/u', $str)) {
           return 1;
        }
    }
    return false;
}

function oworgi($str){
    return base64_decode($str);
}

/* 
* Search and explore filter
*/
function filters($x, $q=null) {
    global $CONF, $LANG;
    $filters = array('contests' => 'Contests', 'contest_type' => 'Contest type', 'users' => 'Users',
        'country' => 'Country', 'gender' => 'Gender', 'posts' => 'Posts', 'unset' => 'Reset');
    $q = $q ? '&query='.$q : '';
    $values = '';   
    foreach ($filters as $key => $val) {
        $data = $drop = '';
        $link = '#';
        if ($key == 'gender') {
            $data = ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="genderDrop"';
            $l = permalink($CONF['url'].'/index.php?a=search&filters='.$key.'&query=%s');         
            $drop = '
              <div class="dropdown-menu dropdown-primary dropdown-menu-right">
                <a class="dropdown-item" href="'.sprintf($l, 'male').'">'.$LANG['male'].'</a>
                <a class="dropdown-item" href="'.sprintf($l, 'female').'">'.$LANG['female'].'</a> 
                <a class="dropdown-item" href="'.sprintf($l, 'other').'">'.$LANG['others'].'</a> 
              </div>';
        } elseif ($key == 'contest_type') {
            $_type = array('pageant' => $LANG['pageant'], 'photo' => $LANG['photo_contest'], 
                'election' => $LANG['election'], 'other' => $LANG['other_contest']);
            $ml = '';
            foreach ($_type as $k => $v) {
                $l = permalink($CONF['url'].'/index.php?a=search&filters='.$key.'&query=%s');
                $ml .= '<a class="dropdown-item" href="'.sprintf($l, $k).'">'.$v.'</a>';
            }
            $data = ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="contestDrop"'; 
            $l = permalink($CONF['url'].'/index.php?a=search&filters='.$key.'&query=%s');         
            $drop = '
              <div class="dropdown-menu dropdown-primary">
                '.$ml.' 
              </div>';
        } else {
            $link = permalink($CONF['url'].'/index.php?a=search&filters='.$key.$q);            
        }
        $class = $x == $key ? ' text-success active' : '';
        $values .= '
        <li class="nav-item">
          <a class="nav-link'.$class.'" href="'.$link.'"'.$data.'>'.$val.' <i class="fa fa-angle-double-right text-info"></i></a>
          '.$drop.'
        </li>';
    }
    return $values;
}

/* 
* Set the extra form fields for login
*/
function extra_fields() {
    global $CONF, $PTMPL, $LANG, $settings, $referrer;

    $fbconnect = $recaptcha = $invite_code = $phone_number = '';
    if($settings['fbacc']) {
        // Generate a session to prevent CSFR attacks
        if (!isset($_SESSION['state'])) {
            $_SESSION['state'] = md5(uniqid(rand(), TRUE)); 
        }
        // Facebook Login Url
        $fbconnect = '<a class="btn btn-fb" href="https://www.facebook.com/dialog/oauth?client_id='.$settings['fb_appid'].'&redirect_uri='.$CONF['url'].'/connection/connect.php?facebook=true&state='.$_SESSION['state'].'&scope=public_profile,email">Facebook <i class="fa fa-facebook ml-1"></i></a>';
    }
    $captcha_url = '/includes/vendor/goCaptcha/goCaptcha.php?gocache='.strtotime('now');
    if($settings['captcha']) {
        // Captcha
        $recaptcha = ' 
            <div class="md-form form-sm d-flex">
                <i class="fa fa-clock-o prefix"></i>
                <input name="recaptcha" type="text" id="recaptcha2" class="form-control form-control-sm" autocomplete="off">
                <label for="username">'.$LANG['recaptcha'].'</label>
                <span class="ml-2" id="recaptcha-img"><img width="120px" src="'.$CONF['url'].$captcha_url.'" /></span>
            </div> ';
    }  
    if ($settings['invite_only']) {
        $invite_code_post = (isset($_POST['invite_code'])) ? $_POST['invite_code'] : '';
        $info = ($settings['fbacc']) ? '<small class="d-flex justify-content-center red-text border grey lighten-4 p-1">'.$LANG['invite_only_info'].'</small>' : '';
        $invite_code = '
        '.$info.'
        <div class="md-form form-sm mb-3">
            <i class="fa fa-key prefix"></i>
            <input name="invite_code" type="text" id="invite_code" class="form-control form-control-sm" autocomplete="off" value="'.$invite_code_post.'">
            <label for="invite_code">'.$LANG['invite_code'].'</label> 
        </div>';
    }
    if ($settings['activation'] == 'phone') { 
        $_phone = isset($_POST['phone']) ? $_POST['phone'] : '+';
        $phone_number = ' 
        <div class="md-form form-sm mb-3">
            <i class="fa fa-phone prefix"></i>
            <input name="phone" type="text" id="phone" class="form-control form-control-sm" value="'.$_phone.'">
            <label for="phone">'.$LANG['phone_number'].'</label> 
        </div>';
    } 

    $fields =  
        array('fbconnect' => $fbconnect, 'recaptcha' => $recaptcha, 'invite_code' => $invite_code,
            'phone_number' => $phone_number);
    return $fields;
}

function connector_card($type = 0, $referrer = null) {
    global $CONF, $PTMPL, $LANG, $settings;

    $extra_ = extra_fields();
    $post_user = isset($_POST['username']) ? $_POST['username'] : '';
    $post_email = isset($_POST['email']) ? $_POST['email'] : '';
    $forgot_link = permalink($CONF['url'].'/index.php?a=recovery');

    $login_form = '
    <form> 
      <div class="md-form form-sm mb-3">
          <i class="fa fa-envelope prefix"></i>
          <input name="username" type="text" id="username" class="form-control form-control-sm {$invalid1}" data-validation="length alphanumeric" data-validation-length="min6" value="'.$post_user.'">
          <label for="username">'.$LANG['username'].'</label> 
      </div>

      <div class="md-form form-sm mb-3">
          <i class="fa fa-lock prefix"></i>
          <input name="password" type="password" id="password" class="form-control form-control-sm {$einvalid1}" data-validation="required length" data-validation-length="min6"> 
          <!-- (<span id="maxlength">50</span> characters left) -->
          <label for="password">'.$LANG['password'].'</label> 
      </div> 
      
      <div > 
        <div class="fmd-form form-check">
          <input name="remember" type="checkbox" class="form-check-input" id="meRemember">
          <label class="form-check-label" for="meRemember">'.$LANG['remember'].'</label>
        </div>
      </div>                              
      <div class="text-center mt-1">
        '.$extra_['fbconnect'].'
        <button name="login" id="login-btn" type="button" class="btn btn-info" onclick="connector(0, \''.$referrer.'\')">'.$LANG['login'].'<i class="fa fa-sign-in ml-1"></i></button>
      </div>
    </form>';

    $signup_form = '
    <form>
      '.$extra_['invite_code'].' 

      <div class="md-form form-sm mb-3">
          <i class="fa fa-user prefix"></i>
          <input name="username" type="text" id="username2" class="form-control form-control-sm" data-validation="required length" data-validation-length="min5" value="'.$post_user.'">
          <label for="username2">'.$LANG['username'].'</label>       
      </div>

      <div class="md-form form-sm mb-3">
          <i class="fa fa-envelope prefix"></i>
          <input name="email" type="email" id="email2" class="form-control form-control-sm" data-validation="email" value="'.$post_email.'">
          <label for="email2">'.$LANG['email'].'</label> 
      </div>

      '.$extra_['phone_number'].'

      <div class="md-form form-sm mb-3">
          <i class="fa fa-lock prefix"></i>
          <input name="password" type="password" id="password2" class="form-control form-control-sm" data-validation="required length" data-validation-length="min5">
          <label for="password2">'.$LANG['password'].'</label>
      </div> 

      '.$extra_['recaptcha'].'

      <div class="text-center form-sm mt-1">
        '.$extra_['fbconnect'].'
        <button name="signup" id="signup-btn" type="button" class="btn btn-info" onclick="connector(1, \''.$referrer.'\')">'.$LANG['signup'].'<i class="fa fa-sign-in ml-1"></i></button>
      </div>
    </form>';

    $tabbed_card = ' 
    <div class="col-md-6 col-xl-5 mb-4 mt-2">
      <ul class="nav nav-tabs nav-justified md-tabs blue-gradient darken-3" id="myTabJust" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#login-tab" role="tab" id="login"><i class="fa fa-user mr-1"></i> '.$LANG['login'].'</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#signup-tab" role="tab" id="signup"><i class="fa fa-user-plus mr-1"></i> '.$LANG['signup'].'</a>
        </li>
      </ul> 
      <div class="tab-content card pt-5" id="connector-tab">  
          <div class="tab-pane fade in active show" id="login-tab" role="tabpanel">
            <div id="loader"></div>
            <div class="text-center mx-2" id="login-message"></div>

            <div class="card-body mb-1"> 
                '.$login_form.'
            </div>
            <div class="card-footer">
              <div class="options text-center text-md-right mt-1">
                <p class="pt-1"><a href="'.$forgot_link.'" class="blue-text">'.$LANG['forgot_password'].'</a></p>
              </div> 
            </div>
          </div>

          <div class="tab-pane fade" id="signup-tab" role="tabpanel">
            <div id="loader-2"></div>
            <div class="text-center mx-2" id="signup-message"></div>
            <div class="card-body">
                 '.$signup_form.'
            </div>

            <div class="card-footer">
              <div class="options text-center text-md-right mt-1">
                <p class="pt-1"><a href="'.$forgot_link.'" class="blue-text">'.$LANG['forgot_password'].'</a></p>
              </div> 
            </div>
          </div> 
      </div>
    </div>';

    $login_card = '
    <div class="col-md-6 col-xl-5 mb-4 order-first">
        <div class="card pt-2" id="connector-tab">   
            <div id="loader"></div>
            <div class="text-center mx-2" id="login-message"></div>

            <div class="card-body mb-1"> 
                <h1> '.$LANG['login'].'</h1>
                '.$login_form.'
            </div> 
            <div class="card-footer">
                <div class="options text-center text-md-right mt-1">
                    <p class="pt-1"><a href="'.$forgot_link.'" class="blue-text">'.$LANG['forgot_password'].'</a></p>
                </div> 
            </div>  
        </div>
    </div>';

    return $type == 0 ? $tabbed_card : $login_card;
}

/* 
* Truncate text
*/
function myTruncate($str, $limit, $break=" ", $pad="...") {

    // return with no effect if string is shorter than $limit
    if(strlen($str) <= $limit) return $str;

    // is $break is present between $limit and the strings end?
    if(false !== ($break_pos = strpos($str, $break, $limit))) {
        if($break_pos < strlen($str) - 1) {
            $str = substr($str, 0, $break_pos) . $pad;
        }
    } 
    return $str;
}

/* 
* Remove special html tags from string
*/
function rip_tags($string) { 
    // ----- remove HTML TAGs ----- 
    $string = preg_replace ('/<[^>]*>/', ' ', $string); 
    // $string = filter_var($string, FILTER_SANITIZE_STRING);
    
    // ----- remove control characters ----- 
    $string = str_replace("\r", '', $string);    // --- replace with empty space
    $string = str_replace("\n", ' ', $string);   // --- replace with space
    $string = str_replace("\t", ' ', $string);   // --- replace with space
    
    // ----- remove multiple spaces ----- 
    $string = trim(preg_replace('/ {2,}/', ' ', $string));
    
    return $string; 
} 

/* 
* Create url referer to safely redirect users
*/
function urlReferrer($url, $type) {
    if ($type == 0) {
        $url = str_replace('/', '@', $url); 
    } else {
        $url = str_replace('@', '/', $url); 
    }
 
    return $url;
} 

/* 
* redirect page
*/
function redirect($location = '', $type = 0) {
    global $CONF;
    if ($type) {
        header('Location: '.$location);
    } else {
        if($location) {
            header('Location: '.permalink($CONF['url'].'/index.php?a='.$location));
        } else {
            header('Location: '.permalink($CONF['url'].'/index.php'));
        }        
    }

    exit;
}

/* 
* Get a link from text
*/
function decodeLink($partern, $x=0) { 
    // If www. is found at the beginning add http in front of it to make it a valid html link
    $y = $x==1 ? 'warning' : 'warning';

    if(substr($partern[1], 0, 4) == 'www.') {
        $link = 'http://'.$partern[1];
    } else {
        $link = $partern[1];
    }
    return '<a class="text-'.$y.'" href="'.$link.'" target="_blank" rel="nofollow">'.$link.'</a>'; 
}

function page_navigator($url, $startpage, $previouspage, $nextpage, $curpage, $endpage, $key) {
    $navigation = '';
    if ($endpage > 1) {
        if($curpage != $startpage){
            $navigation .= ' <a href="'.$url.'&'.$key.'='.$startpage.'"><i class="fa fa-angle-double-left"></i> First Page</a> ';                 
        }
        if($curpage >= 2){
            $navigation .= ' <a href="'.$url.'&'.$key.'='.$previouspage.'"><i class="fa fa-chevron-left"></i> Previous Page</a> ';                  
        }
        $navigation .= ' <a href="'.$url.'&'.$key.'='.$curpage.'"><i class="fa fa-th mx-2"></i></a> '; 

        if($curpage != $endpage){
            $navigation .= ' <a href="'.$url.'&'.$key.'='.$nextpage.'"> Next Page <i class="fa fa-chevron-right"></i></a> ';

            $navigation .= ' <a href="'.$url.'&'.$key.'='.$endpage.'"> Last Page <i class="fa fa-angle-double-right"></i></a> ';                         
        }
        return '<hr class="bg-primary"><div class="d-block text-center m-2 border border-info p-2 rounded">'.$navigation.'</div>';  
    }
}


/* 
* Check if this a a live ajax request
*/
function trueAjax() { 
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
        return true;
    } else {
        return false;
    }
} 
