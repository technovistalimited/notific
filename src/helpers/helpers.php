<?php
use Mayeenulislam\Notific\Models\NotificModel;

/**
 * Helper: notify()
 *
 * @since 1.0.0 Introduced.
 *
 * @see   NotificModel::notify() Notify the user.
 * ---------------------------------------------------------------------
 */
function notify( $userId, $message, $notificationType = '', $metaData = '', $createdBy = '' )
{
	return NotificModel::notify( $userId, $message, $notificationType, $metaData, $createdBy );
}

/**
 * Helper: getNotifications()
 *
 * @since 1.0.0 Introduced.
 *
 * @see   NotificModel::getNotifications() Get user notifications.
 * ---------------------------------------------------------------------
 */
function getNotifications( $userId, $fetch = 'all' )
{
	return NotificModel::getNotifications( $userId, $fetch );
}

/**
 * Helper: markNotificationRead()
 *
 * @since 1.0.0 Introduced.
 *
 * @see   NotificModel::markNotificationRead() Mark the notification read.
 * ---------------------------------------------------------------------
 */
function markNotificationRead( $userId, $notificationId = '' )
{
	return NotificModel::markNotificationRead( $userId, $notificationId );
}
