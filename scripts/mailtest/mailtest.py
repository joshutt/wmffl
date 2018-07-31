
import sys
import re
import string
import mailbox
import pickle
import gzip

mbx = mailbox.UnixMailbox(sys.stdin)
ms = mbx.next()
tokenList = {}
tlist = pickle.load(gzip.GzipFile('datafile.gz','r'))
if (tlist != None) :
    tokenList = tlist
while (ms != None) :
    content = ms.fp
#    print ms.getaddr("From")[1]
    #print ms.getheader("To")
    #print "*************************"
    linelist = content.readlines()
    num = 0
    for line in linelist :
        pieces = re.split('[%s%s]+'%(string.punctuation, string.whitespace), line.strip())
        num += len(pieces)
        for piece in pieces :
            piece = piece.lower()
            if (tokenList.has_key(piece)) :
                tokenList[piece]+=1
            else :
                tokenList[piece] = 1
        #if (ms.getaddr("From")[1].startswith("wmffl")) :
        #    print pieces
        #print line.strip()
    #print "*************************"
    #print "*************************"
    #print 
#    print num
    ms = mbx.next()

for token in tokenList.keys() :
    if (tokenList[token] > 1) :
        print "%s - %d"%(token, tokenList[token])

pickle.dump(tokenList, gzip.open("datafile.gz", "w"))
