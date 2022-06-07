<?php
require_once __DIR__."/class.AdminBase.php"; 

class AdminLTEextends extends BaseAdmin{
  
  var $style_admin = 'AdminLTE';
  var $is_admin_navigation = true;
  var $profile_img = '';
 
  function __construct () {
    
    parent::__construct();
    
    $this->setMineMenuAddData();
    
  }
 
  function getBreadCrumbs(){
    $output = '';$i = 0;
    #pri($this->bread);
    $output .= '
      <li><a href="/'.ADM_DIR.'"><i class="fas fa-tachometer-alt"></i> Главная</a></li>';
    
    if(is_array($this->bread) && $count = count($this->bread))
      foreach($this->bread as $k => $v){
        if((++$i >= $count) && ($i > 1) && $this->title) break;
        $output .= '
        <li><a href="'.$v.'">'.$k.'</a></li>';
      }
    $output .= '
      <li class="active">'.$this->title.'</li>';
    
    return $output;
  }
  
  function getHeaderNotificationLTE(){
    $output = '';
    
    $output .= '
<!-- Messages: style can be found in dropdown.less-->
          <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-envelope-o"></i>
              <span class="label label-success">4</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have 4 messages</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                  <li><!-- start message -->
                    <a href="#">
                      <div class="float-left">
                        <img src="'.IA_URL.'admin_style/a_lte/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                      </div>
                      <h4>
                        Support Team
                        <small><i class="fa fa-clock-o"></i> 5 mins</small>
                      </h4>
                      <p>Why not buy a new awesome theme?</p>
                    </a>
                  </li>
                  <!-- end message -->
                </ul>
              </li>
              <li class="footer"><a href="#">See All Messages</a></li>
            </ul>
          </li>
          <!-- Notifications: style can be found in dropdown.less -->
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
              <span class="label label-warning">10</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have 10 notifications</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                  <li>
                    <a href="#">
                      <i class="fa fa-users text-aqua"></i> 5 new members joined today
                    </a>
                  </li>
                </ul>
              </li>
              <li class="footer"><a href="#">View all</a></li>
            </ul>
          </li>
          <!-- Tasks: style can be found in dropdown.less -->
          <li class="dropdown tasks-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-flag-o"></i>
              <span class="label label-danger">9</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">You have 9 tasks</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                  <li><!-- Task item -->
                    <a href="#">
                      <h3>
                        Design some buttons
                        <small class="float-right">20%</small>
                      </h3>
                      <div class="progress xs">
                        <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                          <span class="sr-only">20% Complete</span>
                        </div>
                      </div>
                    </a>
                  </li>
                  <!-- end task item -->
                </ul>
              </li>
              <li class="footer">
                <a href="#">View all tasks</a>
              </li>
            </ul>
          </li>
    ';
    
    return $output;
  }
  
  function getHeader(){
    $output = '';
    $this->dieRights();
    $output .= '
<!-- Site wrapper -->
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="'./*IA_URL*/'/'.'" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>A</b>DM</span>
      <!-- logo for regular state and mobile devices -->';
      /*$output .= '
      <span class="logo-lg"><b>Admin</b> PANEL </span>';*/
      $output .= '
      <span class="logo-lg">'.$_SERVER['SERVER_NAME'].'</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">';
    #$output .= $this->getHeaderNotificationLTE();
    $user_name = $this->getUserName();
    $output .= '
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="'.$this->profile_img.'" class="user-image" alt="User Image">
              <span class="d-none d-sm-block">'.$user_name.'</span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="'.$this->profile_img.'" class="img-circle" alt="User Image">

                <p>
                  '.$user_name.' - '.$this->user['title'].' 
                  <small>Последняя активность '.date("d.m.Y").'</small>
                </p>
              </li>';
    $output1 = '
              <!-- Menu Body -->
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center">
                    <a href="#">Подпищики</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Скидки</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Друзья</a>
                  </div>
                </div>
                <!-- /.row -->
              </li>';
    $output .= '
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="float-left">
                  <a href="'.IA_URL.'accounts.php?edits='.$this->user['id'].'" class="btn btn-default btn-flat">Аккаунт</a>
                </div>
                <div class="float-right">
                  <a href="'.IA_URL.'?logout" class="btn btn-default btn-flat">Выход</a>
                </div>
              </li>
            </ul>
          </li>';
    $output .= ' 
          <style>
          @media (max-width: 767px){
            .skin-blue .main-header .navbar .dropdown-menu.rg_menu li a {
                color: #777;
            }
            .skin-blue .main-header .navbar .dropdown-menu.rg_menu li a:hover {
                background: #367fa9;
                color: #fff;
            }
          }
          </style>
        <li class="dropdown" >
          <a class="dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
          </a>
          <ul class="dropdown-menu rg_menu">';
  /*$output .= '
            <li><a href="'.IA_URL.'currency.php" ><i class="fa fa-money fa-fw"></i> Курсы валют</a></li>';*/
  if ($_SESSION["WA_USER"]['is_admin']) {
    $output .= '
             <li><a href="'.IA_URL.'accounts.php"><i class="fa fa-user fa-fw"></i> Пользователи</a></li>';
  }
  $output .= '
            <li><a href="'.IA_URL.'config.php"><i class="fas fa-cog"></i> Настройки</a></li>
            <li class="divider"></li>
            <li><a href="'.IA_URL.'?logout"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
          </ul>
          <!-- /.dropdown-user -->
        </li>
        <!-- /.dropdown -->';
          /*<!-- Control Sidebar Toggle Button -->
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fas fa-cog"></i></a>
          </li>*/
    $output .= '
        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">';
  $output .= $this->getLeftMenu();
  

  $output .= '
  </aside>';
    #$output .= $this->getTopMenu();

    return $output;
  }
  
