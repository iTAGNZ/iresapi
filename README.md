# iRES API v1 - Demo

**NOTE: THIS API DEMO IS CURRENTLY INACTIVE**
Please either star this repository, or visit back soon for definitive documentation and examples.

## Contents
1. [Introduction](#introduction)
2. [Authentication](#authentication)
3. [Resources](#resources)
4. [Response Structures](#response-structures)

## Introduction

Here are some guidelines for use:
- iRES API (v1) is a RESTful API, incorrect HTTP methods will generate an error message
- Requests **must** be over SSL, other protocols will generate an error message
- An API version number must be present in the URL after the `api` parameter
- Authentication iswith a valid API token (public key) and OAuth style HMAC checksum using your private key
- Possible response formats are: JSON (default), XML.
- Responses are in JSON format by default with corresponding headers, e.g. a successful response not returning any useful data:

```json
{
	"code": 200,
	"message": "",
	"description": ""
}
```
	
- "pretty" formatting can be turned off by specifying `noPretty` in the query string:

`/api/v1/operators?token=YOURAPIACCESSTOKEN&...&noPretty`

```json
{"code":200,"message":"","description":""}
```

- Responses can be returned in XML format by appending `.xml` to the endpoint. Pretty formatting applies to XML also.

`/api/v1/operators.xml?token=YOURAPIACCESSTOKEN&...`

Here is an example of an API request URL to iRES. This resource will return a list of operators:

`https://ires.co.nz/api/v1/operators?token=YOURAPIACCESSTOKEN&timestamp=1390511270&checksum=[HMAC-CHECKSUM]`

## Authentication
The iRES API uses an OAuth style [HMAC](http://en.wikipedia.org/wiki/Hash-based_message_authentication_code) checksum to sign the request data with your private key and the **sha256** algorithm. You must provide your API token (public key), a UNIX timestamp of when the request is generated, and a HMAC checksum which should represent all data you are including in the current request (including the API token and timestamp). For example in a `GET` request with no additional criteria, you would calculate the checksum of only your API token and timestamp. If you had specified `limit=5` you would calculate the checksum of that criteria joined together.

Here is a PHP example:

```php
<?php
$private_key = 'yoursupersecretkey';

$request_data = array(
	'token' => 'yourapitoken',
	'timestamp' => time(),
	'limit' => 5,
	'order' => 'DESC'
);

// generate the required checksum by combining all request data and using 
// hash_hmac to hash using sha256 as the algorithm and $private_key as the key
$request_data['checksum'] = hash_hmac('sha256', implode($request_data), $private_key);

// now that $request_data contains a signed HMAC of itself, you can make 
// an API call using $request_data
?>
```

Timestamps will remain valid for 10 minutes, after this an `Authentication Failed` error will be returned. If you are having problems with this because of your server's configuration, get in touch with iRES support.

Your public and private keys will be assigned to you by iRES. **Never** share your private key, or include it in request data sent to iRES. For an online example of HMAC generation, [click here](http://www.freeformatter.com/hmac-generator.html).

### Checking your authentication

You can use the `authinfo` endpoint to return information associated with your authenticated API request and may be useful when setting up your API requests, or to validate your login credentials:

`https://ires.co.nz/api/v1/authinfo?token=YOURAPIACCESSTOKEN&timestamp=1390511270&checksum=[HMAC-CHECKSUM]`

Example output:

```json
{}
	"api_token": "YOURAPITOKEN", // your API token (public key)
	"api_channel_type": "[Agent/Operator/External]", // this is an iRES setting
	"api_channel_id": "[channel_id]", // this is an iRES setting
	"api_name": "iRES API Demo\/Example", // your application name, assigned when your API access is set up
	"api_url": "[YOURAPPURL]", // your application url, assigned when your API access is set up
	"api_last_accessed": "2014-01-24 10:00:55", // datetime of when you last made a request
	"api_status": "Active"
}
```

## Resources
- **operators[/id]** - *Gets a list of iRES operators if `id` is not specified. If the `id` is specified, this method will return information about the specified operator.*
- **operators/id/products** - *Gets a list of products associated with the specified operator.*

Optional query string parameters may be specified to search, sort and filter the results returned:

#### Filtering
- `limit`: defines how many results to return. *Default: 20*, maximum: 50. *Note:* If a limit above the maximum is given, limit will revert to default.
- `heroProduct`: if set, will only return the single hero product for that operator, or none. *Default: null*
- `operatorType`: activity, accommodation, rental, passenger, external. *Default: activity*

#### Sorting
- `order`: defines the sort order. Options are name *(default)*, ID, website, phone or contact, with or without `ASC` *(default)* or `DESC`

#### Searching
- `q`: multi-word search string spaced by `+`, e.g. `Air+Skydive`. *Default: null*

## Response structures

### Success

All `GET` responses will return associated data along with headers and a `200 OK` HTTP status code.

#### JSON
A `GET` request will respond on success with a JSON object containing the requested data. Example:

```json
[
	{
		"id": "1",
		"name": "Demo Operator",
		"website": "www.demooperator.com",
		"phone": "123 4567",
		"contact": "John Smith"
	},
	{
		"id": "2",
		"name": "Demo Operator 2",
		"website": "www.seconddemooperator.com",
		"phone": "234 5678",
		"contact": "Joe Bloggs"
	}
]
```

#### XML
A `GET` request will respond on success with an XML document containing the requested data. The data set will be wrapped in the endpoint name (e.g. `operators`) and the
items will be wrapped in a singular representation of the endpoint name (e.g. `operator`). Example:

```xml
<?xml version="1.0"?>
<operators>
  <operator>
    <id>1</id>
    <name>Demo Operator</name>
    <website>www.demooperator.com</website>
    <phone>123 4567</phone>
    <contact>John Smith</contact>
  </operator>
  <operator>
    <id>2</id>
    <name>Demo Operator 2</name>
    <website>www.seconddemooperator.com</website>
    <phone>234 5678</phone>
    <contact>Joe Bloggs</contact>
  </operator>
</operators>
```

### Error
A `GET` request that fails for any reason will return relevant headers and an HTTP status code relating to the error type, e.g. `404 Not Found` for an incorrect URL.
**Please note:** If an incorrect response format is specified, the corresponding error will always be served in JSON format.

#### JSON
A JSON `GET` request failure will contain **three** main items:
- `code`: a copy of the HTTP status code, e.g. `404`
- `message`: a short message explaining the error
- `description`: a more detail description of the error
- additional items may be returned to help fix the error, e.g. if an incorrect output type was sent (not JSON or XML), the error would contain `supportedFormats` with a list of supported formats.

Example:

```json
{
	"code": 415,
	"message": "Output type not supported",
	"description": "The requested output type is not supported: CSV",
	"supportedFormats": [
		"JSON",
		"XML"
	]
}
```
#### XML
An XML `GET` request failure will contain the same items as above. The body will be wrapped in an `<errors>` tag, and each item will be wrapped in an `<error>` tag. Example:

```xml
<?xml version="1.0"?>
<errors>
  <error>
    <code>401</code>
    <message>Authentication required</message>
    <description>You must provide a valid API token.</description>
  </error>
</errors>
```