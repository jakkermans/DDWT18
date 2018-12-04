<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost:3307', 'ddwt18_week3', 'ddwt18', 'ddwt18');

$cred = set_cred('ddwt18', 'ddwt18');
/* Create Router instance */
$router = new \Bramus\Router\Router();
$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred){
    if (!check_cred($cred)){
        echo 'Authentication required.';
        http_response_code(401);
        exit();
    }
    echo "Succesfully authenticated";
});


// Add routes here
$router->mount('/api', function() use ($router, $db) {
    http_content_type('application/json');
    /* GET for reading all series */

    $router->get('/series', function() use($db) {
        echo json_encode(get_series($db));
    });

    // will result in '/api/id'
    $router->get('/series/(\d+)', function($id) use ($db) {
        $serie_info = get_serieinfo($db, $id);
        echo json_encode($serie_info);
    });
    $router->delete('/series/(\d+)', function ($id) use ($db) {
        echo json_encode(remove_serie($db, $id));
    });
    $router->post('/series/', function () use($db) {
       $feedback = add_serie($db, $_POST);
       echo json_encode($feedback);
    });
    $router->put('/series/(\d+)', function ($id) use($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        $feedback = update_serie($db, $serie_info);
        echo json_encode($feedback);
    });
    $router->set404(function() {
        header('HTTP/1.1 404 Not Found');
        echo "404 error: this page was not found.";
    });
});

/* Run the router */
$router->run();