  function getLTELeftSidebarMenu($show = 0){
    $output = '';
    $output .= '
    <li class="treeview">
          <a href="#">
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            <span class="float-right-container">
              <i class="fa fa-angle-left float-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../../index.html"><i class="fa fa-circle-o"></i> Dashboard v1</a></li>
            <li><a href="../../index2.html"><i class="fa fa-circle-o"></i> Dashboard v2</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-files-o"></i>
            <span>Layout Options</span>
            <span class="float-right-container">
              <span class="label label-primary float-right">4</span>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../layout/top-nav.html"><i class="fa fa-circle-o"></i> Top Navigation</a></li>
            <li><a href="../layout/boxed.html"><i class="fa fa-circle-o"></i> Boxed</a></li>
            <li><a href="../layout/fixed.html"><i class="fa fa-circle-o"></i> Fixed</a></li>
            <li><a href="../layout/collapsed-sidebar.html"><i class="fa fa-circle-o"></i> Collapsed Sidebar</a></li>
          </ul>
        </li>
        <li>
          <a href="../widgets.html">
            <i class="fa fa-th"></i> <span>Widgets</span>
            <span class="float-right-container">
              <small class="label float-right bg-green">Hot</small>
            </span>
          </a>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-pie-chart"></i>
            <span>Charts</span>
            <span class="float-right-container">
              <i class="fa fa-angle-left float-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../charts/chartjs.html"><i class="fa fa-circle-o"></i> ChartJS</a></li>
            <li><a href="../charts/morris.html"><i class="fa fa-circle-o"></i> Morris</a></li>
            <li><a href="../charts/flot.html"><i class="fa fa-circle-o"></i> Flot</a></li>
            <li><a href="../charts/inline.html"><i class="fa fa-circle-o"></i> Inline charts</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-laptop"></i>
            <span>UI Elements</span>
            <span class="float-right-container">
              <i class="fa fa-angle-left float-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../UI/general.html"><i class="fa fa-circle-o"></i> General</a></li>
            <li><a href="../UI/icons.html"><i class="fa fa-circle-o"></i> Icons</a></li>
            <li><a href="../UI/buttons.html"><i class="fa fa-circle-o"></i> Buttons</a></li>
            <li><a href="../UI/sliders.html"><i class="fa fa-circle-o"></i> Sliders</a></li>
            <li><a href="../UI/timeline.html"><i class="fa fa-circle-o"></i> Timeline</a></li>
            <li><a href="../UI/modals.html"><i class="fa fa-circle-o"></i> Modals</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-edit"></i> <span>Forms</span>
            <span class="float-right-container">
              <i class="fa fa-angle-left float-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../forms/general.html"><i class="fa fa-circle-o"></i> General Elements</a></li>
            <li><a href="../forms/advanced.html"><i class="fa fa-circle-o"></i> Advanced Elements</a></li>
            <li><a href="../forms/editors.html"><i class="fa fa-circle-o"></i> Editors</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-table"></i> <span>Tables</span>
            <span class="float-right-container">
              <i class="fa fa-angle-left float-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../tables/simple.html"><i class="fa fa-circle-o"></i> Simple tables</a></li>
            <li><a href="../tables/data.html"><i class="fa fa-circle-o"></i> Data tables</a></li>
          </ul>
        </li>
        <li>
          <a href="../calendar.html">
            <i class="fa fa-calendar"></i> <span>Calendar</span>
            <span class="float-right-container">
              <small class="label float-right bg-red">3</small>
              <small class="label float-right bg-blue">17</small>
            </span>
          </a>
        </li>
        <li>
          <a href="../mailbox/mailbox.html">
            <i class="fa fa-envelope"></i> <span>Mailbox</span>
            <span class="float-right-container">
              <small class="label float-right bg-yellow">12</small>
              <small class="label float-right bg-green">16</small>
              <small class="label float-right bg-red">5</small>
            </span>
          </a>
        </li>
        <li class="treeview active">
          <a href="#">
            <i class="fa fa-folder"></i> <span>Examples</span>
            <span class="float-right-container">
              <i class="fa fa-angle-left float-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="invoice.html"><i class="fa fa-circle-o"></i> Invoice</a></li>
            <li><a href="profile.html"><i class="fa fa-circle-o"></i> Profile</a></li>
            <li><a href="login.html"><i class="fa fa-circle-o"></i> Login</a></li>
            <li><a href="register.html"><i class="fa fa-circle-o"></i> Register</a></li>
            <li><a href="lockscreen.html"><i class="fa fa-circle-o"></i> Lockscreen</a></li>
            <li><a href="404.html"><i class="fa fa-circle-o"></i> 404 Error</a></li>
            <li><a href="500.html"><i class="fa fa-circle-o"></i> 500 Error</a></li>
            <li class="active"><a href="blank.html"><i class="fa fa-circle-o"></i> Blank Page</a></li>
            <li><a href="pace.html"><i class="fa fa-circle-o"></i> Pace Page</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-share"></i> <span>Multilevel</span>
            <span class="float-right-container">
              <i class="fa fa-angle-left float-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
            <li>
              <a href="#"><i class="fa fa-circle-o"></i> Level One
                <span class="float-right-container">
                  <i class="fa fa-angle-left float-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-circle-o"></i> Level Two</a></li>
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Level Two
                    <span class="float-right-container">
                      <i class="fa fa-angle-left float-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
          </ul>
        </li>
        <li><a href="../../documentation/index.html"><i class="fa fa-book"></i> <span>Documentation</span></a></li>
        <li class="header">LABELS</li>
        <li><a href="#"><i class="fa fa-circle-o text-red"></i> <span>Important</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-yellow"></i> <span>Warning</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-aqua"></i> <span>Information</span></a></li>
    ';
    
    if($show){
      return $output; 
    }else{
      return ''; 
    }
     
  }
  
