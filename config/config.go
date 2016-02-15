package config

// copypast (and strongly modified) from  https://github.com/chrisftw/config/blob/master/config.go

import (
	"bufio"
	"os"
	"regexp"
	"strconv"
)

var configData map[string]map[string]string
var re *regexp.Regexp
var environment string

func init() {
	configData = make(map[string]map[string]string)
	re = regexp.MustCompile("^\\s*([\\w-]*)\\s*:\\s*(.*)\\s*")
	environment = "dev"
	if len(os.Args) > 1 {
		environment = os.Args[1]
	}
}

// Return current environment,  dev is default
func GetEnv() string {
	return environment
}

func Get(setting string) string {
	environmentMap := fetchenvironment()
	val, _ := environmentMap[setting]
	return val
}

func GetUint(setting string) uint64 {
	environmentMap := fetchenvironment()
	val, _ := environmentMap[setting]
	parsedVal, _ := strconv.ParseUint(val, 10, 64)
	return parsedVal
}

func GetInt(setting string) int64 {
	environmentMap := fetchenvironment()
	val, _ := environmentMap[setting]
	parsedVal, _ := strconv.ParseInt(val, 10, 64)
	return parsedVal
}

func GetFloat(setting string) float64 {
	environmentMap := fetchenvironment()
	val, _ := environmentMap[setting]
	parsedVal, _ := strconv.ParseFloat(val, 64)
	return parsedVal
}

func GetBool(setting string) bool {
	environmentMap := fetchenvironment()
	val, _ := environmentMap[setting]
	parsedVal, _ := strconv.ParseBool(val)
	return parsedVal
}

func fetchenvironment() map[string]string {
	environmentMap, ok := configData[environment]
	// singleton
	if !ok {
		importSettingsFromFile(environment)
		environmentMap, _ = configData[environment]
	}
	return environmentMap
}

func importSettingsFromFile(environment string) {
	configData[environment] = make(map[string]string)
	file, err := os.Open("config/" + environment + ".conf")
	defer file.Close()
	if err != nil {
		panic("Open config file fail: config/" + environment + ".conf. Please run application as ./app [dev] ")
		return
	}
	reader := bufio.NewReader(file)
	scanner := bufio.NewScanner(reader)

	scanner.Split(bufio.ScanLines)

	for scanner.Scan() {
		line := scanner.Text()
		parsedLine := re.FindStringSubmatch(line)
		if len(parsedLine) == 3 {
			configData[environment][parsedLine[1]] = parsedLine[2]
		}
	}
}
