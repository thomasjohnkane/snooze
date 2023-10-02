<?php

declare(strict_types=1);

namespace Thomasjohnkane\Snooze\Tests;

use File;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Thomasjohnkane\Snooze\ClassKeyMap;
use Thomasjohnkane\Snooze\Facades\Snooze;
use Thomasjohnkane\Snooze\Serializer;
use Thomasjohnkane\Snooze\ServiceProvider;
use Thomasjohnkane\Snooze\Tests\Models\User;
use Thomasjohnkane\Snooze\Tests\Notifications\TestInterruptableNotification;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotification;
use Thomasjohnkane\Snooze\Tests\Notifications\TestNotificationTwo;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->initializeDirectory($this->getTempDirectory());
        $this->setUpDatabase($this->app);

        ClassKeyMap::map([
            'user' => User::class,
            'test-interruptable-notification' => TestInterruptableNotification::class,
            'test-notification' => TestNotification::class,
            'test-notification-two' => TestNotificationTwo::class,
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'snooze' => Snooze::class,
        ];
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__.DIRECTORY_SEPARATOR.'temp'.($suffix == '' ? '' : DIRECTORY_SEPARATOR.$suffix);
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTempDirectory().'/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        file_put_contents($this->getTempDirectory().'/database.sqlite', null);

        $this->artisan('migrate')->run();

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->timestamps();
        });

        foreach (range(1, 5) as $index) {
            User::create(
                [
                    'name' => "user{$index}",
                    'email' => "user{$index}@example.com",
                    'password' => "password{$index}",
                ]
            );
        }
    }
}
