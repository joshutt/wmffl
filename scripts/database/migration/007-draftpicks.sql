alter table draftpicks
    add id int first;

alter table draftpicks
    modify id int;

create temporary table tmp_draftid
(
    season  int,
    round   int,
    pick    int,
    orgTeam int,
    num     int
);

insert into tmp_draftid
select season,
       round,
       pick,
       orgTeam,
       row_number() over (ORDER BY season, round, pick) as 'rownum'
from draftpicks
order by season, round, pick;

update draftpicks dp
    join tmp_draftid td on dp.season = td.season and dp.round = td.round and dp.pick = td.pick
set dp.id = td.num
where dp.pick is not null;

update draftpicks dp
    join tmp_draftid td on dp.season = td.season and dp.round = td.round and dp.orgTeam = td.orgTeam
set dp.id=td.num
where dp.pick is null;

drop table tmp_draftid;

alter table draftpicks
    add constraint draftpicks_pk
        primary key (id);

alter table draftpicks
    modify id int auto_increment;

alter table draftpicks
    auto_increment = 1;