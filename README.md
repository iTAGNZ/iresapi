# iRES API v1 - Demo

**NOTE: THIS API DEMO IS CURRENTLY INACTIVE**
Please either star this repository, or visit back in February 2014 for definitive documentation and examples.

## Introduction

Here are some guidelines for use:
- iRES API (v1) is a RESTful API, incorrect HTTP methods will generate an erorr message
- Requests must be over SSL, other protocols will generate an error message
- An API version number must be present in the URL after the `api` parameter
- A valid API token must be present in the query string as a GET variable named `token`
- Resources that are available to you are deduced from the API token you supply
- Responses are in JSON format, e.g. a successful response not returning any useful data:

	`{
		"code": 200,
		"message": "",
		"description": ""
	}`
	
- "pretty" JSON formatting can be turned off by specifying `pretty=no` in the query string:

    {"code":200,"message":"","description":""}
	
Here is an example of an API request URL to iRES. This resource will return a list of operators:

    https://ires.co.nz/api/v1/operators?token=YOURAPIACCESSTOKEN

## Resources
- **operators[/id]** - *Gets a list of iRES operators if `id` is not specified. If the `id` is specified, this method will return information about the specified operator.*
- **operators/id/products** - *Gets a list of products associated with the specified operator.*

Optional query string parameters may be specified to search, sort and filter the results returned:

#### Filtering
- `limit`: defines how many results to return. *Default: 20*, maximum: 50
- `heroProduct`: if set, will only return the single hero product for that operator, or none. *Default: null*

#### Sorting
- `order`: defines the sort order. Options are `name` *(default)*, `ASC` *(default)* or `DESC`

#### Searching
- `q`: multi-word search string spaced by `+`, e.g. `Air+Skydive`. *Default: null*

