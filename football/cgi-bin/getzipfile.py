#!/usr/local/python

import time
week = (time.localtime(time.time())[7] - 244)/7 
#week = 7 
#week =6 

class myClass :
	def __init__ (self) :
		self.week = week
		self.id = None
		self.aVar = ''
		self.datetime = ''

fullStats = myClass()

def lineProcess(theString) :
	if (theString[0:5] == 'stats') :
		fullStats.id = theString[5+fullStats.week]

def getStatFile(theData) :
	fullStats.aVar = fullStats.aVar + theData

def getDateTime(theString) :
	fullStats.datetime = theString

import string
from ftplib import FTP
ftp = FTP('www.fflm.com')
ftp.login()
ftp.cwd('files/nfl')
ftp.retrlines('retr mast2002.txt', lineProcess)
id = string.lower(fullStats.id)
week = fullStats.week
#filename = 'f02%02d%s.fs0'%(week,id.lower())
filename = 'f02%02d%s.fs0'%(week,id)
#ftp.cwd('..')
#ftp.retrbinary('retr %s'%filename, getStatFile)
ftp.retrlines('LIST %s'%filename, getDateTime)
ftp.retrbinary('retr %s'%filename, open('myzip.zip', 'wb').write)
ftp.quit()

pieces = string.split(fullStats.datetime)
theDateTime = "%s %s %s"%(pieces[5], pieces[6], pieces[7])
print theDateTime
