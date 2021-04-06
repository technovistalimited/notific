<?php
/**
 * Initialize Notific
 *
 * @package    Laravel
 * @subpackage TechnoVistaLimited/Notific
 */

namespace Technovistalimited\Notific;

use Technovistalimited\Notific\Models\Notification;
use Technovistalimited\Notific\Models\UserNotification;

class Notific
{
    /**
     * Notify.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 With PSR-2 fixes moved from Helpers to Facades.
     *
     * @see Notification::notify() Notify the user.
     */
    public static function notify($userId, $message, $notificationType = '', $metaData = '', $createdBy = '')
    {
        $notification = new Notification;

        return $notification->notify($userId, $message, $notificationType, $metaData, $createdBy);
    }

    /**
     * Get Notifications.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 With PSR-2 fixes moved from Helpers to Facades.
     *
     * @see UserNotification::getNotifications() Get user notifications.
     */
    public static function getNotifications($userId, $arguments = array())
    {
        $userNotification = new UserNotification;

        return $userNotification->getNotifications($userId, $arguments);
    }

    /**
     * Mark Notification as Read.
     *
     * @since 0.2.0 Introduced.
     * @since 1.0.0 With PSR-2 fixes moved from Helpers to Facades.
     *
     * @see UserNotification::markAsRead() Mark the notification as read.
     */
    public static function markNotificationRead($userId, $notificationId = null)
    {
        $userNotification = new UserNotification;

        return $userNotification->markAsRead($userId, $notificationId);
    }

    /**
     * Maybe Unserialize.
     *
     * @since 1.0.0 Introduced.
     *
     * @see Notification::maybeUnserialize() Unserialize, if necessary.
     */
    public static function maybeUnserialize($original)
    {
        $notification = new Notification;

        return $notification->maybeUnserialize($original);
    }

    /**
     * Get Notification Count.
     *
     * @since 1.0.0 Introduced.
     *
     * @see UserNotification::getNotificationCount() Get user notification count.
     */
    public static function getNotificationCount($userId, $status = 'all')
    {
        $userNotification = new UserNotification;

        return $userNotification->getNotificationCount($userId, $status);
    }
}
