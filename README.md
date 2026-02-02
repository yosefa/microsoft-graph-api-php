# Microsoft Graph API PHP Library

## Description

A lightweight PHP library for authenticating and retrieving user data from Microsoft Graph API using OAuth 2.0. This library simplifies the process of obtaining access tokens and fetching user information through Microsoft's authentication system.

## Features

- Easy Microsoft OAuth 2.0 authentication.
- Token retrieval and management.
- Fetch user profile information.
- Supports multiple tenants and scopes.
- Built with Guzzle HTTP client and Microsoft Graph SDK.

## Prerequisites

- PHP 7.4+
- Composer
- Microsoft Azure Active Directory application credentials

## Installation

Install the library using Composer:

```bash
composer require yosefa/microsoft-graph-api-php
```

## Configuration

1. Create a Microsoft Azure AD application in the Azure Portal
2. Obtain the following credentials:
   - Client ID
   - Client Secret
   - Tenant ID
   - Redirect URI

## Usage Example

```php
<?php
use MicrosoftGraphApiPhp\Api;

// Initialize the Microsoft Graph API client
$microsoftGraph = new Api(
    $client_id,
    $client_secret,
    $redirect_uri,
    $tenant_id
);

// Generate authorization URL
$authorizationUrl = $microsoftGraph->get_url_auth();

// Redirect user to authorization URL
header("Location: " . $authorizationUrl);

// After user authorization, exchange code for token and retrieve user data
$userData = $microsoftGraph->get_data($authorizationCode);

if ($userData->valid) {
    // Access user information
    echo "User Name: " . $userData->getDisplayName();
}
```

## Method Reference

### Constructor
```php
public function __construct(
    $client_id, 
    $client_secret, 
    $redirect_uri, 
    $tenant_id = "common", 
    $scopes = array("User.Read"), 
    $state = 42443
)
```

### Methods
- `get_url_auth()`: Generate Microsoft OAuth authorization URL
- `get_token($code)`: Exchange authorization code for access token
- `get_data($code)`: Retrieve authenticated user's profile information

## Scopes

Default scope is `User.Read`. You can specify multiple scopes during initialization:

```php
$scopes = ["User.Read", "Mail.Read"];
$microsoftGraph = new Api($client_id, $client_secret, $redirect_uri, $tenant_id, $scopes);
```

## Error Handling

The library returns objects with a `valid` property:
- `true`: Successful operation
- `false`: Operation failed

Check the `valid` property and handle errors accordingly.

## Dependencies

- [Guzzle HTTP Client](https://docs.guzzlephp.org/)
- [Microsoft Graph SDK](https://github.com/microsoftgraph/msgraph-sdk-php)

## Contributing

Contributions are welcome! Please submit pull requests or open issues on the project repository.
