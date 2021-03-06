import sys
from os import access
from os import F_OK
from time import time
from random import randint
from socket import gethostname
from email import *
from email.Parser import *
from email.Iterators import *
from mailbox import *


def moveMail(theArg) :
    #path = "/home/joshutt/mail/wmffl.com/josh%s"%theArg
    path = "/home/joshutt/football/scripts/mailscripts/maildeposit%s"%theArg
    #print path

    newArg = theArg.replace("/", ".")
    newPath = "/home/joshutt/mail/wmffl.com/josh/%s/new"%newArg
    #print newPath

    # if folder at newPath doesn't exist, create ???
    exists = access(newPath, F_OK)
    if (not exists) :
        return 0
    
    # Read all of mailbox at path
    fp = open(path, "r")

    #msg = message_from_file(fp)
    #_structure(msg)

    #parser = Parser()
    #message = parser.parse(fp)
    #print message

    oldmailbox = PortableUnixMailbox(fp)
    message = oldmailbox.next()
    #message = oldmailbox.next()

    count = 0
    while (message != None) :
        #print message.unixfrom
        #print message
        #print message.fp.read()

        text = "%s%s%s" % (message.unixfrom, message, message.fp.read())

        newName = "%d.JPU%d.%s" % (int(time()), randint(1, 10000000), gethostname()) 
        fp = open("%s/%s"%(newPath, newName), "w")
        #print "Saving as %s"%newName
        fp.write(text)
        fp.close()
        
        message = oldmailbox.next()

    #message.rewindbody()
    #print message

    fp.close()
    return 1



if __name__ == "__main__" : 
    theArg = "/%s"%sys.argv[1]
    #print theArg
    result = moveMail(theArg)
    print result

