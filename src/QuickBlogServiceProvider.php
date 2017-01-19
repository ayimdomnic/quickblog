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
		})

		$this->registerMiddleware();
        $this->registerServiceProviders();
        $this->registerAliases();
	}
}