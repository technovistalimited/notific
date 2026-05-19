<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * Resolved from config('notific.connection') at runtime.
     * If not set, falls back to the default connection.
     *
     * @var string|null
     */
    protected $connection;

    /**
     * Resolve the connection name from config at instantiation.
     *
     * Laravel instantiates migration classes before the full application
     * is booted, so we read directly from the config repository.
     * The NotificServiceProvider's register() method will have already
     * registered the connection into config('database.connections'),
     * but we also support the case where the user has manually defined
     * the connection in their config/database.php.
     */
    public function __construct()
    {
        parent::__construct();

        $connectionName = Config::get('notific.connection');

        if (!empty($connectionName)) {
            // Check if the connection is already registered in database config
            // (either by NotificServiceProvider or manually by the user)
            $connections = Config::get('database.connections', []);

            if (isset($connections[$connectionName])) {
                $this->connection = $connectionName;
            }
            // If the connection name is set but not yet registered in database.connections,
            // the NotificServiceProvider will register it during app boot.
            // In that case, we still set $connection so Schema::connection() is used.
            else {
                $this->connection = $connectionName;
            }
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $schema = $this->connection
            ? Schema::connection($this->connection)
            : Schema::getFacadeRoot();

        $schema->create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->mediumText('message');
            $table->string('notification_type', 20)->nullable();
            $table->longText('meta')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $schema = $this->connection
            ? Schema::connection($this->connection)
            : Schema::getFacadeRoot();

        $schema->dropIfExists('notifications');
    }
}
