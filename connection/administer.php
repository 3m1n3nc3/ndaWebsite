<?php 
require_once(__DIR__ .'/../includes/autoload.php');

$span = 1;

$user_id = $user['id'];
$userApp = new userCallback; 
$gett = new contestDelivery;
$sc = new siteClass;

// Pagination Navigation settings
$perpage = ($_POST['limit'] > 1) ? $_POST['limit'] : $settings['per_table'];

if(isset($_POST['page']) & !empty($_POST['page'])){
    $curpage = $_POST['page'];
} else{
    $curpage = 1;
}

$start = ($curpage * $perpage) - $perpage; 

// Get all available users
$count = count($userApp->userData());
$userApp->limit = $perpage;
$userApp->start = $start; 
$results = $userApp->userData();

// Get all available contests
$c_count = count($gett->get_all_Contest());
$gett->limit = $perpage;
$gett->start = $start; 
$c_results = $gett->get_all_Contest();

// Get all payment info
$p_count = count($userApp->premiumUsers());
$userApp->limit = $perpage;
$userApp->start = $start; 
$p_results = $userApp->premiumUsers();

// Get all Cashout requests
$userApp->x = 'cashout != \'0\' OR approved != \'0\'';
$cor_count = count($userApp->set_bank(0, 0));
$userApp->limit = $perpage;
$userApp->start = $start; 
$cor_results = $userApp->set_bank(0, 0);

// Set the result for the selected request
if ($_POST['type'] == 1) {
  $count = $count;
  $results = $results;
  $span = 7;
} elseif ($_POST['type'] == 2) {
  $count = $p_count;
  $results = $p_results;
  $span = 9;
} elseif ($_POST['type'] == 3) {
  $count = $cor_count;
  $results = $cor_results;  
  $span = 12;
} else {
  $count = $c_count;
  $results = $c_results;
  $span = 8;
}

