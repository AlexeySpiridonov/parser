package init

import (
	"parser.2hive.org/config"
	"parser.2hive.org/db"
	"github.com/op/go-logging"
	"github.com/yvasiyarov/gorelic"
	"gopkg.in/mgo.v2"
	"os"
)

var log = logging.MustGetLogger("init")

func InitDB() (*mgo.Session, error) {
	log.Info("Connect to DB: " + config.Get("dbHost") + " " + config.Get("dbName"))

	mongo, err := mgo.Dial(config.Get("dbHost"))
	if err != nil {
		log.Panic("Can't connect to mongoDB. Server is stopped")
	}

	log.Info("DB ok")

	db.Set(mongo, mongo.DB(config.Get("dbName")))

	return mongo, err
}

func InitLogs() {
	//format := logging.MustStringFormatter("%{time:15:04:05.000} %{module} %{shortfile} -> %{level:.7s} %{id:03x} %{message}")
	format := logging.MustStringFormatter("Parser.%{module} %{shortfile} > %{level:.7s} > %{message}")

	//file to stdout
	log1 := logging.NewLogBackend(os.Stderr, "", 0)
	file, err := os.OpenFile(config.Get("logpath"), os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0666)
	if err != nil {
		log.Panic("Open log file fail: " + config.Get("logpath"))
	}

	log1F := logging.NewBackendFormatter(log1, format)

	//log to file
	log2  := logging.NewLogBackend(file, "", 0)
	log2F := logging.NewBackendFormatter(log2, format)

	//log to syslog
	log3, _ := logging.NewSyslogBackend("")
	log3LeveledF := logging.NewBackendFormatter(log3, format)

	//setup logs
	if config.GetEnv() == "prod" {
		log3Leveled := logging.AddModuleLevel(log3LeveledF)
		log3Leveled.SetLevel(logging.INFO, "")
		logging.SetBackend(log3Leveled)
	} else {
		logging.SetBackend(log1F, log2F, log3LeveledF)
	}

	log.Info("Logs ok")
}

func InitGoRelic() {
	agent := gorelic.NewAgent()
	agent.Verbose = false
	agent.NewrelicLicense = config.Get("newrelicKey")
	agent.NewrelicName = config.Get("newrelicName")
	agent.Run()
	log.Info("NewRelic ok")
}
