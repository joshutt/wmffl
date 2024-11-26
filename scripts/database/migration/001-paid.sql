alter table paid
    add id int first;

update paid p1
    JOIN (select *,
                 ROW_NUMBER() over (PARTITION BY teamid order by season asc) as 'x',
                 ROW_NUMBER() over (PARTITION BY season order by teamid asc) as 'y',
                 (ROW_NUMBER() over (PARTITION BY teamid order by season asc) - 1) * 12 +
                 ROW_NUMBER() over (PARTITION BY season order by teamid asc) as 'z'
          from paid) p2 on p1.teamid = p2.teamid and p1.season = p2.season
SET p1.id=p2.z
WHERE p1.teamid > 0;

alter table paid
    add constraint paid_pk
        primary key (id);

alter table paid
    modify id int auto_increment;