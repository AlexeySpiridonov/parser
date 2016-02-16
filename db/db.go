package db

import (
	"gopkg.in/mgo.v2/bson"
)

type Email struct {
	Id        bson.ObjectId `json:"code,omitempty" bson:"_id,omitempty"`
	Email     string        `json:"email"`
	Url       string        `json:"url"`
	Timestamp int           `json:"timestamp"`
}

type Page struct {
	Id           bson.ObjectId `json:"code,omitempty" bson:"_id,omitempty"`
	Url          string        `json:"url"`
	Parent       string        `json:"parent"`
	ParentWeight int           `json:"parentWeight"`
	Status       int           `json:"status"`
	Timestamp    int           `json:"timestamp"`
}

func GetPageFromDB() (*Page, error) {
	page := &Page{}
	//TODO  add weight check >0
	err := context.Db.C("page").Find(bson.M{"status": 0, "parentWeight":bson.M{"$gt": 0}}).Sort("timestamp").One(&page)
	if err != nil {
		refresh(err)
	}
	log.Debug("Load page", page)
	return page, err
}

func (p Page) SetStatus(status int) {
	log.Debug("Set status ", status, p)
	err := context.Db.C("page").UpdateId(p.Id, bson.M{"$set": bson.M{"status": status}})
	if err != nil {
		refresh(err)
	}
}

func SavePage(page *Page) (bool, error) {
	log.Debug("Save", page)

	alreadyExists := false

	p   := &Page{}
	err := context.Db.C("page").Find(bson.M{"url": page.Url}).One(&p)

	if err != nil {
		err = context.Db.C("page").Insert(page)
		if err != nil {
			refresh(err)
		}
	} else {
		alreadyExists = true
		log.Debug("Already exist", page)
	}

	return alreadyExists, err
}

func SaveEmail(email *Email) (bool, error) {
	log.Debug("Save", email)

	alreadyExists := false

	e   := &Email{}
	err := context.Db.C("email").Find(bson.M{"email": email.Email}).One(&e)

	if err != nil {
		err = context.Db.C("emal").Insert(email)
		if err != nil {
			refresh(err)
		}
	} else {
		alreadyExists = true
		log.Debug("Already exist", email)
	}

	return alreadyExists, err
}
