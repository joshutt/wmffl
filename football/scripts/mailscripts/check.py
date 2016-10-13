import os
import os.path
import redist

def fileList(path) :
    #print
    root = "/home/joshutt/football/scripts/mailscripts/maildeposit"
    files = os.listdir("%s/%s"%(root,path))
    for file in files :
        #print file
        newName = "%s/%s" % (path, file)
        fileName = "%s/%s" % (root, newName)
        #print os.stat(newName)
        #print os.path.isdir(fileName)
        #print fileName
        if (os.path.isdir(fileName)) :
            fileList(newName)
        elif (os.path.isfile(fileName)) :
            #print newName
            #print "File"
            success = redist.moveMail(newName)
            if (success) :
                os.remove(fileName)


#print path
fileList("")
