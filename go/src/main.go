// This is the name of our package
// Everything with this package name can see everything
// else inside the same package, regardless of the file they are in
package main

// These are the libraries we are going to use
// Both "fmt" and "net" are part of the Go standard library
import (
	"database/sql"
	"fmt"
	"html/template"
	"net/http"
	"os"
	"path/filepath"

	_ "github.com/mattn/go-sqlite3"
)

// TodoPageData stores the page data
type TodoPageData struct {
	Title    string
	Contents string
	Debug    string
}

var tpl, tplerr = template.ParseFiles("src/responsive.html")
var webTemplate = template.Must(tpl, tplerr)
var database, _ = sql.Open("sqlite3", ".db/mysqlitedb.db")

func main() {
	rows, _ := database.Query("SELECT name, value FROM variables")
	var name string
	var value string
	for rows.Next() {
		rows.Scan(&name, &value)
		fmt.Println(name + ": " + value)
	}

	fmt.Println("Template error: ", tplerr)

	dir, err := filepath.Abs(filepath.Dir(os.Args[0]))

	fmt.Println("Current dir: " + dir)

	wd, err := os.Getwd()
	fmt.Println("Working dir:" + wd)
	fmt.Println(err)

	// The "HandleFunc" method accepts a path and a function as arguments
	// (Yes, we can pass functions as arguments, and even trat them like variables in Go)
	// However, the handler function has to have the appropriate signature (as described by the "handler" function below)
	http.HandleFunc("/", handler)

	http.HandleFunc("/exit", exitCode)

	fs := http.FileServer(http.Dir("src/inc/"))
	http.Handle("/inc/", http.StripPrefix("/inc/", fs))

	// After defining our server, we finally "listen and serve" on port 8080
	// The second argument is the handler, which we will come to later on, but for now it is left as nil,
	// and the handler defined above (in "HandleFunc") is used
	http.ListenAndServe(":8080", nil)
}

// "handler" is our handler function. It has to follow the function signature of a ResponseWriter and Request type
// as the arguments.
func handler(w http.ResponseWriter, r *http.Request) {
	data := TodoPageData{
		Title:    "My TODO list",
		Contents: "Here are some contents",
	}

	webTemplate.Execute(w, data)
}

func exitCode(w http.ResponseWriter, r *http.Request) {
	fmt.Print("Exiting... bye")
	os.Exit(0)
}
