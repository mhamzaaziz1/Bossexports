# Development Guidelines for Perfex CRM

This document provides essential information for developers working on this project. It covers build/configuration instructions, testing procedures, and development guidelines.

## Build/Configuration Instructions

### Prerequisites
- PHP 7.x or higher
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Node.js and npm (for frontend asset building)

### Database Configuration
The database configuration is stored in `application/config/app-config.php`. This file contains:
- Database credentials (hostname, username, password, database name)
- Base URL configuration
- Session handling settings
- CSRF protection settings

When setting up a new environment, copy `app-config-sample.php` to `app-config.php` and update the settings accordingly:

```php
define('APP_BASE_URL', 'http://your-domain.com/');
define('APP_DB_HOSTNAME', 'localhost');
define('APP_DB_USERNAME', 'your_username');
define('APP_DB_PASSWORD', 'your_password');
define('APP_DB_NAME', 'your_database_name');
```

### Frontend Asset Building
The project uses Grunt for frontend asset building. The main tasks are defined in `Gruntfile.js`.

1. Install dependencies:
   ```
   npm install
   ```

2. Build assets:
   ```
   grunt build-assets
   ```

This will:
- Concatenate and minify JavaScript files
- Compile and minify CSS files
- Add version headers to files
- Process CSS with Autoprefixer for browser compatibility

For development, you can use the watch task to automatically rebuild assets when files change:
```
grunt watch
```

## Testing Information

### Unit Testing
The project uses CodeIgniter's built-in Unit Testing library for testing. Here's how to create and run tests:

1. Create a controller that loads the unit_test library:
   ```php
   class Test extends CI_Controller {
       public function __construct() {
           parent::__construct();
           $this->load->library('unit_test');
       }
       
       public function index() {
           // Your tests here
       }
   }
   ```

2. Add tests using the `run()` method:
   ```php
   $test = 1 + 1;
   $expected_result = 2;
   $test_name = 'Simple addition test';
   $this->unit->run($test, $expected_result, $test_name);
   ```

3. Display test results:
   ```php
   echo $this->unit->report();
   ```

4. Access the test controller in your browser: `http://your-domain.com/test`

### Example Test
Here's a complete example of a test controller:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('unit_test');
    }

    public function index() {
        echo '<h2>Unit Test Example</h2>';
        
        // Simple test
        $test = 1 + 1;
        $expected_result = 2;
        $test_name = 'Simple addition test';
        $this->unit->run($test, $expected_result, $test_name);
        
        // String test
        $test = 'Hello World';
        $expected_result = 'Hello World';
        $test_name = 'String comparison test';
        $this->unit->run($test, $expected_result, $test_name);
        
        // Boolean test
        $test = true;
        $expected_result = 'is_true';
        $test_name = 'Boolean TRUE test';
        $this->unit->run($test, $expected_result, $test_name);
        
        // Display the test results
        echo $this->unit->report();
    }
}
```

### Testing Environment
The application supports different environments (development, testing, production) which can be set in `index.php`:

```php
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
```

For testing, you can set it to 'testing' which will hide errors but still log them.

## Additional Development Information

### Project Structure
The project follows the CodeIgniter MVC framework structure:
- `application/controllers/`: Contains controller classes
- `application/models/`: Contains model classes
- `application/views/`: Contains view files
- `application/config/`: Contains configuration files
- `assets/`: Contains frontend assets (JS, CSS, images)
- `modules/`: Contains modular extensions to the core application

### Modular Architecture
The application uses a modular architecture where additional functionality can be added as modules in the `modules/` directory. Each module can have its own controllers, models, views, and assets.

### Database Migrations
Database changes should be implemented using CodeIgniter's migration system. The current migration version is stored in `application/config/migration.php`.

### Frontend Development
- CSS styles are in `assets/css/` and `assets/themes/perfex/css/`
- JavaScript files are in `assets/js/` and `assets/themes/perfex/js/`
- The build process concatenates and minifies these files into the `assets/builds/` directory

### Session Handling
The application uses database sessions by default, configured in `app-config.php`:
```php
define('SESS_DRIVER', 'database');
define('SESS_SAVE_PATH', 'sessions');
```

### Security
- CSRF protection is enabled by default
- Make sure to use the CSRF token in all forms:
  ```php
  <?php echo form_hidden('csrf_token', $this->security->get_csrf_hash()); ?>
  ```
- Use the built-in query builder or ActiveRecord pattern for database queries to prevent SQL injection