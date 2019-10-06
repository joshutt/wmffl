#!/bin/bash

`cd /home/wmffl/public_html/cgi-bin`
`python /home/wmffl/public_html/cgi-bin/getzipfile.py > /home/wmffl/public_html/activate/update.inc`
`perl /home/wmffl/public_html/cgi-bin/unzip.pl`
`python /home/wmffl/public_html/cgi-bin/newcrack.py` 
`mysql -u wmffl_user -pwmaccess wmffl_football < /home/wmffl/public_html/cgi-bin/out.sql`
#`date -r myzip.zip +%B%e,\ %Y\ at\ %l:%M\ %p > /home/wmffl/public_html/activate/update.inc`
`php /home/wmffl/public_html/admin/updatescores.php`