  function getLeftSidebarMenuItem($k, $v, $prefix = ''){
    $output = $active = ''; 
    
    $pos = strpos($k, $this->check );
    if ($pos === false) {
      $active = '';
    } else {
      $active = ' class="active" ';
    }
    $output .= '
      '.$prefix.'
      <li '.$active.'>
        <a href='.$k.'>';
    if(     isset($this->mainmenu_add_data[$v]['icon']) 
         && $this->mainmenu_add_data[$v]['icon'] ){
      $output .= $this->mainmenu_add_data[$v]['icon'];
    }
         
    $output .= '
          <span>'.$v.'</span>
        </a>
      </li>';
    
    return $output;
  }
  
  function getLeftSidebarMenu(){
    $output = '';
    #pri( $this->mainmenu_add_data ); 
    #pri($this->mainmenu);
    #pri($this->check );
    $sub_header = '';
    foreach ($this->mainmenu as $k=>$v){
      
      if(strripos($k, ".php") === false) {
        $sub_header = '<li class="header">'.$v.'</li>';
        continue;
      }
      
    	if ($this->user["is_admin"]){
        $output .=  $this->getLeftSidebarMenuItem($k, $v, $sub_header);
      }else{
        $ok=false;
    		if (array_key_exists($k, $this->scripts)){
    		  
    			foreach ($this->current_rights as $val){
    				if (in_array($val, $this->scripts[$k])) $ok=true;
    			}
    		}
    		if ($ok){
          $output .= $this->getLeftSidebarMenuItem($k, $v, $sub_header);
        }
    	}
      $sub_header = '';
    }
    
    return $output;
  }
  
