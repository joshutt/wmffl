UPDATE offer SET status='Expired'
WHERE date < DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
AND status='Pending';

