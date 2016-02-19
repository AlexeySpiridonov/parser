package main

import (
	"github.com/AlexeySpiridonov/parser/db"
	"github.com/AlexeySpiridonov/parser/ini"
	"github.com/op/go-logging"
	"io/ioutil"
	"net/http"
	"net/url"
	"runtime"
	"html"
	"time"
	"strings"
	"github.com/mvdan/xurls"
	"sync"
	"regexp"
)

var log = logging.MustGetLogger("main")
var wg sync.WaitGroup

func main() {
	ini.InitLogs()
	ini.InitGoRelic()

	mongo, _ := ini.InitDB()
	defer mongo.Close()

	log.Info("GOMAXPROCS:%d\n", runtime.GOMAXPROCS(0))

	maxRoutines := 30

	wg.Add(maxRoutines)

	for i := 0; i < maxRoutines; i++ {
		time.Sleep(1 * time.Second)
		go process(i)
	}

	wg.Wait()

	log.Info("All workers are finished! Exit!!!")
}

func process(i int) {
	log.Info("Start worker #", i+1)

	defer wg.Done()

	for {
		page, err := db.GetPageFromDB()
		if err != nil {
			// Delete this page? Mark as Error?
			time.Sleep(500 * time.Millisecond)
			continue
		}

		// Set as Processed
		page.SetStatus(1)

		// Process!
		processPage(page)
		//time.Sleep(1 * time.Second)

	}

	log.Info("End worker #", i+1)
}

func  checkStopURL(url string) bool {
	urlStopWords := []string{
		"twitter", "facebook", "flickr", "example", "simple", "domain", "vk.com", "livejournal", "github.com",
		"jquery", "linkedin", "google", "yahoo", "yandex", "cdn.", "fonts.", "maps.", "bootstrap", "googleapis",
		"schema.org", "cloudfront.net", "mail.ru", "porn", "forbes.com", "nytimes.com", "techcrunch.com","bitly.com",
		".jpg", ".png", ".gif", ".js", ".css", ".min", "angel.co",
	}
	for _, word := range urlStopWords {
		if strings.Contains(strings.ToLower(url), word) {
			//log.Debug("Stop URL word: " + word)
			return true
		}
	}
	return false
}

func processPage(page *db.Page) {

	if checkStopURL(page.Url) {
		return
	}

	pageHTML, err := loadHtml(page.Url)
	if err != nil {
		log.Error("Page load error" + err.Error())
		return
	}

	weight := getPageWeight(page, strings.ToLower(pageHTML))

	if weight > 0 {
		//emails
		emails := getEmails(pageHTML)
		for _, email := range emails {
			db.SaveEmail(&db.Email{Email: email, Url: page.Url, Timestamp: db.GetTimestamp()})
		}

		//Urls
		urls := getURLs(pageHTML)
		for _, rawURL := range urls {
			// Parse URL
			parsedURL, err := url.Parse(rawURL)
			if err != nil {
				log.Error("Could not Parse URL: " + rawURL)
				continue
			}

			if checkStopURL(parsedURL.String()) {
				continue
			}

			// http:// by default if none is set (i.e. empty)
			if strings.Trim(parsedURL.Scheme, " ") == "" {
				parsedURL.Scheme = "http";
			}
			db.SavePage(&db.Page{Url: html.UnescapeString(parsedURL.String()), Parent: page.Url, ParentWeight: weight, Status: 0, Timestamp: db.GetTimestamp()})
		}

	} else {
		log.Debug("Skip page with low weight", page)
	}
	//time.Sleep(1 * time.Second)

}

func getPageWeight(page *db.Page, content string) int {
	// @TODO

	// Initial Weight - is the Parent Weight!
	weight := page.ParentWeight - 5

	parent, err  := url.Parse(page.Parent)
	if err != nil {
		return 0
	}

	current, err := url.Parse(page.Url)
	if err != nil {
		return 0
	}

	/*
	  сравниваем url, если он с другого домена, то опускаем вес
	  если есть стоп слова, понижаем вес
	  если есть  run слова,  повышаем вес
	*/

	if strings.ToLower(parent.Host) != strings.ToLower(current.Host) {
		weight = weight - 30
	}

	contentStopWords := []string{"kitchen", "sex", "porn"}
	for _, word := range contentStopWords {
		// @TODO: implement!
		if strings.Contains(content, word) {
			weight = weight - 10
		}
	}

	contentRunWords := []string{"hippster", "dating", "communication"}
	for _, word := range contentRunWords {
		// @TODO: implement!
		if strings.Contains(content, word) {
			weight = weight + 1
		}
	}
	return weight
}

func getEmails(content string) []string {
	r, _ := regexp.Compile(`[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,10}`)
	return r.FindAllString(content, -1)
}

func getURLs(content string) []string {
	return xurls.Relaxed.FindAllString(content, -1)
}

func loadHtml(url string) (string, error) {

	log.Debug("Get URL: " + url)

	response, err := http.Get(url)

	if err != nil {
		return "", err
	} else {
		defer response.Body.Close()
		content, err := ioutil.ReadAll(response.Body)
		return string(content), err
	}
}
