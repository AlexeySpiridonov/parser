package main

import (
	//"github.com/AlexeySpiridonov/goapp-config"
	"parser.2hive.org/db"
	"parser.2hive.org/ini"
	"github.com/op/go-logging"
	"io/ioutil"
	"net/http"
	"net/url"
	"runtime"
	"time"
	"strings"
	"github.com/mvdan/xurls"
	"sync"
	//htmlparser "github.com/calbucci/go-htmlparser"
	//"github.com/PuerkitoBio/goquery"
)

var log = logging.MustGetLogger("main")
var wg sync.WaitGroup

func main() {
	ini.InitLogs()
	ini.InitGoRelic()

	mongo, _ := ini.InitDB()
	defer mongo.Close()

	log.Info("GOMAXPROCS:%d\n", runtime.GOMAXPROCS(0))

	maxRoutines := 1

	wg.Add(maxRoutines)

	for i := 0; i < maxRoutines; i++ {
		go process(i)
	}

	wg.Wait()

	log.Debug("All workers are finished! Exit!!!")
}

func process(i int) {
	log.Debug("Start worker #", i+1)

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

		time.Sleep(1 * time.Second)
	}

	log.Debug("End worker #", i+1)
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
		for _, url := range urls {
			db.SavePage(&db.Page{Url: url, Parent: page.Url, ParentWeight: weight, Status: 0, Timestamp: db.GetTimestamp()})
		}

	} else {
		log.Debug("Skip page with low weight", page)
	}
}

func getPageWeight(page *db.Page, content string) int {
	// @TODO

	// Initial Weight - is the Parent Weight!
	weight := page.ParentWeight

	parent, err  := url.Parse(page.Parent)
	if err != nil {
		return weight
	}

	current, err := url.Parse(page.Url)
	if err != nil {
		return weight
	}

	/*
	  сравниваем url, если он с другого домена, то поднимаем вес
	  если есть стоп слова, понижаем вес
	  если есть  run слова,  повышаем вес
	*/

	if strings.ToLower(parent.Host) != strings.ToLower(current.Host) {
		weight++
	}

	urlStopWords := []string{
		"twitter", "facebook", "flickr", "example", "simple", "domain",
		"jquery", "linkedin", "google", "yahoo", "yandex", "cdn.", "fonts.", "maps.", "bootstrap", "googleapis",
		"schema.org", "cloudfront.net", 
		".jpg", ".png", ".gif",
	}
	for _, word := range urlStopWords {
		if strings.Contains(strings.ToLower(current.Host), word) {
			weight--
		}
	}

	contentStopWords := []string{"development", "kitchen", "sex", "porn"}
	for _, word := range contentStopWords {
		// @TODO: implement!
		if strings.Contains(content, word) {
			weight--
		}
	}

	contentRunWords := []string{"hippster", "social", "communication"}
	for _, word := range contentRunWords {	
		// @TODO: implement!
		if strings.Contains(content, word) {
			weight++
		}
	}

	return weight
}

func getEmails(content string) []string {
	r := []string{}
	return r
}

func getURLs(content string) []string {
	return xurls.Relaxed.FindAllString(content, -1)
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
