<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

include 'model.php';
/* Connect to DB */
$db = connect_db('localhost:3307', 'ddwt18_week2', 'ddwt18','ddwt18');
$nbr_series = count_series($db);
$nbr_users = count_users($db);
$right_column = use_template('cards');

$template = Array(
    1 => Array(
        'name' => 'Home',
        'url' => '/DDWT18/week2/'
    ),
    2 => Array(
        'name' => 'Overview',
        'url' => '/DDWT18/week2/overview/'
    ),
    3 => Array(
        'name' => 'Add Serie',
        'url' => '/DDWT18/week2/add/'
    ),
    4 => Array(
        'name' => 'My Account',
        'url' => '/DDWT18/week2/myaccount/'
    ),
    5 => Array(
        'name' => 'Register',
        'url' => '/DDWT18/week2/register/'
    ),
    6 => Array(
        'name' => 'Login',
        'url' => '/DDWT18/week2/login/'
    ));;

/** Redundant code
 * $nbr_series = count_series($db);
 */
/* Landing page */
if (new_route('/DDWT18/week2/', 'get')) {
    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Home' => na('/DDWT18/week2/', True)
    ]);
    $navigation = get_navigation($template, 1);

    /* Page content */
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/DDWT18/week2/overview/', 'get')) {
    /* Get Number of Series */
    $userid = get_user_id();

    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview', True)
    ]);
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_serie_table(get_series($db), $db);

    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('main');
}

/* Single Serie */
elseif (new_route('/DDWT18/week2/serie/', 'get')) {
    /* Get Number of Series */
    $current_user = get_user_id();

    /* Get series from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);
    $userid = $serie_info['user'];

    if ($current_user == $serie_info['user']) {
        $display_buttons = True;
    } else {
        $display_buttons = False;
    }

    /* Page info */
    $page_title = $serie_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview/', False),
        $serie_info['name'] => na('/DDWT18/week2/serie/?serie_id='.$serie_id, True)
    ]);
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = sprintf("Information about %s", $serie_info['name']);
    $page_content = $serie_info['abstract'];
    $nbr_seasons = $serie_info['seasons'];
    $creators = $serie_info['creator'];
    $added_by = (get_username($db, $userid)['firstname']." ".get_username($db, $userid)['lastname']);

    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('serie');
}

/* Add serie GET */
elseif (new_route('/DDWT18/week2/add/', 'get')) {
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Add Series' => na('/DDWT18/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/DDWT18/week2/add/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('new');
}

/* Add serie POST */
elseif (new_route('/DDWT18/week2/add/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }

    /* Add serie to database */
    $feedback = add_serie($db, $_POST, $_SESSION['user_id']);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/add/?error_msg=%s',
        json_encode($feedback)));

    include use_template('new');
}

/* Edit serie GET */
elseif (new_route('/DDWT18/week2/edit/', 'get')) {
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    /* Get serie info from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        sprintf("Edit Series %s", $serie_info['name']) => na('/DDWT18/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $serie_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT18/week2/edit/';

    /* Choose Template */
    include use_template('new');
}

/* Edit serie POST */
elseif (new_route('/DDWT18/week2/edit/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }

    $info = get_serieinfo($db, $_POST['serie_id']);
    /* Update serie in database */
    $feedback = update_serie($db, $_POST, $_SESSION['user_id'], $info['user']);
    $error_msg = get_error($feedback);

    redirect(sprintf('/DDWT18/week2/serie/?error_msg=%s&serie_id=%s&user_id=%s',
        json_encode($feedback), $_POST['serie_id'], $_SESSION['user_id']));



    /* Choose Template */
    include use_template('serie');
}

/* Remove serie */
elseif (new_route('/DDWT18/week2/remove/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    /* Remove serie in database */
    $feedback = remove_serie($db, $_POST['serie_id']);
    $error_msg = get_error($feedback);

    redirect(sprintf('/DDWT18/week2/overview/?error_msg=%s&serie_id=%s',
        json_encode($feedback), $_POST['serie_id']));


    /* Choose Template */
    include use_template('main');
}

elseif (new_route('/DDWT18/week2/myaccount/', 'get')) {
    /* Check if logged in */
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }

    $userid = $_SESSION['user_id'];
    $page_title = 'My Account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2', False),
        'My account' => na('/DDWT18/week2/myaccount/', True)
    ]);
    $navigation = get_navigation($template, 4);

    /* Page content*/
    $page_subtitle = 'Your personal account';
    $page_content = 'Here you can find your own personal account';
    $user = (get_username($db, $userid)['firstname']." ".get_username($db, $userid)['lastname']);
    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    include use_template('account');
}

elseif (new_route('/DDWT18/week2/register/', 'get')) {
    $page_title = 'Register here';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2', False),
        'My account' => na('/DDWT18/week2/register/', True)
    ]);
    $navigation = get_navigation($template, 5);

    /* Page content*/
    $page_subtitle = 'Register here to get contribute to the overview';
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    include use_template('register');
}

/* Register POST */
elseif (new_route('/DDWT18/week2/register/', 'post')){
    /* Register user */
    $error_msg = register_user($db, $_POST);
    /* Redirect to homepage */
    redirect(sprintf('/DDWT18/week2/register/?error_msg=%s',
        json_encode($error_msg)));
}

/* Login GET */
elseif (new_route('/DDWT18/week2/login/', 'get')){
    if ( check_login() ) {
        redirect('/DDWT18/week2/myaccount/');
    }
 /* Page info */
 $page_title = 'Login';
 $breadcrumbs = get_breadcrumbs([
 'DDWT18' => na('/DDWT18/', False),
 'Week 2' => na('/DDWT18/week2/', False),
 'Login' => na('/DDWT18/week2/login/', True)
 ]);
 $navigation = get_navigation($template, 6);
    ;
 /* Page content */
 $page_subtitle = 'Use your username and password to login';
 /* Get error msg from POST route */
 if ( isset($_GET['error_msg']) ) { $error_msg = get_error($_GET['error_msg']); }

 /* Choose Template */
 include use_template('login');
}

/* Login POST */
elseif (new_route('/DDWT18/week2/login/', 'post')){
    /* Login user */
    $feedback = login_user($db, $_POST);
    /* Redirect to homepage */
    redirect(sprintf('/DDWT18/week2/login/?error_msg=%s',
        json_encode($feedback)));
}

elseif (new_route('/DDWT18/week2/logout/', 'get')) {
    $error_msg = logout_user($db);
    redirect(sprintf('/DDWT18/week2/?error_msg=%s', json_encode($error_msg)));
}

else {
    http_response_code(404);
}