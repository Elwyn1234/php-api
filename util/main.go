package main

import (
	"database/sql"
	"encoding/json"
	"flag"
	"fmt"
	"log"
	"os"

	"github.com/fatih/color"
	_ "github.com/go-sql-driver/mysql"
)

func main() {
  var errorWriter ErrorWriter
  logError = log.New(errorWriter, "ERROR: ", log.Ldate|log.Ltime|log.Lshortfile)

  if (len(os.Args) < 2) {
    logError.Fatal("No subcommands specified. Exiting.")
  }
  
  createdbFlags := flag.NewFlagSet("createdb", flag.PanicOnError)
  var createdbFlagsForce bool
  createdbFlags.BoolVar(&createdbFlagsForce, "f", false, "")
  createdbFlags.BoolVar(&createdbFlagsForce, "force", false, "Force the entire database to be dropped and recreated.")

  installFlags := flag.NewFlagSet("install", flag.PanicOnError)
  var installFlagsForce bool
  installFlags.BoolVar(&installFlagsForce, "f", false, "")
  installFlags.BoolVar(&installFlagsForce, "force", false, "Force the entire database to be dropped and recreated.")

  switch os.Args[1] {
  case "install":
    err := installFlags.Parse(os.Args[2:])
    if (err != nil) { log.Print(err.Error()) }

    log.Print("install flags force: ", installFlagsForce)
    createdb(installFlagsForce)
    addTestData()

  case "createdb":
    err := createdbFlags.Parse(os.Args[2:])
    if (err != nil) { log.Print(err.Error()) }
    createdb(createdbFlagsForce)

  case "dropdb":
    dropDatabase(rootOpenDatabase(""))
  default:
    logError.Fatal("The subcommand provided is not a recognised subcommand. Exiting.")
  }
}

func rootOpenDatabase(dbname string) (pool *sql.DB) {
  pool, err := sql.Open("mysql", fmt.Sprintf("root:o1M@2UO4ngwg!i9R$3hvLSVpt@(localhost:3307)/%v", dbname)) // TODO: get the password from a file
  if (err != nil) { logError.Fatal(err.Error()) } // TODO: error handling

  pool.SetConnMaxLifetime(0)
  pool.SetMaxIdleConns(1)
  pool.SetMaxOpenConns(1)
  if err := pool.Ping(); err != nil { logError.Fatal(err.Error()) }

  return pool
}

func dropDatabase(pool *sql.DB) {
  log.Print("Dropping dvden Database.")
  _, err := pool.Exec("DROP DATABASE dvden")
  if (err != nil) { logError.Fatal(err.Error()) }
  log.Print("Database dropped!")
}

func createdb(forceCreation bool) {
  pool := rootOpenDatabase("")
  if (forceCreation){
    dropDatabase(pool)
  }
  log.Print("Creating dvden Database.")
  _, err := pool.Exec(`CREATE DATABASE dvden;`)
  if (err != nil) { logError.Fatal(err.Error()) }
  log.Print("dvden database successfully created!")
  _, err = pool.Exec(`USE dvden;`)
  if (err != nil) { logError.Fatal(err.Error()) }
  log.Print("dvden database selected.")
    
  _, err = pool.Exec(`CREATE TABLE items (
    name VARCHAR(50) NOT NULL
  );`)
  if (err != nil) { logError.Fatal(err.Error()) }
  log.Print("items table created!")
}

func addTestData() {
  pool := rootOpenDatabase("dvden")
  
  testdata, err := os.ReadFile("./testdata.json")
  if (err != nil) {
    logError.Fatal("Failed to read file testdata.json")
  }
  var dvden Dvden
  err = json.Unmarshal(testdata, &dvden)
  if (err != nil) {
    logError.Fatal(err)
  }

  for i := 0; i < len(dvden.Items); i++ {
    _, err = pool.Exec(`INSERT INTO items (name) VALUES (?);`, dvden.Items[i].Name) // TODO: more secure credentials
    if (err != nil) { logError.Fatal(err.Error()) }
  }
  log.Print("Test data created for the items table!")
}

type Dvden struct {
  Items []Item
}
type Item struct {
  Name string
}
func (errorWriter ErrorWriter) Write(p []byte) (n int, err error) { // TODO: fix common code across modules
  color.Red(string(p))
  return 0, nil
}
type ErrorWriter struct {}
var logError *log.Logger

