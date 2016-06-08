##PHP Instagram SDK

This package implement Instagram's endpoint in PHP

Installation
```
composer require jeevan/instagram_api
```

Usage
```
use Jeevan\Instagram;

$config = [
    'CLIENT_ID' => CLIENT_ID,
    'CLIENT_SECRET' => CLIENT_SECRET,
    'REDIRECT_URI' => REDIRECT_URI
];

$scope = [
    'basic',
];

$instagram = new Instagram($config, $scope);
```

Get authorization URL
```
$url = $instagram->getAuthorizationURL();
```
Let user click on the link so that they can give permission to your app

Once redirected back
```
$instagram->processAccessToken();
```

Now we can call the endpoint, example: Get current user's details
```
$user = $instagram->getSelf();
```