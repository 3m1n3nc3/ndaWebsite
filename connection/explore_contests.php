<?php
require_once(__DIR__ .'/../includes/autoload.php');

$user_id = $user['id'];
$gett = new contestDelivery; 

// Pagination Navigation settings
$perpage = $settings['per_explore'];

if(isset($_POST['page']) & !empty($_POST['page'])){
    $curpage = $_POST['page'];
} else{
    $curpage = 1;
}

$start = ($curpage * $perpage) - $perpage;
$count = count($gett->getContest());
$gett->limit = $perpage; 
$gett->start = $start; 

// Make a search query
(isset($_POST['search'])) ? $gett->search = $_POST['search'] : '';
$c_results = $gett->getContest(); 

// Pagination Logic
$endpage = ceil($count/$perpage);
$startpage = 1;
$nextpage = $curpage + 1;
$previouspage = $curpage - 1;

$echo =''; 
$echo .= '<div class="row">';
if ($c_results) { 
  foreach ($c_results as $rs => $key) {
    if ($key['active'] == 1) {
        if ($key['votes']>0) {
            $d = 'Voted '.$key['votes'].' times'; $c = 'badge-success';
        } else {
            $d = 'No Votes'; $c = 'badge-warning';
        }
    } else {
        $d = 'Inactive'; $c = 'badge-danger';
    }

    if ($key['cover'] == '') {
      $photo = 'default.jpg';
    } else {
      $photo = $key['cover'];
    }

    $echo .= ' 
      <div class="col-md-4 mt-2">
        <div class="card mb-1 aqua-gradient h-100">
          <div class="view overlay">
            <img class="card-img-top" src="'.$CONF['url'].'/uploads/cover/contest/'.$photo.'" alt="'.$key['title'].'"  style="display: block; object-position: 50% 50%; width: 100%; height: 100%; object-fit: cover;" id="photo_'.$key['id'].'">
            <a onclick="profileModal('.$key['id'].', '.$key['id'].', 2)">
              <div class="mask rgba-white-light flex-center font-weight-bold">Quick Preview</div>
            </a>
          </div>

          <div class="card-body">
            <a onclick="shareModal(1, '.$key['id'].')" class="activator waves-effect waves-light mr-2"><i class="fa fa-share-alt"></i></a> 
            <a href="'.permalink($CONF['url'].'/index.php?a=contest&s='.$key['safelink']).'" class="black-text" id="contest-url'.$key['id'].'"><h4>'.$key['title'].' <i class="fa fa-angle-double-right"></i></h4></a> 
          </div>
          <div class="card-footer cloudy-knoxville-gradient"> 
            <span class="badge badge-pill '.$c.'">'.$d.'</span>
          </div>
        </div>                
      </div>  
    ';
  }
} else {
  $echo .= '<h1 class="container text-info p-4">No '.$LANG['contest'].'s</h1> ';
}

$echo .= '</div> ';
$navigation = '';
if ($endpage > 1) {
  if ($curpage != $startpage) {
    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$startpage.', 1)" class="text-black mx-1"><i class="fa fa-angle-double-left"></i></a>';
  }

  if ($curpage >= 2) {
    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$previouspage.', 1)" class="text-black mx-1"><i class="fa fa-angle-left"></i></a>';
  }

    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$curpage.', 1)" class="text-black mx-1"><i class="fa fa-th-large"></i></a>';

  if($curpage != $endpage){
    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$nextpage.', 1)" class="text-black mx-1"><i class="fa fa-angle-right"></i></a>';

    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$endpage.', 1)" class="text-black mx-1"><i class="fa fa-angle-double-right"></i></a>';
  }

  $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';
} else {
  $navigation .= '';
}
$echo .= '<div class="mt-5 text-center"><hr class="bg-warning">' .$navigation. '</div>';
echo $echo;