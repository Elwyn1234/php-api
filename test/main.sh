#!/bin/env bash
# set -o xtrace



##########################################
## MOVIES
##########################################

API_TOKEN=$(curl -sS --data @data/tokens-post-admin.json https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/token.php | jq -r '.token')

# Nuke
curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -X DELETE \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/nuke.php

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    --data @data/movies-post-1.json \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php
curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    --data @data/movies-post-2.json \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php

echo "Listing all movies"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php \
    | jq .

##########################################
## MOVIES - Delete
##########################################

echo "Enter a movie id to delete from the list"
read movie_id

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -X DELETE \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php/${movie_id}

echo "Listing all movies"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php \
    | jq .

##########################################
## MOVIES - Update
##########################################

echo "Press enter"
read
echo "Update the remaining movie with the following payload"
cat data/movies-patch.json | jq .
read

movie_id=$(curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php \
    | jq -r '.[0].id')

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    --data @data/movies-patch.json \
    -X PATCH \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php/${movie_id}

echo "Listing all movies"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php \
    | jq .





##########################################
## USERS
##########################################

##########################################
## USERS - Create
##########################################

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    --data @data/users-post-1.json \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php
curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    --data @data/users-post-2.json \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php

echo "Listing all users"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php \
    | jq .

##########################################
## USERS - Delete
##########################################

echo "Enter a user id to delete from the list"
read user_id

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -X DELETE \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php/${user_id}

echo "Listing all users"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php \
    | jq .

##########################################
## USERS - Update
##########################################

echo "Press enter"
read
echo "Update the remaining user with the following payload"
cat data/users-patch.json | jq .
read

user_id=$(curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php \
    | tac | tac | jq 'del(.[] | select(.role == "admin"))' | jq '.[0].id' \
  )

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    --data @data/users-patch.json \
    -X PATCH \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php/${user_id}

echo "Listing all users"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php \
    | jq .





##########################################
## FAVOURITES
##########################################

# Setup

echo "Adding an extra movie"
echo "Listing all movies"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    --data @data/movies-post-3.json \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php

first_movie_id=$(curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php \
    | jq -r '.[0].id')

second_movie_id=$(curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php \
    | jq -r '.[1].id')

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php \
    | jq .

##########################################
## FAVOURITES - Create
##########################################

echo "Favouriting both the above movies"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -X POST \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php/${user_id}/favourite-movies/${first_movie_id}
curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -X POST \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php/${user_id}/favourite-movies/${second_movie_id}

echo "Listing favourite-movies movies"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php/${user_id}/favourite-movies \
    | jq .

##########################################
## FAVOURITED-BY
##########################################

echo "Listing users that favourited movie ${second_movie_id}"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php/${second_movie_id}/favourited-by \
    | jq .

echo "Listing users that favourited movie ${first_movie_id}"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/movies.php/${first_movie_id}/favourited-by \
    | jq .

##########################################
## FAVOURITES - Delete
##########################################

echo "Enter a movie id to delete from the favourite-movies list above"
read movie_id

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -X DELETE \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php/${user_id}/favourite-movies/${movie_id}

echo "Listing all favourite-movies"
read

curl \
    -sS \
    -H "Authorization: Bearer ${API_TOKEN}" \
    https://digitech.ncl-coll.ac.uk/~ejohn/dvden/controllers/users.php/${user_id}/favourite-movies \
    | jq .

