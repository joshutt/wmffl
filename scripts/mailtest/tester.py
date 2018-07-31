import sys
from time import time
from whrandom import randint
from socket import gethostname
from email import *

data = ""
for line in sys.stdin :
    data = "%s%s"%(data, line)
#print data


msg = Parser.Parser().parsestr(data)

#print msg['Subject']
#print msg['From']
#print msg['To']
#print msg.keys()


newName = "%d.JPU%d.%s" % (int(time()), randint(1, 10000000), gethostname()) 
path = "/home/joshutt/mail/wmffl.com/josh/.Lists.beer/new"
fp = open("%s/%s"%(path, newName), "w")
fp.write(data)
fp.close()
