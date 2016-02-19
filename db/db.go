package db

import (
	"gopkg.in/mgo.v2/bson"
	"gopkg.in/mgo.v2"
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
	ParentWeight int           `json:"parentweight"`
	Status       int           `json:"status"`
	Timestamp    int           `json:"timestamp"`
}

func GetPageFromDB() (*Page, error) {
	page := &Page{}

	c := bson.M{"status": 0, "parentweight": bson.M{"$gt": 0}}

	change := mgo.Change{
        Update: bson.M{"$set": bson.M{"status": 1}},
        Upsert: false,
        Remove: false,
        ReturnNew: true,
	}

	//err := context.Db.C("page").Find().Sort("-parentweight").One(&page)
	_, err := context.Db.C("page").Find(c).Sort("-parentweight").Apply(change, &page)

	if err != nil {
		refresh(err)
	}
	//log.Debug("Load page " + page.Url)
	return page, err
}

func (p Page) SetStatus(status int) {
	err := context.Db.C("page").UpdateId(p.Id, bson.M{"$set": bson.M{"status": status}})
	if err != nil {
		refresh(err)
	}
}

func SavePage(page *Page) (bool, error) {

	alreadyExists := false

	p   := &Page{}
	err := context.Db.C("page").Find(bson.M{"url": page.Url}).One(&p)

	if err != nil {
		err = context.Db.C("page").Insert(page)
		log.Info("Add page " + page.Url)
		if err != nil {
			refresh(err)
		}
	} else {
		alreadyExists = true
		//log.Debug("Already exist " + page.Url)
	}

	return alreadyExists, err
}

func SaveEmail(email *Email) (bool, error) {

	alreadyExists := false

	e   := &Email{}
	err := context.Db.C("email").Find(bson.M{"email": email.Email}).One(&e)

	if err != nil {
		err = context.Db.C("email").Insert(email)
		log.Info("Add email: " + email.Email)
		if err != nil {
			refresh(err)
		}
	} else {
		alreadyExists = true
		log.Debug("Already exist: " + email.Email)
	}

	return alreadyExists, err
}
