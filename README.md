iRES v1 API - Demo
=======

NOTE: THIS API IS CURRENTLY NOT ACTIVE
Please either star this repository, or visit back in February 2014 for definitive documentation and examples.

Introduction
=======

Here are some guidelines for use:
- iRES API (v1) is a RESTful API, incorrect HTTP methods will generate an erorr message
- Requests must be over SSL, other protocols will generate an error message
- An API version number must be present in the URL after the "api" parameter
- A valid API token must be present in the query string as a GET variable named "token"
- Responses are in JSON format

Here is an example of an API request URL to iRES. This resource will return a list of operators:
- https://ires.co.nz/api/v1/operators?token=YOURAPIACCESSTOKEN