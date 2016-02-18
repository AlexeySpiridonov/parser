package main

import (
	"github.com/AlexeySpiridonov/parser/db"
	"github.com/AlexeySpiridonov/parser/ini"
	"github.com/op/go-logging"
	"io/ioutil"
	"net/http"
	"net/url"
	"runtime"
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

	maxRoutines := 16

	wg.Add(maxRoutines)

	for i := 0; i < maxRoutines; i++ {
		time.Sleep(3 * time.Second)
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

		time.Sleep(3 * time.Second)
	}

	log.Info("End worker #", i+1)
}

func processPage(page *db.Page) {
	pageHTML, err := loadHtml(page.Url)
	if err != nil {
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

			// http:// by default if none is set (i.e. empty)
			if strings.Trim(parsedURL.Scheme, " ") == "" {
				parsedURL.Scheme = "http";
			}
			db.SavePage(&db.Page{Url: parsedURL.String(), Parent: page.Url, ParentWeight: weight, Status: 0, Timestamp: db.GetTimestamp()})
		}

	} else {
		log.Debug("Skip page with low weight", page)
	}
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
		weight = weight - 50
	}

	urlStopWords := []string{
		"twitter", "facebook", "flickr", "example", "simple", "domain", "vk.com", "livejournal",
		"jquery", "linkedin", "google", "yahoo", "yandex", "cdn.", "fonts.", "maps.", "bootstrap", "googleapis",
		"schema.org", "cloudfront.net",
		".jpg", ".png", ".gif", ".js", ".css",
	}
	for _, word := range urlStopWords {
		if strings.Contains(strings.ToLower(current.Host), word) {
			log.Debug("stop word: " + word)
			return 0
		}
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
	log.Debug("Load url: " + url)

	response, err := http.Get(url)

	if err != nil {
		log.Error("Load url error: " + err.Error())
		return "", err
	} else {
		defer response.Body.Close()
		content, err := ioutil.ReadAll(response.Body)
		return string(content), err
	}
}
