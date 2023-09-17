<?php
require_once('../models/movie.php');
require_once('./utils.php');

/*******************************/
/* READ MOVIE
/*******************************/

function readMovieResultSet($query) {
  $movies = array();
  while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $movie = new Movie();
    $movie->id = $row['id'];
    $movie->name = $row['name'];
    $movie->description = $row['description'];
    $movie->weeklyPrice = $row['weeklyPrice'];
    $movie->thumbnailUrl = $row['thumbnailUrl'];
    $movie->availableToRent = $row['availableToRent'];
    $movie->rating = $row['rating'];
    $movie->diskType = $row['diskType'];

    $movies[] = $movie;
  }
  return $movies;
}

function readMovie($db, $movieId) {
  $query = $db->prepare('SELECT * FROM movies WHERE id = :movieId;');
  $query->bindParam(':movieId', $movieId, PDO::PARAM_INT);
  $query->execute();
  return readMovieResultSet($query);
}



/*******************************/
/* VALIDATION
/*******************************/

function assertValidMovie($db, $movieId) {
  $movies = readMovie($db, $movieId);
  if (count($movies) < 1) {
    errorAndExit(400,"Invalid Movie ID");
  }
}

function assertRequiredMovieFields($json_request) {
  if (!isset($json_request->name)) {
    errorAndExit(400,"Bad Request: name not given");
  }

  if (!isset($json_request->weeklyPrice)) {
    errorAndExit(400,"Bad Request: weeklyPrice not given");
  }

  if (!isset($json_request->availableToRent)) {
    errorAndExit(400,"Bad Request: availableToRent not given");
  }

  if (!isset($json_request->diskType)) {
    errorAndExit(400,"Bad Request: diskType not given");
  }
}

function assertValidMovieFields($json_request) {

  // Name

  if (isset($json_request->name)) {
    if (!is_string($json_request->name)) {
      errorAndExit(400,"Bad Request: name must be a string");
    }
    if (strlen($json_request->name) > 32) {
      errorAndExit(400,"Bad Request: name must not be greater than 32 characters");
    }
  }

  // Description

  if (isset($json_request->description)) {
    if (!is_string($json_request->description)) {
      errorAndExit(400,"Bad Request: description must be a string");
    }
  }

  // Weekly Price

  if (isset($json_request->weeklyPrice)) {
    if (!is_int($json_request->weeklyPrice)) {
      errorAndExit(400,"Bad Request: weeklyPrice must be an int");
    }
    if ($json_request->weeklyPrice < 0) {
      errorAndExit(400,"Bad Request: weeklyPrice must not be less than 0");
    }
  }

  // Thumbnail URL

  if (isset($json_request->thumbnailUrl)) {
    if (!is_string($json_request->thumbnailUrl)) {
      errorAndExit(400,"Bad Request: thumbnailUrl must be a string");
    }
    if (strlen($json_request->thumbnailUrl) > 256) {
      errorAndExit(400,"Bad Request: thumbnailUrl must not be greater than 256");
    }
  }

  // Available to Rent

  if (isset($json_request->availableToRent)) {
    if (!is_string($json_request->availableToRent)) {
      errorAndExit(400,"Bad Request: availableToRent must be a string");
    }
    if ($json_request->availableToRent !== "yes" && $json_request->availableToRent !== "no") {
      errorAndExit(400,"Bad Request: availableToRent must have a value of 'yes' or 'no'");
    }
  }

  // Rating

  if (isset($json_request->rating)) {
    if (!is_int($json_request->rating)) {
      errorAndExit(400,"Bad Request: rating must be an int");
    }
    if ($json_request->rating < 0 || $json_request->rating > 10) {
      errorAndExit(400,"Bad Request: rating must be between 0 and 10");
    }
  }

  // diskType

  if (isset($json_request->diskType)) {
    if (!is_string($json_request->diskType)) {
      errorAndExit(400,"Bad Request: diskType must be a string");
    }
    if ($json_request->diskType !== "Blu-ray" && $json_request->diskType !== "DVD") {
      errorAndExit(400,"Bad Request: diskType must have a value of 'Blu-ray' or 'DVD'");
    }
  }
}

?>
