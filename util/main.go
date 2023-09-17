package main

import (
	"database/sql"
	"encoding/json"
	"log"
	"os"

	"github.com/fatih/color"
	_ "github.com/go-sql-driver/mysql"
)

func main() {
  createdb()
}

func rootOpenDatabase(dbname string) (pool *sql.DB) {
  // pool, err := sql.Open("mysql", fmt.Sprintf("Server=digitech.ncl-coll.ac.uk;Database=ejohn_dvden;Uid=ejohn;Pwd=Pssw0rd12;", dbname))
  pool := sql.Open("mysql", `ejohn:P@ssw0rd12@tcp(mysql:host=digitech.ncl-coll.ac.uk;dbname=ejohn_dvden)/`) // TODO: get the password from a file

  pool.SetConnMaxLifetime(0)
  pool.SetMaxIdleConns(1)
  pool.SetMaxOpenConns(1)

  return pool
}

func dropDatabase(pool *sql.DB) {
  log.Print("Dropping dvden Database.")
  pool.Exec("DROP DATABASE dvden")
  log.Print("Database dropped!")
}

func createdb() {
  pool := rootOpenDatabase("ejohn_dvden")
  dropDatabase(pool)
  log.Print("Creating dvden Database.")
  pool.Exec(`CREATE DATABASE dvden;`)
  log.Print("dvden database successfully created!")
  pool.Exec(`USE dvden;`)
  log.Print("dvden database selected.")
    
  pool.Exec(`CREATE TABLE movies (
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    price 900 NOT NULL,
    rentalPrice 300 NOT NULL
  );`)
  log.Print("movies table created!")
}
