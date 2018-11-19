<?php
/**
 * Model
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Check if the route exist
 * @param string $route_uri URI to be matched
 * @param string $request_type request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    }
}

/**
 * Creates a new navigation array item using url and active status
 * @param string $url The url of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template filename of the template without extension
 * @return string
 */
function use_template($template){
    $template_doc = sprintf("views/%s.php", $template);
    return $template_doc;
}

/**
 * Creates breadcrumb HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        }else{
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the navigation
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }else{
            $navigation_exp .= '<li class="nav-item">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pritty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creats HTML alert code with information about the success or failure
 * @param bool $type True if success, False if failure
 * @param string $message Error/Success message
 * @return string
 */
function get_error($feedback){
    $error_exp = '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
    return $error_exp;
}
/**
 * Creates a connection with the database
 */
function connect_db($host, $db, $user, $passwd) {
    /*Create a connection with the database.*/
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE   => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $passwd, $options);
    } catch (\PDOException $e) {
        echo sprintf("Failed to connect. %s",$e->getMessage());
    }
    return $pdo;
}

/**
 * Counts the number of series that are present in the database.
 * @param PDO $pdo database object
 * @return mixed
 */
function count_series($pdo) {
    /*Count the number of series in the database.*/
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $number_series = $stmt->rowCount();
    return $number_series;
}

/**
 * Collects all the information in the database and stores it in a multidimensional array.
 * @param PDO $pdo database object
 * @return mixed
 */
function get_series($pdo) {
    /*Collect all information in the database and create an array.*/
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = Array();

    /*Store all information in the database in a multidimensional array*/
    foreach ($series as $key => $value) {
        foreach ($value as $user_key => $user_input) {
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $series_exp;
}

/**
 * Takes a multidimensional array and creates a table using that array.
 * @param $series
 * @return string
 */
function get_series_table($series) {
    /*Create a table with the information in the array.*/
    $table_exp = '
        <table class="table table-hover">
        <thead>
        <tr>
        <th scope="col">Series</th>
        <th scope="col"></th>
        </tr>
        </thead>
        <tbody>';
        foreach ($series as $key => $value) {
            $table_exp .= '
            <tr>
            <th scope="row">'.$value['name'].'</th>
            <td><a href="/DDWT18/week1/serie/?serie_id='.$value['id'].'" role="button" class="btn btn-primary" methods="GET">More info</a></td>
            </tr>
            ';
        }
    $table_exp .= '
    </tbody>
    </table>';
    return $table_exp;
}

/**
 * Collects the information of a serie in the database using a specific id that corresponds with the serie.
 * @param PDO $pdo database object
 * @return mixed
 */
function get_series_info($pdo, $serie_id) {
    /*Collects information of one entry.*/
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id= ?');
    $stmt->execute([$serie_id]);
    $serie_info = $stmt->fetch();
    $serie_info_exp = Array();

    foreach ($serie_info as $key => $value) {
        $serie_info_exp[$key] = htmlspecialchars($value);
    }
    return $serie_info_exp;
}

/**
 * Takes the information from the form and creates a new entry for the database with this information.
 * @param PDO $pdo database object
 * @return mixed
 */
function add_series($pdo) {
    /*Check if the seasons field contains a number.*/
    if (!is_numeric($_POST['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Please enter a number in the Seasons field.'
        ];
    }
    /*Check if all fields are set.*/
    if (
        empty($_POST['Name']) or
        empty($_POST['Creator']) or
        empty($_POST['Seasons']) or
        empty($_POST['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }
    /* Check if serie already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$_POST['Name']]);
    $serie = $stmt->rowCount();
    if ($serie){
        return [
            'type' => 'danger',
            'message' => 'This series was already added.'
        ];
    }
    /* Add Serie */
    $stmt = $pdo->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['Name'],
        $_POST['Creator'],
        $_POST['Seasons'],
        $_POST['Abstract']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' added to Series Overview.", $_POST['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not added. Try it again.'
        ];
    }
}

/**
 * Takes information from the form and updates an entry in the database with this information using a specific id corresponding with the serie that is being edited.
 * @param PDO $pdo database object
 * @return mixed
 */
function update_series($pdo) {
    /* Check if all fields are set */
    if (
        empty($_POST['Name']) or
        empty($_POST['Creator']) or
        empty($_POST['Seasons']) or
        empty($_POST['Abstract']) or
        empty($_POST['serieId'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check data type */
    if (!is_numeric($_POST['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field
Seasons.'
        ];
    }

    /* Get current series name */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$_POST['serieId']]);
    $serie = $stmt->fetch();
    $current_name = $serie['name'];

    /* Check if serie already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$_POST['Name']]);
    $serie = $stmt->fetch();
    if ($_POST['Name'] == $serie['name'] and $serie['name'] != $current_name){
        return [
            'type' => 'danger',
            'message' => sprintf("The name of the series cannot be changed. %s already exists.",
                $_POST['Name'])
        ];
    }

    /* Update Serie */
    $stmt = $pdo->prepare("UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?");
    $stmt->execute([
        $_POST['Name'],
        $_POST['Creator'],
        $_POST['Seasons'],
        $_POST['Abstract'],
        $_POST['serieId']
    ]);
    $updated = $stmt->rowCount();
    if ($updated == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was edited!", $_POST['Name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'The series was not edited. No changes were detected'
        ];
    }
}

/**
 * Removes an entry from the database using a specific id that corresponds with the serie that is being removed.
 * @param PDO $pdo database object
 * @return mixed
 */
function remove_serie($pdo, $serie_id, $serie_name) {
    /*Remove series*/
    $stmt = $pdo->prepare('DELETE FROM series WHERE id = ?');
    $stmt->execute([$serie_id]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was successfully deleted!", $serie_name)
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'The series was not deleted. No changes were made in the database.'
        ];
    }
}
