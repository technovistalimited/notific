# Changelog

> **CHANGES**<br>
> Use the following command for the commits in-between two releases:<br>
> `git log 'v1.0.0'..'v1.0.1' --oneline` # changes between v1.0.0-v1.0.1<br>
> `git log 'v1.0.0'..master --oneline` # changes between v1.0.0-master

## Unreleased

<details>
  <summary>
    Changes that have landed in master but are not yet released.
    <strong>Click to see more</strong>.
  </summary>

* Nothing for now :)

</details>

## `v1.0.0` - 2021-04-06 - First Public Release

* Reorganized the structure of the package to match with the new Laravel package architecture
* New API based on Facades (Has breaking changes)
  * Prefix all the Notific functions with `Notific::`, eg. `getNotifications()` will be `Notific::getNotifications()`
  * Modify the `composer.json` and **remove** this line from `files` array: `"packages/technovistalimited/notific/src/helpers/helpers.php"`
  * Run `composer dump-autoload` again
  * In database, the `notifications.id` and `user_notifications.id` are now `bigInteger`
* Removed helper function
* Fixes in Cache time (since Laravel 5.8 cache time is in seconds)
* Reorganized model code into their own models
* Reorganized migrations into separate files
* PSR-2 compatible code
* Minimum Laravel version bumped to v5.8

## `v0.2.0` - 2017-08-21 - Transferred Ownership

* The package was developed for TechnoVista Limited, by the developers of TechnoVista. Due to the absence of a Github account, the package was hosted on Mayeenul Islam's personal account. Now it's transferred to TechnoVista Limited's Github account.

Readme file updated with un-installation documentation.
* Documentation: Uninstallation

## `v0.1.4` - 2017-05-30 - Bug fixed

* Fixed a directory name to cope in Linux environment (Issue #1)

## `v0.1.3` - 2017-05-18 - Configurable cache feature

* Added `'is_cache'` in configuration file

### Migration requirement

A New configurable parameter is been added to enable/disable caching throughout the package. Requested to sync the `config\notific.php` with the file `packages\mayeenulislam\notific\src\config\notific.php` for the changes


## `v0.1.2` - 2017-05-18 - Bug fixed and organized

* Organized the use of `getNotifications()` with an arguments array. User can pass several variables using the array
* Fix: Paginated result fixed with caching

## `v0.1.1` - 2017-05-15 - Feature added version

* Added count to set number of notifications to be fetched


## `v0.1.0` - 2017-05-14 - First version

* Database table creation using migration
* Store notifications into the database
* Fetch notifications from the database
* Store and fetch any types of additional information for notification with metadata
* Cache data to save some valuable resources
