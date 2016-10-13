import os
import os.path
import redist

def fileList(path) :
    #print
    root = "/home/joshutt/football/scripts/mailtest/maildeposit"
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
            redist.moveMail(newName)
            os.remove(fileName)


path = "/home/joshutt/football/scripts/mailtest/maildeposit"
print path
fileList("")
