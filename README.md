# The Poker Society API

## Signup

```
POST /ps/v1/signup

{
    "username": "bobdylan",
    "email": "a@4geeks.co",
    "password": "almostThere"
}
```
The reponse will include the user_id if you need it
```
{
    "username": "bobdylan",
    "email": "a@4geeks.co",
    "password": "almostThere",
    "user_id": 3
}
```

## To obtain a new token

```
POST /jwt-auth/v1/token

{
	username: "admin",
	password: "password"
}
```

The reponse status code could be 200 (success) or 403 (failure because a bad user/password combibation), if you get a different number then you probably doing wrong the request.

If you get a 200 then the token will be on the response like this:

```
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9qd3QuZGV2IiwiaWF0IjoxNDM4NTcxMDUwLCJuYmYiOjE0Mzg1NzEwNTAsImV4cCI6MTQzOTE3NTg1MCwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMSJ9fX0.YNe6AyWW4B7ZwfFE5wJ0O6qQ8QFcYizimDmBy6hCH_8",
    "user_display_name": "admin",
    "user_email": "admin@localhost.dev",
    "user_nicename": "admin"
}
```


## To save the user schedules

```
POST /ps/v1/schedules/<username>

[
	{
		"id": 1,
		"name": "Vegas 2012",
		"total": 9000,
		"attempts": [
			{
				"tournamentName": "Pompano Beach",
				"tournamentId": 123,
				"price": 3000,
				"bullets": 2
			}
		]
	}
    ...
]

```
Response


## To retrieve all the user schedules

```
GET /ps/v1/schedules/<username>
```
Response
```js

[
	{
		"id": 1,
		"name": "Vegas 2012",
		"total": 9000,
		"attempts": [
			{
				"tournamentName": "Pompano Beach",
				"tournamentId": 123,
				"price": 3000,
				"bullets": 2
			}
		]
	},
	{
		"id": 2,
		"name": "Florida 2013",
		"total": 9000,
		"attempts": [
			{
				"tournamentName": "Pompano Beach",
				"tournamentId": 123,
				"price": 3000,
				"bullets": 2
			}
		]
	}
]
```