  function getProfilImg(){
    if(isset($_SESSION["WA_USER"]["img"]) && $_SESSION["WA_USER"]["img"]){
      $this->profile_img = '/images/accounts/slide/'.$_SESSION["WA_USER"]["img"];
    }else{
      $this->profile_img = IA_URL.'admin_style/a_lte/dist/img/user2-160x160.jpg';
    }
  }
  
  function getUserName(){
    $output = '';
    
    $this->getProfilImg();
    
    if(isset($_SESSION["WA_USER"]["fullname"]) && $_SESSION["WA_USER"]["fullname"]){
      $output .= $_SESSION["WA_USER"]["fullname"];
    }else{
      $output .= 'Иван Иванович(???)';
    }
    
    return $output;
  }
  
  function getLeftMenu(){
    $output = '';
    
    $output .= '
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="float-left image">
          <img src="'.$this->profile_img.'" class="img-circle" alt="'.$this->user['fullname'].'">
        </div>
        <div class="float-left info">
          <p>'.$this->getUserName().'</p>     
          <a href="#"><i class="fa fa-circle text-success"></i> В сети</a>
        </div>
      </div>
      <!-- search form -->
      <form action="'.IA_URL.'search.php" method="post" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="search_admin_q" class="form-control" placeholder="Поиск..." value = "'.$this->search_query.'">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">ОСНОВНАЯ НАВИГАЦИЯ</li>
        '.$this->getLeftSidebarMenu().'
        '.$this->getLTELeftSidebarMenu().'
        <li class="sidebar_sitelink"
          style = "
            text-align: left;
            font-size:64px;
          "
        >
          <a href = "//'.$this->sitelink.'" target = "_blank">
            '.$this->sitelink.'
          </a>
        </li>
      </ul>
      
    </section>
    <!-- /.sidebar -->
    ';
    
    #<li><a href="https://adminlte.io/themes/AdminLTE/index.html" target = "_blank"><i class="fas fa-tachometer-alt"></i> <span>Шаблон</span></a></li>
    
    return $output;
  }
  
  function getFooter($output = ''){
    
    $output .= '
<footer class="main-footer">
    <!--div class="float-right hidden-xs">
      <b>Версия</b> 3.180725
    </div-->
    <strong> &copy; '.date("Y").' <a href="http://'.$this->sitelink.'" target = "_blank">'.$this->sitelink.'</a></strong> <b>Версия</b> 3.180725
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>

      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fas fa-cog"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane" id="control-sidebar-home-tab">
        <h3 class="control-sidebar-heading">Recent Activity</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-birthday-cake bg-red"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Langdon\'s Birthday</h4>

                <p>Will be 23 on April 24th</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-user bg-yellow"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                <p>New phone +1(800)555-1234</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                <p>nora@example.com</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-file-code-o bg-green"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                <p>Execution time 5 seconds</p>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

        <h3 class="control-sidebar-heading">Tasks Progress</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Custom Template Design
                <span class="label label-danger float-right">70%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Update Resume
                <span class="label label-success float-right">95%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-success" style="width: 95%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Laravel Integration
                <span class="label label-warning float-right">50%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Back End Framework
                <span class="label label-primary float-right">68%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

      </div>
      <!-- /.tab-pane -->
      <!-- Stats tab content -->
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <!-- /.tab-pane -->
      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Report panel usage
              <input type="checkbox" class="float-right" checked>
            </label>

            <p>
              Some information about this general settings option
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Allow mail redirect
              <input type="checkbox" class="float-right" checked>
            </label>

            <p>
              Other sets of options are available
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Expose author name in posts
              <input type="checkbox" class="float-right" checked>
            </label>

            <p>
              Allow the user to show his name in blog posts
            </p>
          </div>
          <!-- /.form-group -->

          <h3 class="control-sidebar-heading">Chat Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Show me as online
              <input type="checkbox" class="float-right" checked>
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Turn off notifications
              <input type="checkbox" class="float-right">
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Delete chat history
              <a href="javascript:void(0)" class="text-red float-right"><i class="far fa-trash-alt"></i></a>
            </label>
          </div>
          <!-- /.form-group -->
        </form>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar\'s background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
    ';
    
    return $output;
  }
  
