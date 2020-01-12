package inc

import (
	"errors"
	"net/http"
	"time"
)

// Strtotime Converts a string to unix time
func Strtotime(str string) (int64, error) {
	layout := "2006-01-02 15:04:05"
	t, err := time.Parse(layout, str)
	if err != nil {
		return 0, err
	}
	return t.Unix(), nil
}

// filterInput returns a GET parameter using the given request
func filterInput(r *http.Request, param string) (string, error) {
	value, ok := r.URL.Query()[param]

	if ok && len(value[0]) > 1 {
		return value[0], nil
	}

	return "", errors.New("Error")
}
