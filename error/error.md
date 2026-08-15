# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[22P02]: Invalid text representation: 7 ERROR:  invalid input syntax for type uuid: "0"
CONTEXT:  unnamed portal parameter $1 = '...' (Connection: pgsql, Host: 127.0.0.1, Port: 5432, Database: k2_net, SQL: select * from "users" where "id" = 0 and "users"."deleted_at" is null limit 1)

PHP 8.5.6
Laravel 12.65.0
127.0.0.1:8000

## Stack Trace

0 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:838
1 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:794
2 - vendor/laravel/framework/src/Illuminate/Database/Connection.php:411
3 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3505
4 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3490
5 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:4080
6 - vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:3489
7 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:902
8 - vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:884
9 - vendor/laravel/framework/src/Illuminate/Database/Concerns/BuildsQueries.php:366
10 - vendor/laravel/framework/src/Illuminate/Auth/EloquentUserProvider.php:74
11 - vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php:226
12 - vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php:197
13 - vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php:260
14 - vendor/laravel/framework/src/Illuminate/Session/DatabaseSessionHandler.php:220
15 - vendor/laravel/framework/src/Illuminate/Session/DatabaseSessionHandler.php:207
16 - vendor/laravel/framework/src/Illuminate/Session/DatabaseSessionHandler.php:193
17 - vendor/laravel/framework/src/Illuminate/Support/helpers.php:393
18 - vendor/laravel/framework/src/Illuminate/Session/DatabaseSessionHandler.php:192
19 - vendor/laravel/framework/src/Illuminate/Session/DatabaseSessionHandler.php:132
20 - vendor/laravel/framework/src/Illuminate/Session/Store.php:187
21 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:248
22 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:129
23 - vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
24 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
25 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:36
26 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
27 - vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:74
28 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
29 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
30 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:821
31 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:800
32 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:764
33 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:753
34 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:200
35 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
36 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
37 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php:31
38 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
39 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:21
40 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php:51
41 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
42 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php:27
43 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
44 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php:109
45 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
46 - vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php:61
47 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
48 - vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php:58
49 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
50 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php:22
51 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
52 - vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php:26
53 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
54 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:137
55 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:175
56 - vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:144
57 - vendor/laravel/framework/src/Illuminate/Foundation/Application.php:1220
58 - public/index.php:20
59 - vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php:23

## Request

GET /

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
* **sec-ch-ua**: "Not=A?Brand";v="99", "Google Chrome";v="151", "Chromium";v="151"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Linux"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: none
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9,id;q=0.8
* **cookie**: remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6ImxlRnFuY01XcjZ5K2NTb2VLY1BaS2c9PSIsInZhbHVlIjoiUFpIekV0UTVsSlhMTUJVZCs4UHBwYWh6OURjVUwvUXJ4VFZLSEo0QlJ6M29LUDVIRVBHRVk2R1JldmNjcjcyRldoamJMTU53eHVqV0V0c3BDV2xUU1BvTWpWbTg2VVFERU5kdEppcDNPS1c5eXVwN0FzR3JvQlRsaFVYZFdEVUhPZnJvakloZjEwdVByMTIxZFJHS0ljcmozWkZidnlFMXFFSFNKbzd4eEdzTW1nVVl3Sit4dDhVTXBDSmtwSWROY3RNTDdsaU03VlBWK1E5d2dOby9mMlhaaTJyQUFnSjVscnZYWU5aUDRaVT0iLCJtYWMiOiIzNTQ0NGQ5YmMyNTBiY2JhMzEwNDM4NzJjZWRmNDhlNGY4OWMxYWNlYWI5MmQzNjkxZGJhYmRmMzA4ZjY2MjFhIiwidGFnIjoiIn0%3D; XSRF-TOKEN=eyJpdiI6IktyQW1ieklDcWJOWjZaanh3S0ZvR2c9PSIsInZhbHVlIjoiUW1NVkhSejBTb2grWDRWUE5lUG5nTjc1a2M0RTZJcGc2S3ljNFk4MjZMSGtnVGxkR3BPWnFUTHNpSG1BTUhwV0t2c3R1bDU4aUJXaUNVVFBneXBnSkw5blhvd0haUVN6c2tUUTJUQVRUc2ZKTmxoU2d0dlhtbzBnTGgzaTRUZ0giLCJtYWMiOiIxZGNkOWQ3NjdiNmQ1ODVkMWEwYWY3YjY0NjkxOWE0NmUzY2Y4MTgwOGVhZTlmODA0YjY3ZjJkZGRkYTYxZDVkIiwidGFnIjoiIn0%3D; k2-net-session=eyJpdiI6IktrN1A5cGs1ODBPdndDS25iRkFRbUE9PSIsInZhbHVlIjoiRVdFR1dlTVlaZXYzNDF0ZGZqYzY1QjZKb1IzSkpDdnI5MDZrV1BxMkFlcjl2WGRsRkE2b3MwRG03RmFicUVYNG1YNlM1SCs4RFc4RW1ZODkvRzgzRmk3bkJxVyt5ck00NjNDSWxpZDJ1YXNXc0N0TkgvdGFxT1ZEOW1WTWdBU2giLCJtYWMiOiJmN2NjMTYyNjQxYzhjMjM3MWYxMGIwZWUzNzQ2OTA1M2JmYTU5NTEzZDRkNmE2YzY0NWU2MzYzOGUyMzA4Njg0IiwidGFnIjoiIn0%3D

## Route Context

controller: Closure
middleware: web

## Route Parameters

No route parameter data available.

## Database Queries

* pgsql - select * from "sessions" where "id" = 'zW6OdpxhKt6AJZNa6lG4d4hZZSAtHDMWS0ErOIFR' limit 1 (7.97 ms)
