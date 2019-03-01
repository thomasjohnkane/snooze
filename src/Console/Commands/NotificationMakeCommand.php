<?php

namespace Thomasjohnkane\SimpleScheduledNotifications\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Artisan;

class NotificationMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:notification:scheduled {name} {--m|mail : Whether to create a Mailable class} {--mm : Whether to create a Mailable class AND an email view (blade file)} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new Schedulable Notification';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Notification';

    protected $className;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return;
        }

        // Conditionall make the other files
        if ($this->option('mm')) {
            $this->makeMailable(TRUE);
        }  elseif ($this->option('mail')) {
            $this->makeMailable(FALSE);
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        if ($this->option('mm')) {
            $class = str_replace('DummyClassMailable', $class . 'Mailable', $class);
        }

        return $class;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('mm')) {
            return __DIR__.'/../stubs/markdown-notification.stub';
        }
        return __DIR__.'/../stubs/notification.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Notifications';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the notification already exists'],

        ];
    }

    /**
     * Make the Mailable class.
     *
     * @return mixed
     */
    protected function makeMailable($markdown)
    {
        $exitCode = Artisan::call('make:mail:scheduled', [
            'name' => $this->argument('name'),
            '--markdown' => ($markdown) ? $this->hyphenatedName() : FALSE
        ]);
    }

    protected function hyphenatedName()
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $this->argument('name')));
    }
}
