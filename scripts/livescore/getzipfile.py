#!/usr/local/python

import sys, time, os
#week = (time.localtime(time.time())[7] - 245)/7 + 1
#week = (time.localtime(time.time())[7] - 246)/7 + 1
#week = (time.localtime(time.time())[7] - 250)/7 + 1
#week = (time.localtime(time.time())[7] - 248)/7 + 1     # 2010
#week = (time.localtime(time.time())[7] - 247)/7 + 1     # 2011
#week = (time.localtime(time.time())[7] - 252)/7 + 1     # 2011
nowTime = time.localtime()
week = (nowTime[7] - 252)/7 + 1
year = (nowTime[0])
#week=2 

if (len(sys.argv) >= 2) :
    outputFile =  sys.argv[1]
    if (len(sys.argv) >= 3) :
        week = int(sys.argv[2])
        if (len(sys.argv) >= 4) :
            year = int(sys.argv[3])
else :
    outputFile = "myzip.zip"
print week

class myClass :
	def __init__ (self) :
		self.week = week
		self.id = None
		self.aVar = ''
		self.datetime = ''

fullStats = myClass()

def lineProcess(theString) :
	#print "Process %s"%theString
	if (theString[0:5] == 'stats') :
		fullStats.id = theString[5+fullStats.week]
		#print "Got %s"%fullStats.id

def getStatFile(theData) :
	fullStats.aVar = fullStats.aVar + theData

def getDateTime(theString) :
	fullStats.datetime = theString

import string
# from ftplib import FTP
# ftp = FTP('www.fflm.com')
# ftp.login()
# ftp.cwd('files/nfl')
# #ftp.retrlines('retr mast2002.txt', lineProcess)
# ftp.retrlines('retr mast2003.txt', lineProcess)

import httplib
conn = httplib.HTTPConnection("www.fflm.com")
#conn.request("GET", "/files/nfl/mast%d.txt"%year)
#response = conn.getresponse()
#data1 = response.read()
#print data1
#data2 = string.split(data1)
#for line in data2:
#	#print line
#	lineProcess(line)
#
#id = string.lower(fullStats.id)
#week = fullStats.week
##filename = 'f15%02d%s.fs0'%(week,id)
#filename = 'f%02d%02d%s.fs0'%(year-2000, week,id)
##filename = 'f06%02d%s.fs0'%(week,id)
#conn.close()

conn.request("GET", "/gameday/gameday.zip")
#conn.request("GET", "/gameday/gameday%02d.zip"%week)
#conn.request("GET", "/files/nfl/%s"%filename)
response = conn.getresponse()
savData = response.read()
fullStats.datetime=response.getheader("Last-Modified")
outWrite= open(outputFile, 'wb')
outWrite.write(savData)
conn.close()

os.environ['TZ'] = 'GMT'
tptime = time.strptime(fullStats.datetime, "%a, %d %b %Y %H:%M:%S GMT")
os.environ['TZ'] = 'US/Eastern'
diff = time.mktime(time.gmtime())-time.mktime(time.localtime())
realtime = time.mktime(tptime)-diff
#time.tzset()
print time.strftime("%b %d - %I:%M %p", time.localtime(realtime))

#pieces = string.split(fullStats.datetime)
#theDateTime = "%s %s %s"%(pieces[2], pieces[1], pieces[4])
#import os
#formatt = '%b %d %H:%M'
#os.environ['TZ'] = 'UTC'
#tptime = time.strptime(theDateTime, formatt)
#lttime = list(tptime)
#lttime[0]=2003
#tptime = tuple(lttime)
#secs = time.mktime(tptime)
#os.environ['TZ'] = 'US/Eastern'
#acttime = time.localtime(secs)
#theDateTime = time.strftime(formatt, acttime)

#formatt = '%b %d %H:%M'
#import os
#os.environ['TZ'] = 'US/Eastern'
#crtime = time.localtime(time.time())
#theDateTime=time.strftime(formatt, crtime)

#print theDateTime
