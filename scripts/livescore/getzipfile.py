#!/usr/bin/env python3

import sys, time, os
import http.client

nowTime = time.localtime()
week = (nowTime[7] - 252) // 7 + 1
year = (nowTime[0])

if (len(sys.argv) >= 2):
    outputFile = sys.argv[1]
    if (len(sys.argv) >= 3):
        week = int(sys.argv[2])
        if (len(sys.argv) >= 4):
            year = int(sys.argv[3])
else:
    outputFile = "myzip.zip"
print(week)

class myClass:
    def __init__(self):
        self.week = week
        self.id = None
        self.aVar = ''
        self.datetime = ''

fullStats = myClass()

def lineProcess(theString):
    if (theString[0:5] == 'stats'):
        fullStats.id = theString[5 + fullStats.week]

def getStatFile(theData):
    fullStats.aVar = fullStats.aVar + theData

def getDateTime(theString):
    fullStats.datetime = theString

conn = http.client.HTTPConnection("www.fflm.com")

conn.request("GET", "/gameday/gameday.zip")
response = conn.getresponse()
savData = response.read()
fullStats.datetime = response.getheader("Last-Modified")
outWrite = open(outputFile, 'wb')
outWrite.write(savData)
conn.close()

os.environ['TZ'] = 'GMT'
tptime = time.strptime(fullStats.datetime, "%a, %d %b %Y %H:%M:%S GMT")
os.environ['TZ'] = 'US/Eastern'
diff = time.mktime(time.gmtime()) - time.mktime(time.localtime())
realtime = time.mktime(tptime) - diff
print(time.strftime("%b %d - %I:%M %p", time.localtime(realtime)))