// Pagination Logic
$endpage = ceil($count/$perpage);
$startpage = 1;
$nextpage = $curpage + 1;
$previouspage = $curpage - 1;
$nb = 0;
 
  $main_content=''; 
  if ($results) {
    foreach ($results as $rs => $key) {
      $nb = $nb+1;  

      $premium_check = $userApp->premiumStatus($key['id'], 1); 
      $promote_link = '';

      if (!$premium_check) {
        $promote_link = '
          <a class="px-2 float-right" href="'.permalink($CONF['url'].'/index.php?a=settings&b=users&promote='.$key['id']).'">Promote <i class="fa fa-check-circle text-success  "></i></a>';
      }

      if ($_POST['type'] == 1) {
        // Manage users
        $fullname = realName($key['username'], $key['fname'], $key['lname']);
        $sts = ($key['status'] == 0) ? 'Unverified' : (($key['status'] == 1) ? 'Suspended' : 'Active');

        $main_content .=  '  
          <tr>
            <th scope="row">'.$nb.'</th>
            <td>
              <a href="'.permalink($CONF['url'].'/index.php?a=profile&user='.$key['id']).'">'.$fullname.'</a>
            </td>
            <td>'.$key['city'].'</td>
            <td>'.$key['state'].'</td>
            <td>'.$key['country'].'</td>
            <td>'.$key['role'].'</td>  
            <td>'.$sts.'</td>  
            <td>
              '.$promote_link.'
              <a class="px-2 float-right" href="'.permalink($CONF['url'].'/index.php?a=settings&b=users&edit='.$key['id']).'">Edit <i class="fa fa-edit text-info"></i></a>
              <a class="px-2 float-right" href="'.permalink($CONF['url'].'/index.php?a=settings&b=users&delete='.$key['id']).'">Delete <i class="fa fa-trash text-danger"></i></a>
            </td>
          </tr> '; 
      } elseif ($_POST['type'] == 2) {
        // Manage Payments
        $plan = explode('_', $key['plan']); 
        $sts = ($key['status']) ? 'Active' : 'Suspended';
        $main_content .=  '  
          <tr>
            <th scope="row">'.$nb.'</th>
            <td>
              <a href="'.permalink($CONF['url'].'/index.php?a=profile&user='.$key['payer_id']).'" class="font-weight-bold text-info">'.$key['payer_firstname'].' '.$key['payer_lastname'].'</a>
            </td>
            <td>'.$sts.'</td>
            <td>'.$key['payment_id'].'</td>
            <td>'.$key['payer_country'].'</td>
            <td>'.$key['amount'].'</td>
            <td>'.$plan[0].'</td>
            <td>'.$key['trx_id'].'</td>
            <td>'.$key['currency'].'</td> 
            <td>
              <a class="px-2 float-right" href="'.permalink($CONF['url'].'/index.php?a=settings&b=payments&edit='.$key['payer_id']).'">Edit <i class="fa fa-edit text-info"></i></a>
              <a class="px-2 float-right" href="'.permalink($CONF['url'].'/index.php?a=settings&b=payments&delete='.$key['payer_id']).'">Delete <i class="fa fa-trash text-danger"></i></a>
            </td>
          </tr> '; 
      } elseif ($_POST['type'] == 3) {
        // Manage Cashout requests  

        $sc->what = sprintf('user = \'%s\'', $key['user_id']);
        $credit = $sc->passCredits(0)[0];

        $class = ($key['cashout'] && $key['approved'] == 0) ? 'info' : (($key['approved'] == 1) ? 'warning' : 'success');

        $main_content .=  '  
          <tr class="text-'.$class.'">
            <th scope="row">'.$nb.'</th>
            <td>'.$key['username'].'</td>
            <td>'.$credit['balance'].'</td>
            <td>'.$key['cashout'].'</td>
            <td>'.$key['paypal'].'</td>
            <td>'.$key['bank_name'].'</td>
            <td>'.$key['bank_address'].'</td>
            <td>'.$key['sort_code'].'</td>
            <td>'.$key['account_name'].'</td>
            <td>'.$key['account_number'].'</td>
            <td>'.$key['aba'].'</td>
            <td>
              <a href="'.permalink($CONF['url'].'/index.php?a=settings&b=cashout&approve='.$key['user_id']).'">
              <i class="fa fa-check-circle text-warning"></i> </a>
              <a href="'.permalink($CONF['url'].'/index.php?a=settings&b=cashout&paid='.$key['user_id']).'"> 
              <i class="fa fa-check-circle text-success"></i> </a>
              <a href="'.permalink($CONF['url'].'/index.php?a=settings&b=cashout&decline='.$key['user_id']).'"> 
              <i class="fa fa-times-circle text-danger"></i> </a>
            </td>
          </tr> '; 
      } else {
        // Manage contests
        $sts = ($key['status']) ? 'Active' : 'Inactive';
        $ftd = ($key['featured']) ? 'Yes' : 'No';
        $rcmd = ($key['recommend']) ? 'Yes' : 'No';
        $main_content .=  '  
          <tr>
            <th scope="row">'.$nb.'</th>
            <td>
              <a href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$key['id']).'">'.$key['title'].'</a>
            </td>
            <td>
              <a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$key['creator']).'">'.$key['creator'].'</a>
            </td>
            <td>'.$key['country'].'</td>
            <td>'.$sts.'</td>
            <td>'.$ftd.'</td>
            <td>'.$rcmd.'</td> 
            <td>
              <a class="px-1" href="'.permalink($CONF['url'].'/index.php?a=settings&b=contests&edit='.$key['id']).'">Edit <i class="fa fa-edit text-info"></i></a>
              <a class="px-1" href="'.permalink($CONF['url'].'/index.php?a=settings&b=contests&delete='.$key['id']).'">Delete <i class="fa fa-trash text-danger"></i></a>
            </td>
          </tr> ';         
      }
    }
  } else {
    $main_content .= '<tr><td colspan="'.$span.'"><h2 class="p-1 text-center">Nothing to show</h2></td></tr>';
  } 

  $loader = '<div class="mt-2 text-center"><div class="saving-load mr-auto"></div>';

  $navigation = '';

  if ($endpage > 1) {
    if ($curpage != $startpage) {
      $navigation .= '<a href="#" onclick="load_manage_admin('.$start.', '.$perpage.', '.$startpage.', '.$_POST['type'].')" class="text-black mx-1"><i class="fa fa-angle-double-left"></i></a>';
    }

    if ($curpage >= 2) {
      $navigation .= '<a href="#" onclick="load_manage_admin('.$start.', '.$perpage.', '.$previouspage.', '.$_POST['type'].')" class="text-black mx-1"><i class="fa fa-angle-left"></i></a>';
    }

      $navigation .= '<a href="#" onclick="load_manage_admin('.$start.', '.$perpage.', '.$curpage.', '.$_POST['type'].')" class="text-black mx-1"><i class="fa fa-th-large"></i></a>';

    if($curpage != $endpage){
      $navigation .= '<a href="#" onclick="load_manage_admin('.$start.', '.$perpage.', '.$nextpage.', '.$_POST['type'].')" class="text-black mx-1"><i class="fa fa-angle-right"></i></a>';

      $navigation .= '<a href="#" onclick="load_manage_admin('.$start.', '.$perpage.', '.$endpage.', '.$_POST['type'].')" class="text-black mx-1"><i class="fa fa-angle-double-right"></i></a>';
    }

    $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';
  } else {
    $navigation .= '';
  }

  $data = array('main_content' => $main_content, 'loader' => $loader, 'navigation' => $navigation);

  echo json_encode($data, JSON_UNESCAPED_SLASHES); 