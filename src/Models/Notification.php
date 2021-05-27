<?php

namespace Technovistalimited\Notific\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Technovistalimited\Notific\Models\UserNotification;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'message',
        'notification_type',
        'meta',

        'created_by',
        'created_at',
    ];

    /**
     * API: Notify.
     *
     * The method will store the notification into the respective tables.
     *
     * @param  integer|array        $userId           User ID or Array of user IDs.
     * @param  string               $message          Notification message.
     * @param  string               $notificationType Type of the notification.
     * @param  string|integer|array $metaData         Any meta information.
     * @param  null|integer         $createdBy        Created by user ID.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static. PSR-2 fixes.
     *
     * @see $this->storeNotification() Storing notification.
     * @see UserNotification::store()  Assigning notification to user[s].
     *
     * @return boolean True if done, false otherwise.
     */
    public function notify($userId, $message, $notificationType, $metaData, $createdBy)
    {
        // Clear the user notification cache.
        $this->clearCache($userId);

        // Store the notification.
        $notificationId = $this->store($message, $notificationType, $metaData, $createdBy);

        $userNotification = new UserNotification;

        // Notify the user one by one.
        if (!empty($notificationId)) {
            if (is_array($userId)) {
                foreach ($userId as $uId) {
                    $userNotification->store($uId, $notificationId);
                }
            } else {
                $userNotification->store($userId, $notificationId);
            }

            return true;
        }

        return false;
    }

    /**
     * Store Notification.
     *
     * @param  string               $message          Notification message.
     * @param  string               $notificationType Type of the notification.
     * @param  string|integer|array $metaData         Any meta information.
     * @param  null|integer         $createdBy        Created by user ID.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static. PSR-2 fixes.
     *
     * @return integer Notification ID, otherwise null.
     */
    public function store($message, $notificationType = '', $metaData = '', $createdBy = '')
    {
        /**
         * Set default to NotificationType.
         * @var string.
         */
        $notificationType = !empty($notificationType) ? $notificationType : 'notification';

        /**
         * MaybeSerialize Meta data.
         * Serialize meta data if necessary.
         * @var integer|string|array.
         * ...
         */
        $metaData = $this->maybeSerialize($metaData);

        /**
         * Typecast the author info.
         * @var null|integer.
         * ...
         */
        $createdBy = !empty($createdBy) ? intval($createdBy) : null;

        return DB::table('notifications')
            ->insertGetId([
                'message'           => trim($message),
                'notification_type' => trim($notificationType),
                'meta'              => $metaData,
                'created_by'        => $createdBy,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Clear specific cache.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static.
     * @since 1.0.2 Fixed for array of User IDs.
     *
     * @param integer|array $userId User ID/IDs to clear cache for.
     */
    public function clearCache($userId)
    {
        /**
         * Is cache enabled?
         * Whether or not the caching is enabled.
         * @var boolean.
         * ...
         */
        $isCache = (bool) config('notific.cache.is_cache');

        // Cache is disabled, no need to proceed.
        if (!$isCache) {
            return;
        }


        if (is_array($userId)) {
	        foreach ($userId as $uId) {
	        	/**
		         * Cache Key.
		         * Manage the cache files with the key defined.
		         * @var string.
		         * ...
		         */
		        $cacheKey = "notific_$uId";

		        if (Cache::has($cacheKey)) {
		            Cache::forget($cacheKey);
		        }
	        }
        } else {
        	/**
	         * Cache Key.
	         * Manage the cache files with the key defined.
	         * @var string.
	         * ...
	         */
	        $cacheKey = "notific_$userId";

	        if (Cache::has($cacheKey)) {
	            Cache::forget($cacheKey);
	        }
        }
    }

    /**
     * Parse Arguments.
     *
     * Parse user defined arguments and mix them with default
     * arguments defined.
     *
     * Adopted from WordPress Core, then modified.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static.
     *
     * @param  array $args     User defined arguments.
     * @param  array $defaults Default arguments.
     * @return array           Merged version of arguments.
     */
    public function parseArguments($args, $defaults)
    {
        if (!is_array($args) || !is_array($defaults)) {
            return 'Both the parameters need to be array';
        }

        $r = &$args;

        return array_merge($defaults, $r);
    }

    /**
     * Unserialize if necessary.
     *
     * Adopted from WordPress Core.
     *
     * @param array|string $original Data want to unserialize.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static.
     *
     * @return array|string Data | Unserialized data.
     */
    public function maybeUnserialize($original)
    {
        // Don't attempt to unserialize data that wasn't serialized, going in.
        if ($this->isSerialized($original)) {
            return @unserialize($original);
        }

        return $original;
    }

    /**
     * Serialize data.
     *
     * Adopted from WordPress Core.
     *
     * @param string|array $data Data to be serialized.
     *
     * @since 0.2.0 Introduced.
     *
     * @return string Serialized data | String.
     */
    public function maybeSerialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }

        if ($this->isSerialized($data, false)) {
            return serialize($data);
        }

        return $data;
    }


    /**
     * Check whether is serialized?
     *
     * Adopted from WordPress Core.
     *
     * @param string|array $data   Data to check serialization.
     * @param boolean      $strict Strict or not.
     *
     * @since 0.2.0 Introduced.
     *
     * @return boolean True | False.
     */
    private static function isSerialized($data, $strict = true)
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                // Or, else fall through.
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }

        return false;
    }
}
