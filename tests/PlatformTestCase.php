<?php namespace Pikanji\DbQueueManager\Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

/** Minimal real framework services; no application kernel, network disks or real DB. */
abstract class PlatformTestCase extends TestCase
{
    protected $app;
    protected $database;
    protected $tempDirectory;
    private $previousContainer;
    private $previousFacadeApplication;
    private $previousConnectionResolver;
    private $previousModelDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $this->previousFacadeApplication = Facade::getFacadeApplication();
        $this->previousConnectionResolver = \Winter\Storm\Database\Model::getConnectionResolver();
        $this->previousModelDispatcher = \Winter\Storm\Database\Model::getEventDispatcher();
        $this->tempDirectory = sys_get_temp_dir() . '/dbqueue-platform-' . bin2hex(random_bytes(12));
        mkdir($this->tempDirectory, 0700, true);
        $this->app = new Application(PLATFORM_TEST_CMS_ROOT);
        $this->app->instance('config', new Repository([
            'app' => ['key' => str_repeat('x', 32), 'locale' => 'en', 'timezone' => 'UTC'],
            'cms' => ['storage' => ['uploads' => ['disk' => 'local', 'folder' => 'uploads']]],
            'filesystems' => ['default' => 'local', 'disks' => [
                'local' => ['driver' => 'local', 'root' => $this->tempDirectory],
            ]],
        ]));
        $this->app->instance('events', new Dispatcher($this->app));
        $this->app->instance('files', new \Winter\Storm\Filesystem\Filesystem());
        $this->app->instance('log', new \Psr\Log\NullLogger());
        $this->app->instance('request', Request::create('https://localhost-gbc-pilot/test'));
        $translator = new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en');
        $this->app->instance('translator', $translator);
        $this->app->instance('validator', new \Illuminate\Validation\Factory($translator, $this->app));
        (new \Winter\Storm\Filesystem\FilesystemServiceProvider($this->app))->register();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);

        // The ONLY connection is an ephemeral SQLite database, regardless of .env.
        $this->database = new Manager($this->app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->database->setEventDispatcher($this->app['events']);
        $this->database->bootEloquent();
        $this->app->instance('db', $this->database->getDatabaseManager());
        \Winter\Storm\Database\Model::clearBootedModels();
        \Winter\Storm\Database\Model::flushDuplicateCache();
    }

    protected function tearDown(): void
    {
        try {
            Request::setTrustedProxies([], 0);
            \Carbon\Carbon::setTestNow();
            \Winter\Storm\Argon\Argon::setTestNow();
            \Winter\Storm\Database\Model::clearBootedModels();
            \Winter\Storm\Database\Model::flushDuplicateCache();
            if ($this->previousConnectionResolver) {
                \Winter\Storm\Database\Model::setConnectionResolver($this->previousConnectionResolver);
            } else {
                \Winter\Storm\Database\Model::unsetConnectionResolver();
            }
            if ($this->previousModelDispatcher) {
                \Winter\Storm\Database\Model::setEventDispatcher($this->previousModelDispatcher);
            } else {
                \Winter\Storm\Database\Model::unsetEventDispatcher();
            }
            $this->database->getConnection()->disconnect();
            $this->app['files']->deleteDirectory($this->tempDirectory);
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($this->previousFacadeApplication);
            Container::setInstance($this->previousContainer);
        } finally {
            parent::tearDown();
        }
    }
}