  function blankPage($output = ''){
    
    #$output .= $this->getMineMenu();
    
    $output .= '
              <div class="row">
                  <div class="col-lg-12">
    ';
    $this->header = "Главная"; 
    if ($_SESSION["WA_USER"]["fullname"]){
      $this->title = 'Добро пожаловать, '.$_SESSION["WA_USER"]["fullname"]; 
      #$output .=  '<h1 class="page-header">Добро пожаловать, '.$_SESSION["WA_USER"]["fullname"].'</h1>';
    }
      $output .= '<div style = "    text-align: center;">';
      $output .= '<ul class="nav nav-pills nav-stacked" style="max-width:250px; margin: 0 auto; ">';
      #pri( $this->mainmenu_add_data );
      $sub_header = '';
      foreach ($this->mainmenu as $k=>$v)
      {
        if(strripos($k, ".php") === false) {
          $sub_header = '<li class="header"><b>'.$v.'</b></li>';
          if ($this->user["is_admin"]) $output .= $sub_header;
          continue;
        }
        $icon = '';
        if( isset($this->mainmenu_add_data[$v]['icon']) 
            && $this->mainmenu_add_data[$v]['icon'] ){
            $icon = $this->mainmenu_add_data[$v]['icon'];
        }
      	if ($this->user["is_admin"]) $output .= "<li><a href='$k'>$icon $v</a></li>";
      	else
      	{
      		if (array_key_exists($k, $this->scripts)) {
      		  $ok=false;
            if( isset($this->current_rights) && $this->current_rights){
              foreach ($this->current_rights as $val){
      				  if (in_array($val, $this->scripts[$k])) $ok=true;
      			  }  
            }	
      		}
      		if ($ok) $output .= "<li><a href='$k'>$icon $v</a></li>";
      	}
        $sub_header = '';
      }
      /*if ($_SESSION["WA_USER"]['is_admin']) {
	      $output .= '<li><a href="accounts.php">Пользователи</a></li>';
      }*/
      
      $output .= '<li><a href="'.IA_URL.'?logout" target="_top" class="domain">Выход</a></li>';
      $output .= '</ul>';
    $output .= '</div>';
                      
    $output .= ' 
                  </div>
                  <!-- /.col-lg-12 -->
              </div>
              <!-- /.row -->
    ';
    
    return $output;
  }
  
  function getLoginPage(){
    $output = '';
    $output1 = '
    <div>
      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            <form  method="POST" action="'.IA_URL.'?auth" >
              <h1>Панель управления</h1>
              <div>
                <input type="text" class="form-control" placeholder="login" name="login" required="" autofocus />
              </div>
              <div>
                <input type="password" class="form-control"  placeholder="Password" name="password"  value="" required="" />
              </div>
              <div>
                <button class="btn btn-default submit">Войти</button>
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                <div>
                  <h1><i class="fa fa-paw"></i> '.ADM_DIR.' !</h1>
                  <p>©'.date("Y").' Все права защещиены.</br> Control panel by <a href="https://'.$this->sitelink.'">'.$this->sitelink.'</a></p>
                </div>
              </div>
            </form>
          </section>
        </div>
      
      </div>
    </div>
    ';
    $this->adminHeaderScripts .= '
    <!-- iCheck -->
    <link rel="stylesheet" href="'.IA_URL.'admin_style/vendor/iCheck/skins/square/blue.css">
    ';
    $this->adminFooterScripts .= '
    <!-- iCheck -->
    <script src="'.IA_URL.'admin_style/vendor/iCheck/icheck.min.js"></script>
    <script>
      $(function () {
        $("input").iCheck({
          checkboxClass: "icheckbox_square-blue",
          radioClass: "iradio_square-blue",
          increaseArea: "20%" // optional
        });
      });
    </script>';
    #<a href="/'.ADM_DIR.'"><b>'.ADM_DIR.'</b>LTE</a>
    $output = '
<div class="login-box ">
  <div class="login-logo">
    <a href="/'.ADM_DIR.'"><b>Admin</b> PANEL</a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body login_content">
    <p class="login-box-msg">Войдите для начала работы</p>

    <form  method="POST" action="'.IA_URL.'?auth" >
      <div class="form-group has-feedback">';
        #<input type="email" class="form-control" placeholder="Email">
    $output .= '
        <input type="text" class="form-control" placeholder="login" name="login" required="" autofocus />
        <span class="fas fa-sign-out-alt form-control-feedback"></span>
        
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" value="" required="" >
        <span class="fas fa-lock form-control-feedback"></span>
      </div>
      <div class="row align-items-center">
        <div class="col-8">
          <div class="checkbox icheck">
            <label>
              <input type="checkbox"> Запомнить меня
            </label>
          </div>
        </div>
        <!-- /.col -->
        <div class="col-4">
          <button type="submit" class="btn btn-primary btn-block btn-flat">Войти</button>
        </div>
        <!-- /.col -->
      </div>
    </form>
    
    <div class = "login_content" style = "text-align: center">
      <h1><i class="fa fa-paw"></i> '.ADM_DIR.' !</h1>
      <p>©'.date("Y").' Все права защещиены.</br> Admin panel by <a href="https://'.$this->sitelink.'">'.$this->sitelink.'</a></p>
    </div>
    <style>
    .login_content {
      color: #73879C;
      /*font-family: "Helvetica Neue",Roboto,Arial,"Droid Sans",sans-serif;*/
      font-size: 13px;
      font-weight: 400;
      line-height: 1.471;
    }
    .login_content h1 {
        /*font: 400 25px Helvetica,Arial,sans-serif;*/
        font-size: 25px;
        letter-spacing: -.05em;
        line-height: 20px;
        margin: 10px 0 30px;
    }
    </style>
  </div>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
    ';
    
    /*
        <div class="social-auth-links text-center">
      <p>- OR -</p>
      <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in using
        Facebook</a>
      <a href="#" class="btn btn-block btn-social btn-google btn-flat"><i class="fa fa-google-plus"></i> Sign in using
        Google+</a>
    </div>
    <!-- /.social-auth-links -->

    <a href="#">I forgot my password</a><br>
    <a href="register.html" class="text-center">Register a new membership</a>
    */
    
    
    return $output;
  }
  
