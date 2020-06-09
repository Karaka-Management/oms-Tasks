# Task

## Create

```php
$response = new HttpResponse();
$request  = new HttpRequest();

$request->setData('id', <id>);
$request->setData('title', <title>);
$request->setData('description', <description>);
$request->setData('due', <due>);
$request->setData('status', <status>);
$request->setData('type', <type>);
$request->setData('priority', <priority>);
$request->setData('closable', true|false); // optional
$request->setData('editable', true|false); // optional

$module = $this->app->moduleManager->get('Tasks');
$module->apiTaskCreate($request, $response);
```

#### Web

| HTTP Method | URI    |
| ----------- | ------ |
| POST        | /tasks |

```
{
    "id": <id>,
    "title": <title>,
    "description": <description>,
    "due": <due>,
    "status": <status>,
    "type": <type>,
    "priority": <priority>,
    "closable": true|false
    "editable": true|false
}
```

### Type

Aside from the normal tasks it's also possible to mark task completely invisible in the task overview and task list. This may be helpful if other modules want to make use of the Tasks module without directly showing this to the user. Additionally, it's also possible to define task templates which can be re-used.

```php
$response = new HttpResponse();
$request  = new HttpRequest();

$request->setData('id', <id>);
$request->setData('type', <type>);

$module = $this->app->moduleManager->get('Tasks');
$module->apiTaskSet($request, $response);
```

#### Web

| HTTP Method | URI    |
| ----------- | ------ |
| POST        | /tasks |

```
{
    "id": <id>,
    "type": <type>
}
```

### Editable

```php
$response = new HttpResponse();
$request  = new HttpRequest();

$request->setData('id', <id>);
$request->setData('editable', true|false);

$module = $this->app->moduleManager->get('Tasks');
$module->apiTaskSet($request, $response);
```

#### Web

| HTTP Method | URI    |
| ----------- | ------ |
| POST        | /tasks |

```
{
    "id": <id>,
    "editable": true|false
}
```

### Closable

By default tasks are closable by a user in the task itself however, in some situations users should not be able to manually close a task but the task should be closed automatically depending on actions in a different module. By defining a task as none-closable the task can only be closed based on the action in a different module and a user cannot directly close a task in the Tasks module.

```php
$response = new HttpResponse();
$request  = new HttpRequest();

$request->setData('id', <id>);
$request->setData('closable', true|false);

$module = $this->app->moduleManager->get('Tasks');
$module->apiTaskSet($request, $response);
```

#### Web

| HTTP Method | URI    |
| ----------- | ------ |
| POST        | /tasks |

```
{
    "id": <id>,
    "closable": true|false
}
```

## Status Change

### API

#### Internal

```php
$response = new HttpResponse();
$request  = new HttpRequest();

$request->setData('id', <id>);
$request->setData('status', <status>);

$module = $this->app->moduleManager->get('Tasks');
$module->apiTaskSet($request, $response);
```

#### Web

| HTTP Method | URI    |
| ----------- | ------ |
| POST        | /tasks |

```
{
    "id": <id>,
    "status": <status>
}
```