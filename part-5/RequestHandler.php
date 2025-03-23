<?php

namespace App;

use DateTime;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use IntlDateFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class RequestHandler
{
    private Database $database;
    private Logger $logger;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->logger = new Logger('RequestHandler');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/logs/app.log', Level::Debug));
    }

    public function handleRequest(): void
    {
        $request = ServerRequest::fromGlobals();
        $method = $request->getMethod();
        $this->logger->info("Received request", ['method' => $method]);

        try {
            if ($method === 'POST') {
                $parsedBody = $request->getParsedBody();
                $overrideMethod = isset($parsedBody['_method']) ? strtoupper($parsedBody['_method']) : null;

                if (in_array($overrideMethod, ['PUT', 'DELETE'], true)) {
                    $method = $overrideMethod;
                }
            }

            $path = $request->getUri()->getPath();
            $this->logger->info("Handling path", ['path' => $path]);

            switch ($method) {
                case 'POST':
                    $this->handlePost($request);
                    break;
                case 'PUT':
                    $this->handlePut($request);
                    break;
                case 'DELETE':
                    $this->handleDelete($request);
                    break;
                case 'GET':
                    $this->handleGet();
                    break;
                default:
                    $this->sendResponse(new Response(405, [], "Method Not Allowed"));
                    $this->logger->warning("Invalid method", ['method' => $method]);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error handling request", ['message' => $e->getMessage()]);
            $this->sendResponse(new Response(500, [], "Internal Server Error"));
        }
    }

    private function renderFullPage(): string
    {
        $tasks = $this->database->getConnection()->query("SELECT * FROM tasks")->fetchAll();
        $fmt = new IntlDateFormatter('de_DE', IntlDateFormatter::LONG, IntlDateFormatter::NONE);

        $html = "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>To-Do List</title>
            <link rel='stylesheet' href='/styles.css'>
        </head>
        <body>
            <div id='task-container'>
            <h1>To-Do List</h1>
            <form method='POST'>
                <input type='text' name='description' placeholder='Enter a task' required>
                <button type='submit'>Add Task</button>
            </form>
            <ul>";

        foreach ($tasks as $task) {
            $dateFormatted = $fmt->format(new DateTime($task['created_at']));
            $html .= "<li>
                {$task['description']}  <small>{$dateFormatted}</small>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='id' value='{$task['id']}'>
                    <input type='hidden' name='state' value='" . (!$task['state'] ? 1 : 0) . "'>
                    <input type='hidden' name='_method' value='PUT'>
                    <button type='submit'>" . ($task['state'] ? 'Mark as Incomplete' : 'Mark as Complete') . "</button>
                </form>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='id' value='{$task['id']}'>
                    <button type='submit' name='_method' value='DELETE'>Delete</button>
                </form>
            </li>";
        }

        $html .= "</ul>
         </div>
        </body>
        </html>";

        return $html;
    }

    private function handlePost($request): void
    {
        $data = $this->parseRequestData($request);

        $description = $data['description'] ?? '';

        if (empty($description)) {
            $this->sendResponse(new Response(400, [], "Task description is required."));
            return;
        }

        $this->database->addTask($description);
        $this->sendResponse(new Response(201, [], $this->renderFullPage()));
    }



    private function handlePut($request): void
    {
        $data = $this->parseRequestData($request);

        $id = $data['id'] ?? null;
        $state = isset($data['state']) ? (int)$data['state'] : null;

        if (!$id || $state === null) {
            $this->sendResponse(new Response(400, [], "Task ID and state are required."));
            return;
        }

        if (!in_array($state, [0, 1], true)) {
            $this->sendResponse(new Response(400, [], "Invalid state value. Use 0 or 1."));
            return;
        }

        $this->database->changeTaskState($id, $state);
        $this->sendResponse(new Response(200, [], $this->renderFullPage()));
    }



    private function handleDelete($request): void
    {
        $data = $this->parseRequestData($request);

        $id = $data['id'] ?? null;

        if (!$id) {
            $this->sendResponse(new Response(400, [], "Task ID is required."));
            return;
        }

        $this->database->deleteTask((int) $id);
        $this->sendResponse(new Response(200, [], $this->renderFullPage()));
    }

    private function handleGet(): void
    {
        $this->sendResponse(new Response(200, [], $this->renderFullPage()));
    }

    private function parseRequestData($request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== false) {
            return json_decode($request->getBody()->getContents(), true) ?? [];
        }

        if ($request->getMethod() === 'PUT' || $request->getMethod() === 'DELETE') {
            parse_str($request->getBody()->getContents(), $parsedData);
            return $parsedData;
        }

        return $request->getParsedBody() ?? [];
    }

    private function sendResponse(Response $response): void
    {
        if (!headers_sent()) {
            http_response_code($response->getStatusCode());
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }
        echo $response->getBody();
    }
}
