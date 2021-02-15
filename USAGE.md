
## What it provides?
* A name.verb based authorisation with middleware:
```php
use Larapress\CRUD\Middleware\CRUDAuthorize;

Route::middleware([..., CRUDAuthorize::class])
    ->group(function () {
		...
	});
```
* A ``BaseCRUDController`` class to extend all controllers from and let it handle all resource management, you just register all your resources and their verbs
```php
use Larapress\CRUD\CRUDControllers\BaseCRUDController;

class MyResourceController extends BaseCRUDController
{
    public static function registerRoutes()
    {
        parent::registerCrudRoutes(
            $name, // the resource name; comments, posts, pages, etc.
            self::class,
            ActivateCodeCRUDProvider::class // a reference to a class implementing the interface ICRUDProvider
        );
    }
}
```
* An interface to dictate how a resource should be stored, secured, reported, queried (or any other custom verb) by outside world: ``ICRUDProvider``. It's essentially ``Laravel Gates`` with more control
```php
use Larapress\CRUD\Base\BaseCRUDProvider;
use Larapress\CRUD\Base\ICRUDProvider;

class MyResourceCRUDProvider implements ICRUDProvider
{
	use BaseCRUDProvider;

	// my resources Eloquent model class
	public $model = MyResource::class;
	
	// do some validations before a new objects can be stored
	public $createValidations = [
		'name' => 'required|string|max:190',
		'title' => 'required|string|max:190',
			...
	];
	
	// do some validations before updating an object
	public $updateValidations = [
		'name' => 'required|string|max:190',
		'title' => 'required|string|max:190',
			...
	];
	
	// allow a request to ask for sorting on these columns
	public $validSortColumns = ['id', 'name', 'type'];
	
	// allow a request to ask for attaching these relations
	public $validRelations = ['domain'];
	
	// dont attache any relation for the default query
	public $defaultShowRelations = [];
	
	// allow a request to search in these columns
	public $searchColumns = ['name', 'type'];
	
	// and many more options ...

	/**
	 * @param array  $args
	 *
	 * @return array
	 */
    public function onBeforeQuery(Builder $query)
    {
    	/** ICRUDUser */
    	$user = Auth::user();
    	if ($user->hasRole('something') {
    		$query->with(['domain' => function($q) {
    			... filter the relation based on user roles and permissions
    		}]);
    	}
    
        return $query;
    }
    
	/**
	* @param array  $args
	*
	* @return array
	*/
	public function onBeforeCreate($args)
	{
		// preprocess request inputs
		$args['flags'] = 0;
		return $args;
	}
    	
	/**
	* @param MyResource $object
	* @param array  $input_data
	*
	* @return void
	*/
	public function onAfterUpdate( $object, $input_data )
	{
		... post process upject after update
	}
	
	// and many more optionss to override ...
}
```
# How to use it?
* require the package
```php
composer require peynman/larapress-crud
```

* publish vendor files
```php
php artisan vendor:publish --tags=larapress-crud
```

* Point the config path ``larapress.crud.user.class`` to your User class

* Implement ICRUDUser interface on your User class and use BaseCRUDUSer trait to attach user/role/permissions system to your User model
```php
class User extends Authenticatable implements ..., ICRUDUser
{
	// whatever stuff you want

	use BaseCRUDUser;
	
	// whatever stuff you want
}
```
* Follow the pattern above to create your resource Controllers and CRUDProviders

* use CRUDAuthorize middleware and call your controllers ``registerRoutes()`` function

* Done
