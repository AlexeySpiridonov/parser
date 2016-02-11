package parser

import (
	_ "./config"
	"./db"
	i "./init"
	"github.com/op/go-logging"
	"io/ioutil"
	"net/http"
	"runtime"
	"time"
)

var log = logging.MustGetLogger("main")

func main() {
	i.InitLogs()
	i.InitGoRelic()
	mongo, _ := i.InitDB()
	defer mongo.Close()

	log.Info("GOMAXPROCS:%d\n", runtime.GOMAXPROCS(0))

	for {
		page, err := db.GetPageFromDB()
		if err != nil {
			continue
		}
		text, _ := loadHtml(page.Url)
		if err != nil {
			continue
		}
		weight := getWeight(page)

		if weight > 0 {

			//emails
			emails := getEmails(text)
			for _, email := range emails {
				db.SaveEmail(db.Email{Email: email, Url: page.Url, Timestamp: db.GetTimestamp()})
			}

			//Urls
			urls := getURLs(text)
			for _, url := range urls {
				db.SavePage(db.Page{Url: url, Weight: weight, Status: 0, Timestamp: db.GetTimestamp()})
			}

		} else {
			log.Debug("Skip page with low weight", page)
		}

		page.SetStatus(1)
		time.Sleep(1 * time.Second)

	}

}

func getWeight(page db.Page) int {
	//TODO
	return 1
}

func getEmails(text string) []string {
	r := []string{}
	return r
}

func getURLs(text string) []string {
	r := []string{}
	return r
}

func loadHtml(url string) (string, error) {
	log.Debug("Load url", url)
	response, err := http.Get(url)
	if err != nil {
		log.Error("Load url error" + err.Error())
		return "", err
	} else {
		defer response.Body.Close()
		content, err := ioutil.ReadAll(response.Body)
		return string(content), err
	}
}
