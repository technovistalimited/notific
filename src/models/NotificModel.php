<?php

namespace Mayeenulislam\Notific\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificModel extends Model
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
     * @since  1.0.0 Introduced.
     *
     * @see    self::storeNotification()					Storing notification.
     * @see    self::storeUserNotification()				Assigning notification to user[s].
     *
     * @param  integer|array 			$userId     		User ID or Array of user IDs.
     * @param  string 					$message   			Notification message.
     * @param  string 					$notificationType   Type of the notification.
     * @param  string|integer|array 	$metaData   		Any meta information.
     * @param  null|integer 			$createdBy  		Created by user ID.
     * @return boolean              						True if done, false otherwise.
     * ---------------------------------------------------------------------
     */
    public static function notify( $userId, $message, $notificationType, $metaData, $createdBy )
    {
    	// Clear the user notification cache first.
    	self::clearNotificationCache( $userId );

    	// Store the notification first.
    	$notificationId = self::storeNotification( $message, $notificationType, $metaData, $createdBy );

    	// Notify the user one by one.
    	if( !empty($notificationId) ) {
    		if( is_array($userId) ) {
    			foreach( $userId as $uId ) {
    				self::storeUserNotification( $uId, $notificationId );
    			}
    		} else {
    			self::storeUserNotification( $userId, $notificationId );
    		}

    		return true;
    	}

    	return false;

    }

    /**
     * Store Notification.
     *
     * @since  1.0.0 Introduced.
     *
     * @param  string 					$message   			Notification message.
     * @param  string 					$notificationType   Type of the notification.
     * @param  string|integer|array 	$metaData   		Any meta information.
     * @param  null|integer 			$createdBy  		Created by user ID.
     * @return integer              						Notification ID, otherwise null.
     * ---------------------------------------------------------------------
     */
    public static function storeNotification( $message, $notificationType = '', $metaData = '', $createdBy = '' )
    {
    	if( empty($message) ) return 'Message cannot be empty';

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
		$metaData  = self::maybeSerialize($metaData);

		/**
		 * Typecast the author info.
		 * @var null|integer.
		 * ...
		 */
		$createdBy = !empty($createdBy) ? intval($createdBy) : null;

    	$notificationID = DB::table('notifications')->insertGetId([
            'message'           => trim($message),
            'notification_type' => trim($notificationType),
            'meta'              => $metaData,
            'created_by'        => $createdBy,
            'created_at'        => date('Y-m-d H:m:s')
    	]);

    	return $notificationID;
    }


    /**
     * Store User Notification.
     *
     * @since  1.0.0 Introduced.
     *
     * @param  integer|array 	$userId       		User ID or Array of user IDs.
     * @param  integer 			$notificationId 	Notification ID.
     * @return integer                  			User Notification ID, otherwise null.
     * ---------------------------------------------------------------------
     */
    public static function storeUserNotification( $userId, $notificationId )
    {
    	if( empty($userId) ) return 'User ID should be there';
    	if( empty($notificationId) ) return 'Notification ID should be there';

    	$userNotifyId = DB::table('user_notifications')->insertGetId([
			'user_id'         => intval($userId),
			'notification_id' => intval($notificationId),
			'is_read'         => 0
    	]);

    	return $userNotifyId;
    }

    /**
     * Clear specific cache.
     *
     * @since  1.0.0 Introduced.
     *
     * @param  integer $userId User ID to clear cache for.
     * @return void.
     * ---------------------------------------------------------------------
     */
    public static function clearNotificationCache( $userId )
    {
    	if(empty($userId)) return 'You must define a user ID';

    	/**
    	 * Cache Key.
    	 * Manage the cache files with the key defined.
    	 * @var string.
    	 * ...
    	 */
    	$cacheKey = "notific_$userId";

    	if( Cache::has($cacheKey) ) {
    		Cache::forget($cacheKey);
    	}
    }

    /**
     * Mark Notification as read.
     *
     * @since  1.0.0 Introduced.
     *
     * @param  integer $userId         User ID.
     * @param  integer $notificationId Notification ID.
     * @return boolean                 If done true, false otherwise.
     * ---------------------------------------------------------------------
     */
    public static function markNotificationRead( $userId, $notificationId = '' )
    {
    	if(empty($userId)) return 'You must define a user ID';

    	if( empty($notificationId) ) {
	    	$result = DB::table('user_notifications')
	    	        ->where('user_id', $userId)
	                ->update([
						'is_read'    => 1,
						'updated_at' => date('Y-m-d H:m:s')
	                ]);
        } else {
        	$result = DB::table('user_notifications')
	    	        ->where('user_id', $userId)
	    	        ->where('notification_id', $notificationId)
	                ->update([
						'is_read'    => 1,
						'updated_at' => date('Y-m-d H:m:s')
	                ]);
        }


        if( empty($result) ) {
    		return false;
        } else {
        	// Clear the user notification cache first.
    		self::clearNotificationCache( $userId );

    		return true;
        }
    }

	/**
	 * Retrieve Notifications.
	 *
	 * @since  1.0.0 Introduced.
	 *
	 * @param  integer $userId   Individual user ID.
	 * @param  array   $arguments {
	 *     Optional. Array of Query parameters.
	 *
	 *     @type string $read_status 		The notification read status.
	 *           							Accepts 'read', 'unread', 'all'.
	 *           							Default 'all'.
	 *     @type string $order          	Designates ascending or descending order of
	 *           							notifications.
	 *           							Accepts 'ASC', 'DESC'.
	 *           							Default 'DESC'.
	 *     @type string $orderby        	Sort retrieved posts by parameter. Single
	 *           							option can be passed.
	 *           							Accepts any valid column name from db table.
	 *     @type boolean $paginate      	Whether to enable pagination or not.
	 *           							Default false - pagination DEactivated.
	 *     @type boolean $items_per_page	Fetch the number of items.
	 *           							Accepts any positive integer.
	 *           							Default -1 - fetch everything.
	 * }
	 * @return array  Notifications object.
	 * ---------------------------------------------------------------------
	 */
	public static function getNotifications( $userId, $arguments = array() )
	{
		if( empty($userId) ) return 'User ID must be set';

		$defaults = array(
			'read_status'    => 'all',
			'order'          => 'DESC',
			'orderby'        => 'created_at',
			'paginate'       => false,
			'items_per_page' => -1,
		);

		$baseArgs = self::parseArguments( $arguments, $defaults );

		switch ($baseArgs['read_status']) {
			case 'read':
				$isRead = 1;
				break;

			case 'unread':
				$isRead = 0;
				break;

			default:
				$isRead = 0;
				break;
		}

		/**
		 * Cache Key.
		 * Manage the cache files with the key defined.
		 * @var string.
		 * ...
		 */
		$cacheKey = "notific_$userId";

	    /**
	     * Override the Cache Time, if you want.
	     * Default: 10 minutes.
	     * @var integer.
	     * ...
	     */
	    $cacheTime = (int) config('notific.cache.cache_time');

	    /**
	     * Cache and return data.
	     * @var null|object.
	     * ...
	     */
	    $values = Cache::remember($cacheKey, $cacheTime, function() use( $userId, $baseArgs, $isRead ) {

	    	$query = DB::table( 'user_notifications' )
	                ->leftJoin( 'notifications', 'user_notifications.notification_id', '=', 'notifications.id' )
	                ->where( 'user_notifications.user_id', $userId )
	                ->select( 'notifications.*' );

	    	if( 'all' === $baseArgs['read_status'] ) {
	    		$query = $query;
	    	} else {
	    		$query = $query->where( 'user_notifications.is_read', $isRead );
	    	}

	    	$query = $query->orderBy($baseArgs['orderby'], $baseArgs['order']);

	    	if( -1 != $baseArgs['items_per_page'] ) {
	    		if( $baseArgs['paginate'] ) {
	    			// BUG - needed a hack, done below.
	    			// using get() instead of paginate() because Cache CANNOT return LengthAwarePaginator object.
	    			$query = $query->get();
	    		} else {
	    			$count = abs( intval( $baseArgs['items_per_page'] ) );
		    		$query = $query->take($count)->get();
	    		}
	    	} else {
				$query = $query->get();
	    	}

	    	return $query;

	    });

	    /**
	     * MaybeUnserialize.
	     * Unserialize the meta value if necessary.
	     * @var array.
	     * ...
	     */
	    foreach( $values as $key => $value ) :
			$values[$key]->meta = self::maybeUnserialize($value->meta);
	    endforeach;

	    /**
	     * Fixed Bug: Pagination on Cached data.
	     *
	     * Laravel Cache::remember is always returning an 'array' of the data retrieved,
	     * not the LengthAwarePaginator class object. Hence we need to prepare the
	     * pagination on our own.
	     *
	     * With the hack we're using LengthAwarePaginator class to get current page number,
	     * current path, then making the array into a Laravel collection, and slicing it
	     * to let the result paginate each time.
	     *
	     * Finally using the LengthAwarePaginator class to build the paginated object for
	     * us to let us use the $object->links() whenever necessary.
	     *
	     * @since   1.0.0 Introduced.
	     *
	     * @author  psampaz
	     * @link    http://psampaz.github.io/custom-data-pagination-with-laravel-5/
	     *
	     * @author  Joey Hammett
	     * @link    http://psampaz.github.io/custom-data-pagination-with-laravel-5/#comment-2787553172
	     *
	     * @author  Elvis Magagula
	     * @link    http://psampaz.github.io/custom-data-pagination-with-laravel-5/#comment-2836459319
	     * ...
	     */
	    if( $baseArgs['paginate'] ) {
			// Get current page form url e.g. &page=6.
			$currentPage = LengthAwarePaginator::resolveCurrentPage();

			// Get current path.
			$currentPath = LengthAwarePaginator::resolveCurrentPath();

			// Create a new Laravel collection from the array data.
			$collection  = new Collection($values);

			// Define how many items we want to be visible in each page.
			$perPage     = abs( intval( $baseArgs['items_per_page'] ) );

			// Slice the collection to get the items to display in current page.
			$results     = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();

			// Create our paginator and pass it to the view.
			$values      = new LengthAwarePaginator($results, count($collection), $perPage, $currentPage, ['path' => $currentPath]);
	    }

	    return $values;
	}

	/**
	 * Parse Arguments.
	 *
	 * Parse user defined arguments and mix them with default
	 * arguments defined.
	 *
	 * Adopted, but modified from WordPress Core.
     *
     * @since  1.0.0 Introduced.
     *
	 * @param  array $args      User defined arguments.
	 * @param  array $defaults  Default arguments.
	 * @return array            Merged version of arguments.
	 * ---------------------------------------------------------------------
	 */
	public static function parseArguments( $args, $defaults )
	{
		if( ! is_array($args) || ! is_array($defaults) ) {
			return 'Both the parameters need to be array';
		}

		$r =& $args;

		return array_merge( $defaults, $r );
	}


    /**
     * Unserialize if necessary.
     *
     * Adopted from WordPress Core.
     *
     * @since  1.0.0 Introduced.
     *
     * @param  array|string $original Data want to unserialize.
     * @return array|string           Data | Unserialized data.
     * ---------------------------------------------------------------------
     */
    public static function maybeUnserialize( $original )
    {
        if ( self::isSerialized( $original ) ) // don't attempt to unserialize data that wasn't serialized, going in
            return @unserialize( $original );

        return $original;
    }


    /**
     * Serialize data.
     *
     * Adopted from WordPress Core.
     *
     * @since  1.0.0 Introduced.
     *
     * @param  string|array $data   Data to be serialized.
     * @return string               Serialized data | String.
     * ---------------------------------------------------------------------
     */
    public static function maybeSerialize( $data )
    {
        if ( is_array( $data ) || is_object( $data ) ) {
            return serialize( $data );
        }

        if ( self::isSerialized( $data, false ) ) {
            return serialize( $data );
        }

        return $data;
    }


    /**
     * Check whether is serialized?
     *
     * Adopted from WordPress Core.
     *
     * @since  1.0.0 Introduced.
     *
     * @param  string|array $data       Data to check serialization.
     * @param  boolean      $strict     Strict or not.
     * @return boolean                  True | False.
     * ---------------------------------------------------------------------
     */
    private static function isSerialized( $data, $strict = true )
    {
        // if it isn't a string, it isn't serialized.
        if ( ! is_string( $data ) ) {
            return false;
        }
        $data = trim( $data );
        if ( 'N;' == $data ) {
            return true;
        }
        if ( strlen( $data ) < 4 ) {
            return false;
        }
        if ( ':' !== $data[1] ) {
            return false;
        }
        if ( $strict ) {
            $lastc = substr( $data, -1 );
            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $data, ';' );
            $brace     = strpos( $data, '}' );
            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace )
                return false;
            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 )
                return false;
            if ( false !== $brace && $brace < 4 )
                return false;
        }
        $token = $data[0];
        switch ( $token ) {
            case 's' :
                if ( $strict ) {
                    if ( '"' !== substr( $data, -2, 1 ) ) {
                        return false;
                    }
                } elseif ( false === strpos( $data, '"' ) ) {
                    return false;
                }
                // or else fall through
            case 'a' :
            case 'O' :
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
        }
        return false;
    }
}
