<?php

namespace Technovistalimited\Notific\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Technovistalimited\Notific\Models\Notification;

class UserNotification extends Model
{
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'notification_id',
        'is_read',

        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];


    /**
     * Store User Notification.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static with PSR-2 fixes and relocated in own model.
     *
     * @param  integer|array $userId         User ID or Array of user IDs.
     * @param  integer       $notificationId Notification ID.
     *
     * @return integer User Notification ID, otherwise null.
     */
    public function store($userId, $notificationId)
    {
        return DB::table('user_notifications')
            ->insertGetId([
                'user_id'         => intval($userId),
                'notification_id' => intval($notificationId),
                'is_read'         => 0
            ]);
    }

    /**
     * Mark Notification as read.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static with PSR-2 fixes and relocated in own model.
     *
     * @param integer $userId         User ID.
     * @param integer $notificationId Notification ID. Default: null.
     *
     * @return boolean If done true, false otherwise.
     */
    public function markAsRead($userId, $notificationId = null)
    {
        $result = DB::table('user_notifications')
            ->where('user_id', $userId);

        if (!empty($notificationId)) {
            $result = $result->where('notification_id', $notificationId);
        }

        $result = $result->update([
            'is_read'    => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (empty($result)) {
            return false;
        }

        // Clear the user notification cache first.
        $notification = new Notification;
        $notification->clearCache($userId);

        return true;
    }

    /**
     * Query the Notifications.
     *
     * Made a separate method to reUse the code.
     *
     * @param integer $userId   User ID.
     * @param array   $baseArgs Array of arguments.
     * @param boolean $isRead   isRead status.
     * @param boolean $isCache  Whether cache is enabled or not.
     *
     * @access private
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static with PSR-2 fixes and relocated in own model.
     *
     * @return null|object Object if result found, null otherwise.
     */
    private function queryNotifications($userId, $baseArgs, $isRead, $isCache)
    {
        $query = DB::table('user_notifications')
            ->leftJoin('notifications', 'user_notifications.notification_id', '=', 'notifications.id')
            ->where('user_notifications.user_id', $userId)
            ->select(
                'notifications.*',
                'user_notifications.is_read',
            );

        if ('all' === $baseArgs['read_status']) {
            $query = $query;
        } else {
            $query = $query->where('user_notifications.is_read', $isRead);
        }

        $query = $query->orderBy($baseArgs['orderby'], $baseArgs['order']);

        if (-1 != $baseArgs['items_per_page']) {
            $count = abs(intval($baseArgs['items_per_page']));
            if ($baseArgs['paginate']) {
                // BUG - needed a hack, done below.
                // using get() instead of paginate() because Cache CANNOT return LengthAwarePaginator object.
                if ($isCache) {
                    $query = $query->get();
                } else {
                    $query = $query->paginate($count);
                }
            } else {
                $query = $query->take($count)->get();
            }
        } else {
            $query = $query->get();
        }

        return $query;
    }

    /**
     * Retrieve Notifications.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 Modified to be non-static with PSR-2 fixes and relocated in own model.
     *
     * @see $this->queryNotifications() Reusable query method.
     *
     * @param integer $userId   Individual user ID.
     * @param array   $arguments {
     *  Optional. Array of Query parameters.
     *
     *  @type string $read_status
     *   The notification read status.
     *   Accepts 'read', 'unread', 'all'.
     *   Default 'all'.
     *
     *  @type string $order
     *   Designates ascending or descending order of notifications.
     *   Accepts 'ASC', 'DESC'.
     *   Default 'DESC'.
     *
     *  @type string $orderby
     *   Sort retrieved posts by parameter. Single option can be passed.
     *   Accepts any valid column name from db table.
     *   Default 'created_at'.
     *
     *  @type boolean $paginate
     *   Whether to enable pagination or not.
     *   Default false - pagination DEactivated.
     *
     *  @type boolean $items_per_page
     *   Fetch the number of items.
     *   Accepts any positive integer.
     *   Default -1 - fetch everything.
     * }
     *
     * @return array  Notifications object.
     */
    public function getNotifications($userId, $arguments = array())
    {
        $defaults = array(
            'read_status'    => 'all',
            'order'          => 'DESC',
            'orderby'        => 'created_at',
            'paginate'       => false,
            'items_per_page' => -1,
        );

        $notification = new Notification;
        $baseArgs     = $notification->parseArguments($arguments, $defaults);

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
         * Is cache enabled?
         * Whether or not the caching is enabled.
         * @var boolean.
         * ...
         */
        $isCache = (bool) config('notific.cache.is_cache');

        if ($isCache) {
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
             * @see  $this->queryNotifications()
             * @var  null|object.
             * ...
             */
            $values = Cache::remember($cacheKey, $cacheTime, function () use ($userId, $baseArgs, $isRead, $isCache) {
                return $this->queryNotifications($userId, $baseArgs, $isRead, $isCache);
            });
        } else {
            $values = $this->queryNotifications($userId, $baseArgs, $isRead, $isCache);
        }

        /**
         * MaybeUnserialize.
         * Unserialize the meta value if necessary.
         * @var array.
         * ...
         */
        foreach ($values as $key => $value) :
            $values[$key]->meta = $notification->maybeUnserialize($value->meta);
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
         * @since 0.2.0 Introduced.
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
        if ($isCache && $baseArgs['paginate']) {
            // Get current page form url e.g. &page=6.
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            // Get current path.
            $currentPath = LengthAwarePaginator::resolveCurrentPath();

            // Create a new Laravel collection from the array data.
            $collection = new Collection($values);

            // Define how many items we want to be visible in each page.
            $perPage = abs(intval($baseArgs['items_per_page']));

            // Slice the collection to get the items to display in current page.
            $results = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();

            // Create our paginator and pass it to the view.
            $values = new LengthAwarePaginator($results, count($collection), $perPage, $currentPage, ['path' => $currentPath]);
        }

        return $values;
    }

    /**
     * Get Notification Count.
     *
     * @param integer $userId User ID.
     * @param string  $status Status.
     *
     * @since 1.0.0 Introduced.
     *
     * @return integer Count in number.
     */
    public function getNotificationCount($userId, $status = 'all') {
        $_status = array();

        switch ($status) {
            case 'all':
                $_status['read_status'] = 'all';
                break;

            case 'unread':
                $_status['read_status'] = 'unread';
                break;

            case 'read':
                $_status['read_status'] = 'read';
                break;
        }

        return count($this->getNotifications($userId, $_status));
    }
}
