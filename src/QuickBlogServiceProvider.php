<? php

namespace Ayimdomnic\QuickBlog;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;


class QuickBlogServiceProvider extends ServiceProvider
{

	protected $providers = [

	];

	protected $aliases = [];

	public function register()
	{
		$this->app->bind('ayimdomnic.quickblog', function(){
			$database = $this->app['database'];
			$config = $this->app['config'];

			return new QuickBlog($database, $config);
		});

		$this->registerMiddleware();
        $this->registerServiceProviders();
        $this->registerAliases();
	}

	public function boot()
    {
        // Load the routes for the package
        include __DIR__.'Routes/web.php';
        $this->publish();
        $this->loadViewsFrom(__DIR__.'Resources/views', 'quickblog');
        $this->loadViewsFrom(__DIR__.'/../Example/Views', 'quickBlogPublic');
        // Make the config file accessible even when the files are not published
        $this->mergeConfigFrom(__DIR__.'/config/quickblog.php', 'quickblog');
        $this->loadTranslationsFrom(__DIR__.'Resources/lang/', 'quickblog');
        $this->registerCommands();
        // Register the class that serves extra validation rules
        $this->app['validator']->resolver(
            function(
                $translator,
                $data,
                $rules,
                $messages = array(),
                $customAttributes = array()
            ) {
            return new Validation($translator, $data, $rules, $messages, $customAttributes);
        });
    }
    ///////////////////////////////////////////////////////////////////////////
    // Helper methods
    ///////////////////////////////////////////////////////////////////////////
    /**
     * @return void
     */
    private function registerMiddleware()
    {
        $this->app['router']->middleware('QuickBlogAdminAuthenticate', 'Ayimdomnic\QuickBlog\Middleware\BlogifyAdminAuthenticate');
        $this->app['router']->middleware('QuickBlogVerifyCsrfToken', 'Ayimdomnic\QuickBlog\Middleware\BlogifyVerifyCsrfToken');
        $this->app['router']->middleware('CanEditPost', 'Ayimdomnic\QuickBlog\Middleware\CanEditPost');
        $this->app['router']->middleware('DenyIfBeingEdited', 'Ayimdomnic\QuickBlog\Middleware\DenyIfBeingEdited');
        $this->app['router']->middleware('QuickBlogGuest', 'Ayimdomnic\QuickBlog\Middleware\Guest');
        $this->app['router']->middleware('HasAdminOrAuthorRole', 'Ayimdomnic\QuickBlog\Middleware\HasAdminOrAuthorRole');
        $this->app['router']->middleware('HasAdminRole', 'Ayimdomnic\QuickBlog\Middleware\HasAdminRole');
        $this->app['router']->middleware('RedirectIfAuthenticated', 'Ayimdomnic\QuickBlog\Middleware\RedirectIfAuthenticated');
        $this->app['router']->middleware('IsOwner', 'Ayimdomnic\QuickBlog\Middleware\IsOwner');
        $this->app['router']->middleware('CanViewPost', 'Ayimdomnic\QuickBlog\Middleware\CanViewPost');
        $this->app['router']->middleware('ProtectedPost', 'Ayimdomnic\QuickBlog\Middleware\ProtectedPost');
        $this->app['router']->middleware('ConfirmPasswordChange', 'Ayimdomnic\QuickBlog\Middleware\ConfirmPasswordChange');
    }
    /**
     * @return void
     */
    private function registerServiceProviders()
    {
        foreach ($this->providers as $provider)
        {
            $this->app->register($provider);
        }
    }
    /**
     * @return void
     */
    private function registerAliases()
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->aliases as $key => $alias)
        {
            $loader->alias($key, $alias);
        }
    }
    /**
     * @return void
     */
    private function publish()
    {
        // Publish the config files for the package
        $this->publishes([
            __DIR__.'/../config' => config_path('quickblog/'),
        ], 'config');
        $this->publishes([
            __DIR__.'/../public/assets' => base_path('/public/assets/quickblog/'),
            __DIR__.'/../public/ckeditor' => base_path('public/ckeditor/'),
            __DIR__.'/../public/datetimepicker' => base_path('public/datetimepicker/')
        ], 'assets');
        $this->publishes([
            __DIR__.'/../views/admin/auth/passwordreset/' => base_path('/resources/views/auth/'),
            __DIR__.'/../views/mails/resetpassword.blade.php' => base_path('/resources/views/emails/password.blade.php')
        ], 'pass-reset');
    }
    private function registerCommands()
    {
        $this->commands([
            'Ayimdomnic\QuickBlog\Commands\QuickBlogMigrateCommand',
            'Ayimdomnic\QuickBlog\Commands\QuickBlogSeedCommand',
            'Ayimdomnic\QuickBlog\Commands\QuickBlogGeneratePublicPartCommand',
            'Ayimdomnic\QuickBlog\Commands\QuickBlogCreateRequiredDirectories',
        ]);
    }
}