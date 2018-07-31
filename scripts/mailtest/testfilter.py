import sys
from time import time
from whrandom import randint
from socket import gethostname

print "%d.JPU%d.%s" % (int(time()), randint(1, 10000000), gethostname()) 
