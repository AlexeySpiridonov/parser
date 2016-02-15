package db

import (
	"github.com/op/go-logging"
	"gopkg.in/mgo.v2"
	"time"
)

var log = logging.MustGetLogger("db")

type Context struct {
	Session *mgo.Session
	Db      *mgo.Database
}

var (
	context Context
)

func Get() Context {
	return context
}

func Set(session *mgo.Session, db *mgo.Database) {
	context = Context{session, db}
}

func refresh(err error) {
	log.Error(err.Error())
	if err.Error() == "EOF" {
		log.Warning("DB connect autoRefresh")
		context.Session.Refresh()
	}
}

func GetTimestamp() int {
	return int(time.Now().Unix())
}
