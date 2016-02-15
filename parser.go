package main

import (
	"parser.2hive.org/config"
	"parser.2hive.org/db"
	"parser.2hive.org/init"
	"github.com/op/go-logging"
	"io/ioutil"
	"net/http"
	"runtime"
	"time"
	"net/url"
	//htmlparser "github.com/calbucci/go-htmlparser"
	"github.com/PuerkitoBio/goquery"
)

var log = logging.MustGetLogger("main")

func main() {
	init.InitLogs()
	init.InitGoRelic()
	mongo, _ := init.InitDB()
	defer mongo.Close()

	log.Info("GOMAXPROCS:%d\n", runtime.GOMAXPROCS(0))

	for {
		page, err := db.GetPageFromDB()
		if err != nil {
			// Delete this page? Mark as Error? Infinite loop!
			continue
		}

		go getContentByURL(page.Url)

		page.SetStatus(1)

		time.Sleep(1 * time.Second)
	}
}

func getContentByURL(url string) {

	//doc, err := goquery.NewDocument("http://moscow.startups-list.com/") 
	doc, err := goquery.NewDocument(url) 
	if err != nil {
		log.Error("Load url error" + err.Error())
		return
	}

	doc.Find("a").Each(func(i int, s *goquery.Selection) {
		href, exists := s.Attr("href")

		if !exists {
			log.Error("href is empty, skipping...")
			return
		}

		weight := getWeight(url, href)

		if weight > 0 {
			// Save
			alreadyExists, err := db.SavePage(&db.Page{Url: href, Parent: url, ParentWeight: weight, Status: 1, Timestamp: db.GetTimestamp()})

			// And Parse. if New!
			if !alreadyExists {
				go getContentByURL(url)
			}
		} else {
			log.Debug("Skip page with low weight", page)
		}
	})
}

//func getWeight(page db.Page, text string) int {
func getWeight(parentUrl, currentUrl string) int {
	// @TODO

	weight := 0

	parent  := url.Parse(parentUrl)
	current := url.Parse(currentUrl)

	if parent.Host != current.Host {
		weight++
	}

	/*
	  сравниваем url, если он с другого домена, то поднимаем вес
	  если есть стоп слова, понижаем вес
	  если есть  run слова,  повышаем вес
	*/

	stopWords := []string{"twitter.com", "facebook.com", "flickr.com" "example.com", "simple.com", "domain.com"}

	for word := range stopWords {
		if current.Host == word {
			weight--
		}
	}

	return weight
}

func getEmails(text string) []string {
	r := []string{}
	return r
}

func getURLs(text string) []string {
	r := []string{}
	return r
}

/*func loadHtml(url string) (string, error) {
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
}*/
