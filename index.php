<?php
header('Content-Type: application/json; charset=utf-8');

require 'config/Database.php';
require 'config/Auth.php';
require 'models/EventModel.php';

$db = new Database();
$pdo = $db->getConnection();

$auth = new Auth($pdo);
$model = new EventModel($pdo);

$auth->requireApiKey();

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$segments = explode('/', $uri);

$entity = null;
$id = null;

$apiIndex = array_search('api', $segments);
if ($apiIndex !== false && isset($segments[$apiIndex + 1])) {
    $entity = $segments[$apiIndex + 1];
    if (isset($segments[$apiIndex + 2]) && is_numeric($segments[$apiIndex + 2])) {
        $id = (int)$segments[$apiIndex + 2];
    }
} else {
    if (isset($segments[1])) {
        $entity = $segments[1];
        if (isset($segments[2]) && is_numeric($segments[2])) {
            $id = (int)$segments[2];
        }
    }
}

if ($entity !== 'events') {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE && in_array($method, ['POST', 'PUT'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

switch ($method) {
    case 'GET':
        if ($id) {
            $res = $model->getById($id);
            if (!$res) {
                http_response_code(404);
                echo json_encode(['error' => 'Not found']);
            } else {
                echo json_encode($res);
            }
        } else {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $res = $model->getAll($limit, $offset);
            echo json_encode($res);
        }
        break;

    case 'POST':
        if (empty($input['title']) || empty($input['date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'title and date are required']);
            break;
        }
        $newId = $model->create($input);
        http_response_code(201);
        echo json_encode(['id' => $newId]);
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            break;
        }
        if (empty($input['title']) || empty($input['date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'title and date are required']);
            break;
        }
        $ok = $model->update($id, $input);
        if ($ok) echo json_encode(['message' => 'Updated']);
        else {
            http_response_code(500);
            echo json_encode(['error' => 'Update failed']);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            break;
        }
        $ok = $model->delete($id);
        if ($ok) echo json_encode(['message' => 'Deleted']);
        else {
            http_response_code(500);
            echo json_encode(['error' => 'Delete failed']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