  function getIndexContent($output = ''){

    if (!isset($_SESSION["WA_USER"])){
      $output .= $this->getLoginPage();
    }else { 
      
      $this->setContent($this->blankPage());
      $output .= $this->getContent();
      
      $this->dieRights();
      $this->logsWrite();
    }
    
    return $output;
  }

  function getContent($output = ''){
    
    #$output .= $this->getMineMenu();
    
    $this->adminHeader = $this->getHeader();
    $this->adminFooter = $this->getFooter(); 
    
    $output .= $this->adminHeader;
    $output .= '
    <!-- =============================================== -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          '.$this->header;
          #<small>it all starts here</small>
    $output .= '
        </h1>
        <ol class="breadcrumb">
          '.$this->getBreadCrumbs().'
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        
        <!-- Default box -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">'.$this->title.'</h3>
            
            <div class="box-tools float-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>';
                
              /*<button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>*/
    $output .= '
            </div>
          </div>
          <div class="box-body">';
    $output .= $this->content;
    $output .= '
          </div>
          <!-- /.box-body -->
          <div class="box-footer">
            '.$this->cont_footer.'
          </div>
          <!-- /.box-footer-->
        </div>
        <!-- /.box -->
      	
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->';
    
    $output .= $this->adminFooter;
    
    return $output;
  }

  function showAdmin($view = 'index'){
    
    $this->checkRights();
    
    $this->adminHead = $this->getHead();
    
    switch($view){
      case 'index':
        $this->adminContent = $this->getIndexContent();
      break; 
      
      case 'content':
        if (!isset($_SESSION["WA_USER"])){
          $this->adminContent = $this->getIndexContent();
        }else{
          $this->adminContent = $this->getContent();
        }
      break; 
      
      case '404':
        $this->adminContent = $this->get404Content();
      break; 
    }
    
    (!isset($_SESSION["WA_USER"])) ? $body_class = "hold-transition login-page"  : $body_class = "hold-transition skin-blue sidebar-mini";
    
    $output = '';
    
    $output .= $this->adminDoctype;
    $output .= '
<html lang="ru">
  <head>

';
    $output .= $this->adminCharset.'
    ';
    $output .= '<title>'.$this->adminTitle.'</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
';
    $output .= '<meta name="description" content="'.$this->adminDescription.'">
';
    $output .= '<meta name="keywords" content="'.$this->adminKeywords.'">

';
    $output .= $this->adminHead;    
    $output .= $this->adminHeaderScripts;
    
    $output .= '
  </head>
  <body class="'.$body_class.'">';
        $output .= $this->adminContent;
        $output .= $this->adminFooterScripts;
        $output .= '
  </body>
</html>';
    
    return $output;
  }
  
}
 