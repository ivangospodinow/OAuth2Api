<?php

use App\Tests\Traits\EntityMocker;

require_once __DIR__ . '/../tests/Traits/EntityMocker.php';

$tester = new class

{
    use EntityMocker;

    const URL = 'http://127.0.0.1:8888';
    private $token;

    function start()
    {
        echo 'Testing Projects and Tasks by calling localhost instance of the api.' . PHP_EOL;
        if (false === $this->get('/')['status'] ?? false) {
            echo 'Localhost api is not running. To start it use: `composer run-script localhost`. Exiting...' . PHP_EOL;
            exit;
        }

        echo 'Obtaining token...' . PHP_EOL;
        $result = $this->post('/api/login_check', ['username' => 'admin', 'password' => 'admin']);
        $token = $result['token'] ?? false;
        if ($token === false) {
            echo 'Unable to obtain token. Please make sure that the local server and database are running.' . PHP_EOL;
            exit;
        }
        $this->token = $token;

        echo 'Token: ' . $token . PHP_EOL;

        $projects = $this->createData();
        echo 'Writing test' . PHP_EOL;
        foreach ($projects as $pkey => $project) {
            $tasks = $project['tasks'];
            unset($project['tasks']);

            $result = $this->post('/api/project', $project);
            $projectId = $result['data']['id'];
            $projects[$pkey]['id'] = $projectId;

            echo '.';
            foreach ($tasks as $tkey => $task) {
                $task['project'] = $projectId;
                $result = $this->post('/api/task', $task);
                $taskId = $result['data']['id'];
                $projects[$pkey]['tasks'][$tkey]['id'] = $taskId;
                echo '.';
            }
        }

        echo PHP_EOL;
        echo 'Reading test' . PHP_EOL;

        foreach ($projects as $pkey => $project) {
            $tasks = $project['tasks'];

            $result = $this->get('/api/project?filter[id]=' . $project['id']);
            $projectId = $result['data']['list'][0]['id'];

            echo $projectId === $project['id'] ? '.' : 'X';

            foreach ($tasks as $tkey => $task) {
                $result = $this->get('/api/task?filter[id]=' . $task['id']);
                $taskId = $result['data']['list'][0]['id'];

                echo $taskId === $task['id'] ? '.' : 'X';
            }
        }

        echo PHP_EOL;
        echo 'Updating test' . PHP_EOL;

        foreach ($projects as $pkey => $project) {
            $tasks = $project['tasks'];

            $updatedProjectData = $this->getProjectDataMock($project['id']);

            $this->put('/api/project/' . $project['id'], $updatedProjectData);

            $result = $this->get('/api/project?filter[id]=' . $project['id']);
            $item = $result['data']['list'][0];

            echo $updatedProjectData['id'] === $item['id'] && $updatedProjectData['title'] === $item['title'] ? '.' : 'X';

            foreach ($tasks as $tkey => $task) {

                $updatedTaskData = $this->getTaskDataMock($task['id']);
                $updatedTaskData['project'] = $project['id'];

                $this->put('/api/task/' . $task['id'], $updatedTaskData);

                $result = $this->get('/api/task?filter[id]=' . $task['id']);
                $item = $result['data']['list'][0];

                echo $updatedTaskData['id'] === $item['id'] && $updatedTaskData['name'] === $item['name'] ? '.' : 'X';
            }
        }

        echo PHP_EOL;
        echo 'Deleting test' . PHP_EOL;

        foreach ($projects as $pkey => $project) {
            $tasks = $project['tasks'];

            foreach ($tasks as $tkey => $task) {

                $this->delete('/api/task/' . $task['id']);

                $result = $this->get('/api/task?filter[id]=' . $task['id']);
                $item = $result['data']['list'];

                echo empty($item) ? '.' : 'X';
            }

            $this->delete('/api/project/' . $project['id']);

            $result = $this->get('/api/project?filter[id]=' . $project['id']);

            $item = $result['data']['list'];

            echo empty($item) ? '.' : 'X';
        }

        echo PHP_EOL;
        echo 'Testing done.' . PHP_EOL;
    }

    function createData()
    {
        $projects = [];
        // at least one with no tasks
        $projects[] = $this->getProjectDataMock();
        unset($projects[0]['id']);
        $projects[0]['tasks'] = [];

        $o = 10;
        while (--$o > 0) {
            $project = $this->getProjectDataMock();
            unset($project['id']);
            $tasks = [];

            $t = rand(0, 9);
            while (--$t >= 0) {
                $task = $this->getTaskDataMock();
                unset($task['id']);
                $tasks[] = $task;
            }

            $project['tasks'] = $tasks;
            $projects[] = $project;
        }

        return $projects;
    }

    function get($path)
    {
        $headers = [
            'Content-Type: application/json',
        ];
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this::URL . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        return $this->checkForErrors(@json_decode($result, true) ?: []);
    }

    function post($path, $data)
    {
        return $this->request($path, $data, 'POST');
    }

    function put($path, $data)
    {
        return $this->request($path, $data, 'PUT');
    }

    function delete($path)
    {
        return $this->request($path, [], 'DELETE');
    }

    function request($path, $data, $type)
    {
        $headers = [
            'Content-Type: application/json',
        ];
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this::URL . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);

        return $this->checkForErrors(@json_decode($result, true) ?: []);
    }

    function checkForErrors(array $response): array
    {
        if (isset($response['errors'])) {
            echo 'Error: ' . $response['code'] . ' ';
            $errors = [];
            foreach ($response['errors'] as $error) {
                $errors[] = sprintf(
                    '%s: %s',
                    $error['property'],
                    $error['message']
                );
            }
            echo implode(', ', $errors);
            exit;
        }

        return $response;
    }

};

$tester->start();
