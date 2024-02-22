# Laravel 10 API Template

---

POSTMAN COLLECTION:

[<img src="https://run.pstmn.io/button.svg" alt="Run In Postman" style="width: 128px; height: 32px;">](https://app.getpostman.com/run-collection/12320182-6fcac736-ede7-433f-8f2d-67ed43b992e0?action=collection%2Ffork&source=rip_markdown&collection-url=entityId%3D12320182-6fcac736-ede7-433f-8f2d-67ed43b992e0%26entityType%3Dcollection%26workspaceId%3D6b862512-f677-440f-a1d0-e79ff8671d6a)

## Features:

-   [API versioning (Currently V1)](#api-versioning)
-   [Email/Password authentication](#emailpassword-authentication)
-   [Users management](#users-management)
-   [Roles and permissions authorization and management](#roles-and-permissions-authorization-and-management)
-   [Permissions generator command](#permissions-generator-command)
-   [Super admin creator command](#super-admin-creator-command)
-   [Auditing](#auditing)
-   [Exception logging](#exception-logging)

### API Versioning

The whole API routes will be prefixed by the version (currently v1).

Example:

```
http://localhost:8000/api/v1/users
```

### Email/Password authentication

This template supports email/password authentication out of the box.

If you want to extend or add any other authentication methods please refer to [Laravel Documentation](https://laravel.com/docs/10.x/authentication)

### Users management

Users management (CRUD) is supported by the template out of the box. User can be managed by **Super admin** or authorized users.

### Roles and permissions authorization and management

As per the template, roles and permissions management (ACL) endpoints will be authorized to **Super admin** only.

_Note: **permissions** will be assigned to **roles**, and **roles** will be assigned to **users**. If you want to customize this, please refer to the [Spatie laravel-permission documentation](https://spatie.be/docs/laravel-permission/v6/introduction)_

Instead of creating your own permission, you can generate permissions using _**permission:sync**_ command. See [Permissions generator command](#permissions-generator-command) section.

### Permissions generator command

Permission can be generated based on the models in the **app/Models** folder using the command below:

```
php artisan permission:sync
```

Available arguments:
|Command|Shorthand|Description|
|---|---|---|
|--clean|-C|Delete all existing permissions and generate permissions|
|--policies|-P|Generate policies for the models|
|--oep|-O|Override exisiting policies|
|--yes-to-all|-Y|Execute all possible outcomes|

### Super admin creator command

**Super admin** role can be assigned to a user using the command below:

```
php artisan role:super-admin
```

_Note: If you have only one user in your database, the user will be automatically assigned **Super admin** role._

Available arguments:
|Command|Accept|Description|
|---|---|---|
|--user=|User ID|Assign **Super admin** role to the given user|

_If the **--user=** argument is not given, a list of users will be shown on the terminal._

### Auditing

You can simply audit the database changes by implementing **Auditable** and using **AuditingAuditable** trait in your model.

Example:

```
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Example implements Auditable
{
    use AuditingAuditable;
}
```

_For users having **Super admin** role, you can simply use **/audits** endpoint to fetch all the database audits._

For more information read the [documentation](https://laravel-auditing.com/)

### Exception logging

You can setup external logging system with **Telegram (Telegram bot)** by adding the following values in the **.env** file:

```
TELEGRAM_LOGGER_BOT_TOKEN=
TELEGRAM_LOGGER_CHAT_ID=
```

For this template Telegram logger is configured to log exceptions from **app/Exceptions/Handler.php** file. You can check and modify **logToTelegram** function in the file or simply uncomment the lines in the file as shown below:

```
// if (env('APP_ENV') === 'production') {
//     $this->logToTelegram($request, $message);
// }
```

For more information read the [documentation](https://github.com/grkamil/laravel-telegram-logging)

---

_This is not the final version of the template, and will be improved and updated over time._

_By [Ramdinpuia29](https://github.com/Ramdinpuia29)_
