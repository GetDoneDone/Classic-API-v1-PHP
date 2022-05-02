# DoneDone Classic API v1 PHP Wrapper

## REQUIREMENT
PHP version 5.* (developed against 5.3.9)
PHP's cURL module

## USAGE
To use the PHP library with a DoneDone project, you will need to enable the API option under the Project Settings page.

Please see http://www.getdonedone.com/api fore more detailed documentation.

## EXAMPLES 
```php
/**
 * Initializing
 */
require_once "path/to/DoneDone.php";

$domain = "YOUR_COMPANY_DOMAIN"; /* e.g. wearemammoth */
$token = "YOUR_API_TOKEN";
$username = "YOUR_USERNAME";
$password = "YOUR_PASSWORD";

$issueTracker = new IssueTracker($domain, $token, $username, $password);

/**
 * Calling the API 
 *
 * API methods can be accessed by calling IssueTracker::API(), or by calling the equivalent shorthand.
 *
 * The examples below will get all your projects with the API enabled.
 */
$issueTracker->API("Projects");
// or
$issueTracker->getProjects();
```
