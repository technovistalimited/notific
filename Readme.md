# 🔔 Notific

Notific is a simple, and minimal, static notification system for Laravel. It stores notification data based on user_id, and fetch notifications for the user_id. It's not _that_ feature rich.

The package **has no UI**, it just a database based cached data-driven mechanism of organized methods. You have to build you own UI.

[![GitHub release](https://img.shields.io/github/release-pre/technovistalimited/notific.svg?style=flat-square)](https://github.com/technovistalimited/notific/releases)
[![GitHub license](https://img.shields.io/badge/license-GPL2.0+-blue.svg?style=flat-square)](https://raw.githubusercontent.com/technovistalimited/notific/master/LICENSE.txt)
[![Laravel package](https://img.shields.io/badge/laravel-yes-orange.svg?style=flat-square)](https://laravel.com/)

**License:** GPL-2.0+<br>
**Requires:** Laravel 5.8<br>
**Tested up to:** Laravel 8.x<br>
**Developers:** [Mayeenul Islam](https://github.com/mayeenulislam), [Nazmul Hasan](https://github.com/nazmulcse)

---
<center>
_________NOTICE_________<br>
The package is developed only for a sole project. If it helps you, that's why it's here.<br>
We might not support the package full throttle. But bug reports are welcome.
</center>


---

## Features

* Database table creation using migration
* Store notifications into the database
* Fetch notifications from the database
* Store and fetch any types of additional information for notification with metadata
* Cache data to save some valuable resources - configurable

Features that are _not_ present:

* Event listener and real-time notification

## Installation

### Step 1: Put the package in place

Open up the command console on the root of your app and run:

```bash
git clone git@github.com:technovistalimited/notific.git packages/technovistalimited/notific/
```

### Step 2: Add the repository to your app

#### **composer.json**

Open up the `composer.json` of your app root and add the following line under `psr-4` `autoload` array:

```json
"Technovistalimited\\Notific\\": "packages/technovistalimited/notific/src"
```

So that they would look similar to:

```
"autoload": {
    "psr-4": {
        "Technovistalimited\\Notific\\": "packages/technovistalimited/notific/src"
    },
}
```

#### **Providers array**

Add the following string to `config/app.php` under `providers` array:

```php
Technovistalimited\Notific\NotificServiceProvider::class,
```

#### **Aliases array**

Add the following line to the `config/app.php` under aliases array:

```php
'Notific' => Technovistalimited\Notific\Facades\Notific::class,
```

### Step 3: Let the composer do the rest

Open up the command console on the root of your app and run:

```bash
composer dump-autoload
```

### Step 4: Configuration and migration

Make configuration and migration files ready first:

```bash
php artisan vendor:publish --tag=notific
```

Run the migration

```bash
php artisan migrate
```

## Configuration

Change configuration in `config/notific.php`.

### Cache status

Set whether to use the cache or not, under `'cache' => ['is_cache']`. Accepted value: `true` (enabled), `false` (disabled)<br>
Default: _true_ - enabled

### Cache time

Change the time under `'cache' => ['cache_time']`. Accepted value: any positive integer to denote seconds.<br>
Default: _10_ minutes

## API: How to use

If you defined the helper class correctly, the package is easy to use:

### Store notification

To store notification, simply place the following method where you want to plug it into. The method will also clear the cache for the user to update the notification history.

 ```php
 Notific::notify( $userId, $message, $notificationType, $metaData, $createdBy );
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


### Get notifications

Get the notifications by user ID.

 ```php
 Notific::getNotifications( $userId, $arguments );
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

### Get notification count

Get the total count of notifications by user ID.

 ```php
 Notific::getNotificationCount( $userId, $status );
 ```

> **$userID** : _integer_<br>
> User ID for which to fetch the notifications.
>
> **$status** : _string_ : (optional)<br>
> The notification read status. Accepts: `'all'`, `'read'`, `'unread'`.<br>
> _default: `all` - fetch all the notification count_

### Mark notification as _read_

Mark the notification as _read_ when actually they are. You might need AJAX to mark them _read_ on the fly.

 ```php
Notific::markNotificationRead( $userId, $notificationId );
 ```

> **$userID** : _integer_<br>
> User ID to mark the notification as _read_ for.
>
> **$notificationId** : _integer_ : (optional)<br>
> If you want to mark each of the notification as read, pass the notification ID<br>
> _default: empty - mark all as read_


## Examples

### 01. Create a notification

With the following examples, a user with the ID `21` will be notified accordingly:

```php
// Notified with a simple message.
Notific::notify( 21, 'Your application submitted.' ) );

// Notified with a message and date.
Notific::notify( 21, sprintf( 'Your application submitted on %s is approved.', date('d F Y') ) );

// Notified with a different type of notification.
Notific::notify( 21, 'Your application submitted.', 'message' ) );

// Notified with some meta data incorporated.
Notific::notify( 21, 'Your application is approved. Click to see it.', 'notification', array('link' => 'http://link.to/the/application/' ) ) );

// Notified with someone who (with ID 8) assigned the notification
Notific::notify( 21, 'Your application submitted.', 'notification', '', 8 ) );

// Notify multiple users (with ID 21, 4, and 5) at a time
Notific::notify( [21, 4, 5], 'An application is submitted. Please check.' );
```

### 02. Get notifications

With the following examples, we're fetching the notifications assigned to a user with ID `21`:

```php
// Get all the notifications.
Notific::getNotifications( 21 );

// Get unread notifications only.
Notific::getNotifications( 21, array( 'read_status' => 'unread' ) );

// Get read notifications only.
Notific::getNotifications( 21, array( 'read_status' => 'read' ) );

// Get read notifications and maximum 10 of them only.
Notific::getNotifications( 21, array( 'read_status' => 'all', 'items_per_page' => 10 ) );

// Get all notifications and paginate them to 50 per page only.
Notific::getNotifications( 21, array( 'paginate' => true, 'items_per_page' => 50 ) );
```

### 03. Get notification count

With the following examples, we're fetching the count of notifications assigned to a user with ID `21`:

```php
// Get all notifications of the user ID 21.
Notific::getNotificationCount( 21 );
Notific::getNotificationCount( 21, 'all' );

// Get only the 'unread' notifications of the user ID 21.
Notific::getNotificationCount( 21, 'unread' );

// Get only the 'read' notifications of the user ID 21.
Notific::getNotificationCount( 21, 'read' );
```

### 04. Mark notification as _read_

```php
// Mark all the notifications as 'read' for the user with ID 21.
Notific::markNotificationRead( 21 );

// Mark notification number 56 as 'read' for the user with ID 21.
Notific::markNotificationRead( 21, 56 );
```

### 05. Maybe Unserialize

Adopted from WordPress, this function can detect whether to unserialize a string or not and serialize, if it's a serialized data. If you make custom methods, you might need to convert the serialized meta data back into array - this method will help you then.

```php
// Unserialize if serialized.
Notific::maybeUnserialize( $string );
```

## Contributions

Any bug report is welcome. We might not support the package as you might require. But we will definitely try to fix the bugs as long as they meet our leisure.

If you want to contribute code, feel free to add [Pull Request](https://github.com/technovistalimited/notific/pulls).

## Uninstallation

Before uninstalling the package, search the whole project of any declaration of:

* `Notific::notify(`,
* `Notific::getNotifications(`, and
* `Notific::markNotificationRead(`,

and remove the functions from the source, or comment them out. Otherwise, they will generate a fatal error after uninstallation.

Now, open up the command console, and type:

```bash
composer remove packages/technovistalimited/notific
```

When it is done, revert the installation process manually:

1. Open `composer.json` file and remove the two declarations of 'notific' under `psr-4` under `autoload`.
2. Open `config/app.php` and remove the line `...NotificServiceProvider::class` under `providers` and `aliases` array.
3. Remove the configuration file `notific.php` in `config/notific.php`
4. Delete the two database tables manually (as the package is not released): table:`notifications`, and table:`user_notifications`.

When you are done, open console and run:

```bash
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
