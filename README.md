Start: <b>15:25</b>

End: <b>18:15</b>


<h1>How to test?</h1>

• download the project
• cp .env.example .env
• docker compose up -d (at the root of the project)
• docker compose exec app php artisan key:generate
• docker compose exec app php artisan storage:link
• docker compose exec app php artisan migrate

<h3>To run user import from randomuser.me</h3>
Open Postman and send POST request to 
<u>http://localhost/api/randomUser</u>
Also you can set body -> raw:
<pre>{
    "limit": 2
}</pre>
This is not a required parameter, if it is not specified, the default value will be 10 users

<h3>To get imported users</h3>
Open Postman and send GET request to 
<u>http://localhost/api/randomUser</u>
Also you can set body -> raw:
<pre>{
    "type": "xml",
    "field": "name",
    "orderBy": "asc",
    "limit": 50,
    "page": 1
}</pre>

<b>type</b> can be specified as xml or json,
<b>field</b> is the field by which we will sort
<b>orderBy</b> you sort type (asc: a - z / desc: z - a)
<b>limit</b> is the limit of users on one page
<b>page</b> is simple pagination
All fields are also optional
By default, sorting will be performed by surname desc

<h3>To run Unit Test</h3>
• docker compose exec app php artisan test

<h3>Additionally:</h3>
Made an email and the ability to log in for users. This can be checked by mailhog:
• Go to http://localhost:8025
• import users through PostMan and see the email, there will be login and pass
• then go to http://localhost and click login on the top right