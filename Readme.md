# Notific

Notific is a simple, and minimal, static notification system for Laravel. It stores notification data based on user_id, and fetch notifications for the user_id. It's not _that_ feature rich.

[![GitHub release](https://img.shields.io/github/release-pre/technovistalimited/notific.svg?style=flat-square)](https://github.com/technovistalimited/notific/releases)
[![GitHub license](https://img.shields.io/badge/license-GPL2.0+-blue.svg?style=flat-square)](https://raw.githubusercontent.com/technovistalimited/notific/master/LICENSE.txt)
[![Laravel package](https://img.shields.io/badge/laravel-yes-orange.svg?style=flat-square)](https://laravel.com/)

**License:** GPL-2.0+<br>
**Developers:** [Mayeenul Islam](https://github.com/mayeenulislam), [Nazmul Hasan](https://github.com/nazmulcse)

---
<div align="center">
_________NOTICE_________<br>
The package is developed only for a sole project. If it helps you, that's why it's here.<br>
We might not support the package full throttle. But bug reports are welcome.
</div>


---

## Features

* Database table creation using migration
* Store notifications into database
* Fetch notifications from database
* Store and fetch any types of additional information for notification with meta data
* Cache data to save some valuable resources - configurable

Features that are _not_ present:
* Event listener and real time notification

## Installation

The package is **NOT AVAILABLE in Packagist**, hence you have to download it from this repository.<br>
[<kbd>**DOWNLOAD v0.2.0**</kbd>](https://github.com/technovistalimited/notific/releases/tag/v0.2.0)

#### Step 1: Put the package in place
* Create a directory in your app root with the name `packages`.
* Create another directory under `packages` named `technovistalimited`. (vendor name)
* Download the latest release, extract the archive and put it under the `packages\technovistalimited` directory.
* Your directory structure would be: `packages\technovistalimited\notific\src\...`

#### Step 2: Add the repository to your app
**composer.json**

Open up the `composer.json` of your app root and add the following line under `psr-4` `autoload` array:
```
"Technovistalimited\\Notific\\": "packages/technovistalimited/notific/src"
```

And add the following line under `files` `autoload` array:
```
"packages/technovistalimited/notific/src/helpers/helpers.php"
```

So that they would look similar to:
```
"autoload": {
    "psr-4": {
        "Technovistalimited\\Notific\\": "packages/technovistalimited/notific/src"
    },
    "files": [
        "packages/technovistalimited/notific/src/helpers/helpers.php"
    ]
}
```

**Providers array**

Add the following string to `config/app.php` under `providers` array:

```php
Technovistalimited\Notific\NotificServiceProvider::class,
```

#### Step 3: Let composer do the rest

Open up command console on the root of your app and run:

```
composer dump-autoload
```

#### Step 4: Configuration and migration
Make configuration and migration file[s] ready first:
```
php artisan vendor:publish --provider="Technovistalimited\Notific\NotificServiceProvider"
```

Run the migration
```
php artisan migrate
```

## Configuration
Change configuration in `config/notific.php`.

### Cache status:
Set whether to use the cache or not, under `'cache' => ['is_cache']`. Accepted value: `true` (enabled), `false` (disabled)<br>
_default: true_ - enabled

### Cache time:
Change the time under `'cache' => ['cache_time']`. Accepted value: any positive integer to denote minute.<br>
_default: 10_ minutes

## API: How to use
If you defined the helper class correctly, the package is easy to use:

#### Store notification
To store notification, simply place the following method where you want to plug it into. The method will also clear the cache for the user to update notification history.
 ```php
 notify( $userId, $message, $notificationType, $metaData, $createdBy );
 ```

> **$userID** : _integer/array_<br>
> User ID or array of user IDs to notify.
>
> **$message** : _string_<br>
> The message with which you want to notify with.<br>
>
> **$notificationType** : _string_ : (optional)<br>
> The notification type if you have any, other than notification.<br>
> _default: `'notification'`_
>
> **$metaData** : _integer/string/array_ : (optional)<br>
> Whatever additional information you want to pass with. Whether pass them as integer, string or as an array.<br>
> _default: empty_
>
> **$createdBy** : _integer_ : (optional)<br>
> If you want to keep trace who issued the notification.<br>
> _default: empty_


#### Get notifications
Get the notifications by user ID.
 ```php
 getNotifications( $userId, $arguments );
 ```

> **$userID** : _integer_<br>
> User ID for which to fetch the notifications.
>
> **$arguments** : _array_ : (optional)<br>
> Array of Query parameters.
> _default: `array()` - fetch notifications based on default settings_
>
>> **$read_status** : _string_<br>
>> The notification read status. Accepts: `'all'`, `'read'`, `'unread'`.<br>
>> _default: `'all'`_
>>
>> **$order** : _string_<br>
>> Designates ascending or descending order of notifications. Accepts `'ASC'`, `'DESC'`.<br>
>> _default: `'DESC'`_
>>
>> **$orderby** : _string_<br>
>> Sort retrieved posts by parameter. Single option can be passed. Accepts any valid column name from db table.<br>
>> _default: `'created_at'`_
>>
>> **$paginate** : _boolean_<br>
>> Whether to enable pagination or not.<br>
>> _default: `false` - pagination DEactivated_
>>
>> **$items_per_page** : _integer_<br>
>> Fetch the number of items. Accepts any positive integer.<br>
>> _default: `-1` - fetch everything_


#### Mark notification as _read_
Mark the notification as _read_ when actually they are. You might need AJAX to mark them _read_ on the fly.
 ```php
 markNotificationRead( $userId, $notificationId );
 ```

> **$userID** : _integer_<br>
> User ID to mark the notification as _read_ for.
>
> **$notificationId** : _integer_ : (optional)<br>
> If you want to mark each of the notification as read, pass the notification ID<br>
> _default: empty - mark all as read_


## Example

### 01. Create a notification
With the following examples a user with the ID `21` will be notified accordingly:
```php
// Notified with a simple message.
notify( 21, 'Your application submitted.' ) );

// Notified with a message and date.
notify( 21, sprintf( 'Your application submitted on %s is approved.', date('d F Y') ) );

// Notified with a different type of notification.
notify( 21, 'Your application submitted.', 'message' ) );

// Notified with some meta data incorporated.
notify( 21, 'Your application is approved. Click to see it.', 'notification', array('link' => 'http://link.to/the/application/' ) ) );

// Notified with someone who (with ID 8) assigned the notification
notify( 21, 'Your application submitted.', 'notification', '', 8 ) );

// Notify multiple users (with ID 21, 4, and 5) at a time
notify( [21, 4, 5], 'An application is submitted. Please check.' );
```

### 02. Get notifications
With following examples we're fetching the notifications assigned to a user with ID `21`:
```php
// Get all the notifications.
getNotifications( 21 );

// Get unread notifications only.
getNotifications( 21, array( 'read_status' => 'unread' ) );

// Get read notifications only.
getNotifications( 21, array( 'read_status' => 'read' ) );

// Get read notifications and maximum 10 of them only.
getNotifications( 21, array( 'read_status' => 'all', 'items_per_page' => 10 ) );

// Get all notifications and paginate them to 50 per page only.
getNotifications( 21, array( 'paginate' => true, 'items_per_page' => 50 ) );
```

### 03. Mark notification as _read_
```php
// Mark all the notifications as read for user with ID 21.
markNotificationRead( 21 );

// Mark notification number 56 as read for the user with ID 21.
markNotificationRead( 21, 56 );
```

## Contributions
Any bug report is welcome. We might not support the package as you might require. But we will definitely try to fix the bugs as long as they meet our leisure.

If you want to contribute code, feel free to add [Pull Request](https://github.com/technovistalimited/notific/pulls).

## Un-installation
Before un-installing the package, search the whole project of any declaration of `notify(`, `getNotifications(`, and `markNotificationRead(`, and remove the functions from the source, or comment 'em out. Otherwise they will generate fatal error after the un-installation.

Now, open command console, and type:
```
composer remove packages/technovistalimited/notific
```
When it's done, revert the installation process manually:

1. Open `composer.json` file and remove the two declarations of 'notific' under `psr-4` and `files` under `autoload`.
2. Open `config/app.php` and remove the line `...NotificServiceProvider::class` under `providers` array.
3. Remove the configuration file `notific.php` in `config/notific.php`
4. Delete the two database tables manually (as the package is not released): table:`notifications`, and table:`user_notifications`.

When you are done, open console and run:
```
composer dump-autoload
```
and
```
php artisan clear-compiled
```
If the package directory is not removed automatically, please manually remove the directory `packages/technovistalimited`.

You're done!

## Credits
All the credit goes to the almighty God first. Thanks to Mr. Amiya Kishore Saha who let both of us make our first Laravel package. Thanks to Mr. Kamrul Hasan for reviewing the progress and suggesting his ideas. And thanks to [TechnoVista Limited](http://technovista.com.bd/) for supporting the initiative. Thanks to [Notifynder](https://github.com/fenos/Notifynder) - a Laravel notification package available at Packagist from Fabrizio - we followed them to learn. Thanks to [WordPress](https://wordpress.org/) - a GPL licensed web framework - we took some of their code thankfully.
